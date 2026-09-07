<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

$db = new mysqli("localhost", "root", "", "lovecrafty", "3307");

// Handle status update if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'], $_POST['id_order'])) {
    $newStatus = $_POST['status'];
    $orderId = (int)$_POST['id_order'];
    
    // Validate the status
    $validStatuses = ['Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $updateQuery = "UPDATE orders SET status = ? WHERE id_order = ?";
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param('si', $newStatus, $orderId);
        
        if ($stmt->execute()) {
            $message = '<div class="status-message success">Order status updated successfully!</div>';
            // Refresh the page to show the updated status
            header("Location: order_list.php?success=1");
            exit();
        } else {
            $message = '<div class="status-message error">Error updating order status: ' . $db->error . '</div>';
        }
    } else {
        $message = '<div class="status-message error">Invalid status selected</div>';
    }
}

// Query to get all orders with user information (always executed)
$query = "SELECT o.*, u.username, u.email 
          FROM orders o
          JOIN users u ON o.id_user = u.id_user
          ORDER BY o.order_date ASC";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Orders</title>
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
        .status-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success { background-color: #dff0d8; color: #3c763d; }
        .error { background-color: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <h1>Client Orders</h1>
    <a href="dashboard.php" class="btn" style="background: #6c757d;">Dashboard</a>
    
    <?php
    // Show success message if redirected after update
    if (isset($_GET['success'])) {
        echo '<div class="status-message success">Order status updated successfully!</div>';
    }
    // Show other messages if they exist
    if (isset($message)) {
        echo $message;
    }
    ?>
    
    <table border="1">
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Order Date</th>
            <th>Card Name</th>
            <th>Card Number</th>
            <th>Address</th>
            <th>City</th>
            <th>Options</th>
        </tr>
        <?php while ($order = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($order['id_order']) ?></td>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td><?= htmlspecialchars($order['email']) ?></td>
            <td><?= date('M d, Y H:i', strtotime($order['order_date'])) ?></td>
            <td><?= htmlspecialchars($order['card_name']) ?></td>
            <td><?= htmlspecialchars($order['card_number']) ?></td>
            <td><?= htmlspecialchars($order['address']) ?></td>
            <td><?= htmlspecialchars($order['city']) ?></td>
            <td>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="id_order" value="<?= $order['id_order'] ?>">
                    | Status: 
                    <select name="status" onchange="this.form.submit()" style="border:none; background:none; padding:0;">
                        <option value="Processing" <?= $order['status']=='Processing'?'selected':'' ?>>Processing</option>
                        <option value="Shipped" <?= $order['status']=='Shipped'?'selected':'' ?>>Shipped</option>
                        <option value="Delivered" <?= $order['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                        <option value="Cancelled" <?= $order['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                    </select>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>