<?php
session_start();
require_once '../chocolaterie-backend/config.php'; // Adjust path as needed

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../chocolaterie-backend/api/users/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        // Prevent removing your own admin status
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot remove your own admin status!";
            header("Location: listar_todos.php");
            exit();
        }

        // Update user role to regular user
        $stmt = $conn->prepare("UPDATE users SET role = 'usuario' WHERE id_user = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        $_SESSION['success'] = "Admin privileges removed successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating user role: " . $e->getMessage();
    }
}

header("Location: listar_todos.php");
exit();
?>