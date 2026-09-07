<?php
session_start();

// Simple check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LoveCrafty - Online Chocolate Shop</title>
  <link rel="stylesheet" href="css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar">
    <div class="logo">
      <a href="index.php"><img src="img/logo.png" alt="LoveCrafty Logo"></a>
      <span></span>
    </div>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li class="dropdown">
        <a href="items.html">Shop</a>
        <ul class="dropdown-menu">
          <li><a href="#dark-chocolate">Dark Chocolate</a></li>
          <li><a href="#milk-chocolate">Milk Chocolate</a></li>
          <li><a href="#white-chocolate">White Chocolate</a></li>
          <li><a href="#special-offers">Special Offers</a></li>
        </ul>
      </li>
      <li><a href="about.html">About Us</a></li>
      <li><a href="login.html">Account</a></li>
    </ul>
    <div class="cart">
      <a href="cart.html"><img src="img/cart-icon.png" alt="Cart"></a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-content">
      <h1>Indulge in the Finest Chocolates</h1>
      <p>Handcrafted with the powers of R'Lyeh, delivered to your doorstep.</p>
      <a href="#shop" class="btn">Shop Now!</a>
    </div>
  </section>

  <!-- Featured Products Section -->
  <section class="featured-products" id="shop">
    <h2>Featured Products</h2>
    <div class="product-grid">
      <div class="product-card">
        <img src="img/dark-chocolate.jpg" alt="Dark Chocolate">
        <h3>Dark Chocolate</h3>
        <p>$12.99</p>
        <button class="btn" onclick="addToCart(2)">Add to Cart</button>
      </div>
      <div class="product-card">
        <img src="img/milk-chocolate.jpg" alt="Milk Chocolate">
        <h3>Milk Chocolate</h3>
        <p>$10.99</p>
        <button class="btn" onclick="addToCart(1)">Add to Cart</button>
      </div>
      <div class="product-card">
        <img src="img/white-chocolate.jpg" alt="White Chocolate">
        <h3>White Chocolate</h3>
        <p>$11.99</p>
        <button class="btn" onclick="addToCart(3)">Add to Cart</button>
      </div>
    </div>
  </section>

  <!-- Newsletter Section -->
  <section class="newsletter">
    <h2>Offer YOUR mortal soul to OUR eternal Newsletter</h2>
    <p>Get the latest updates, exclusive offers, and killer surprises!</p>
    <form>
      <input type="email" placeholder="Enter your email" required>
      <button type="submit" class="btn">Sign out your soul</button>
    </form>
  </section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About LoveCrafty</h3>
            <p>Chocolaterie with a thousand faces and a thousand more delights. Our mission is to bring our god to your life, <i>even if it takes one bite at a time</i>.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="items.html">Shop</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="login.html">Account</a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="adminstuff/dashboard.php">Admin View</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p>Email: info@rlyehanchocolat.com</p>
            <p>Phone: +1 (123) 456-7890</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 LoveCrafty™. All rights reserved.</p>
    </div>
</footer>
   <!-- Include your JavaScript file -->
   <script src="js/app.js"></script>
</body>
</html>