<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (empty($_SESSION['product_status_csrf_token'])) {
    $_SESSION['product_status_csrf_token'] = bin2hex(random_bytes(32));
}

$query = "SELECT p.*, c.nombre AS categoria
          FROM productos p
          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
          ORDER BY p.id_producto ASC";
$products = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($products as $product) {
    if (!(int)$product['activo']) {
        continue;
    }

    $stock = (int)$product['stock'];
    $minimumStock = (int)$product['stock_minimo'];

    if ($stock <= 0) {
        $outOfStockCount++;
    } elseif ($stock <= $minimumStock) {
        $lowStockCount++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h1>Product Management</h1>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
    <a href="add_product.php" class="btn btn-add">Add New Product</a>
    <a href="inventory_history.php" class="btn btn-history">Movement history</a>
    <p>Low stock: <?= $lowStockCount ?> | Out of stock: <?= $outOfStockCount ?></p>

    <?php foreach (['success', 'error'] as $messageType): ?>
        <?php if (isset($_SESSION[$messageType])): ?>
            <p class="<?= $messageType === 'error' ? 'low-stock' : '' ?>">
                <?= htmlspecialchars($_SESSION[$messageType]) ?>
            </p>
            <?php unset($_SESSION[$messageType]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Minimum stock</th>
            <th>Status</th>
            <th>Shop visibility</th>
            <th>Category</th>
            <th>Image URL</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <?php
            $stock = (int)$product['stock'];
            $minimumStock = (int)$product['stock_minimo'];
            $statusLabel = $stock <= 0 ? 'Out of stock' : ($stock <= $minimumStock ? 'Low stock' : 'Available');
            $statusClass = $stock <= 0 ? 'status-out' : ($stock <= $minimumStock ? 'status-low' : 'status-available');
            $isActive = (int)$product['activo'] === 1;
        ?>
        <tr>
            <td><?= htmlspecialchars($product['id_producto']) ?></td>
            <td><?= htmlspecialchars($product['nombre']) ?></td>
            <td><?= htmlspecialchars($product['descripcion']) ?></td>
            <td>$<?= number_format($product['precio'], 2) ?></td>
            <td><?= $stock ?></td>
            <td><?= $minimumStock ?></td>
            <td><span class="stock-status <?= $statusClass ?>"><?= $statusLabel ?></span></td>
            <td><?= $isActive ? 'Visible' : 'Hidden' ?></td>
            <td><?= htmlspecialchars($product['categoria'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($product['image_url']) ?></td>
            <td>
                <a href="edit_product.php?id=<?= $product['id_producto'] ?>">Edit</a>
                | <a href="adjust_stock.php?id=<?= $product['id_producto'] ?>">Adjust stock</a>
                | <form method="POST" action="delete_product.php" class="inline-form">
                    <input type="hidden" name="id" value="<?= (int)$product['id_producto'] ?>">
                    <input type="hidden" name="activo" value="<?= $isActive ? '0' : '1' ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['product_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="status-button"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>