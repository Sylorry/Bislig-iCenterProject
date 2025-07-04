-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 09:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'ACCESSORIES'),
(2, 'AIRPODS'),
(3, 'ANDROID'),
(4, 'IPAD'),
(5, 'IPHONE'),
(6, 'PC SET'),
(7, 'PRINTER'),
(8, 'LAPTOP');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expenses_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expenses_id`, `date`, `description`, `amount`, `category`, `created_at`) VALUES
(1, '2025-05-28', 'repairs', 5000.00, 'maintenance', '2025-05-27 19:18:28'),
(2, '2025-05-28', 'repairs', 78880.00, 'utilities', '2025-05-27 19:20:53'),
(3, '2025-05-28', 'repairs', 20000.00, 'rent', '2025-05-28 03:57:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `product` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `purchase_price` int(255) NOT NULL,
  `selling_price` int(255) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `image3` varchar(255) DEFAULT NULL,
  `image4` varchar(255) DEFAULT NULL,
  `image5` varchar(255) DEFAULT NULL,
  `image6` varchar(255) DEFAULT NULL,
  `image7` varchar(255) DEFAULT NULL,
  `image8` blob DEFAULT NULL,
  `archived` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `brand`, `product`, `model`, `storage`, `purchase_price`, `selling_price`, `status`, `stock_quantity`, `image1`, `image2`, `image3`, `image4`, `image5`, `image6`, `image7`, `image8`, `archived`) VALUES
('AIRPODS_GEN2', 2, 'APPLE', 'AIRPODS', 'GEN 2', 'Not Available', 1500, 1800, 'OLD', 10, 'product_images/AIRPODS_1_1747353541.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('AIRPODS_GEN3', 2, 'APPLE', 'AIRPODS', 'GEN 3', 'Not Available', 1500, 1800, 'CURRENT', 10, 'product_images/AIRPODS_1_1747353522.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('AIRPODS_PRO2', 2, 'APPLE', 'AIRPODS', 'PRO 2', 'Not Available', 2000, 2300, 'OLD', 11, 'product_images/AIRPODS_1_1747353581.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_A3_064', 3, 'REDMI', 'ANDROID', 'A3', '64GB', 18000, 22000, 'NEW', 10, 'product_images/ANDROID_1_1747353910.png', 'product_images/ANDROID_2_1747353910.png', 'product_images/ANDROID_3_1747353910.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_A3X_064', 3, 'OPPO', 'ANDROID', 'A3X', '64GB', 15000, 18000, 'OLD', 10, 'product_images/ANDROID_1_1747375930.png', 'product_images/ANDROID_2_1747375930.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_A3X_128', 3, 'OPPO', 'ANDROID', 'A3X', '128GB', 15000, 17000, 'CURRENT', 10, 'product_images/ANDROID_1_1747376045.png', 'product_images/ANDROID_2_1747376045.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_GALA06_064', 3, 'SAMSUNG', 'ANDROID', 'GALAXY A06', '64GB', 25000, 26000, 'CURRENT', 10, 'product_images/ANDROID_1_1747374835.png', 'product_images/ANDROID_2_1747374835.png', 'product_images/ANDROID_3_1747374835.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_GALA06_128', 3, 'SAMSUNG', 'ANDROID', 'GALAXY A06', '128GB', 27000, 29000, 'CURRENT', 10, 'product_images/ANDROID_1_1747374881.png', 'product_images/ANDROID_2_1747374881.png', 'product_images/ANDROID_3_1747374881.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_HOT601_128', 3, 'INFINIX', 'ANDROID', 'HOT601', '128GB', 53000, 54000, 'OLD', 10, 'product_images/ANDROID_1_1747377665.png', 'product_images/ANDROID_2_1747377665.png', 'product_images/ANDROID_3_1747377665.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_HOT601_256', 3, 'INFINIX', 'ANDROID', 'HOT601', '256GB', 45000, 50000, 'OLD', 10, 'product_images/ANDROID_1_1747377624.png', 'product_images/ANDROID_2_1747377624.png', 'product_images/ANDROID_3_1747377624.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_V40LITE_256', 3, 'VIVO', 'ANDROID', 'V40 LITE 4G', '256GB', 33000, 35000, 'OLD', 10, 'product_images/ANDROID_1_1747376694.png', 'product_images/ANDROID_2_1747376694.png', 'product_images/ANDROID_3_1747376694.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_V50_256', 3, 'VIVO', 'ANDROID', 'V50', '256GB', 25000, 27000, 'OLD', 10, 'product_images/ANDROID_1_1747354008.png', 'product_images/ANDROID_2_1747354008.png', 'product_images/ANDROID_3_1747354008.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_X6_128', 3, 'HONOR', 'ANDROID', 'X6GB', '128GB', 27000, 30000, 'OLD', 10, 'product_images/ANDROID_1_1747381240.png', 'product_images/ANDROID_2_1747381240.png', 'product_images/ANDROID_3_1747381240.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_X7C_256', 3, 'HONOR', 'ANDROID', 'X7C', '256GB', 43000, 45000, 'CURRENT', 10, 'product_images/ANDROID_1_1747381542.png', 'product_images/ANDROID_2_1747381542.png', 'product_images/ANDROID_3_1747381542.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_X9C_256', 3, 'HONOR', 'ANDROID', 'X9C', '256GB', 56000, 58000, 'OLD', 10, 'product_images/ANDROID_1_1747381121.png', 'product_images/ANDROID_2_1747381121.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_Y04_064', 3, 'VIVO', 'ANDROID', 'Y04', '64GB', 23000, 24000, 'OLD', 10, 'product_images/ANDROID_1_1747353772.png', 'product_images/ANDROID_2_1747353772.png', 'product_images/ANDROID_3_1747353772.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_Y04_128', 3, 'VIVO', 'ANDROID', 'Y04', '128GB', 20000, 23000, 'OLD', 10, 'product_images/ANDROID_1_1747353739.png', 'product_images/ANDROID_2_1747353739.png', 'product_images/ANDROID_3_1747353739.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_Y19S_128', 3, 'VIVO', 'ANDROID', 'Y19S', '128GB', 17000, 20000, 'OLD', 10, 'product_images/ANDROID_1_1747353650.png', 'product_images/ANDROID_2_1747353650.png', 'product_images/ANDROID_3_1747353650.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_Y19S_256', 3, 'VIVO', 'ANDROID', 'Y19S', '256GB', 22000, 24000, 'OLD', 10, 'product_images/ANDROID_1_1747353684.png', 'product_images/ANDROID_2_1747353684.png', 'product_images/ANDROID_3_1747353684.png', NULL, NULL, NULL, NULL, '', '0'),
('ANDROID_ZERO30_256', 3, 'INFINIX', 'ANDROID', 'ZERO30 5G', '256GB', 56000, 60000, 'CURRENT', 10, 'product_images/ANDROID_1_1747379161.png', 'product_images/ANDROID_2_1747379161.png', 'product_images/ANDROID_3_1747379161.png', NULL, NULL, NULL, NULL, '', '0'),
('EPSON_L121', 7, 'EPSON', 'PRINTER', 'L121', 'Not Available', 7500, 500, 'CURRENT', 7, 'product_images/PRINTER_1_1747615265.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('EPSON_L3210', 7, 'EPSON', 'PRINTER', 'L3210', 'Not Available', 7000, 8000, 'old', 7, 'product_images/PRINTER_1_1747615298.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('EPSON_L3250', 7, 'EPSON', 'PRINTER', 'L3250', 'Not Available', 6000, 8000, 'old', 7, 'product_images/PRINTER_1_1747613505.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('GIFT_SET', 1, 'APPLE', 'ACCESSORIES', 'GIFT_SET', 'Not Available', 2000, 24000, 'OLD', 10, 'images/products/GIFT_SET_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('IPAD_10THGEN_256', 4, 'APPLE', 'IPAD', '10TH GEN', '256GB', 0, 0, 'OLD', 10, 'product_images/IPAD_1_1748102157.png', 'product_images/IPAD_2_1748102157.png', 'product_images/IPAD_3_1748102157.png', 'product_images/IPAD_4_1748102157.png', NULL, NULL, NULL, '', '0'),
('IPAD_5THGEN_128', 4, 'APPLE', 'IPAD', '5TH GEN', '128GB', 6000, 7000, 'OLD', 12, 'product_images/IPAD_1_1747787204.png', 'product_images/IPAD_2_1747787204.png', 'product_images/IPAD_3_1747787204.png', NULL, NULL, NULL, NULL, '', '0'),
('IPAD_6THGEN_128', 4, 'APPLE', 'IPAD', '6TH GEN', '128GB', 0, 0, 'OLD', 10, 'product_images/IPAD_1_1748101989.png', 'product_images/IPAD_2_1748101989.png', 'product_images/IPAD_3_1748101989.png', NULL, NULL, NULL, NULL, '', '0'),
('IPAD_7THGEN_128', 4, 'APPLE', 'IPAD', '7TH GEN', '128GB', 0, 0, 'OLD', 10, 'product_images/IPAD_1_1748102059.png', 'product_images/IPAD_2_1748102059.png', 'product_images/IPAD_3_1748102059.png', NULL, NULL, NULL, NULL, '', '0'),
('IPAD_9THGEN_064', 4, 'APPLE', 'IPAD', '9TH GEN', '64GB', 0, 0, 'OLD', 6, 'product_images/IPAD_1_1748102091.png', 'product_images/IPAD_2_1748102091.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPAD_AIR1_064', 4, 'APPLE', 'IPAD', 'AIR 1', '64GB', 2000, 3000, 'NEW', 10, 'product_images/IPAD_1_1747787061.png', 'product_images/IPAD_2_1747787061.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPAD_AIR2_128', 4, 'APPLE', 'IPAD', 'AIR 2', '128GB', 3500, 4000, 'NEW', 10, 'product_images/IPAD_1_1747787115.png', 'product_images/IPAD_2_1747787115.png', 'product_images/IPAD_3_1747787115.png', NULL, NULL, NULL, NULL, '', '0'),
('IPAD_AIR4_128', 4, 'APPLE', 'IPAD', 'AIR 4', '128GB', 5000, 5500, 'NEW', 10, 'product_images/IPAD_1_1747787170.png', 'product_images/IPAD_2_1747787170.png', 'product_images/IPAD_3_1747787170.png', 'product_images/IPAD_4_1747787170.png', 'product_images/IPAD_5_1747787170.png', NULL, NULL, '', '0'),
('IPAD_MINI1_016', 4, 'APPLE', 'IPAD', 'MINI 1', '16GB', 0, 0, 'CURRENT', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_11_064GB', 5, 'APPLE', 'IPHONE', '11', '64GB', 19000, 21000, 'OLD', 10, 'product_images/IPHONE_1_1747315438.png', 'product_images/IPHONE_2_1747315438.png', 'product_images/IPHONE_3_1747315438.png', 'product_images/IPHONE_4_1747315438.png', 'product_images/IPHONE_5_1747315438.png', 'product_images/IPHONE_6_1747315438.png', NULL, '', '0'),
('IPHONE_11_128GB', 5, 'APPLE', 'IPHONE', '11', '128GB', 22000, 24000, 'CURRENT', 10, 'product_images/IPHONE_1_1747315495.png', 'product_images/IPHONE_2_1747315495.png', 'product_images/IPHONE_3_1747315495.png', 'product_images/IPHONE_4_1747315495.png', 'product_images/IPHONE_5_1747315495.png', 'product_images/IPHONE_6_1747315495.png', NULL, '', '0'),
('IPHONE_11_256GB', 5, 'APPLE', 'IPHONE', '11', '256GB', 26000, 30000, 'OLD', 15, 'product_images/IPHONE_1_1747315612.png', 'product_images/IPHONE_2_1747315612.png', 'product_images/IPHONE_3_1747315612.png', 'product_images/IPHONE_4_1747315612.png', 'product_images/IPHONE_5_1747315612.png', 'product_images/IPHONE_6_1747315612.png', NULL, '', '0'),
('IPHONE_11PM_256GB', 5, 'APPLE', 'IPHONE', '11PM', '256GB', 22000, 24000, 'OLD', 10, 'product_images/IPHONE_1_1747313077.png', 'product_images/IPHONE_2_1747313077.png', 'product_images/IPHONE_3_1747313077.png', 'product_images/IPHONE_4_1747313077.png', NULL, 'product_images/IPHONE_6.png', NULL, '', '0'),
('IPHONE_11PM_512GB', 5, 'APPLE', 'IPHONE', '11PM', '512GB', 22000, 23500, 'OLD', 10, 'product_images/IPHONE_1_1747312993.png', 'product_images/IPHONE_2_1747312993.png', 'product_images/IPHONE_3_1747312993.png', 'product_images/IPHONE_4_1747312993.png', NULL, NULL, NULL, '', '0'),
('IPHONE_11PRO_064GB', 5, 'APPLE', 'IPHONE', '11PRO', '64GB', 27000, 29000, 'OLD', 10, 'product_images/IPHONE_1_1747313939.png', 'product_images/IPHONE_2_1747313939.png', 'product_images/IPHONE_3_1747313939.png', 'product_images/IPHONE_4_1747313939.png', NULL, NULL, NULL, '', '0'),
('IPHONE_11PRO_256GB', 5, 'APPLE', 'IPHONE', '11PRO', '256GB', 26640, 28000, 'old', 10, 'product_images/IPHONE_1_1747313894.png', 'product_images/IPHONE_2_1747313894.png', 'product_images/IPHONE_3_1747313894.png', 'product_images/IPHONE_4_1747313894.png', NULL, NULL, NULL, '', '0'),
('IPHONE_11PRO_512GB', 5, 'APPLE', 'IPHONE', '11PRO', '512GB', 26000, 29000, 'OLD', 10, 'product_images/IPHONE_1_1747313860.png', 'product_images/IPHONE_2_1747313860.png', 'product_images/IPHONE_3_1747313860.png', 'product_images/IPHONE_4_1747313860.png', NULL, NULL, NULL, '', '0'),
('IPHONE_12_128GB', 5, 'APPLE', 'IPHONE', '12', '128GB', 27000, 29500, 'OLD', 10, 'product_images/IPHONE_1_1747311014.png', 'product_images/IPHONE_2_1747311014.png', 'product_images/IPHONE_3_1747311014.png', 'product_images/IPHONE_4_1747311014.png', 'product_images/IPHONE_5_1747311014.png', 'product_images/IPHONE_6_1747311014.png', NULL, '', '0'),
('IPHONE_12_256GB', 5, 'APPLE', 'IPHONE', '12', '256GB', 23000, 25000, 'CURRENT', 15, 'product_images/IPHONE_1_1747310926.png', 'product_images/IPHONE_2_1747310926.png', 'product_images/IPHONE_3_1747310926.png', 'product_images/IPHONE_4_1747310926.png', 'product_images/IPHONE_5_1747310926.png', 'product_images/IPHONE_6_1747310926.png', NULL, '', '0'),
('IPHONE_12PM_128GB', 5, 'APPLE', 'IPHONE', '12PM', '128GB', 39000, 41000, 'CURRENT', 10, 'product_images/IPHONE_1_1747314723.png', 'product_images/IPHONE_2_1747314691.png', 'product_images/IPHONE_3_1747314691.png', 'product_images/IPHONE_4_1747314723.png', NULL, NULL, NULL, '', '0'),
('IPHONE_12PM_256GB', 5, 'APPLE', 'IPHONE', '12PM', '256GB', 35000, 38000, 'OLD', 10, 'product_images/IPHONE_1_1747314578.png', 'product_images/IPHONE_2_1747314578.png', 'product_images/IPHONE_3_1747314578.png', 'product_images/IPHONE_4_1747314578.png', NULL, NULL, NULL, '', '0'),
('IPHONE_12PM_512GB', 5, 'APPLE', 'IPHONE', '12PM', '512GB', 34000, 36000, 'OLD', 13, 'product_images/IPHONE_1_1747314531.png', 'product_images/IPHONE_2_1747314531.png', 'product_images/IPHONE_3_1747314531.png', 'product_images/IPHONE_4_1747314531.png', NULL, NULL, NULL, '', '0'),
('IPHONE_12PRO_128GB', 5, 'APPLE', 'IPHONE', '12PRO', '128GB', 32000, 34000, 'OLD', 10, 'product_images/IPHONE_1_1747314484.png', 'product_images/IPHONE_2_1747314484.png', 'product_images/IPHONE_3_1747314484.png', 'product_images/IPHONE_4_1747314484.png', NULL, NULL, NULL, '', '0'),
('IPHONE_12PRO_256GB', 5, 'APPLE', 'IPHONE', '12PRO', '256GB', 34000, 37000, 'OLD', 10, 'product_images/IPHONE_1_1747314412.png', 'product_images/IPHONE_2_1747314412.png', 'product_images/IPHONE_3_1747314412.png', 'product_images/IPHONE_4_1747314412.png', NULL, NULL, NULL, '', '0'),
('IPHONE_13_128GB', 5, 'APPLE', 'IPHONE', '13', '128GB', 55000, 65000, 'OLD', 10, 'product_images/IPHONE_1_1747304960.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_13_256GB', 5, 'APPLE', 'IPHONE', '13', '256GB', 33000, 45000, 'OLD', 10, 'product_images/IPHONE_1_1747305007.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_13PM_128GB', 5, 'APPLE', 'IPHONE', '13PM', '128GB', 44000, 50000, 'CURRENT', 10, 'product_images/IPHONE_1_1747302214.png', 'product_images/IPHONE_2_1747301903.png', 'product_images/IPHONE_3_1747302191.png', 'product_images/IPHONE_4_1747302199.png', 'product_images/IPHONE_5_1747302222.png', NULL, NULL, '', '0'),
('IPHONE_13PM_256GB', 5, 'APPLE', 'IPHONE', '13PM', '256GB', 53000, 55000, 'OLD', 10, 'product_images/IPHONE_1_1747302345.png', 'product_images/IPHONE_2_1747302077.png', 'product_images/IPHONE_3_1747302077.png', 'product_images/IPHONE_4_1747302077.png', 'product_images/IPHONE_5_1747302077.png', NULL, NULL, '', '0'),
('IPHONE_13PM_512GB', 5, 'APPLE', 'IPHONE', '13PM', '512GB', 55000, 6000, 'OLD', 10, 'product_images/IPHONE_1_1747302135.png', 'product_images/IPHONE_2_1747314313.png', 'product_images/IPHONE_3_1747314313.png', 'product_images/IPHONE_4_1747314313.png', 'product_images/IPHONE_5_1747314313.png', NULL, NULL, '', '0'),
('IPHONE_13PRO_128GB', 5, 'APPLE', 'IPHONE', '13PRO', '128GB', 44000, 50000, 'CURRENT', 10, 'product_images/IPHONE_1_1747302329.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_13PRO_256GB', 5, 'APPLE', 'IPHONE', '13PRO', '256GB', 55000, 66000, 'OLD', 10, 'product_images/IPHONE_1_1747300238.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_13PRO_512GB', 5, 'APPLE', 'IPHONE', '13PRO', '512GB', 16000, 16000, 'OLD', 10, 'product_images/IPHONE_1_1747313423.png', 'product_images/IPHONE_2_1747313423.png', 'product_images/IPHONE_3_1747313423.png', 'product_images/IPHONE_4_1747313423.png', 'product_images/IPHONE_5_1747313423.png', NULL, NULL, '', '0'),
('IPHONE_14_128GB', 5, 'APPLE', 'IPHONE', '14', '128GB', 45000, 55000, 'CURRENT', 10, 'product_images/IPHONE_1_1747361878.png', 'product_images/IPHONE_2_1747361878.png', 'product_images/IPHONE_3_1747361878.png', 'product_images/IPHONE_4_1747361878.png', 'product_images/IPHONE_5_1747361878.png', 'product_images/IPHONE_6_1747361878.png', NULL, '', '0'),
('IPHONE_14_256GB', 5, 'APPLE', 'IPHONE', '14', '256GB', 56000, 58000, 'OLD', 10, 'product_images/IPHONE_1_1747361959.png', 'product_images/IPHONE_2_1747361959.png', 'product_images/IPHONE_3_1747361959.png', 'product_images/IPHONE_4_1747361959.png', 'product_images/IPHONE_5_1747361959.png', 'product_images/IPHONE_6_1747361959.png', NULL, '', '0'),
('IPHONE_14PM_128GB', 5, 'APPLE', 'IPHONE', '14PM', '128GB', 44000, 50000, 'CURRENT', 10, 'product_images/IPHONE_1_1747359149.png', 'product_images/IPHONE_2_1747359149.png', 'product_images/IPHONE_3_1747359149.png', 'product_images/IPHONE_4_1747359149.png', NULL, NULL, NULL, '', '0'),
('IPHONE_14PM_256GB', 5, 'APPLE', 'IPHONE', '14PM', '256GB', 53000, 55000, 'CURRENT', 18, 'product_images/IPHONE_1_1747359206.png', 'product_images/IPHONE_2_1747359206.png', 'product_images/IPHONE_3_1747359206.png', 'product_images/IPHONE_4_1747359206.png', NULL, NULL, NULL, '', '0'),
('IPHONE_14PM_512GB', 5, 'APPLE', 'IPHONE', '14PM', '512GB', 55000, 57000, 'OLD', 10, 'product_images/IPHONE_1_1747359248.png', 'product_images/IPHONE_2_1747359248.png', 'product_images/IPHONE_3_1747359248.png', 'product_images/IPHONE_4_1747359248.png', NULL, NULL, NULL, '', '0'),
('IPHONE_14PRO_128GB', 5, 'APPLE', 'IPHONE', '14PRO', '128GB', 59000, 61000, 'CURRENT', 10, 'product_images/IPHONE_1_1747359434.png', 'product_images/IPHONE_2_1747359434.png', 'product_images/IPHONE_3_1747359434.png', 'product_images/IPHONE_4_1747359434.png', NULL, NULL, NULL, '', '0'),
('IPHONE_14PRO_256GB', 5, 'APPLE', 'IPHONE', '14PRO', '256GB', 55000, 57000, 'CURRENT', 10, 'product_images/IPHONE_1_1747359343.png', 'product_images/IPHONE_2_1747359343.png', 'product_images/IPHONE_3_1747359343.png', 'product_images/IPHONE_4_1747359343.png', NULL, NULL, NULL, '', '0'),
('IPHONE_6S_32GB', 5, 'APPLE', 'IPHONE', '6S', '32GB', 0, 0, 'OLD', 0, 'product_images/IPHONE_1_1746707824.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '1'),
('IPHONE_6SP_128GB', 5, 'APPLE', 'IPHONE', '6SP', '128GB', 0, 0, 'CURRENT', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '1'),
('IPHONE_6SP_64GB', 5, 'APPLE', 'IPHONE', '6SP', '64GB', 0, 0, 'CURRENT', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '1'),
('IPHONE_7_128GB', 5, 'APPLE', 'IPHONE', '7', '128GB', 7000, 9000, 'CURRENT', 10, 'product_images/IPHONE_1_1747353354.png', 'product_images/IPHONE_2_1747353354.png', 'product_images/IPHONE_3_1747353354.png', 'product_images/IPHONE_4_1747353354.png', NULL, NULL, NULL, '', '0'),
('IPHONE_7_256GB', 5, 'APPLE', 'IPHONE', '7', '256GB', 11690, 13000, 'CURRENT', 10, 'product_images/IPHONE_1_1748458058.png', 'product_images/IPHONE_2_1747353322.png', 'product_images/IPHONE_3_1747353322.png', 'product_images/IPHONE_4_1748458058.png', NULL, NULL, NULL, '', '0'),
('IPHONE_7P_128GB', 5, 'APPLE', 'IPHONE', '7P', '128GB', 34000, 40000, 'CURRENT', 10, 'product_images/IPHONE_1_1747353267.png', 'product_images/IPHONE_2_1747353267.png', 'product_images/IPHONE_3_1747353267.png', 'product_images/IPHONE_4_1747353267.png', NULL, NULL, NULL, '', '0'),
('IPHONE_8_128GB', 5, 'APPLE', 'IPHONE', '8', '128GB', 9100, 11000, 'CURRENT', 10, 'product_images/IPHONE_1_1748457954.png', 'product_images/IPHONE_2_1747353222.png', 'product_images/IPHONE_3_1748457954.png', NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_8P_256GB', 5, 'APPLE', 'IPHONE', '8P', '256GB', 16250, 19000, 'CURRENT', 10, 'product_images/IPHONE_1_1747389091.png', 'product_images/IPHONE_2_1747389091.png', 'product_images/IPHONE_3_1747389091.png', NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_8P_64GB', 5, 'APPLE', 'IPHONE', '8P', '64GB', 16250, 18000, 'CURRENT', 10, 'product_images/IPHONE_1_1748457868.png', 'product_images/IPHONE_2_1748457868.png', 'product_images/IPHONE_3_1748457868.png', NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_X_064GB', 5, 'APPLE', 'IPHONE', 'X', '64GB', 19000, 22000, 'OLD', 9, 'product_images/IPHONE_1_1747322246.png', 'product_images/IPHONE_2_1747322246.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_X_256GB', 5, 'APPLE', 'IPHONE', 'X', '256GB', 19000, 23000, 'CURRENT', 10, 'product_images/IPHONE_1_1747322278.png', 'product_images/IPHONE_2_1747322278.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_XR_064GB', 5, 'APPLE', 'IPHONE', 'XR', '64GB', 33000, 35000, 'CURRENT', 10, 'product_images/IPHONE_1_1747320239.png', 'product_images/IPHONE_2_1747320239.png', 'product_images/IPHONE_3_1747320239.png', 'product_images/IPHONE_4_1747320239.png', 'product_images/IPHONE_5_1747320239.png', 'product_images/IPHONE_6_1747320239.png', NULL, '', '0'),
('IPHONE_XR_128GB', 5, 'APPLE', 'IPHONE', 'XR', '128GB', 33700, 35000, 'OLD', 10, 'product_images/IPHONE_1_1747320335.png', 'product_images/IPHONE_2_1747320335.png', 'product_images/IPHONE_3_1747320335.png', 'product_images/IPHONE_4_1747320335.png', 'product_images/IPHONE_5_1747320335.png', 'product_images/IPHONE_6_1747320335.png', NULL, '', '0'),
('IPHONE_XR_256GB', 5, 'APPLE', 'IPHONE', 'XR', '256GB', 35000, 37000, 'OLD', 10, 'product_images/IPHONE_1_1747320288.png', 'product_images/IPHONE_2_1747320288.png', 'product_images/IPHONE_3_1747320288.png', 'product_images/IPHONE_4_1747320288.png', 'product_images/IPHONE_5_1747320288.png', 'product_images/IPHONE_6_1747320288.png', NULL, '', '0'),
('IPHONE_XS_256GB', 5, 'APPLE', 'IPHONE', 'XS', '256GB', 45000, 50000, 'OLD', 10, 'product_images/IPHONE_1_1747318657.png', 'product_images/IPHONE_2_1747318657.png', 'product_images/IPHONE_3_1747318657.png', NULL, NULL, NULL, NULL, '', '0'),
('IPHONE_XSMAX_256GB', 5, 'APPLE', 'IPHONE', 'XS MAX', '256GB', 55000, 60000, 'OLD', 10, 'product_images/IPHONE_1_1747318603.png', 'product_images/IPHONE_2_1747318603.png', 'product_images/IPHONE_3_1747318603.png', NULL, NULL, NULL, NULL, '', '0'),
('LAPTOP_IDEAPADSLIM3_NULL', 8, 'LENOVO', 'LAPTOP', 'IDEAPAD SLIM 3', 'Not Available', 29000, 33000, 'CURRENT', 5, 'product_images/LAPTOP_1_1747615353.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('LAPTOP_LATITUDE3560_NULL', 8, 'DELL', 'LAPTOP', 'LATITUDE 3560', 'Not Available', 18000, 22000, 'CURRENT', 5, 'product_images/LAPTOP_1_1747615407.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('LAPTOP_T490S_NULL', 8, 'LENOVO', 'LAPTOP', 'THINKPAD T490S', 'Not Available', 20000, 25000, 'OLD', 5, 'product_images/LAPTOP_1_1747615376.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('MAC_AIR_2015', 8, 'APPLE', 'LAPTOP', 'MAC_AIR_2015', '128GB', 18000, 20000, 'OLD', 10, 'product_images/LAPTOP_1_1748098660.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('MAG_SAFE', 1, 'APPLE', 'ACCESSORIES', 'MAG SAFE', 'Not Available', 0, 0, 'OLD', 10, 'product_images/ACCESSORIES_1_1748252249.png', 'product_images/ACCESSORIES_2_1748252249.png', 'product_images/ACCESSORIES_3_1748252249.png', 'product_images/ACCESSORIES_4_1748252249.png', 'product_images/ACCESSORIES_5_1748252249.png', 'product_images/ACCESSORIES_6_1748252249.png', NULL, NULL, '0'),
('NEW-IPHONE-10THGEN-064', 4, 'APPLE', 'IPAD', '10TH GEN', '64GB', 0, 0, 'OLD', 10, 'product_images/IPAD_1_1748101951.png', 'product_images/IPAD_2_1748101951.png', 'product_images/IPAD_3_1748101951.png', 'product_images/IPAD_4_1748101951.png', NULL, NULL, NULL, '', '0'),
('NEW-IPHONE-13-128', 5, 'APPLE', 'IPHONE', '13', '128GB', 236222, 757484, 'OLD', 10, 'product_images/IPHONE_1_1747372958.png', 'product_images/IPHONE_2_1747372958.png', 'product_images/IPHONE_3_1747372958.png', 'product_images/IPHONE_4_1747372958.png', 'product_images/IPHONE_5_1747372958.png', 'product_images/IPHONE_6_1747372958.png', NULL, '', '0'),
('NEW-IPHONE-16-128', 5, 'APPLE', 'IPHONE', '16', '128GB', 95000, 97000, 'CURRENT', 10, 'product_images/IPHONE_1_1747373201.png', 'product_images/IPHONE_2_1747373201.png', 'product_images/IPHONE_3_1747373201.png', NULL, 'product_images/IPHONE_5_1747373201.png', NULL, NULL, '', '0'),
('NEW-IPHONE-16E-128', 5, 'APPLE', 'IPHONE', '16E', '128GB', 89000, 96000, 'OLD', 3, 'product_images/IPHONE_1_1747373904.png', 'product_images/IPHONE_2_1747373904.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('NEW-IPHONE-9THGEN-064', 4, 'APPLE', 'IPAD', '9TH GEN', '64GB', 2000, 3000, 'OLD', 2, 'product_images/IPAD_1_1748457682.png', 'product_images/IPAD_2_1748457682.png', NULL, NULL, NULL, NULL, NULL, '', '0'),
('PCSET_AMD_A8_7680', 6, 'PCSET', 'PC SET', 'AMD A8 7680', 'MODIFIED', 27000, 30000, 'OLD', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('PCSET_RYZEN_3', 6, 'PCSET', 'PC SET', 'RYZEN 3', 'MODIFIED', 25000, 29000, 'OLD', 10, 'product_images/PC_SET_1_1747383346.png', NULL, NULL, NULL, NULL, NULL, NULL, '', '0'),
('SIM_CARD_DITO', 1, 'DITO', 'SIM CARD', 'MICRO', 'Not Available', 0, 0, 'OLD', 10, 'product_images/SIM_CARD_1_1748268742.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('SIM_CARD_GLOBE', 1, 'GLOBE', 'SIM CARD', 'MICRO', 'Not Available', 0, 0, 'OLD', 10, 'product_images/SIM_CARD_1_1748163013.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('SIM_CARD_GOMO', 1, 'GOMO', 'SIM CARD', 'MICRO', 'Not Available', 0, 0, 'OLD', 10, 'product_images/SIM_CARD_1_1748163000.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('SIM_CARD_SMART', 1, 'Smart', 'SIM CARD', 'MICRO', 'Not Available', 45, 60, 'old', 9, 'images/products/SIM_CARD_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('SIM_CARD_TM', 1, 'TM', 'SIM CARD', 'MICRO', 'Not Available', 45, 65, 'old', 5, 'images/products/SIM CARD1_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('SIM_CARD_TNT', 1, 'TNT', 'SIM CARD', 'MICRO', 'Not Available', 40, 60, 'OLD', 5, 'images/products/SIM_CARD_TNT_1.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('USB_C_LIGHTNING_CABLE', 1, 'APPLE', 'ACCESSORIES', 'LIGHTNING_CABLE', 'Not Available', 0, 0, 'OLD', 8, 'product_images/ACCESSORIES_1_1748251603.png', 'product_images/ACCESSORIES_2_1748251603.png', 'product_images/ACCESSORIES_3_1748251603.png', 'product_images/ACCESSORIES_4_1748251603.png', NULL, NULL, NULL, NULL, '0'),
('USB_LIGHTNING', 1, 'APPLE', 'ACCESSORIES', 'USB_LIGHTNING', 'Not_Available', 450, 550, 'OLD', 0, 'product_images/ACCESSORIES_1_1748251565.png', 'product_images/ACCESSORIES_2_1748251565.png', 'product_images/ACCESSORIES_3_1748251565.png', 'product_images/ACCESSORIES_4_1748251565.png', NULL, NULL, NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `profit`
--

CREATE TABLE `profit` (
  `profit_id` int(11) NOT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `total_cogs` decimal(10,2) DEFAULT NULL,
  `gross_profit` decimal(10,2) DEFAULT NULL,
  `expenses` decimal(10,2) DEFAULT NULL,
  `net_profit` decimal(10,2) DEFAULT NULL,
  `date_of_sale` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profit`
