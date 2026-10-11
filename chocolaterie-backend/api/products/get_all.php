<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../../config.php';
/** @var PDO $conn */

try {
    $stmt = $conn->query('SELECT id_producto, nombre, descripcion, precio, stock, stock_minimo, image_url FROM productos ORDER BY id_producto');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure price is formatted as number
    $formattedProducts = array_map(function($product) {
        $stock = (int)$product['stock'];
        $minimumStock = (int)$product['stock_minimo'];

        return [
            'id_producto' => (int)$product['id_producto'],
            'nombre' => $product['nombre'],
            'descripcion' => $product['descripcion'],
            'precio' => (float)$product['precio'], // Force float conversion
            'stock' => $stock,
            'stock_minimo' => $minimumStock,
            'estado_stock' => $stock <= 0 ? 'agotado' : ($stock <= $minimumStock ? 'bajo' : 'disponible'),
            'image_url' => $product['image_url'] ?: 'img/default-product.png'
        ];
    }, $products);
    
    echo json_encode($formattedProducts);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch products']);
}
?>