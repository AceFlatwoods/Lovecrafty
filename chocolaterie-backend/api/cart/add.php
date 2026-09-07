<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Debugging: Log the received data
    error_log('Received data: ' . print_r($data, true));

    if (empty($data['userId']) || empty($data['productId']) || empty($data['quantity'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $userId = $data['userId'];
    $productId = $data['productId'];
    $quantity = $data['quantity'];

    try {
        // Check if the product already exists in the user's cart
        $stmt = $conn->prepare('SELECT * FROM carts WHERE id_user = :userId AND id_producto = :productId');
        $stmt->execute([':userId' => $userId, ':productId' => $productId]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Update the quantity if the product is already in the cart
            $newQuantity = $existingItem['quantity'] + $quantity;
            $stmt = $conn->prepare('UPDATE carts SET quantity = :quantity WHERE id_cart = :cartId');
            $stmt->execute([':quantity' => $newQuantity, ':cartId' => $existingItem['id_cart']]);
        } else {
            // Add a new item to the cart
            $stmt = $conn->prepare('INSERT INTO carts (id_user, id_producto, quantity) VALUES (:userId, :productId, :quantity)');
            $stmt->execute([':userId' => $userId, ':productId' => $productId, ':quantity' => $quantity]);
        }

        echo json_encode(['message' => 'Product added to cart']);
    } catch (PDOException $e) {
        // Debugging: Log the error
        error_log('Database error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add product to cart']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>