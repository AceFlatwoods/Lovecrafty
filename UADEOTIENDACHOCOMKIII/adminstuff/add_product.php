<?php
session_start();
require_once '../chocolaterie-backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../../login.html");
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
    try {
        $stmt = $conn->prepare("
            INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, image_url)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['stock'],
            $_POST['id_categoria'],
            $_POST['image_url']
        ]);
        
        $_SESSION['success'] = "Product created successfully!";
        header("Location: product_list.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error creating product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; }
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
            background: #28a745; 
            color: white; 
            padding: 10px 15px; 
            border: none;
            border-radius: 4px; 
            cursor: pointer;
            text-decoration: none;
            margin-right: 10px;
        }
        .btn:hover { background: #218838; }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .error { 
            color: #dc3545; 
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            background-color: #f8d7da;
            border-radius: 4px;
        }
        .success { 
            color: #155724; 
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            background-color: #d4edda;
            border-radius: 4px;
        }
    </style>
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