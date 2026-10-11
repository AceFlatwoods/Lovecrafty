
## Product catalog and inventory

The shop page (`items.html`) loads the full product catalog from `productos`; the home page links to it from the featured products section. Basic product creation, editing, and deletion pages are available from the admin dashboard under **Products**. Deletion is currently physical, and stock can still be overwritten from the edit form. Checkout now checks and deducts stock transactionally, recording each sale in `movimientos_inventario`. To grant an existing account access, set its `users.role` value to `admin` in MySQL, then sign in again. Fresh database imports include the `role` column; for an existing database, run:

```sql
ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';
UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
```

The local database connection uses MySQL on port `3306` (the default XAMPP port); update `chocolaterie-backend/config.php` if your server uses another port.

## Inventory feature status

Statuses below reflect the current application and SQL dump. `Partial` means a related basic capability exists, but it does not meet the full inventory requirement.

| # | Feature | Status | Current implementation / gap |
|---|---|---|---|
| 1 | Register products with category, price, and image | Partial | `adminstuff/add_product.php` saves these fields, but the image is an optional URL/path rather than a validated upload. |
| 2 | Edit product information | Implemented (basic) | `adminstuff/edit_product.php` updates product fields, including stock directly. |
| 3 | Soft-delete products | Not implemented | `adminstuff/delete_product.php` physically deletes products when allowed; there is no active/inactive column. |
| 4 | Quick stock entries and exits | Partial | Checkout validates available stock and records sale exits. There is not yet an admin workflow for receiving stock or making manual adjustments. |
| 5 | Inventory movement history | Partial | Opening balances and checkout sales are stored in `movimientos_inventario`; a movement history screen/API is still missing. |
| 6 | Alerts for minimum or zero stock | Implemented (basic) | Each product has an editable minimum. `adminstuff/product_list.php` shows low/out-of-stock status and counts; `api/products/get_all.php` returns `estado_stock`. There is no dedicated alerts page/API. |
| 7 | Filter by name, category, and inventory state | Partial | Name search exists in `testing.html` through `api/products/search.php`. Category and stock-state filters are absent, and the search is not part of the admin inventory list. |
| 8 | Inventory value, out-of-stock, and low-stock indicators | Not implemented | The admin dashboard has no inventory statistics. |

### Recommended starting point

The inventory database migration is in place. Next, add an admin workflow for stock entries and adjustments, followed by a movement history view/API. The existing `db/tables/` scripts document the schema migration and opening-balance records.

Soft delete, category/state filters, total inventory value, and a dedicated alerts view/API remain follow-up work.

## Database table scripts

`db/tables/` contains one SQL reference/migration file per inventory table. Back up the database first, select the existing `lovecrafty` database, and run these scripts in order:

1. `db/tables/productos.sql`
2. `db/tables/movimientos_inventario.sql`

Run `productos.sql` only once: it converts existing `NULL` stock values to `0`, makes `stock` non-nullable, and adds `stock_minimo` (default `5`) and `activo` (default active). The movements script creates the history table and inserts one opening adjustment per product with stock above zero; it guards against duplicate opening rows if rerun. These scripts only change the database schema/data; the PHP endpoints and admin screens still need to be implemented or connected to use the new fields and table.