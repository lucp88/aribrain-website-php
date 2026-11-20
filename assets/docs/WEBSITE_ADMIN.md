# ARIbrain Website — Admin & Setup Guide

Documentation for deploying, managing, and maintaining the ARIbrain website.

---

## Table of Contents

1. [Hosting & Infrastructure](#hosting--infrastructure)
2. [Deployment](#deployment)
3. [Database](#database)
4. [User & Permission System](#user--permission-system)
5. [Content Management](#content-management)
6. [Email Service](#email-service)
7. [Access Gate (Under Construction Mode)](#access-gate)
8. [Admin Panel](#admin-panel)
9. [Troubleshooting](#troubleshooting)

---

## Hosting & Infrastructure

| Component | Details |
|-----------|---------|
| **Host** | Vimexx (shared hosting) |
| **Domain** | aribrain.eu |
| **Stack** | PHP + MySQL |
| **File transfer** | FileZilla (FTP) |
| **DB management** | phpMyAdmin (via Vimexx control panel) |
| **Local dev** | `php -S localhost:8000` |

### FTP Access

FTP credentials are available in the Vimexx control panel. Files are uploaded to the `public_html` directory (or `domains/aribrain.eu/public_html`).

---

## Deployment

### First-time Setup

1. **Create database** in Vimexx control panel (MySQL-beheer)
2. **Import schema** — upload `sql/schema.sql` via phpMyAdmin
3. **Update config** — set production DB credentials in `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_db_name');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('SITE_URL', 'https://aribrain.eu');
   ```
4. **Upload files** via FileZilla to `public_html`
5. **Create first admin** — visit `https://aribrain.eu/index.php?page=admin/login` (setup form appears when no admin exists)

### Updating the Site

1. Make changes locally
2. Test with `php -S localhost:8000`
3. Upload changed files via FileZilla
4. If database schema changed: run the new SQL via phpMyAdmin

### Important: Do NOT Upload

- `setup-database.php` (security risk)
- `.git/` directory
- `CLAUDE.md` (internal documentation)

### Exporting Local Data to Production

```bash
mysqldump -u admin_peek -p'yourpassword' -h 127.0.0.1 --set-gtid-purged=OFF --no-tablespaces aribrain_db 2>/dev/null > aribrain_dump.sql
```

Then import `aribrain_dump.sql` via phpMyAdmin on Vimexx. The `2>/dev/null` suppresses warnings that would otherwise corrupt the SQL file.

---

## Database

### Connection

The site uses PDO with a singleton pattern (`config/database.php`). All queries use prepared statements to prevent SQL injection.

### Tables

| Table | Purpose |
|-------|---------|
| `admins` | Original admin accounts (full access) |
| `users` | Invited users with roles (admin/editor/viewer) |
| `publications` | Papers and talks displayed on the publications page |
| `page_sections` | Content sections for the ARIbrain product page |
| `use_cases` | Use case entries for the Use Cases page |
| `team_members` | Team members for the About page |
| `news_posts` | News/blog posts for the News page |
| `forum_questions` | Community forum questions |
| `forum_replies` | Replies to forum questions |
| `forum_tags` | Tags for categorizing forum questions |
| `forum_question_tags` | Many-to-many link between questions and tags |
| `forum_categories` | Forum categories (not yet active) |
| `forum_posts` | Forum posts (legacy, not yet active) |

### Schema Updates

**Always** update `sql/schema.sql` when making database changes. This file is the source of truth for production deployment.

### Compatibility Note

Vimexx runs an older MySQL version. Avoid using `utf8mb4_0900_ai_ci` collation — use `utf8mb4_general_ci` instead.

---

## User & Permission System

### Two Account Systems

The site has two account tables that work together:

1. **`admins` table** — Original admin accounts created via the setup form or directly in the database. Always have full admin privileges.
2. **`users` table** — Invited accounts with role-based permissions, managed through the admin panel.

### Roles & Permissions

| Permission | Admin | Editor | Viewer |
|------------|:-----:|:------:|:------:|
| View website (through gate) | Yes | Yes | Yes |
| Access admin panel | Yes | Yes | No |
| Manage publications | Yes | Yes | No |
| Manage news posts | Yes | Yes | No |
| Edit pages (ARIbrain, Use Cases, Team) | Yes | Yes | No |
| Moderate forum | Yes | Yes | No |
| Invite & manage users | Yes | No | No |
| Delete users | Yes | No | No |

### How Roles Work

- **Admin** — Full access to everything including user management. Can be from either the `admins` table or `users` table with `role = 'admin'`.
- **Editor** — Can log into the admin panel and manage content (publications, news, pages, forum) but cannot see or access the Users section. Cannot invite new users.
- **Viewer** — Can only view the website through the access gate. No admin panel access.

### User Invitation Flow

1. An admin goes to **Admin Panel → Users**
2. Fills in name, email, and role → clicks **Send Invitation**
3. A unique invitation token is generated (valid for 7 days)
4. An HTML email is sent with a "Set Your Password" button
5. The invited user clicks the link and sets their password
6. Account status changes from `invited` → `active`
7. User can now log in through the gate or (if admin/editor) the admin panel

### Login URLs

| URL | Who can log in |
|-----|---------------|
| `https://aribrain.eu/` (gate) | All active users and admins |
| `https://aribrain.eu/index.php?page=admin/login` | Admins and editors only |
| `https://aribrain.eu/index.php?page=user/login` | All active users |

### Managing Users

From the admin panel Users page, admins can:
- **Invite** new users (sends email)
- **Resend** invitation (generates new token, extends expiry)
- **Activate/Deactivate** existing users
- **Delete** users permanently

---

## Content Management

All editable pages are managed through the admin panel. Both admins and editors can create, edit, and delete content.

### Admin Sidebar Navigation

| Item | URL | Description |
|------|-----|-------------|
| Dashboard | `?page=admin/dashboard` | Overview with links to all sections |
| Publications | `?page=admin/publications` | Manage papers and talks |
| News | `?page=admin/news` | Create and publish news posts |
| Pages | `?page=admin/pages` | Edit ARIbrain, Use Cases, and Team content |
| Forum | `?page=admin/forum` | Moderate questions and replies |
| Users | `?page=admin/users` | Manage users (admin only) |

The sidebar is defined once in `includes/admin/sidebar.php` and shared across all admin pages.

### News Posts (`admin/news`)

Blog-style posts displayed on the News page.

| Field | Required | Notes |
|-------|:--------:|-------|
| Title | Yes | Post headline |
| Excerpt | No | Short summary shown on listing |
| Content | Yes | Full post body, HTML supported |
| Author | No | Defaults to current admin username |
| Publish Date | No | When the post was/will be published |
| Published | No | Checkbox — only published posts appear on the website |
| Featured | No | Checkbox — featured posts get visual emphasis |

**Frontend behavior:** Only published posts are shown, ordered by publish date (newest first). Featured posts display with a highlighted border and badge.

### Pages (`admin/pages`)

The Pages section uses a tab interface to manage three different types of page content:

#### ARIbrain Tab (`?tab=product`)

Content sections for the "What is ARIbrain?" product page. Each section becomes a block on the page.

| Field | Required | Notes |
|-------|:--------:|-------|
| Section Title | Yes | e.g. "Key Features", "How It Works" |
| Content | No | HTML supported — use `<p>`, `<ul>`, `<strong>` etc. |
| Sort Order | No | Lower numbers appear first (default: 0) |
| Active | No | Checkbox — inactive sections are hidden from the website |

**Frontend behavior:** Sections display in sort order, alternating between white and grey backgrounds.

#### Use Cases Tab (`?tab=use-cases`)

Individual use case entries shown as cards on the Use Cases page.

| Field | Required | Notes |
|-------|:--------:|-------|
| Title | Yes | Use case name |
| Summary | No | Short description shown on the card |
| Content | No | Detailed content, HTML supported |
| Image URL | No | Path to image, e.g. `assets/images/example.png` |
| Sort Order | No | Lower numbers appear first |
| Featured | No | Featured use cases get visual emphasis |
| Active | No | Inactive entries are hidden |

**Frontend behavior:** Use cases display in a 2-column grid. Featured entries have a green border. Images appear above the card content.

#### Team Tab (`?tab=team`)

Team members displayed on the About page.

| Field | Required | Notes |
|-------|:--------:|-------|
| Name | Yes | Full name |
| Role / Position | Yes | e.g. "Principal Investigator", "PhD Researcher" |
| Bio | No | Short biography |
| Email | No | Creates a mailto link on the card |
| Website URL | No | Personal/university page link |
| Photo URL | No | Path to photo, e.g. `assets/images/team/name.jpg` |
| Sort Order | No | Lower numbers appear first |
| Active | No | Inactive members are hidden |

**Frontend behavior:** Team members display in a 3-column grid. If no photo is provided, the member's initial is shown in a green circle. Email and website links appear as icons below the bio.

### HTML Content Tips

The content fields for pages, use cases, and news posts support HTML. Common patterns:

```html
<!-- Paragraph -->
<p>Your text here.</p>

<!-- Bold text -->
<p>This is <strong>important</strong>.</p>

<!-- Bullet list -->
<ul>
    <li>First item</li>
    <li>Second item</li>
</ul>

<!-- Link -->
<p>See our <a href="https://github.com/AriBrain/ari-core">GitHub repository</a>.</p>
```

---

## Email Service

### How It Works

The site uses PHP's built-in `mail()` function, which works on Vimexx shared hosting out of the box. No external email service (SendGrid, Mailgun, etc.) is required.

### Email Configuration

Emails are sent from:
```
From: ARIbrain <noreply@aribrain.eu>
Reply-To: contact@aribrain.org
```

These are configured in:
- `config/config.php` — `CONTACT_EMAIL` constant
- `pages/admin/users.php` — `sendInvitationEmail()` function (From header)

### Invitation Email

The invitation email is an HTML email with:
- ARIbrain branding (dark header with green accent)
- Personalized greeting with the user's name
- Role assignment displayed
- "Set Your Password" call-to-action button
- Fallback plain-text link
- 7-day expiry notice
- Leiden University footer

### Email Deliverability Tips

On shared hosting, emails may end up in spam. To improve deliverability:
- Ensure the domain has proper **SPF records** (check Vimexx DNS settings)
- Consider adding a **DKIM record** via Vimexx
- The `From` address domain (`aribrain.eu`) should match the hosting domain

### Testing Emails

When testing locally, `mail()` will likely fail (no mail server). The system handles this gracefully — it creates the user and shows a message that the email could not be sent, with an option to resend from the admin panel.

---

## Access Gate

### Under Construction Mode

The website is currently behind a login gate. All public pages redirect to a login screen that shows:
- "Under Construction" badge
- Message explaining the site is in development
- Login form for registered users

### Who Can Pass the Gate

- Users from the `users` table with `status = 'active'`
- Admins from the `admins` table with `is_active = 1`

### Pages NOT Behind the Gate

These pages bypass the gate and are always accessible:
- `index.php?page=admin/login` — Admin login
- `index.php?page=user/login` — User login
- `index.php?page=set-password&token=...` — Password setup from invitation

### Removing the Gate

When the site is ready to go public, remove the gate block in `index.php`:

```php
// Remove or comment out this block:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isUser = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
if (!$isAdmin && !$isUser) {
    require_once __DIR__ . '/pages/gate.php';
    exit;
}
```

---

## Admin Panel

### Access

URL: `https://aribrain.eu/index.php?page=admin/login`

### Sections

| Section | URL | Description | Permissions |
|---------|-----|-------------|-------------|
| Dashboard | `?page=admin/dashboard` | Overview with links to all sections | Admin, Editor |
| Publications | `?page=admin/publications` | Add/edit/delete papers and talks | Admin, Editor |
| News | `?page=admin/news` | Create and publish news posts | Admin, Editor |
| Pages | `?page=admin/pages` | Edit ARIbrain, Use Cases, Team content | Admin, Editor |
| Forum | `?page=admin/forum` | Moderate questions, manage replies and tags | Admin, Editor |
| Users | `?page=admin/users` | Invite and manage users | Admin only |

### Shared Sidebar

The admin sidebar is defined in `includes/admin/sidebar.php` and included by all admin pages via:
```php
<?php $activePage = 'dashboard'; require __DIR__ . '/../../includes/admin/sidebar.php'; ?>
```

The `$activePage` variable controls which item is highlighted. The Users link is automatically hidden for editors.

### Adding a New Admin Page

1. Create `pages/admin/newpage.php`
2. Add `'admin/newpage'` to the `$adminPages` array in `index.php`
3. Add the sidebar link in `includes/admin/sidebar.php`
4. Optionally add a card to the dashboard

---

## Troubleshooting

### Common Issues

**"Empty result set" when importing SQL**
This is normal for `CREATE TABLE` and `INSERT` statements — it means the query succeeded.

**Email not received**
- Check the spam folder
- Verify the user's email address is correct
- Resend the invitation from the admin panel
- Check Vimexx SPF/DKIM settings

**`utf8mb4_0900_ai_ci` error on import**
Replace all occurrences with `utf8mb4_general_ci`:
```bash
sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_general_ci/g' your_dump.sql
```

**Gate login not working after deploying**
Make sure `config/config.php` has the correct production database credentials.

**User can't log into admin panel**
Only users with `role = 'admin'` or `role = 'editor'` can access the admin panel. Viewers can only view the website.

**Invitation link expired**
Tokens expire after 7 days. An admin can click "Resend" on the Users page to generate a new token.

**Pages show "Content Coming Soon" even after adding content**
Ensure the entries have the "Active" checkbox checked (for pages/use cases/team) or "Published" checked (for news).

---

*Last updated: March 2026*
