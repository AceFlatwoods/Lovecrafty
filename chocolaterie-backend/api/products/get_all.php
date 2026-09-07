<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../config.php';
/** @var PDO $conn */

try {
    $stmt = $conn->query('SELECT * FROM productos');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure price is formatted as number
    $formattedProducts = array_map(function($product) {
        return [
            'id_producto' => (int)$product['id_producto'],
            'nombre' => $product['nombre'],
            'descripcion' => $product['descripcion'],
            'precio' => (float)$product['precio'], // Force float conversion
            'image_url' => 'img/' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $product['nombre'])) . '.jpg'
        ];
    }, $products);
    
    echo json_encode($formattedProducts);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch products']);
}
?>