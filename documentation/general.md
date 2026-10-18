
## Product catalog and inventory

The shop page (`items.html`) loads active products from `productos`; the home page links to it from the featured products section. Admins can create and edit products and deactivate or reactivate them under **Products**. Deactivation hides a product from the shop without deleting its record. Stock changes are made through the admin stock-adjustment workflow or checkout, and both record inventory movements. To grant an existing account access, set its `users.role` value to `admin` in MySQL, then sign in again. Fresh database imports include the `role` column; for an existing database, run:

```sql
ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
```

The local database connection uses MySQL on port `3306` (the default XAMPP port); update `chocolaterie-backend/config.php` if your server uses another port.

## Inventory feature status

Statuses below reflect the current application and SQL dump. `Partial` means a related basic capability exists, but it does not meet the full inventory requirement.

| # | Feature | Status | Current implementation / gap |
|---|---|---|---|
| 1 | Register products with category, price, and image | Partial | `adminstuff/add_product.php` saves these fields, but the image is an optional URL/path rather than a validated upload. Initial stock above zero is recorded as an opening entry. |
| 2 | Edit product information | Implemented (basic) | `adminstuff/edit_product.php` updates product details; stock is changed separately through the movement-recording workflow. |
| 3 | Soft-delete products | Implemented (basic) | Admins can deactivate/reactivate products from `adminstuff/product_list.php`. Inactive products are hidden from the shop and product search, cannot be added to carts, and prevent checkout if already in a cart. The records and order history remain intact. |
| 4 | Quick stock entries and exits | Implemented (basic) | Admins can add stock or record an exit from `adminstuff/adjust_stock.php`. Each operation locks and updates the product and records its signed quantity, before/after balances, reason, user, and time in one transaction. Checkout also records sale exits. |
| 5 | Inventory movement history | Implemented (basic) | `adminstuff/inventory_history.php` shows recorded movements with date, product, type, signed quantity, before/after stock, reason, and user. Product/type filters and pagination are available. Changes from before movement tracking was enabled cannot be reconstructed. |
| 6 | Alerts for minimum or zero stock | Partial | Each product has an editable minimum. `adminstuff/product_list.php` shows low/out-of-stock status and counts; `api/products/get_all.php` returns `estado_stock`. There is no dedicated alerts page/API or notification. |
| 7 | Filter by name, category, and inventory state | Implemented (basic) | `adminstuff/product_list.php` filters products by name, category, and stock level (available, low, or out). Inactive products remain visible in the admin list so they can be reactivated. |
| 8 | Inventory value, out-of-stock, and low-stock indicators | Implemented (basic) | `adminstuff/product_list.php` shows the total value (`price × stock`) for active products and counts active products with low or zero stock. |

### Recommended starting point

The admin workflow for stock entries/exits, movement-history view, product activation toggle, product filters, and the inventory summary are implemented.

A dedicated alerts view/API remains follow-up work.

## Database table scripts

`db/tables/` contains SQL reference/migration files for the inventory schema. The current `db/lovecraftyNew.sql` dump already includes `stock_minimo`, `activo`, and `movimientos_inventario`, including movement rows. For an older existing database that does not have those fields/table, back it up and apply these scripts in order:

1. `db/tables/productos.sql`
2. `db/tables/movimientos_inventario.sql`

Run `productos.sql` only once: it converts existing `NULL` stock values to `0`, makes `stock` non-nullable, and adds `stock_minimo` (default `5`) and `activo` (default active). The movements script creates the movement table and inserts one opening adjustment per product with stock above zero; it guards against duplicate opening rows if rerun. The application connection in `chocolaterie-backend/config.php` currently selects the database `lovecrafty`; ensure that matches the database name where the dump was imported before testing.