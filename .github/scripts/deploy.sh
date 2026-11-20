#!/usr/bin/env bash
# Helper for .github/workflows/deploy.yml
#
#   deploy.sh upload-runner     Upload deploy/migrate.php + sql/migrations/*.sql to <server-dir>/_deploy/
#   deploy.sh remove-runner     Delete <server-dir>/_deploy/ again (and verify it is gone)
#   deploy.sh runner <action>   POST <action> to the runner, print its JSON response
#   deploy.sh sync-code         Upload new/changed site files, delete files removed from the repo
#
# Environment:
#   FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, FTP_SERVER_DIR (e.g. domains/aribrain.eu/public_html/)
#   FTP_PROTOCOL (ftps|ftp, default ftps), FTP_PORT (default 21)
#   FTP_EXTRA_CA_CERTS (optional) PEM file with intermediates the FTP server doesn't send;
#                      added on top of the system trust store (verification stays on)
#   SITE_URL (e.g. https://aribrain.eu), DEPLOY_TOKEN
#
# Credentials are passed to curl through config files on stdin/temp files,
# never on the command line.
set -euo pipefail

cmd="${1:-}"

ftp_setup() {
    : "${FTP_SERVER:?FTP_SERVER is not set}"
    : "${FTP_USERNAME:?FTP_USERNAME is not set}"
    : "${FTP_PASSWORD:?FTP_PASSWORD is not set}"
    : "${FTP_SERVER_DIR:?FTP_SERVER_DIR is not set}"

    REMOTE_DIR="${FTP_SERVER_DIR%/}/_deploy"
    BASE_URL="ftp://${FTP_SERVER}:${FTP_PORT:-21}"

    CURL_CFG="$(mktemp)"
    CA_BUNDLE=""
    trap 'rm -f "$CURL_CFG" ${CA_BUNDLE:+"$CA_BUNDLE"}' EXIT
    chmod 600 "$CURL_CFG"
    # Escape backslashes and double quotes for curl's config file format.
    local user pass
    user=$(printf '%s' "$FTP_USERNAME" | sed 's/\\/\\\\/g; s/"/\\"/g')
    pass=$(printf '%s' "$FTP_PASSWORD" | sed 's/\\/\\\\/g; s/"/\\"/g')
    printf 'user = "%s:%s"\n' "$user" "$pass" > "$CURL_CFG"

    CURL=(curl --silent --show-error --fail --config "$CURL_CFG" --connect-timeout 30)
    if [ "${FTP_PROTOCOL:-ftps}" = "ftps" ]; then
        CURL+=(--ssl-reqd)
        if [ -n "${FTP_EXTRA_CA_CERTS:-}" ]; then
            # --cacert replaces the default store, so combine system roots + extra intermediates.
            local system_bundle=""
            for f in /etc/ssl/certs/ca-certificates.crt /etc/pki/tls/certs/ca-bundle.crt /etc/ssl/cert.pem; do
                if [ -f "$f" ]; then system_bundle="$f"; break; fi
            done
            CA_BUNDLE="$(mktemp)"
            cat ${system_bundle:+"$system_bundle"} "$FTP_EXTRA_CA_CERTS" > "$CA_BUNDLE"
            CURL+=(--cacert "$CA_BUNDLE")
        fi
    fi
}

# On TLS failures, show which certificates the FTP server presents (public info only).
tls_diagnose() {
    echo "::group::Certificate chain presented by ${FTP_SERVER}:${FTP_PORT:-21} (FTP over TLS)"
    if command -v timeout > /dev/null; then
        timeout 20 openssl s_client -starttls ftp -connect "${FTP_SERVER}:${FTP_PORT:-21}" \
            -servername "$FTP_SERVER" -showcerts < /dev/null 2> /dev/null |
            grep -E '^ *[0-9]+ s:|^ *i:|Verify return code' || echo "(could not read certificate chain)"
    fi
    echo "::endgroup::"
}

ftp_curl() {
    local rc=0
    "${CURL[@]}" "$@" || rc=$?
    if [ "$rc" -eq 60 ] || [ "$rc" -eq 35 ]; then
        tls_diagnose
    fi
    return "$rc"
}

upload_runner() {
    ftp_setup
    echo "Uploading migration runner to ${REMOTE_DIR}/"
    ftp_curl --ftp-create-dirs -T deploy/migrate.php "${BASE_URL}/${REMOTE_DIR}/migrate.php"
    shopt -s nullglob
    for f in sql/migrations/*.sql; do
        ftp_curl --ftp-create-dirs -T "$f" "${BASE_URL}/${REMOTE_DIR}/migrations/$(basename "$f")"
        echo "  migrations/$(basename "$f")"
    done
}

remove_runner() {
    ftp_setup
    echo "Removing ${REMOTE_DIR}/"
    local quote=() name
    # Delete whatever is in migrations/ on the server (not just what's in the repo).
    while IFS= read -r name; do
        name="${name%$'\r'}"
        [ -n "$name" ] && [ "$name" != "." ] && [ "$name" != ".." ] && quote+=(-Q "*DELE ${REMOTE_DIR}/migrations/${name}")
    done < <("${CURL[@]}" --list-only "${BASE_URL}/${REMOTE_DIR}/migrations/" 2>/dev/null || true)
    quote+=(-Q "*DELE ${REMOTE_DIR}/migrate.php" -Q "*RMD ${REMOTE_DIR}/migrations" -Q "*RMD ${REMOTE_DIR}")

    # Quote commands run before listing the home directory; '*' = continue on failure.
    ftp_curl "${quote[@]}" --list-only "${BASE_URL}/" > /dev/null

    if "${CURL[@]}" --list-only "${BASE_URL}/${REMOTE_DIR}/" > /dev/null 2>&1; then
        echo "::error::${REMOTE_DIR}/ still exists on the server. Delete it manually via FTP."
        exit 1
    fi
    echo "Runner removed."
}

# ---------------------------------------------------------------------------
# Code sync
#
# Deploys the files tracked in git (minus the exclusions below). A manifest of
# "sha256  path" lines is kept on the server NEXT TO the web root (not
# downloadable). Only files whose hash changed are uploaded, and only files
# listed in the previous manifest can be deleted, so files that exist only on
# the server (config.php, uploaded team photos, ...) are never touched.
# ---------------------------------------------------------------------------

# Never uploaded, never deleted.
is_excluded() {
    case "$1" in
        .github/*|.claude/*|deploy/*|sql/*|*.sql) return 0 ;;
        config/config.php|setup-database.php|CLAUDE.md|.maintenance) return 0 ;;
        assets/images/team/*) return 0 ;;
        .git*|*/.git*|.DS_Store|*/.DS_Store) return 0 ;;
    esac
    return 1
}

