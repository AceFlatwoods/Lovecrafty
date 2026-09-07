<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare('
            INSERT INTO orders 
            (id_user, card_name, card_number, expiry, cvv, address, city, zip) 
            VALUES 
            (:userId, :cardName, :cardNumber, :expiry, :cvv, :address, :city, :zip)
        ');
        $stmt->execute([
            ':userId' => $data['userId'],
            ':cardName' => $data['cardName'],
            ':cardNumber' => $data['cardNumber'],
            ':expiry' => $data['expiry'],
            ':cvv' => $data['cvv'],
            ':address' => $data['address'],
            ':city' => $data['city'],
            ':zip' => $data['zip']
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // Get cart items
        $stmt = $conn->prepare('
            SELECT id_producto, quantity 
            FROM carts 
            WHERE id_user = :userId
        ');
        $stmt->execute([':userId' => $data['userId']]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add order items
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare('
                INSERT INTO order_items 
                (id_order, id_producto, quantity, price) 
                VALUES 
                (:orderId, :productId, :quantity, 
                (SELECT precio FROM productos WHERE id_producto = :productId))
            ');
            $stmt->execute([
                ':orderId' => $orderId,
                ':productId' => $item['id_producto'],
                ':quantity' => $item['quantity']
            ]);
        }
        
        // Clear cart
        $stmt = $conn->prepare('DELETE FROM carts WHERE id_user = :userId');
        $stmt->execute([':userId' => $data['userId']]);
        
        $conn->commit();
        echo json_encode(['message' => 'Order placed successfully']);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to place order']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>