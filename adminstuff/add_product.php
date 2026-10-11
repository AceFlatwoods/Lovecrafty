<?php
session_start();
require_once __DIR__ . '/../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Get all categories for dropdown
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM categorias");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $minimumStock = filter_var($_POST['stock_minimo'] ?? null, FILTER_VALIDATE_INT);
    $initialStock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);

    if ($minimumStock === false || $minimumStock === null || $minimumStock < 0 ||
        $initialStock === false || $initialStock === null || $initialStock < 0 || $initialStock > 2147483647) {
        $_SESSION['error'] = "Stock values must be non-negative whole numbers within the supported range.";
    } else {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("
                INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo, id_categoria, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_POST['nombre'],
                $_POST['descripcion'],
                $_POST['precio'],
                $_POST['stock'],
                $minimumStock,
                $_POST['id_categoria'],
                $_POST['image_url']
            ]);

            $productId = (int)$conn->lastInsertId();
            if ($initialStock > 0) {
                $stmt = $conn->prepare("
                    INSERT INTO movimientos_inventario
                        (id_producto, id_usuario, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
                    VALUES (?, ?, 'entrada', ?, 0, ?, 'Stock inicial del producto')
                ");
                $stmt->execute([$productId, $_SESSION['user_id'], $initialStock, $initialStock]);
            }

            $conn->commit();
            $_SESSION['success'] = "Product created successfully!";
            header("Location: product_list.php");
            exit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('Product creation failed: ' . $e->getMessage());
            $_SESSION['error'] = "Error creating product. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="form-container">
        <h1>Add New Product</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="nombre">Product Name:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Description:</label>
                <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="precio">Price ($):</label>
                <input type="number" id="precio" name="precio" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Initial Stock:</label>
                <input type="number" id="stock" name="stock" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label for="stock_minimo">Minimum Stock Alert:</label>
                <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" required>
                <small>Show a low-stock warning when stock reaches this quantity or lower.</small>
            </div>
            
            <div class="form-group">
                <label for="id_categoria">Category:</label>
                <select id="id_categoria" name="id_categoria" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id_categoria'] ?>">
                            <?= htmlspecialchars($category['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image_url">Image URL:</label>
                <input type="text" id="image_url" name="image_url" placeholder="https://sometotallylegitsite.com/image_here.png">
                <small>Leave blank if no image available, ONLY USE PNG's and JPG's!</small>
            </div>
            
            <button type="submit" class="btn">Create Product</button>
            <a href="product_list.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>