--

INSERT INTO `profit` (`profit_id`, `sales_id`, `total_sales`, `total_cogs`, `gross_profit`, `expenses`, `net_profit`, `date_of_sale`) VALUES
(11, NULL, 282000.00, 27000.00, 255000.00, 0.00, 255000.00, '2025-05-15'),
(12, NULL, 3000.00, 2000.00, 1000.00, 0.00, 1000.00, '2025-05-20'),
(13, NULL, 126000.00, 116000.00, 10000.00, 0.00, 10000.00, '2025-05-22'),
(14, NULL, 610.00, NULL, 570.00, 0.00, 570.00, '2025-05-26'),
(15, NULL, 60.00, NULL, 20.00, 0.00, 20.00, '2025-05-28'),
(16, NULL, 615.00, NULL, 165.00, 0.00, 165.00, '2025-06-27'),
(17, NULL, 125.00, NULL, 80.00, 0.00, 80.00, '2025-06-28'),
(18, NULL, 99375.00, NULL, 99375.00, 0.00, 99375.00, '2025-06-30'),
(19, NULL, 2200.00, NULL, 400.00, 0.00, 400.00, '2025-07-01');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reservation_date` date DEFAULT NULL,
  `reservation_time` time DEFAULT NULL,
  `proof_of_payment` longtext DEFAULT NULL,
  `reservation_fee` decimal(65,0) DEFAULT NULL,
  `remaining_reservation_fee` decimal(65,0) DEFAULT NULL,
  `product_count` int(11) DEFAULT 1,
  `STATUS` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `product_id_1` varchar(100) DEFAULT NULL,
  `product_id_2` varchar(100) DEFAULT NULL,
  `product_id_3` varchar(100) DEFAULT NULL,
  `product_id_4` varchar(100) DEFAULT NULL,
  `product_id_5` varchar(100) DEFAULT NULL,
  `product_name_1` varchar(255) DEFAULT NULL,
  `product_name_2` varchar(255) DEFAULT NULL,
  `product_name_3` varchar(255) DEFAULT NULL,
  `product_name_4` varchar(255) DEFAULT NULL,
  `product_name_5` varchar(255) DEFAULT NULL,
  `product_brand_1` varchar(100) DEFAULT NULL,
  `product_brand_2` varchar(100) DEFAULT NULL,
  `product_brand_3` varchar(100) DEFAULT NULL,
  `product_brand_4` varchar(100) DEFAULT NULL,
  `product_brand_5` varchar(100) DEFAULT NULL,
  `product_model_1` varchar(100) DEFAULT NULL,
  `product_model_2` varchar(100) DEFAULT NULL,
  `product_model_3` varchar(100) DEFAULT NULL,
  `product_model_4` varchar(100) DEFAULT NULL,
  `product_model_5` varchar(100) DEFAULT NULL,
  `product_price_1` decimal(10,2) DEFAULT NULL,
  `product_price_2` decimal(10,2) DEFAULT NULL,
  `product_price_3` decimal(10,2) DEFAULT NULL,
  `product_price_4` decimal(10,2) DEFAULT NULL,
  `product_price_5` decimal(10,2) DEFAULT NULL,
  `archived` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `name`, `contact_number`, `address`, `email`, `reservation_date`, `reservation_time`, `proof_of_payment`, `reservation_fee`, `remaining_reservation_fee`, `product_count`, `STATUS`, `created_at`, `product_id_1`, `product_id_2`, `product_id_3`, `product_id_4`, `product_id_5`, `product_name_1`, `product_name_2`, `product_name_3`, `product_name_4`, `product_name_5`, `product_brand_1`, `product_brand_2`, `product_brand_3`, `product_brand_4`, `product_brand_5`, `product_model_1`, `product_model_2`, `product_model_3`, `product_model_4`, `product_model_5`, `product_price_1`, `product_price_2`, `product_price_3`, `product_price_4`, `product_price_5`, `archived`) VALUES
(4, 'NARSAS', '09754124598', 'AWFLKAFNALWFAWLFIOH', 'watsmewip@gmail.com', '2025-06-29', '03:32:17', 'data:image/jpeg;base64,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', 1500, 26100, 3, 'completed', '2025-06-29 09:32:17', 'AIRPODS_GEN2', 'AIRPODS_GEN3', 'GIFT_SET', NULL, NULL, 'AIRPODS', 'AIRPODS', 'ACCESSORIES', NULL, NULL, 'APPLE', 'APPLE', 'APPLE', NULL, NULL, 'GEN 2', 'GEN 3', 'GIFT_SET', NULL, NULL, 1800.00, 1800.00, 24000.00, NULL, NULL, NULL),
(5, 'DADAWDAW', '09754124591', 'DADAWDAWD', 'watsmewipnaenae@gmail.com', '2025-06-29', '03:34:32', 'data:image/jpeg;base64,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', 1000, 24800, 2, 'completed', '2025-06-29 09:34:32', 'AIRPODS_GEN2', 'GIFT_SET', NULL, NULL, NULL, 'AIRPODS', 'ACCESSORIES', NULL, NULL, NULL, 'APPLE', 'APPLE', NULL, NULL, NULL, 'GEN 2', 'GIFT_SET', NULL, NULL, NULL, 1800.00, 24000.00, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `returned_items`
--

CREATE TABLE `returned_items` (
  `return_id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `storage` varchar(100) DEFAULT NULL,
  `quantity_returned` int(11) NOT NULL,
  `previous_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `returned_by` varchar(100) DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sales_id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `product_id` varchar(255) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT NULL,
  `cogs` decimal(10,2) DEFAULT NULL,
  `stock_revenue` decimal(10,2) DEFAULT NULL,
  `date_of_sale` date NOT NULL DEFAULT curdate(),
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sales_id`, `stock_id`, `product_id`, `selling_price`, `quantity_sold`, `cogs`, `stock_revenue`, `date_of_sale`, `purchase_price`) VALUES
(18, 27, 'PCSET_AMD_A8_7680', 30000.00, 1, 0.00, 30000.00, '2025-05-15', 0.00),
(19, 28, 'NEW-IPHONE-16E-128', 96000.00, 2, 0.00, 192000.00, '2025-05-15', 0.00),
(20, 29, 'PCSET_AMD_A8_7680', 30000.00, 1, 0.00, 30000.00, '2025-05-15', 0.00),
(21, 30, 'PCSET_AMD_A8_7680', 30000.00, 1, 27000.00, 30000.00, '2025-05-15', 27000.00),
(22, 31, 'NEW-IPHONE-9THGEN-064', 3000.00, 1, 2000.00, 3000.00, '2025-05-20', 2000.00),
(23, 32, 'NEW-IPHONE-16E-128', 96000.00, 1, 89000.00, 96000.00, '2025-05-22', 89000.00),
(24, 33, 'PCSET_AMD_A8_7680', 30000.00, 1, 27000.00, 30000.00, '2025-05-22', 27000.00),
(25, 34, 'SIM_CARD_TNT', 0.00, 1, NULL, 0.00, '2025-05-26', 0.00),
(26, 35, 'USB_C_LIGHTNING_CABLE', 0.00, 1, NULL, 0.00, '2025-05-26', 0.00),
(27, 36, 'USB_LIGHTNING', 550.00, 1, NULL, 550.00, '2025-05-26', 450.00),
(28, 37, 'SIM_CARD_TNT', 60.00, 1, NULL, 60.00, '2025-05-26', 40.00),
(29, 38, 'SIM_CARD_TNT', 60.00, 1, NULL, 60.00, '2025-05-28', 40.00),
(30, 39, 'SIM_CARD_TM', 65.00, 1, NULL, 65.00, '2025-06-27', 45.00),
(31, 40, 'USB_LIGHTNING', 550.00, 1, NULL, 550.00, '2025-06-27', 450.00),
(32, 41, 'SIM_CARD_TM', 65.00, 1, NULL, 65.00, '2025-06-28', 45.00),
(33, 42, 'SIM_CARD_SMART', 60.00, 1, NULL, 60.00, '2025-06-28', 45.00),
(34, 0, 'SIM_CARD_TNT', 60.00, 1, NULL, 60.00, '2025-06-30', 40.00),
(35, 43, 'SIM_CARD_TNT', 60.00, 1, NULL, 60.00, '2025-06-30', 40.00),
(36, 44, 'SIM_CARD_TM', 65.00, 1, NULL, 65.00, '2025-06-30', 45.00),
(37, 45, 'NEW-IPHONE-9THGEN-064', 3000.00, 1, NULL, 3000.00, '2025-06-30', 2000.00),
(38, 46, 'NEW-IPHONE-16E-128', 96000.00, 1, NULL, 96000.00, '2025-06-30', 89000.00),
(39, 47, 'SIM_CARD_TM', 65.00, 1, NULL, 65.00, '2025-06-30', 45.00),
(40, 48, 'SIM_CARD_TM', 65.00, 1, NULL, 65.00, '2025-06-30', 45.00),
(41, 49, 'SIM_CARD_TNT', 60.00, 1, NULL, 60.00, '2025-06-30', 40.00),
(42, 50, 'USB_C_LIGHTNING_CABLE', 0.00, 1, NULL, 0.00, '2025-06-30', 0.00),
(43, 51, 'USB_LIGHTNING', 550.00, 4, NULL, 2200.00, '2025-07-01', 450.00);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `stock_id` int(255) NOT NULL,
  `product_id` varchar(255) DEFAULT NULL,
  `date_of_purchase` date DEFAULT NULL,
  `quantity_sold` int(11) DEFAULT 0,
  `purchase_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`stock_id`, `product_id`, `date_of_purchase`, `quantity_sold`, `purchase_price`) VALUES
(1, 'SIM_CARD_TNT', '2025-06-30', 1, 40.00),
(27, '0', '2025-05-15', 1, 0.00),
(28, '0', '2025-05-15', 2, 0.00),
(29, '0', '2025-05-15', 1, 0.00),
(30, '0', '2025-05-15', 1, 27000.00),
(31, '0', '2025-05-20', 1, 2000.00),
(32, '0', '2025-05-22', 1, 89000.00),
(33, '0', '2025-05-22', 1, 27000.00),
(34, '0', '2025-05-26', 1, 0.00),
(35, '0', '2025-05-26', 1, 0.00),
(36, '0', '2025-05-26', 1, 450.00),
(37, '0', '2025-05-26', 1, 40.00),
(38, '0', '2025-05-28', 1, 40.00),
(39, '0', '2025-06-27', 1, 45.00),
(40, '0', '2025-06-27', 1, 450.00),
(41, '0', '2025-06-28', 1, 45.00),
(42, '0', '2025-06-28', 1, 45.00),
(43, 'SIM_CARD_TNT', '2025-06-30', 1, 40.00),
(44, 'SIM_CARD_TM', '2025-06-30', 1, 45.00),
(45, 'NEW-IPHONE-9THGEN-064', '2025-06-30', 1, 2000.00),
(46, 'NEW-IPHONE-16E-128', '2025-06-30', 1, 89000.00),
(47, 'SIM_CARD_TM', '2025-06-30', 1, 45.00),
(48, 'SIM_CARD_TM', '2025-06-30', 1, 45.00),
(49, 'SIM_CARD_TNT', '2025-06-30', 1, 40.00),
(50, 'USB_C_LIGHTNING_CABLE', '2025-06-30', 1, 0.00),
(51, 'USB_LIGHTNING', '2025-07-01', 4, 450.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `movement_type` enum('IN','OUT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `created_by` varchar(100) DEFAULT 'Admin User',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`id`, `product_id`, `movement_type`, `quantity`, `previous_stock`, `new_stock`, `created_by`, `notes`, `created_at`) VALUES
(1, 'USB_LIGHTNING', 'IN', 1, 3, 4, 'Admin User', 'Restock - Additional inventory added', '2025-06-28 11:31:12'),
(2, 'AIRPODS_PRO2', 'IN', 1, 9, 10, 'Admin User', 'Restock - Additional inventory added', '2025-06-28 11:33:19'),
(3, 'AIRPODS_PRO2', 'IN', 1, 10, 11, 'Admin User', 'Restock - Additional inventory added', '2025-06-28 11:33:50'),
(4, 'EPSON_L121', 'IN', 1, 5, 6, 'Admin User', 'Restock - Additional inventory added', '2025-06-28 11:56:34'),
(5, 'EPSON_L121', 'IN', 1, 6, 7, 'Admin User', 'Restock - Additional inventory added', '2025-06-28 11:57:31'),
(6, 'SIM_CARD_TNT', 'IN', 1, 5, 6, 'Admin User', 'Restock - Additional inventory added', '2025-06-29 23:40:33'),
(7, 'IPAD_10THGEN_256', 'IN', 9, 1, 10, 'Admin User', 'Restock - Additional inventory added', '2025-06-30 12:24:34'),
(8, 'IPAD_MINI1_016', 'IN', 10, 0, 10, 'Admin User', 'Restock - Additional inventory added', '2025-06-30 14:08:52');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `created_by` varchar(100) DEFAULT 'Admin User',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`id`, `product_id`, `quantity`, `previous_stock`, `new_stock`, `created_by`, `notes`, `created_at`) VALUES
(1, 'SIM_CARD_TM', -1, 9, 8, 'Admin User', 'Product sold - TM MICRO (Qty: 1)', '2025-06-28 12:57:18'),
(2, 'SIM_CARD_SMART', -1, 10, 9, 'Admin User', 'Product sold - Smart MICRO (Qty: 1)', '2025-06-28 12:58:38'),
(14, 'SIM_CARD_TNT', -1, 7, 6, 'Admin User', 'Product sold - TNT MICRO (Qty: 1)', '2025-06-29 23:13:37'),
(21, 'SIM_CARD_TNT', -1, 6, 5, 'Admin User', 'Product sold - TNT MICRO (Qty: 1)', '2025-06-29 23:26:20'),
(22, 'SIM_CARD_TM', -1, 8, 7, 'Admin User', 'Product sold - TM MICRO (Qty: 1)', '2025-06-29 23:26:23'),
(23, 'NEW-IPHONE-9THGEN-064', -1, 3, 2, 'Admin User', 'Product sold - APPLE 9TH GEN (Qty: 1)', '2025-06-29 23:27:08'),
(24, 'NEW-IPHONE-16E-128', -1, 4, 3, 'Admin User', 'Product sold - APPLE 16E (Qty: 1)', '2025-06-29 23:27:14'),
(25, 'SIM_CARD_TM', -1, 7, 6, 'Admin User', 'Product sold - TM MICRO (Qty: 1)', '2025-06-29 23:37:20'),
(26, 'SIM_CARD_TM', -1, 6, 5, 'Admin User', 'Product sold - TM MICRO (Qty: 1)', '2025-06-29 23:41:48'),
(27, 'SIM_CARD_TNT', -1, 6, 5, 'Admin User', 'Product sold - TNT MICRO (Qty: 1)', '2025-06-29 23:48:59'),
(28, 'USB_C_LIGHTNING_CABLE', -1, 9, 8, 'Admin User', 'Product sold - APPLE LIGHTNING_CABLE (Qty: 1)', '2025-06-29 23:53:46'),
(29, 'USB_LIGHTNING', -4, 4, 0, 'Admin User', 'Product sold - APPLE USB_LIGHTNING (Qty: 4)', '2025-07-01 07:22:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expenses_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `profit`
--
ALTER TABLE `profit`
  ADD PRIMARY KEY (`profit_id`),
  ADD KEY `sales_id` (`sales_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`STATUS`),
  ADD KEY `idx_date` (`reservation_date`);

--
-- Indexes for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sales_id`),
  ADD KEY `stock_id` (`stock_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expenses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `profit`
--
ALTER TABLE `profit`
  MODIFY `profit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `returned_items`
--
ALTER TABLE `returned_items`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sales_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD CONSTRAINT `returned_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `stock_in_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD CONSTRAINT `stock_out_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
