<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['userId']; // Get the user ID from the query parameters

    try {
        // Fetch cart items with product details
        $stmt = $conn->prepare('
            SELECT c.id_cart, c.quantity, p.id_producto, p.nombre, p.precio 
            FROM carts c
            JOIN productos p ON c.id_producto = p.id_producto
            WHERE c.id_user = :userId
        ');
        $stmt->execute([':userId' => $userId]);
        $cartItems = array_map(function($item) {
            $item['precio'] = (float)$item['precio'];
            return $item;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        echo json_encode(['cart' => $cartItems]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch cart']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>