<?php
header('Content-Type: application/json');
require_once 'config.php';

$request = $_SERVER['REQUEST_URI'];
switch ($request) {
    case '/':
        echo json_encode(['message' => 'Welcome to Chocolaterie Backend!']);
        break;
    case '/api/users/register':
        require_once 'api/users/register.php';
        break;
    case '/api/users/login':
        require_once 'api/users/login.php';
        break;
    case '/api/cart/add':
        require_once 'api/cart/add.php';
        break;
    case strpos($request, '/api/cart/remove') === 0:
        require_once 'api/cart/remove.php';
        break;
    case '/api/cart/view':
        require_once 'api/cart/view.php';
        break;
    case '/api/products/search':
        require_once 'api/products/search.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>