<?php
session_start();
require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$perPage = 25;
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = $page === false || $page < 1 ? 1 : $page;
$productId = filter_var($_GET['product_id'] ?? '', FILTER_VALIDATE_INT);
$productId = $productId === false || $productId < 1 ? null : $productId;
$type = $_GET['type'] ?? '';
if (!is_string($type) || !in_array($type, ['', 'entrada', 'salida', 'ajuste'], true)) {
    $type = '';
}

$products = $conn->query('SELECT id_producto, nombre FROM productos ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
$conditions = [];
$parameters = [];

if ($productId !== null) {
    $conditions[] = 'm.id_producto = :productId';
    $parameters[':productId'] = $productId;
}

if ($type !== '') {
    $conditions[] = 'm.tipo = :type';
    $parameters[':type'] = $type;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM movimientos_inventario AS m
    $where
");
$stmt->execute($parameters);
$totalMovements = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalMovements / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("
    SELECT
        m.id_movimiento,
        m.id_producto,
        m.fecha,
        m.tipo,
        m.cantidad,
        m.stock_anterior,
        m.stock_nuevo,
        m.motivo,
        p.nombre AS producto,
        u.username AS usuario
    FROM movimientos_inventario AS m
    LEFT JOIN productos AS p ON p.id_producto = m.id_producto
    LEFT JOIN users AS u ON u.id_user = m.id_usuario
    $where
    ORDER BY m.fecha DESC, m.id_movimiento DESC
    LIMIT :limit OFFSET :offset
");
foreach ($parameters as $key => $value) {
    $stmt->bindValue($key, $value, $key === ':productId' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queryParameters = [];
if ($productId !== null) {
    $queryParameters['product_id'] = $productId;
}
if ($type !== '') {
    $queryParameters['type'] = $type;
}
$queryString = http_build_query($queryParameters);
$pageUrl = static function (int $targetPage) use ($queryString): string {
    return 'inventory_history.php?' . ($queryString === '' ? '' : $queryString . '&') . 'page=' . $targetPage;
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Movement History</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h1>Inventory Movement History</h1>
    <div class="toolbar">
        <a href="product_list.php" class="btn btn-secondary">Back to Products</a>
        <span><?= $totalMovements ?> movement(s)</span>
    </div>

    <form method="GET" class="filters">
        <label for="product_id">Product
            <select id="product_id" name="product_id">
                <option value="">All products</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int)$product['id_producto'] ?>"
                        <?= $productId === (int)$product['id_producto'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['nombre'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="type">Movement type
            <select id="type" name="type">
                <option value="">All types</option>
                <option value="entrada" <?= $type === 'entrada' ? 'selected' : '' ?>>Entry</option>
                <option value="salida" <?= $type === 'salida' ? 'selected' : '' ?>>Exit</option>
                <option value="ajuste" <?= $type === 'ajuste' ? 'selected' : '' ?>>Adjustment</option>
            </select>
        </label>
        <button type="submit" class="status-button">Filter</button>
        <a href="inventory_history.php">Clear</a>
    </form>

    <?php if ($movements): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous stock</th>
                        <th>New stock</th>
                        <th>Reason</th>
                        <th>Recorded by</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $movement): ?>
                        <?php
                            $typeClass = $movement['tipo'] === 'entrada'
                                ? 'movement-entry'
                                : ($movement['tipo'] === 'salida' ? 'movement-exit' : 'movement-adjustment');
                            $typeLabel = [
                                'entrada' => 'Entry',
                                'salida' => 'Exit',
                                'ajuste' => 'Adjustment'
                            ][$movement['tipo']] ?? $movement['tipo'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($movement['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($movement['producto'] ?? 'Product #' . $movement['id_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="<?= $typeClass ?>"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= ((int)$movement['cantidad'] > 0 ? '+' : '') . (int)$movement['cantidad'] ?></td>
                            <td><?= (int)$movement['stock_anterior'] ?></td>
                            <td><?= (int)$movement['stock_nuevo'] ?></td>
                            <td><?= htmlspecialchars($movement['motivo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($movement['usuario'] ?? 'System / deleted user', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">No inventory movements match these filters.</p>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Movement history pages">
            <span>
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($pageUrl($page - 1), ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                <?php endif; ?>
            </span>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= htmlspecialchars($pageUrl($page + 1), ENT_QUOTES, 'UTF-8') ?>">Next</a>
                <?php endif; ?>
            </span>
        </nav>
    <?php endif; ?>
</body>
</html>
