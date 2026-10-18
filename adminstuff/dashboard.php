
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../chocolaterie-backend/config.php';
// $db = new mysqli("localhost", "root", "", "lovecrafty", "3307"); #Changed, add , "3307"
// if ($db->connect_error) {
//     die("Error de conexión: " . $db->connect_error);
// }

$user_role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Condominios</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <nav>
        <ul>
            <?php if ($user_role == 'admin'): ?>
                <li><a href="order_list.php">Clients orders</a></li>
                <li><a href="listar_todos.php">Clients</a></li>
                <li><a href="product_list.php">Products</a></li>
                <li><a href="inventory_alerts.php">Inventory alerts</a></li>
                <li><a href="address_list.php">Clients order address</a></li>
            <?php else: ?>
                Oops, it seems there was an error, please close this page.
            <?php endif; ?>
            <li><a href="../index.php">Return to Index</a></li>
        </ul>
    </nav>
</body>
</html>