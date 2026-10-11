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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .stock-status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-available { color: #176b37; background: #e2f4e8; }
        .status-low { color: #7a4b00; background: #fff1cc; }
        .status-out { color: #9b1c1c; background: #fde2e2; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .btn { 
            display: inline-block;
            background: #6c757d; 
            color: white; 
            padding: 8px 12px; 
            border-radius: 4px; 
            margin-bottom: 20px;
        }
        .btn:hover { background: #5a6268; }
        .status-button {
            display: inline-block;
            background: #8B4513;
            color: #fff;
            padding: 8px 12px;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
            font: inherit;
        }
        .status-button:hover { background: #6f360f; }
        .status-button:focus-visible { outline: 2px solid #242424; outline-offset: 2px; }
    </style>
</head>
<body>
    <h1>Product Management</h1>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
    <a href="add_product.php" class="btn" style="background: #2898a7;">Add New Product</a>
    <a href="inventory_history.php" class="btn" style="background: #6f42c1;">Movement history</a>
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
                | <form method="POST" action="delete_product.php" style="display: inline;">
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