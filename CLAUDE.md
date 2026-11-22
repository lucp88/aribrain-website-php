# ARIbrain Website

PHP website for the ARIbrain neuroimaging desktop application (PyQt). Hosted on Vimexx (shared hosting, FTP deploy). Plain PHP, no framework, no Composer.

Full admin/deployment guide: `assets/docs/WEBSITE_ADMIN.md` (keep it in sync with changes here).

## Project Structure

```
├── index.php                 # Router (?page=), gate, logout
├── config/
│   ├── config.php            # Site settings, nav items, DB credentials (git-ignored)
│   ├── config.example.php    # Template for config.php
│   └── database.php          # PDO singleton
├── includes/
│   ├── bootstrap.php         # Session + CSRF enforcement + Auth. Every page that uses DB/session requires this
│   ├── security.php          # start_secure_session(), csrf_token/field/verify/enforce()
│   ├── auth.php              # Auth class (login, roles, tokens) — single `users` table
│   ├── mail.php              # send_branded_email(), send_invitation_email(), send_password_reset_email()
│   ├── sanitize.php          # sanitize_html() for CMS HTML output
│   ├── docs-renderer.php     # USER_GUIDE.md -> Docs page (uses vendor/Parsedown.php)
│   ├── maintenance.php       # 503 page while /.maintenance exists (checked in bootstrap.php)
│   ├── auth-layout-start.php / auth-layout-end.php   # Shared layout for login/password pages
│   ├── header.php / footer.php                       # Public site layout
│   └── admin/
│       ├── layout-start.php / layout-end.php         # Shared admin layout (head, sidebar, header)
│       └── sidebar.php
├── pages/
│   ├── gate.php              # "Under construction" login shown to logged-out visitors
│   ├── set-password.php      # Invitation AND password-reset links land here
│   ├── forgot-password.php
│   ├── user/login.php
│   ├── home, product, use-cases, install, docs, publications, about, blog, forum (.php)
│   └── admin/
│       ├── login.php         # Admin login + first-time setup (only while no admin exists)
│       ├── dashboard.php, publications.php, news.php, pages.php, forum.php
│       └── users.php         # Admin only: invite/resend/activate/delete users
├── sql/
│   ├── schema.sql            # Full schema for fresh installs (KEEP UPDATED)
│   └── migrations/           # NNN_description.sql, run by the deploy pipeline
├── setup-database.php        # One-time DB setup (git-ignored; delete after use)
├── deploy/migrate.php        # Migration runner, only on the server during a deploy (as _deploy/)
├── .github/                  # workflows/deploy.yml + scripts/deploy.sh
├── docs/                     # Internal docs, never deployed (DEPLOYMENT.md/.pdf: deploy process explained, Dutch)
└── assets/
    ├── css/style.css, admin.css
    ├── js/main.js
    ├── docs/USER_GUIDE.md, WEBSITE_ADMIN.md
    └── images/ (team/ = uploaded team photos)
```

## Routing

- Public: `index.php?page=pagename` → header + `pages/pagename.php` + footer. Must be in `$validPages`.
- Standalone (own layout, bypass the gate): admin pages, `user/login`, `set-password`, `forgot-password`. Listed in `$standalonePages` in `index.php`.
- `index.php?page=logout` logs out.
- Gate: while the site is under construction, logged-out visitors get `pages/gate.php` for every public page. Remove the `if (!$auth->isLoggedIn())` block in `index.php` to go public.

## Accounts & Auth

- One table: `users`, roles `admin` (everything) / `editor` (admin panel content, no user management) / `viewer` (site only). Login is by **email**.
- `Auth` (`includes/auth.php`): session stores only `user_id`; `currentUser()` reloads the row each request, so deactivation/role changes apply immediately.
  - Admin pages: `$auth->requireAdminPanel()` (admin + editor) or `$auth->requireAdmin()` (admin only), then `$currentUser = $auth->currentUser()` (`id`, `name`, `email`, `role`).
- Invitation and reset tokens: `issueToken($userId, Auth::INVITATION_TTL | Auth::RESET_TTL)` returns the plain token for the email; only its SHA-256 is stored in `users.token_hash`. Expiry is computed in MySQL (`NOW() + INTERVAL`), not PHP, to avoid timezone drift.
- Content attribution columns (`created_by`, `invited_by`, `forum_replies.admin_id`) all reference `users(id)`.

## Security Rules

- **CSRF:** every `<form method="POST">` must include `<?php echo csrf_field(); ?>`. `bootstrap.php` rejects POSTs without a valid token (also when a page file is requested directly).
- **Output:** escape with `htmlspecialchars()`. CMS fields that allow HTML (news content, product sections, use cases) must be printed through `sanitize_html()`, never raw.
- **SQL:** prepared statements only.
- Never commit `config/config.php` or DB dumps (root `*.sql` is git-ignored).

