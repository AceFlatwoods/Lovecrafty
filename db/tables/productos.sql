-- Run once against the existing lovecrafty database.
USE lovecrafty;

-- Existing NULL stock values become zero before stock is made non-nullable.
UPDATE productos
SET stock = 0
WHERE stock IS NULL;

ALTER TABLE productos
    MODIFY COLUMN stock INT(11) NOT NULL DEFAULT 0,
    ADD COLUMN stock_minimo INT(11) UNSIGNED NOT NULL DEFAULT 5 AFTER stock,
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER stock_minimo;