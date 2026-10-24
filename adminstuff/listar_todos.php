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
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h1>Users</h1>
    <a href="dashboard.php" class="btn">Dashboard</a>
    
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