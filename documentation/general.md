
## Product catalog and inventory

The shop page (`items.html`) loads the full product catalog from `productos`; the home page links to it from the featured products section. Product creation, editing, and deletion are available from the admin dashboard under **Products**. To grant an existing account access, set its `users.role` value to `admin` in MySQL, then sign in again. Fresh database imports include the `role` column; for an existing database, run:

```sql
ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
```

The local database connection uses MySQL on port `3306` (the default XAMPP port); update `chocolaterie-backend/config.php` if your server uses another port.