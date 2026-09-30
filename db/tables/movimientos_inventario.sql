-- Run after productos.sql against the existing lovecrafty database.
USE lovecrafty;

CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id_movimiento INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_producto INT(11) NOT NULL,
    id_usuario INT(11) DEFAULT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT(11) NOT NULL COMMENT 'Signed stock delta: positive adds stock, negative removes stock',
    stock_anterior INT(11) NOT NULL,
    stock_nuevo INT(11) NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_movimiento),
    KEY idx_movimientos_producto_fecha (id_producto, fecha),
    KEY idx_movimientos_usuario (id_usuario),
    CONSTRAINT fk_movimientos_producto
        FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimientos_usuario
        FOREIGN KEY (id_usuario) REFERENCES users (id_user)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_movimientos_tipo_cantidad CHECK (
        (tipo = 'entrada' AND cantidad > 0) OR
        (tipo = 'salida' AND cantidad < 0) OR
        (tipo = 'ajuste' AND cantidad <> 0)
    ),
    CONSTRAINT chk_movimientos_saldo CHECK (
        stock_anterior >= 0 AND
        stock_nuevo >= 0 AND
        stock_nuevo = stock_anterior + cantidad
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Preserve each existing product's on-hand stock as its opening adjustment.
-- The NOT EXISTS guard prevents duplicate opening rows if this file is rerun.
INSERT INTO movimientos_inventario
    (id_producto, id_usuario, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
SELECT
    p.id_producto,
    NULL,
    'ajuste',
    p.stock,
    0,
    p.stock,
    'Saldo inicial al habilitar el control de inventario'
FROM productos AS p
WHERE p.stock > 0
  AND NOT EXISTS (
      SELECT 1
      FROM movimientos_inventario AS m
      WHERE m.id_producto = p.id_producto
        AND m.tipo = 'ajuste'
        AND m.motivo = 'Saldo inicial al habilitar el control de inventario'
  );