## Database

### IMPORTANT: Keep sql/schema.sql Updated!
When making database changes:
1. Update `sql/schema.sql` so a fresh install gets the final schema (name constraints explicitly, e.g. `fk_<table>_<column>`)
2. Add a migration `sql/migrations/NNN_description.sql` for the existing production database (plain `;`-separated SQL, no `DELIMITER`). The deploy pipeline runs it after approval.
3. Add the migration name to the `INSERT INTO schema_migrations` at the bottom of `schema.sql`, so fresh installs don't run it
4. Verify: a fresh install from `schema.sql` and a migrated database should end up with identical structure

### Tables
- `users` - All accounts (roles, status `invited`/`active`/`inactive`, token hash)
- `publications` - Papers and talks
- `news_posts` - News page
- `page_sections` - ARIbrain product page sections
- `use_cases` - Use Cases page
- `team_members` - About page (photos in `assets/images/team/`)
- `forum_questions`, `forum_replies`, `forum_tags`, `forum_question_tags` - Community forum

Vimexx runs an older MySQL: use `utf8mb4_general_ci`, not `utf8mb4_0900_ai_ci`.

### Local Development
```bash
mysql -u admin_peek -p -h 127.0.0.1 aribrain_db
```

## Key Patterns

### Adding a New Public Page
1. Create `pages/newpage.php`
2. Add to `$validPages` in `index.php`
3. Add to `$navItems` in `config/config.php` (and `config.example.php`)

### Adding a New Admin Page
1. Create `pages/admin/newpage.php`: require `includes/bootstrap.php`, call `requireAdminPanel()`/`requireAdmin()`, set `$adminTitle` + `$activePage`, wrap content in `includes/admin/layout-start.php` / `layout-end.php`
2. Add `'admin/newpage'` to `$standalonePages` in `index.php`
3. Add the link in `includes/admin/sidebar.php` (and optionally a dashboard card)

### Docs Page (User Guide)
`pages/docs.php` renders `assets/docs/USER_GUIDE.md` (Parsedown 1.8.0 in `includes/vendor/`, safe mode) via `includes/docs-renderer.php`: `##` headings become sections + sidebar links, tables/lists get the `docs-*` classes. To change the guide, edit the Markdown only; it is live after the next deploy.

### Sending Email
Use `send_branded_email()` from `includes/mail.php` (PHP `mail()`, works on Vimexx). Don't copy the HTML template.

### Styling
- CSS variables defined in `:root` in style.css
- Primary color: `#6bcf8e` (pastel green)
- Font: Inter (Google Fonts)
- Responsive breakpoints: 1024px (tablet/mobile menu), 768px (tablet), 480px (phone)

## Run Locally

```bash
php -S localhost:8000
```

## Deployment to Vimexx

GitHub Actions workflow `.github/workflows/deploy.yml`, started manually (Actions → Deploy → Run workflow on `main`). Full setup and failure handling: `assets/docs/WEBSITE_ADMIN.md` → Deployment.

1. **Check migrations**: `.github/scripts/deploy.sh upload-runner` puts `deploy/migrate.php` + `sql/migrations/` in `<server-dir>/_deploy/`; the runner's `status` lists pending migrations
2. **Migrate database** (only if pending): pauses for approval (environment `production`), then the runner sets `/.maintenance`, writes a backup next to the web root and runs pending migrations, recording them in `schema_migrations`
3. **Deploy code**: `deploy.sh sync-code` uploads changed tracked files over FTPS (curl) using a hash manifest stored next to the web root; it only deletes files it deployed itself. Exclusions live in `is_excluded()` in `deploy.sh`. Then `maintenance_off` + smoke test
4. **Clean up**: always removes `_deploy/`

Rules:
- The repo is **public** and so are workflow logs: never print secrets, server paths or database contents. The runner returns only names/status; details go to a log next to the backups.
- Pin third-party actions to a full commit SHA (with a `# vX.Y.Z` comment).
- Don't switch the code upload back to a Node-based FTP action: its client fails on the Vimexx FTPS data connection (ECONNRESET); curl works.
- The Vimexx FTP server omits its intermediate certificates; `.github/certs/ftp-intermediates.pem` supplies them (`FTP_EXTRA_CA_CERTS`). Never disable TLS verification.
- Don't apply migrations by hand in production; the pipeline must record them.
- `config/config.php` on the server holds DB credentials and `DEPLOY_TOKEN` (not in git). Never upload `setup-database.php`, `CLAUDE.md` or SQL dumps.

Test the pipeline locally: run `deploy.sh` against a local FTP server (e.g. `pyftpdlib`) whose home contains `domains/aribrain.eu/public_html/`, served with `php -S`.

## Related
- Main app repo: https://github.com/AriBrain/ari-core
- Developed at Leiden University
