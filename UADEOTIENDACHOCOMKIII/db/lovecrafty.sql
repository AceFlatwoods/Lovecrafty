-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 04, 2025 at 09:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lovecrafty`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id_cart` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id_cart`, `id_user`, `id_producto`, `quantity`, `added_at`) VALUES
(2, 1, 2, 5, '2025-03-02 01:52:06'),
(3, 1, 3, 16, '2025-03-02 02:18:14'),
(17, 1, 1, 1, '2025-03-02 22:37:36'),
(24, 7, 4, 1, '2025-03-07 02:37:43'),
(25, 7, 5, 1, '2025-03-07 02:37:52'),
(26, 7, 6, 1, '2025-03-07 02:37:59'),
(27, 4, 1, 1, '2025-06-04 17:06:23'),
(28, 4, 3, 1, '2025-06-04 17:06:26');

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Standard', 'The usual chocolates everyone likes, milk, dark and white'),
(2, 'Fancy', 'Chocolates with a lot of decor'),
(3, 'Special', 'Chocolate that\'s not quite as common as the standard ones but not as rare as the exotic ones'),
(4, 'Exotic', 'Rare chocolates, only made on order');

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_gerente` int(11) DEFAULT NULL,
  `id_tienda` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `apellido`, `cargo`, `salario`, `fecha_contratacion`, `telefono`, `email`, `id_gerente`, `id_tienda`) VALUES
(1, 'Beatrix ', 'Helvetica', 'Gerente', 180.00, '2021-05-12', '020 7584 8117', 'BeatrixHelvet@derzenchocolat.co.uk', NULL, 1),
(2, 'Dante', 'D\'leau', 'Chocolatier', 150.00, '2021-05-14', '020 7930 7576', 'leauredbone97@gmail.com', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `card_name` varchar(100) NOT NULL,
  `card_number` varchar(20) NOT NULL,
  `expiry` varchar(5) NOT NULL,
  `cvv` varchar(4) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `order_date`, `card_name`, `card_number`, `expiry`, `cvv`, `address`, `city`, `zip`) VALUES
(1, 8, '2025-06-04 17:28:28', 'Onman', '1234567890', '12/29', '123', 'Offstreet', 'Somewhere', '82120'),
(2, 8, '2025-06-04 19:00:38', 'Onman', '1234567890', '12/23', '123', 'Offstreet', 'Somewhere', '82120');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id_order_item` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id_order_item`, `id_order`, `id_producto`, `quantity`, `price`) VALUES
(1, 1, 2, 1, 7.00),
(2, 1, 3, 2, 7.00),
(3, 2, 1, 1, 7.00);

-- --------------------------------------------------------

--
-- Table structure for table `produccion`
--

CREATE TABLE `produccion` (
  `id_lote` int(11) NOT NULL,
  `fecha_produccion` date NOT NULL,
  `cantidad` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produccion`
--

INSERT INTO `produccion` (`id_lote`, `fecha_produccion`, `cantidad`, `id_producto`, `id_empleado`) VALUES
(1, '2021-05-14', 30, 1, 1),
(2, '2021-05-15', 30, 2, 1),
(3, '2021-05-15', 30, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`, `id_categoria`, `image_url`) VALUES
(1, 'Milk chocolate', 'Milk chocolate, our most popular chocolate by far', 7.00, 50, 1, 'img/milk-chocolate.jpg'),
(2, 'Dark chocolate', 'Bitter and dark chocolate, 2nd most popular', 7.00, 50, 1, 'img/dark-chocolate.jpg'),
(3, 'White chocolate', 'Sweet and white, not actually chocolate', 7.00, 30, 1, 'img/white-chocolate.jpg'),
(4, 'Parisian Delight', 'Dark chocolate with milk chocolate decorations depicting the Eifel tower', 10.00, 40, 2, 'img/parisian-delight.jpg'),
(5, 'Valentine\'s box', 'Box filled with 15 small square chocolates with various designs', 30.00, 20, 2, 'img/valentine.jpg'),
(6, 'Peanut chocolate', 'Milk chocolate with peanuts incrusted, quite filling', 8.00, 25, 3, 'img/peanut-chocolate.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tiendas`
--

CREATE TABLE `tiendas` (
  `id_tienda` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tiendas`
--

INSERT INTO `tiendas` (`id_tienda`, `nombre`, `direccion`, `telefono`, `ciudad`, `pais`) VALUES
(1, 'L\'chocolatier demeur', '#9438 Knightsbridge', '020 7584 8117', 'Knightsbridge', 'United Kingdom');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Aceu', 'aceufratwudsu@gmail.com', '1234', '2025-03-02 00:30:49'),
(3, 'testuser', 'test@example.com', 'password123', '2025-03-02 01:40:31'),
(4, 'lord', 'lord@gmail.com', '123', '2025-03-02 20:25:55'),
(5, 'Alice', 'alice@gmail.com', '123', '2025-03-02 20:33:48'),
(6, 'HTML', 'html@gmail.com', 'html', '2025-03-02 23:09:08'),
(7, 'Well', 'well@gmail.com', '12345', '2025-03-07 02:37:31'),
(8, 'Onman', 'on@gmail.com', '123', '2025-06-04 17:07:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id_cart`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_gerente` (`id_gerente`),
  ADD KEY `empleados_ibfk_2` (`id_tienda`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id_order_item`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `produccion`
--
ALTER TABLE `produccion`
  ADD PRIMARY KEY (`id_lote`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indexes for table `tiendas`
--
ALTER TABLE `tiendas`
  ADD PRIMARY KEY (`id_tienda`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id_cart` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id_order_item` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produccion`
--
ALTER TABLE `produccion`
  MODIFY `id_lote` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tiendas`
--
ALTER TABLE `tiendas`
  MODIFY `id_tienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Constraints for table `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`id_gerente`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`id_tienda`) REFERENCES `tiendas` (`id_tienda`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Constraints for table `produccion`
--
ALTER TABLE `produccion`
  ADD CONSTRAINT `produccion_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `produccion_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
