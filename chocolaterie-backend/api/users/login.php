<?php
session_start(); // Add this at the top
header('Content-Type: application/json');
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'];
    $password = $data['password'];

    try {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password_hash']) {
            // Set session variables
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role']; // 2 roles we have, admin and user they are
            
            echo json_encode([
                'message' => 'Login successful', 
                'user' => $user,
                'redirect' => '../index.php' // And a redirect here
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to login: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>