
## Product catalog and inventory

The shop page (`items.html`) loads the full product catalog from `productos`; the home page links to it from the featured products section. Basic product creation, editing, and deletion pages are available from the admin dashboard under **Products**. Deletion is currently physical, and stock can be overwritten from the edit form; there is not yet a stock-movement workflow. To grant an existing account access, set its `users.role` value to `admin` in MySQL, then sign in again. Fresh database imports include the `role` column; for an existing database, run:

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
| 4 | Quick stock entries and exits | Partial | Stock can be overwritten in the edit form, but there are no separate entry/exit operations or movement records. |
| 5 | Inventory movement history | Not implemented | There is no inventory movements table or history screen/API. |
| 6 | Alerts for minimum or zero stock | Partial | `adminstuff/product_list.php` highlights stock at or below a fixed value of 5; there is no per-product minimum or alerts view/API. |
| 7 | Filter by name, category, and inventory state | Partial | Name search exists in `testing.html` through `api/products/search.php`. Category and stock-state filters are absent, and the search is not part of the admin inventory list. |
| 8 | Inventory value, out-of-stock, and low-stock indicators | Not implemented | The admin dashboard has no inventory statistics. |

### Recommended starting point

Start with the **inventory database migration and rules** before implementing more screens or endpoints. The reference scripts in `db/tables/` add a minimum-stock field and active/inactive state to `productos`, then create a `movimientos_inventario` table linked to products. The movement script records each product's current stock as an opening-balance adjustment. This foundation is needed for reliable stock adjustments, history, alerts, filtering, and metrics.

After the schema is in place, implement stock entry/exit operations and their movement records first; then build the movement history, soft delete, alerts, filters, and dashboard indicators on that consistent data.

## Database table scripts

`db/tables/` contains one SQL reference/migration file per inventory table. Back up the database first, select the existing `lovecrafty` database, and run these scripts in order:

1. `db/tables/productos.sql`
2. `db/tables/movimientos_inventario.sql`

Run `productos.sql` only once: it converts existing `NULL` stock values to `0`, makes `stock` non-nullable, and adds `stock_minimo` (default `5`) and `activo` (default active). The movements script creates the history table and inserts one opening adjustment per product with stock above zero; it guards against duplicate opening rows if rerun. These scripts only change the database schema/data; the PHP endpoints and admin screens still need to be implemented or connected to use the new fields and table.