sha256_of() {
    if command -v sha256sum > /dev/null; then sha256sum "$1" | cut -d' ' -f1
    else shasum -a 256 "$1" | cut -d' ' -f1; fi
}

sync_code() {
    ftp_setup
    local web_dir="${FTP_SERVER_DIR%/}"          # e.g. public_html
    local parent=""
    [[ "$web_dir" == */* ]] && parent="${web_dir%/*}/"
    local manifest_path="${parent}.aribrain-deploy-manifest"
    [ "$web_dir" = "." ] && manifest_path=".aribrain-deploy-manifest"

    local work
    work="$(mktemp -d)"
    local local_manifest="$work/local" remote_manifest="$work/remote"

    # 1. Local manifest from tracked files
    git ls-files -z | while IFS= read -r -d '' path; do
        is_excluded "$path" && continue
        [ -f "$path" ] || continue
        printf '%s  %s\n' "$(sha256_of "$path")" "$path"
    done | sort -k2 > "$local_manifest"
    cut -c67- "$local_manifest" > "$work/local_paths"

    # 2. Previous manifest from the server (missing on the first deploy)
    if ! "${CURL[@]}" -o "$remote_manifest" "${BASE_URL}/${manifest_path}" 2> /dev/null; then
        : > "$remote_manifest"
        echo "No previous deploy manifest on the server: uploading all files."
    fi

    # 3. Work out what changed
    local upload=() delete=() path
    while IFS= read -r line; do
        grep -qxF -- "$line" "$remote_manifest" || upload+=("${line:66}")
    done < "$local_manifest"
    while IFS= read -r line; do
        path="${line:66}"
        [ -n "$path" ] || continue
        is_excluded "$path" && continue          # never delete excluded paths, whatever the manifest says
        grep -qxF -- "$path" "$work/local_paths" || delete+=("$path")
    done < "$remote_manifest"

    echo "Files: $(wc -l < "$local_manifest" | tr -d ' ') tracked, ${#upload[@]} to upload, ${#delete[@]} to delete"

    # 4. Upload in batches; curl reuses the control connection within one call
    local args=() n=0 total=0
    for path in ${upload[@]+"${upload[@]}"}; do
        args+=(-T "$path" "${BASE_URL}/${web_dir}/${path}")
        n=$((n + 1)); total=$((total + 1))
        echo "  upload $path"
        if [ "$n" -ge 25 ]; then
            ftp_curl --ftp-create-dirs "${args[@]}"
            args=(); n=0
        fi
    done
    if [ "$n" -gt 0 ]; then
        ftp_curl --ftp-create-dirs "${args[@]}"
    fi

    # 5. Delete files that were removed from the repo
    if [ "${#delete[@]}" -gt 0 ]; then
        local quote=()
        for path in ${delete[@]+"${delete[@]}"}; do
            echo "  delete $path"
            quote+=(-Q "*DELE ${web_dir}/${path}")
        done
        ftp_curl "${quote[@]}" --list-only "${BASE_URL}/" > /dev/null
    fi

    # 6. Save the new manifest last, so an interrupted run retries next time
    ftp_curl -T "$local_manifest" "${BASE_URL}/${manifest_path}"
    rm -rf "$work"
    echo "Sync complete: $total uploaded, ${#delete[@]} deleted."
}

call_runner() {
    : "${SITE_URL:?SITE_URL is not set}"
    : "${DEPLOY_TOKEN:?DEPLOY_TOKEN is not set}"
    local action="$1"
    # Token goes in via a config file on stdin, so it never appears in the process list.
    printf 'header = "X-Deploy-Token: %s"\n' "$DEPLOY_TOKEN" |
        curl --silent --show-error --fail-with-body --max-time 300 --config - \
            --data "action=${action}" "${SITE_URL%/}/_deploy/migrate.php"
}

case "$cmd" in
    upload-runner) upload_runner ;;
    remove-runner) remove_runner ;;
    runner) call_runner "${2:?usage: deploy.sh runner <status|migrate|maintenance_off>}" ;;
    sync-code) sync_code ;;
    *) echo "usage: deploy.sh upload-runner|remove-runner|runner <action>|sync-code" >&2; exit 2 ;;
esac
