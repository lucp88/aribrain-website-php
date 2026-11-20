#!/usr/bin/env bash
# Helper for .github/workflows/deploy.yml
#
#   deploy.sh upload-runner     Upload deploy/migrate.php + sql/migrations/*.sql to <server-dir>/_deploy/
#   deploy.sh remove-runner     Delete <server-dir>/_deploy/ again (and verify it is gone)
#   deploy.sh runner <action>   POST <action> to the runner, print its JSON response
#
# Environment:
#   FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, FTP_SERVER_DIR (e.g. domains/aribrain.eu/public_html/)
#   FTP_PROTOCOL (ftps|ftp, default ftps), FTP_PORT (default 21)
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
    trap 'rm -f "$CURL_CFG"' EXIT
    chmod 600 "$CURL_CFG"
    # Escape backslashes and double quotes for curl's config file format.
    local user pass
    user=$(printf '%s' "$FTP_USERNAME" | sed 's/\\/\\\\/g; s/"/\\"/g')
    pass=$(printf '%s' "$FTP_PASSWORD" | sed 's/\\/\\\\/g; s/"/\\"/g')
    printf 'user = "%s:%s"\n' "$user" "$pass" > "$CURL_CFG"

    CURL=(curl --silent --show-error --fail --config "$CURL_CFG" --connect-timeout 30)
    if [ "${FTP_PROTOCOL:-ftps}" = "ftps" ]; then
        CURL+=(--ssl-reqd)
    fi
}

upload_runner() {
    ftp_setup
    echo "Uploading migration runner to ${REMOTE_DIR}/"
    "${CURL[@]}" --ftp-create-dirs -T deploy/migrate.php "${BASE_URL}/${REMOTE_DIR}/migrate.php"
    shopt -s nullglob
    for f in sql/migrations/*.sql; do
        "${CURL[@]}" --ftp-create-dirs -T "$f" "${BASE_URL}/${REMOTE_DIR}/migrations/$(basename "$f")"
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
    "${CURL[@]}" "${quote[@]}" --list-only "${BASE_URL}/" > /dev/null

    if "${CURL[@]}" --list-only "${BASE_URL}/${REMOTE_DIR}/" > /dev/null 2>&1; then
        echo "::error::${REMOTE_DIR}/ still exists on the server. Delete it manually via FTP."
        exit 1
    fi
    echo "Runner removed."
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
    *) echo "usage: deploy.sh upload-runner|remove-runner|runner <action>" >&2; exit 2 ;;
esac
