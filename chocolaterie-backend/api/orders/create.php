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
    if (!is_array($data) || $userId === false || $userId === null || $userId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'A valid user is required']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('
            SELECT c.id_producto, c.quantity, p.nombre, p.stock, p.precio, p.activo
            FROM carts AS c
            INNER JOIN productos AS p ON p.id_producto = c.id_producto
            WHERE c.id_user = :userId
            FOR UPDATE
        ');
        $stmt->execute([':userId' => $userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$cartItems) {
            $conn->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Your cart is empty']);
            exit;
        }

        foreach ($cartItems as $item) {
            if ((int)$item['activo'] !== 1) {
                $conn->rollBack();
                http_response_code(409);
                echo json_encode([
                    'error' => $item['nombre'] . ' is no longer available. Remove it from your cart before checkout.'
                ]);
                exit;
            }

            if ((int)$item['quantity'] <= 0 || (int)$item['stock'] < (int)$item['quantity']) {
                $conn->rollBack();
                http_response_code(409);
                echo json_encode([
                    'error' => 'Insufficient stock for ' . $item['nombre'],
                    'available' => (int)$item['stock']
                ]);
                exit;
            }
        }

        $stmt = $conn->prepare('
            INSERT INTO orders
                (id_user, card_name, card_number, expiry, cvv, address, city, zip)
            VALUES
                (:userId, :cardName, :cardNumber, :expiry, :cvv, :address, :city, :zip)
        ');
        $stmt->execute([
            ':userId' => $userId,
            ':cardName' => $data['cardName'] ?? '',
            ':cardNumber' => $data['cardNumber'] ?? '',
            ':expiry' => $data['expiry'] ?? '',
            ':cvv' => $data['cvv'] ?? '',
            ':address' => $data['address'] ?? '',
            ':city' => $data['city'] ?? '',
            ':zip' => $data['zip'] ?? ''
        ]);

        $orderId = (int)$conn->lastInsertId();

        foreach ($cartItems as $item) {
            $productId = (int)$item['id_producto'];
            $quantity = (int)$item['quantity'];
            $stockBefore = (int)$item['stock'];
            $stockAfter = $stockBefore - $quantity;

            $stmt = $conn->prepare('
                INSERT INTO order_items (id_order, id_producto, quantity, price)
                VALUES (:orderId, :productId, :quantity, :price)
            ');
            $stmt->execute([
                ':orderId' => $orderId,
                ':productId' => $productId,
                ':quantity' => $quantity,
                ':price' => $item['precio']
            ]);

            $stmt = $conn->prepare('
                UPDATE productos
                SET stock = :stockAfter
                WHERE id_producto = :productId AND stock = :stockBefore
            ');
            $stmt->execute([
                ':stockAfter' => $stockAfter,
                ':productId' => $productId,
                ':stockBefore' => $stockBefore
            ]);

            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('Inventory changed during checkout');
            }

            $stmt = $conn->prepare('
                INSERT INTO movimientos_inventario
                    (id_producto, id_usuario, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
                VALUES
                    (:productId, :userId, \'salida\', :quantity, :stockBefore, :stockAfter, :reason)
            ');
            $stmt->execute([
                ':productId' => $productId,
                ':userId' => $userId,
                ':quantity' => -$quantity,
                ':stockBefore' => $stockBefore,
                ':stockAfter' => $stockAfter,
                ':reason' => 'Venta #' . $orderId
            ]);
        }

        $stmt = $conn->prepare('DELETE FROM carts WHERE id_user = :userId');
        $stmt->execute([':userId' => $userId]);

        $conn->commit();
        echo json_encode(['message' => 'Order placed successfully']);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log('Order creation failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Failed to place order']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>