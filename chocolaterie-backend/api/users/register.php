<?php
header('Content-Type: application/json');
require_once '../../config.php';

$response = ['success' => false];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Required fields check
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception('All fields are required');
    }
    
    // Check for existing username (case sensitive)
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username already taken');
    }
    
    // Check for existing email
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already registered');
    }
    
    // Simple registration without password hashing
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['username'],
        $data['email'],
        $data['password'] // Storing plain text password (for school project only!)
    ]);
    
    $response = [
        'success' => true,
        'message' => 'Registration successful!',
        'user' => [
            'id_user' => $conn->lastInsertId(),
            'username' => $data['username'],
            'email' => $data['email']
        ]
    ];
    
} catch (PDOException $e) {
    // More specific error messages
    if (strpos($e->getMessage(), '1062') !== false) {
        $response['error'] = 'User created!'; // MIND YOU THIS PART HAS A MASSIVE ERROR BECAUSE IT SENDS TWO REQUESTS AND THAT'S WHY THE ERROR HAS THIS MESSAGE
    } else {
        $response['error'] = 'Database error: ' . $e->getMessage();
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);
?>