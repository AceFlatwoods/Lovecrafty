<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}
//require_once '../chocolaterie-backend/config.php';

$db = new mysqli("localhost", "root", "", "lovecrafty", "3307"); #Changed, add , "3307"

$query = "SELECT u.*
          FROM users u
        --   JOIN condominos co ON p.id_condomino = co.id_condomino
          ORDER BY u.id_user ASC";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .pagada { color: green; }
        .pendiente { color: orange; font-weight: bold; }
        .vencida { color: red; }
        .actions { white-space: nowrap; }
        .btn { 
            background: #4CAF50; color: white; padding: 8px 12px; 
            text-decoration: none; border-radius: 4px; 
        }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>Users</h1>
    <a href="dashboard.php" class="btn" style="background: #6c757d;">Dashboard</a>
    
    <table border="1">
        <tr>
            <th>User ID</th>
            <th>User Name</th>
            <th>Mail</th>
            <th>Password</th>
            <th>Role</th>
            <th>Options</th>
        </tr>
        <?php while ($permiso = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($permiso['id_user']) ?></td>
            <td><?= htmlspecialchars($permiso['username']) ?></td>
            <td><?= htmlspecialchars($permiso['email']) ?></td>
            <td><?= $permiso['password_hash'] ?></td>
            <td><?= htmlspecialchars($permiso['role']) ?></td>
            <td>
                <a href="delete_user.php?id=<?= $permiso['id_user'] ?>" 
                onclick="return confirm('Delete this user?')">Delete</a>
                <?php if ($permiso['role'] == 'usuario'): ?>
                    | <a href="make_admin.php?id=<?= $permiso['id_user'] ?>">Make Admin</a>
                <?php else: ?>
                    | <a href="remove_admin.php?id=<?= $permiso['id_user'] ?>">Remove Admin</a>
                <?php endif; ?>
                
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>