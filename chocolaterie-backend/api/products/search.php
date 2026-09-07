<?php
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['query']; // Get the search query from the URL

    try {
        $stmt = $conn->prepare('SELECT * FROM productos WHERE nombre LIKE :query');
        $stmt->execute([':query' => "%$query%"]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['products' => $products]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to search products']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>