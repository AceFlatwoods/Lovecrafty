<?php
session_start();
require_once '../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    try {
        // Check if product exists in any orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE id_producto = ?");
        $stmt->execute([$_GET['id']]);
        $order_count = $stmt->fetchColumn();
        
        if ($order_count > 0) {
            $_SESSION['error'] = "Cannot delete product - it exists in existing orders!";
        } else {
            $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['success'] = "Product deleted successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }
}

header("Location: product_list.php");
exit();
?>