<?php
session_start();
require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (empty($_SESSION['stock_csrf_token'])) {
    $_SESSION['stock_csrf_token'] = bin2hex(random_bytes(32));
}

$productId = filter_var($_GET['id'] ?? $_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
if ($productId === false || $productId === null || $productId <= 0) {
    http_response_code(400);
    exit('A valid product is required.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $type = $_POST['tipo'] ?? '';
    $quantity = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
    $rawReason = $_POST['motivo'] ?? '';
    $reason = is_string($rawReason) ? trim($rawReason) : '';

    if (!is_string($token) || !hash_equals($_SESSION['stock_csrf_token'], $token)) {
        $_SESSION['error'] = 'The form expired. Please try again.';
    } elseif (!is_string($type) || !in_array($type, ['entrada', 'salida'], true)) {
        $_SESSION['error'] = 'Choose a valid stock movement type.';
    } elseif ($quantity === false || $quantity === null || $quantity <= 0 || $quantity > 2147483647) {
        $_SESSION['error'] = 'Quantity must be a positive whole number within the supported range.';
    } elseif ($reason === '' || strlen($reason) > 255) {
        $_SESSION['error'] = 'Enter a reason with no more than 255 characters.';
    } else {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare('SELECT nombre, stock FROM productos WHERE id_producto = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $conn->rollBack();
                $_SESSION['error'] = 'Product not found.';
            } else {
                $stockBefore = (int)$product['stock'];
                $delta = $type === 'entrada' ? $quantity : -$quantity;
                $stockAfter = $stockBefore + $delta;

                if ($stockAfter < 0) {
                    $conn->rollBack();
                    $_SESSION['error'] = 'The exit quantity cannot exceed the available stock.';
                } elseif ($stockAfter > 2147483647) {
                    $conn->rollBack();
                    $_SESSION['error'] = 'The resulting stock exceeds the supported limit.';
                } else {
                    $stmt = $conn->prepare('UPDATE productos SET stock = ? WHERE id_producto = ?');
                    $stmt->execute([$stockAfter, $productId]);

                    $stmt = $conn->prepare('
                        INSERT INTO movimientos_inventario
                            (id_producto, id_usuario, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $productId,
                        $_SESSION['user_id'],
                        $type,
                        $delta,
                        $stockBefore,
                        $stockAfter,
                        $reason
                    ]);

                    $conn->commit();
                    $_SESSION['success'] = 'Stock updated and movement recorded.';
                    header('Location: product_list.php');
                    exit();
                }
            }
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('Stock adjustment failed: ' . $e->getMessage());
            $_SESSION['error'] = 'Could not update stock. Please try again.';
        }
    }

    header('Location: adjust_stock.php?id=' . $productId);
    exit();
}

$stmt = $conn->prepare('SELECT id_producto, nombre, stock FROM productos WHERE id_producto = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjust Product Stock</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <main class="form-container">
        <h1>Adjust Product Stock</h1>
        <h2><?= htmlspecialchars($product['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Current stock: <strong><?= (int)$product['stock'] ?></strong></p>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id_producto" value="<?= (int)$product['id_producto'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['stock_csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="tipo">Movement</label>
                <select id="tipo" name="tipo" required>
                    <option value="entrada">Stock entry</option>
                    <option value="salida">Stock exit</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cantidad">Quantity</label>
                <input type="number" id="cantidad" name="cantidad" min="1" max="2147483647" step="1" required>
            </div>

            <div class="form-group">
                <label for="motivo">Reason</label>
                <textarea id="motivo" name="motivo" maxlength="255" required></textarea>
            </div>

            <button type="submit" class="btn">Save stock movement</button>
            <a href="product_list.php" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
</body>
</html>
