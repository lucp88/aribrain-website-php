# ARIbrain Website

PHP website for the ARIbrain neuroimaging desktop application (PyQt). Hosted on Vimexx.

## Project Structure

```
├── index.php           # Main entry point, handles routing via ?page= parameter
├── config/
│   ├── config.php      # Site settings, nav items, DB credentials
│   └── database.php    # PDO database connection class
├── includes/
│   ├── header.php      # HTML head, navigation, mobile menu
│   ├── footer.php      # Footer links, scripts
│   └── admin/
│       └── auth.php    # Admin authentication class
├── pages/
│   ├── home.php        # Homepage with hero, features, CTA
│   ├── product.php     # ARIbrain app info
│   ├── use-cases.php   # Use cases
│   ├── install.php     # Installation guide
│   ├── docs.php        # Documentation
│   ├── publications.php # Scientific publications & talks (loads from DB)
│   ├── about.php       # Team info
│   ├── blog.php        # News/updates
│   ├── forum.php       # Community forum
│   └── admin/
│       ├── login.php      # Admin login + first-time setup
│       ├── dashboard.php  # Admin dashboard
│       └── publications.php # Manage publications & talks
├── sql/
│   └── schema.sql      # Database schema (KEEP UPDATED!)
├── setup-database.php  # One-time DB setup script (delete after use)
└── assets/
    ├── css/
    │   ├── style.css   # Main styles
    │   └── admin.css   # Admin panel styles
    ├── js/main.js      # Mobile menu, scroll effects
    └── images/         # Logo files, UI screenshot
```

## Database

### IMPORTANT: Keep sql/schema.sql Updated!
When making database changes:
1. Always update `sql/schema.sql` to reflect the final schema
2. This file is used for production deployment on Vimexx
3. Run `setup-database.php` for initial setup, or import `sql/schema.sql` directly

### Current Tables
- `admins` - Website admin accounts
- `publications` - Papers and talks (displayed on publications page)
- `forum_categories` - Forum categories (not yet active)
- `forum_posts` - Forum posts (not yet active)

### Local Development
```bash
mysql -u admin_peek -p -h 127.0.0.1 aribrain_db
```

## Admin Panel

Access: `index.php?page=admin/login`

Features:
- Publications management (add/edit/delete papers and talks)
- Forum management (coming soon)

### Adding New Admin Pages
1. Create `pages/admin/newpage.php`
2. Add to `$adminPages` array in `index.php`
3. Update sidebar nav in dashboard.php and other admin pages

## Key Patterns

### Adding a New Page
1. Create `pages/newpage.php`
2. Add to `$validPages` array in `index.php`
3. Add to `$navItems` array in `config/config.php`

### Routing
- Public: `index.php?page=pagename`
- Admin: `index.php?page=admin/pagename`

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

1. Update `config/config.php` with production DB credentials
2. Import `sql/schema.sql` via phpMyAdmin or MySQL CLI
3. Create first admin account via `index.php?page=admin/login`
4. Delete `setup-database.php` from production server

## Related
- Main app repo: https://github.com/AriBrain/ari-core
- Developed at Leiden University
