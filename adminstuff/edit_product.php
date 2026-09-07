<?php
session_start();
require_once '../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

// Get product details
$product = [];
$categories = [];

try {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all categories
    $stmt = $conn->query("SELECT * FROM categorias");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE productos SET 
                nombre = ?, 
                descripcion = ?, 
                precio = ?, 
                stock = ?, 
                id_categoria = ?, 
                image_url = ?
            WHERE id_producto = ?
        ");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['stock'],
            $_POST['id_categoria'],
            $_POST['image_url'],
            $_POST['id_producto']
        ]);
        
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: product_list.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        .btn { 
            display: inline-block;
            background: #007bff; 
            color: white; 
            padding: 10px 15px; 
            border: none;
            border-radius: 4px; 
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #0069d9; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Edit Product</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="id_producto" value="<?= $product['id_producto'] ?? '' ?>">
        
        <div class="form-group">
            <label>Product Name:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($product['nombre'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Description:</label>
            <textarea name="descripcion" rows="4" required><?= htmlspecialchars($product['descripcion'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Price:</label>
            <input type="number" name="precio" step="0.01" min="0" value="<?= $product['precio'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Stock:</label>
            <input type="number" name="stock" min="0" value="<?= $product['stock'] ?? '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Category:</label>
            <select name="id_categoria" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id_categoria'] ?>" 
                        <?= ($product['id_categoria'] ?? '') == $category['id_categoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Image URL:</label>
            <input type="text" name="image_url" value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">
        </div>
        
        <button type="submit" class="btn">Save Changes</button>
        <a href="product_list.php" class="btn" style="background: #6c757d;">Cancel</a>
    </form>
</body>
</html>