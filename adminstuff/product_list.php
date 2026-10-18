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

$search = $_GET['name'] ?? '';
$search = is_string($search) ? trim($search) : '';
$categoryId = filter_var($_GET['category'] ?? '', FILTER_VALIDATE_INT);
$categoryId = $categoryId === false || $categoryId < 1 ? null : $categoryId;
$inventoryStatus = $_GET['inventory_status'] ?? '';
if (!is_string($inventoryStatus) ||
    !in_array($inventoryStatus, ['', 'available', 'low', 'out'], true)) {
    $inventoryStatus = '';
}

$categories = $conn->query('SELECT id_categoria, nombre FROM categorias ORDER BY nombre')
    ->fetchAll(PDO::FETCH_ASSOC);
$conditions = [];
$parameters = [];

if ($search !== '') {
    $conditions[] = 'p.nombre LIKE :name';
    $parameters[':name'] = '%' . $search . '%';
}

if ($categoryId !== null) {
    $conditions[] = 'p.id_categoria = :categoryId';
    $parameters[':categoryId'] = $categoryId;
}

if ($inventoryStatus === 'out') {
    $conditions[] = 'p.stock <= 0';
} elseif ($inventoryStatus === 'low') {
    $conditions[] = 'p.stock > 0 AND p.stock <= p.stock_minimo';
} elseif ($inventoryStatus === 'available') {
    $conditions[] = 'p.stock > p.stock_minimo';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $conn->prepare("
    SELECT p.*, c.nombre AS categoria
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    $where
    ORDER BY p.id_producto ASC
");
$stmt->execute($parameters);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$inventorySummary = $conn->query('
    SELECT
        COALESCE(SUM(precio * stock), 0) AS inventory_value,
        COALESCE(SUM(stock <= 0), 0) AS out_of_stock_count,
        COALESCE(SUM(stock > 0 AND stock <= stock_minimo), 0) AS low_stock_count
    FROM productos
    WHERE activo = 1
')->fetch(PDO::FETCH_ASSOC);
$inventoryValue = (float)$inventorySummary['inventory_value'];
$outOfStockCount = (int)$inventorySummary['out_of_stock_count'];
$lowStockCount = (int)$inventorySummary['low_stock_count'];
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
    <a href="inventory_alerts.php" class="btn btn-alerts">Inventory alerts</a>
    <section class="inventory-summary" aria-label="Inventory summary">
        <div class="inventory-summary-card">
            <span class="inventory-summary-label">Inventory value</span>
            <strong>$<?= number_format($inventoryValue, 2, '.', ',') ?></strong>
        </div>
        <div class="inventory-summary-card">
            <span class="inventory-summary-label">Low stock</span>
            <strong><?= $lowStockCount ?></strong>
        </div>
        <div class="inventory-summary-card">
            <span class="inventory-summary-label">Out of stock</span>
            <strong><?= $outOfStockCount ?></strong>
        </div>
    </section>

    <form method="GET" class="filters product-filters">
        <label for="name">Name
            <input type="search" id="name" name="name" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label for="category">Category
            <select id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id_categoria'] ?>"
                        <?= $categoryId === (int)$category['id_categoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="inventory_status">Inventory
            <select id="inventory_status" name="inventory_status">
                <option value="">All stock levels</option>
                <option value="available" <?= $inventoryStatus === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="low" <?= $inventoryStatus === 'low' ? 'selected' : '' ?>>Low stock</option>
                <option value="out" <?= $inventoryStatus === 'out' ? 'selected' : '' ?>>Out of stock</option>
            </select>
        </label>
        <button type="submit" class="status-button">Filter</button>
        <a href="product_list.php">Clear filters</a>
    </form>
    <p><?= count($products) ?> product(s) found</p>

    <?php foreach (['success', 'error'] as $messageType): ?>
        <?php if (isset($_SESSION[$messageType])): ?>
            <p class="<?= $messageType === 'error' ? 'low-stock' : '' ?>">
                <?= htmlspecialchars($_SESSION[$messageType]) ?>
            </p>
            <?php unset($_SESSION[$messageType]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <?php if ($products): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Min Stock</th>
            <th>Status</th>
            <th>Shop Visibility</th>
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
            <td>
                <span class="product-image-url" title="<?= htmlspecialchars($product['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($product['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </td>
            <td>
                <div class="product-actions">
                    <a class="product-action-button" href="edit_product.php?id=<?= (int)$product['id_producto'] ?>">Edit</a>
                    <a class="product-action-button" href="adjust_stock.php?id=<?= (int)$product['id_producto'] ?>">Adjust stock</a>
                    <form method="POST" action="delete_product.php" class="inline-form">
                        <input type="hidden" name="id" value="<?= (int)$product['id_producto'] ?>">
                        <input type="hidden" name="activo" value="<?= $isActive ? '0' : '1' ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['product_status_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="status-button"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p class="empty">No products match these filters.</p>
    <?php endif; ?>
</body>
</html>