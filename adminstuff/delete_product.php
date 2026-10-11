<?php
session_start();
require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product_list.php');
    exit();
}

$productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$active = filter_var($_POST['activo'] ?? null, FILTER_VALIDATE_INT);
$token = $_POST['csrf_token'] ?? '';

if ($productId === false || $productId === null || $productId <= 0 ||
    !in_array($active, [0, 1], true) ||
    !is_string($token) || !isset($_SESSION['product_status_csrf_token']) ||
    !hash_equals($_SESSION['product_status_csrf_token'], $token)) {
    $_SESSION['error'] = 'Invalid product status request. Please try again.';
} else {
    try {
        $stmt = $conn->prepare('UPDATE productos SET activo = ? WHERE id_producto = ?');
        $stmt->execute([$active, $productId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = $active === 1
                ? 'Product is now visible in the shop.'
                : 'Product was deactivated and hidden from the shop.';
        } else {
            $stmt = $conn->prepare('SELECT activo FROM productos WHERE id_producto = ?');
            $stmt->execute([$productId]);
            $currentStatus = $stmt->fetchColumn();
            if ($currentStatus === false) {
                $_SESSION['error'] = 'Product not found.';
            } else {
                $_SESSION['success'] = 'Product visibility is unchanged.';
            }
        }
    } catch (PDOException $e) {
        error_log('Product status update failed: ' . $e->getMessage());
        $_SESSION['error'] = 'Could not update product visibility. Please try again.';
    }
}

header('Location: product_list.php');
exit();
?>