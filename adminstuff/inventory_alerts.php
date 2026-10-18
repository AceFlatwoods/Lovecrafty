<?php
session_start();
require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.html');
    exit();
}

$alertType = $_GET['type'] ?? '';
if (!is_string($alertType) || !in_array($alertType, ['', 'out', 'low'], true)) {
    $alertType = '';
}

$conditions = ['p.activo = 1', 'p.stock <= p.stock_minimo'];
if ($alertType === 'out') {
    $conditions[] = 'p.stock <= 0';
} elseif ($alertType === 'low') {
    $conditions[] = 'p.stock > 0';
}

$stmt = $conn->query('
    SELECT p.id_producto, p.nombre, p.stock, p.stock_minimo, c.nombre AS categoria
    FROM productos AS p
    LEFT JOIN categorias AS c ON c.id_categoria = p.id_categoria
    WHERE ' . implode(' AND ', $conditions) . '
    ORDER BY CASE WHEN p.stock <= 0 THEN 0 ELSE 1 END, p.stock ASC, p.nombre ASC
');
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$outOfStockCount = 0;
$lowStockCount = 0;
foreach ($alerts as $alert) {
    if ((int)$alert['stock'] <= 0) {
        $outOfStockCount++;
    } else {
        $lowStockCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Alerts</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h1>Inventory Alerts</h1>
    <div class="toolbar">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        <a href="product_list.php" class="btn">Products</a>
        <span><?= count($alerts) ?> product(s) need attention</span>
    </div>

    <section class="inventory-summary" aria-label="Inventory alert summary">
        <div class="inventory-summary-card">
            <span class="inventory-summary-label">Out of stock</span>
            <strong><?= $outOfStockCount ?></strong>
        </div>
        <div class="inventory-summary-card">
            <span class="inventory-summary-label">Low stock</span>
            <strong><?= $lowStockCount ?></strong>
        </div>
    </section>

    <nav class="alert-filters" aria-label="Filter inventory alerts">
        <a href="inventory_alerts.php" <?= $alertType === '' ? 'aria-current="page"' : '' ?>>All alerts</a>
        <a href="inventory_alerts.php?type=out" <?= $alertType === 'out' ? 'aria-current="page"' : '' ?>>Out of stock</a>
        <a href="inventory_alerts.php?type=low" <?= $alertType === 'low' ? 'aria-current="page"' : '' ?>>Low stock</a>
    </nav>

    <?php if ($alerts): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Minimum stock</th>
                        <th>Alert</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert): ?>
                        <?php
                            $isOutOfStock = (int)$alert['stock'] <= 0;
                            $statusClass = $isOutOfStock ? 'status-out' : 'status-low';
                            $statusLabel = $isOutOfStock ? 'Out of stock' : 'Low stock';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($alert['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($alert['categoria'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int)$alert['stock'] ?></td>
                            <td><?= (int)$alert['stock_minimo'] ?></td>
                            <td><span class="stock-status <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td><a href="adjust_stock.php?id=<?= (int)$alert['id_producto'] ?>" class="btn">Adjust stock</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No active products match this alert filter.</p>
    <?php endif; ?>
</body>
</html>
