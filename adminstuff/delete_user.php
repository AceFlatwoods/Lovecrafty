<?php
session_start();
require_once '../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../chocolaterie-backend/api/users/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete yourself!";
            header("Location: listar_todos.php");
            exit();
        }

        // Delete user from database
        $stmt = $conn->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        $_SESSION['success'] = "User deleted successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
}

header("Location: listar_todos.php");
exit();
?>