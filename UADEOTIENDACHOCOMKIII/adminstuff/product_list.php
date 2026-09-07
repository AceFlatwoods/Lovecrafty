<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$db = new mysqli("localhost", "root", "", "lovecrafty", "3307");

$query = "SELECT p.*, c.nombre as categoria 
          FROM productos p
          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
          ORDER BY p.id_producto ASC";
$result = $db->query($query);
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
        .low-stock { color: red; font-weight: bold; }
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
    <a href="add_product.php" class="btn" style="background: #28a745;">Add New Product</a>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Category</th>
            <th>Image URL</th>
            <th>Actions</th>
        </tr>
        <?php while ($product = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($product['id_producto']) ?></td>
            <td><?= htmlspecialchars($product['nombre']) ?></td>
            <td><?= htmlspecialchars($product['descripcion']) ?></td>
            <td>$<?= number_format($product['precio'], 2) ?></td>
            <td class="<?= $product['stock'] <= 5 ? 'low-stock' : '' ?>"> <!--  We also have this to make it red when the stock is low but we prolly won't showcase it :b -->
                <?= $product['stock'] ?>
            </td>
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
        <?php endwhile; ?>
    </table>
</body>
</html>