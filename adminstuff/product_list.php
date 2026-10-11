<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

require_once __DIR__ . '/../chocolaterie-backend/config.php';

$query = "SELECT p.*, c.nombre AS categoria
          FROM productos p
          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
          ORDER BY p.id_producto ASC";
$products = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($products as $product) {
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
    </style>
</head>
<body>
    <h1>Product Management</h1>
    <a href="dashboard.php" class="btn">Back to Dashboard</a>
    <a href="add_product.php" class="btn" style="background: #2898a7;">Add New Product</a>
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
        ?>
        <tr>
            <td><?= htmlspecialchars($product['id_producto']) ?></td>
            <td><?= htmlspecialchars($product['nombre']) ?></td>
            <td><?= htmlspecialchars($product['descripcion']) ?></td>
            <td>$<?= number_format($product['precio'], 2) ?></td>
            <td><?= $stock ?></td>
            <td><?= $minimumStock ?></td>
            <td><span class="stock-status <?= $statusClass ?>"><?= $statusLabel ?></span></td>
            <td><?= htmlspecialchars($product['categoria'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($product['image_url']) ?></td>
            <td>
                <a href="edit_product.php?id=<?= $product['id_producto'] ?>">Edit</a>
                | <a href="delete_product.php?id=<?= $product['id_producto'] ?>" 
                   onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($product['nombre'])) ?>?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>