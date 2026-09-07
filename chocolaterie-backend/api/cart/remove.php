<?php
header('Content-Type: application/json');
require_once '../../config.php';
/** @var PDO $conn */

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Extract the cart item ID from the URL path
    $requestUri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $requestUri);
    $cartId = end($parts); // Get the last part of the URL (the ID)

    if (!is_numeric($cartId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid cart ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare('DELETE FROM carts WHERE id_cart = :cartId');
        $stmt->execute([':cartId' => $cartId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Product removed from cart']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Cart item not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove product from cart']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>