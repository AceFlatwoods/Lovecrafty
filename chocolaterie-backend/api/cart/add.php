<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
/** @var PDO $conn */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'A JSON object is required']);
        exit;
    }

    $userId = filter_var($data['userId'] ?? null, FILTER_VALIDATE_INT);
    $productId = filter_var($data['productId'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_INT);

    if ($userId === false || $userId === null || $userId <= 0 ||
        $productId === false || $productId === null || $productId <= 0 ||
        $quantity === false || $quantity === null || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Valid user, product, and positive quantity are required']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('SELECT stock FROM productos WHERE id_producto = :productId FOR UPDATE');
        $stmt->execute([':productId' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $conn->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        $stmt = $conn->prepare('SELECT id_cart, quantity FROM carts WHERE id_user = :userId AND id_producto = :productId');
        $stmt->execute([':userId' => $userId, ':productId' => $productId]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
        $cartQuantity = (int)($existingItem['quantity'] ?? 0);
        $availableToAdd = (int)$product['stock'] - $cartQuantity;

        if ($quantity > $availableToAdd) {
            $conn->rollBack();
            http_response_code(409);
            echo json_encode([
                'error' => 'Requested quantity exceeds available stock',
                'available' => max(0, $availableToAdd)
            ]);
            exit;
        }

        if ($existingItem) {
            $newQuantity = $cartQuantity + $quantity;
            $stmt = $conn->prepare('UPDATE carts SET quantity = :quantity WHERE id_cart = :cartId');
            $stmt->execute([':quantity' => $newQuantity, ':cartId' => $existingItem['id_cart']]);
        } else {
            $stmt = $conn->prepare('INSERT INTO carts (id_user, id_producto, quantity) VALUES (:userId, :productId, :quantity)');
            $stmt->execute([':userId' => $userId, ':productId' => $productId, ':quantity' => $quantity]);
        }

        $conn->commit();
        echo json_encode(['message' => 'Product added to cart']);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Cart update failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update cart']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>