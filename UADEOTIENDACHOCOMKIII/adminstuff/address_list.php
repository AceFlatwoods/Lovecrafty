<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$db = new mysqli("localhost", "root", "", "lovecrafty", "3307");

// Query to get all order addresses with user information
$query = "SELECT o.id_order, u.username, u.email, o.order_date, 
                 o.address, o.city, o.zip as postal_code
          FROM orders o
          JOIN users u ON o.id_user = u.id_user
          ORDER BY o.order_date DESC";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Addresses</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions { white-space: nowrap; }
        .btn { 
            background: #4CAF50; color: white; padding: 8px 12px; 
            text-decoration: none; border-radius: 4px; 
        }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>Order Addresses</h1>
    <a href="dashboard.php" class="btn" style="background: #6c757d;">Dashboard</a>
    
    <table border="1">
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Order Date</th>
            <th>Address</th>
            <th>City</th>
            <th>Postal Code</th>
        </tr>
        <?php while ($address = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($address['id_order']) ?></td>
            <td><?= htmlspecialchars($address['username']) ?></td>
            <td><?= htmlspecialchars($address['email']) ?></td>
            <td><?= date('M d, Y', strtotime($address['order_date'])) ?></td>
            <td><?= htmlspecialchars($address['address']) ?></td>
            <td><?= htmlspecialchars($address['city']) ?></td>
            <td><?= htmlspecialchars($address['postal_code']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>