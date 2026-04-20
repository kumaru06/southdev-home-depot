-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: southdev
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cancel_requests`
--

DROP TABLE IF EXISTS `cancel_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cancel_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cancel_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `cancel_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cancel_requests`
--

LOCK TABLES `cancel_requests` WRITE;
/*!40000 ALTER TABLE `cancel_requests` DISABLE KEYS */;
INSERT INTO `cancel_requests` VALUES (4,3,31,'Order placed by mistake','approved','Approved by staff','2026-03-28 16:57:55','2026-03-28 16:58:17'),(5,12,31,'Wrong products ordered','rejected','Rejected by staff','2026-04-03 14:58:25','2026-04-03 15:00:52'),(6,13,31,'Need to change delivery address','rejected','TEST','2026-04-03 15:19:19','2026-04-03 15:28:09'),(7,13,31,'Wrong delivery address','approved','OK','2026-04-03 15:31:19','2026-04-03 15:31:34'),(8,15,31,'Wrong delivery address','rejected','test','2026-04-03 15:32:53','2026-04-03 15:33:33'),(9,19,31,'Need to change delivery address','approved','please make sure when you\'re ordering kindly check you shipping address tangina ka!','2026-04-04 12:44:45','2026-04-04 12:45:46');
/*!40000 ALTER TABLE `cancel_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (92,23,19,2,'2026-03-18 13:22:55');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Hardware','Nuts, bolts, screws, fasteners, hinges, and general hardware supplies',NULL,1,'2026-02-25 12:30:02'),(2,'Construction Materials','Cement, lumber, roofing, drywall, and building essentials',NULL,1,'2026-02-25 12:30:02'),(3,'Tools','Power tools, hand tools, and professional-grade equipment',NULL,1,'2026-02-25 12:30:02'),(4,'Plumbing','Pipes, fittings, valves, faucets, and plumbing accessories',NULL,1,'2026-02-25 12:30:02'),(5,'Electrical Supplies','Wiring, outlets, switches, breakers, and electrical components',NULL,1,'2026-02-25 12:30:02'),(6,'BULL DOG','TEST',NULL,0,'2026-02-28 15:36:53'),(7,'Tiles','Floor tiles, wall tiles, porcelain, ceramic, mosaic, and premium tile collections',NULL,1,'2026-03-03 03:18:13'),(8,'TEST','',NULL,0,'2026-03-21 08:24:30'),(9,'TEST','for Test',NULL,1,'2026-04-02 13:42:01');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `damaged_items`
--

DROP TABLE IF EXISTS `damaged_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `damaged_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reason` text DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `damaged_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `damaged_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `damaged_items`
--

LOCK TABLES `damaged_items` WRITE;
/*!40000 ALTER TABLE `damaged_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `damaged_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `damaged_products`
--

DROP TABLE IF EXISTS `damaged_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `damaged_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `return_request_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `reason` text NOT NULL,
  `status` enum('received','inspected','written_off','repaired') NOT NULL DEFAULT 'received',
  `admin_notes` text DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_dp_product` (`product_id`),
  KEY `idx_dp_status` (`status`),
  KEY `idx_dp_return` (`return_request_id`),
  KEY `idx_dp_date` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `damaged_products`
--

LOCK TABLES `damaged_products` WRITE;
/*!40000 ALTER TABLE `damaged_products` DISABLE KEYS */;
INSERT INTO `damaged_products` VALUES (1,10,5,11,1,'Item arrived damaged or broken ΓÇö TEST DAMAGE','received',NULL,1,'2026-04-01 14:31:46','2026-04-01 14:42:05'),(2,3,6,12,1,'Item arrived damaged or broken ΓÇö test damage','received',NULL,1,'2026-04-01 15:13:52','2026-04-01 15:13:52'),(3,19,15,13,1,'Item arrived damaged or broken','received',NULL,3,'2026-04-03 15:35:35','2026-04-03 15:35:35'),(4,22,20,15,1,'Item arrived damaged or broken ΓÇö test','received',NULL,3,'2026-04-04 12:47:48','2026-04-04 12:47:48'),(5,19,21,16,1,'Item arrived damaged or broken ΓÇö test','received',NULL,3,'2026-04-04 12:49:26','2026-04-04 12:49:26'),(6,20,25,20,1,'Item arrived damaged or broken','received',NULL,1,'2026-04-04 13:17:56','2026-04-04 13:17:56'),(7,21,26,21,1,'Item arrived damaged or broken','received',NULL,1,'2026-04-04 13:28:56','2026-04-04 13:28:56');
/*!40000 ALTER TABLE `damaged_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,1,994,50,'2026-04-04 13:11:15'),(2,2,10,20,'2026-04-11 14:07:02'),(3,3,998,15,'2026-04-01 15:54:09'),(4,4,994,100,'2026-04-20 04:12:06'),(5,5,999,10,'2026-03-21 08:46:17'),(6,6,999,30,'2026-03-21 08:46:17'),(7,7,1000,10,'2026-02-28 08:17:08'),(8,8,1000,25,'2026-04-01 15:48:35'),(9,9,1000,5,'2026-04-01 15:48:35'),(10,10,781,100,'2026-04-01 15:32:21'),(11,11,499,5,'2026-02-28 16:34:45'),(12,12,495,50,'2026-02-28 05:59:33'),(13,13,338,10,'2026-03-28 14:25:49'),(14,14,1000,15,'2026-03-06 05:07:00'),(15,15,1000,20,'2026-02-28 08:16:54'),(17,16,997,10,'2026-03-21 08:46:17'),(30,17,20,10,'2026-02-28 15:37:22'),(31,19,93,17,'2026-04-20 04:17:25'),(32,20,20,27,'2026-04-20 02:58:31'),(33,21,138,24,'2026-04-20 04:08:10'),(34,22,0,18,'2026-04-11 14:07:17'),(35,23,141,15,'2026-04-20 02:58:31'),(36,24,248,13,'2026-03-04 13:55:35'),(37,25,202,28,'2026-03-04 13:55:35'),(38,26,106,24,'2026-03-04 13:55:35'),(40,27,100,10,'2026-03-04 15:09:06'),(41,28,999,10,'2026-03-08 08:26:04');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=436 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,1,'price_updated','Price updated for Porcelain Floor Tile 60x60cm (ID #19): Γé▒850.00 ΓåÆ Γé▒850.01','::1','2026-03-22 07:03:01'),(2,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-03-22 07:03:01'),(3,1,'user_created','User created: testinventory@gmail.com (Role ID: 4)','::1','2026-03-22 07:03:59'),(4,NULL,'user_logout','User logged out','::1','2026-03-22 07:04:46'),(5,NULL,'user_login','Staff logged in: testinventory@gmail.com','::1','2026-03-22 07:04:58'),(6,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-03-23 05:25:55'),(7,NULL,'user_created','New customer registered: markandreyperez@gmail.com','::1','2026-03-23 05:26:43'),(8,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-23 05:27:29'),(9,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-23 06:05:58'),(10,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-23 06:07:27'),(11,1,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 100 ΓåÆ 100. Reason: Manual stock update','::1','2026-03-23 06:10:50'),(12,1,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 100 ΓåÆ 200. Reason: Manual stock update','::1','2026-03-23 06:11:01'),(13,1,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 200 ΓåÆ 200. Reason: Manual stock update','::1','2026-03-23 06:11:08'),(14,29,'user_logout','User logged out','::1','2026-03-23 07:35:23'),(15,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-23 07:35:34'),(16,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-24 07:20:27'),(17,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-25 11:07:19'),(18,29,'user_logout','User logged out','::1','2026-03-25 11:08:21'),(19,3,'user_login','User logged in: staff@southdev.com','::1','2026-03-25 11:08:32'),(20,3,'user_logout','User logged out','::1','2026-03-25 11:09:20'),(21,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-25 11:09:33'),(22,1,'user_logout','User logged out','::1','2026-03-25 11:27:19'),(23,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-25 11:27:27'),(24,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-26 05:53:57'),(25,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-26 05:57:58'),(26,29,'order_created','Order #1 placed, total: Γé▒850.01','::1','2026-03-26 05:58:33'),(27,1,'order_status_updated','Order #1 status changed to: delivered','::1','2026-03-26 05:58:51'),(28,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-26 14:27:47'),(29,29,'user_logout','User logged out','::1','2026-03-26 14:29:50'),(30,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-26 14:43:26'),(31,29,'user_logout','User logged out','::1','2026-03-26 14:43:33'),(32,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-26 14:54:26'),(33,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-26 15:08:50'),(34,3,'user_logout','User logged out','::1','2026-03-26 15:22:40'),(35,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-03-26 15:22:58'),(36,1,'user_logout','User logged out','::1','2026-03-26 15:24:21'),(37,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-26 15:24:33'),(38,3,'user_logout','User logged out','::1','2026-03-26 15:33:51'),(39,NULL,'user_login','User logged in: inventory@demo.local','::1','2026-03-26 15:34:33'),(40,NULL,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 200 ΓåÆ 0. Reason: Manual stock update','::1','2026-03-26 15:38:55'),(41,NULL,'supplier_request','Supplier request for 100 units of Cabinet Door Hinges (Pair) (ID #2)','::1','2026-03-26 15:46:17'),(42,29,'user_logout','User logged out','::1','2026-03-26 15:46:33'),(43,3,'user_login','User logged in: staff@southdev.com','::1','2026-03-26 15:46:45'),(44,NULL,'user_logout','User logged out','::1','2026-03-26 15:53:54'),(45,3,'user_login','User logged in: staff@southdev.com','::1','2026-03-26 15:54:10'),(46,3,'supplier_request','Supplier request for 200 units of Cabinet Door Hinges (Pair) (ID #2)','::1','2026-03-26 16:09:32'),(47,3,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 0 ΓåÆ 100. Reason: Manual stock update','::1','2026-03-26 16:09:45'),(48,3,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 100 ΓåÆ 200. Reason: Manual stock update','::1','2026-03-26 16:10:31'),(49,3,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 200 ΓåÆ 400. Reason: Manual stock update','::1','2026-03-26 16:11:29'),(50,3,'stock_added','Added 50 units to Cabinet Door Hinges (Pair) (ID #2). Reason: Stock purchase/restock','::1','2026-03-26 16:13:26'),(51,3,'user_login','User logged in: staff@southdev.com','::1','2026-03-27 15:41:37'),(52,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-27 15:57:25'),(53,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-28 13:24:42'),(54,29,'user_logout','User logged out','::1','2026-03-28 13:25:25'),(55,NULL,'user_login','User logged in: inventory@demo.local','::1','2026-03-28 13:36:47'),(56,NULL,'user_logout','User logged out','::1','2026-03-28 13:38:41'),(57,NULL,'user_login','User logged in: inventory@demo.local','::1','2026-03-28 13:39:22'),(58,NULL,'user_logout','User logged out','::1','2026-03-28 13:40:31'),(59,NULL,'user_login','Staff logged in: inventory@demo.local','::1','2026-03-28 13:40:44'),(60,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-28 13:41:58'),(61,1,'user_updated','User #28 deleted','::1','2026-03-28 13:42:09'),(62,1,'user_updated','User #27 deleted','::1','2026-03-28 13:42:13'),(63,NULL,'user_logout','User logged out','::1','2026-03-28 13:42:22'),(64,NULL,'user_created','New customer registered: kramdreyan@gmail.com','::1','2026-03-28 13:49:08'),(65,31,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-03-28 13:53:17'),(66,31,'user_logout','User logged out','::1','2026-03-28 13:53:23'),(67,1,'user_created','User created: bulantoy@burnok.com (Role ID: 4)','::1','2026-03-28 13:58:52'),(68,31,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-03-28 14:00:15'),(69,31,'user_logout','User logged out','::1','2026-03-28 14:00:30'),(70,31,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-03-28 14:02:33'),(71,31,'user_logout','User logged out','::1','2026-03-28 14:02:49'),(72,31,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-03-28 14:06:11'),(73,31,'user_logout','User logged out','::1','2026-03-28 14:20:35'),(74,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-28 14:23:30'),(75,29,'user_logout','User logged out','::1','2026-03-28 14:23:42'),(76,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-03-28 14:23:54'),(77,32,'stock_added','Added 300 units to THHN Wire #12 (75m) (ID #13). Reason: Stock purchase/restock','::1','2026-03-28 14:25:49'),(78,32,'user_logout','User logged out','::1','2026-03-28 14:27:10'),(79,29,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-28 14:27:16'),(80,29,'user_logout','User logged out','::1','2026-03-28 15:19:41'),(81,31,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-03-28 15:19:57'),(82,1,'price_updated','Price updated for Porcelain Floor Tile 60x60cm (ID #19): Γé▒850.01 ΓåÆ Γé▒850.00','::1','2026-03-28 15:44:20'),(83,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-03-28 15:44:20'),(84,1,'user_updated','User #3 active status toggled','::1','2026-03-28 15:52:42'),(85,1,'user_updated','User #3 active status toggled','::1','2026-03-28 15:52:46'),(86,1,'user_updated','User #2 active status toggled','::1','2026-03-28 15:52:54'),(87,1,'user_updated','User #2 active status toggled','::1','2026-03-28 15:53:23'),(88,1,'user_updated','User #2 active status toggled','::1','2026-03-28 15:53:39'),(89,1,'user_updated','User #2 active status toggled','::1','2026-03-28 15:58:31'),(90,1,'user_updated','User #2 active status toggled','::1','2026-03-28 16:50:54'),(91,1,'user_updated','User #2 active status toggled','::1','2026-03-28 16:50:59'),(92,31,'order_created','Order #2 placed, total: Γé▒520.00','::1','2026-03-28 16:55:12'),(93,1,'order_status_updated','Order #2 status changed to: delivered','::1','2026-03-28 16:55:33'),(94,31,'return_requested','Return request submitted for Order #2','::1','2026-03-28 16:55:53'),(95,31,'order_created','Order #3 placed, total: Γé▒185.00','::1','2026-03-28 16:57:18'),(96,1,'order_status_updated','Order #3 status changed to: processing','::1','2026-03-28 16:57:32'),(97,31,'cancel_requested','Cancel request submitted for Order #3','::1','2026-03-28 16:57:55'),(98,1,'cancel_approved','Cancel request #4 approved. Order #3 cancelled, stock restored.','::1','2026-03-28 16:58:17'),(99,1,'user_updated','User #2 active status toggled','::1','2026-03-28 17:10:42'),(100,1,'user_updated','User #2 active status toggled','::1','2026-03-28 17:10:47'),(101,1,'user_updated','User #2 active status toggled','::1','2026-03-28 17:10:58'),(102,1,'user_updated','User #2 active status toggled','::1','2026-03-28 17:11:47'),(103,1,'user_updated','User #2 active status toggled','::1','2026-03-28 17:13:08'),(104,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-28 18:03:17'),(105,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 13:08:15'),(106,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 13:10:04'),(107,31,'user_logout','User logged out','::1','2026-03-29 13:10:10'),(108,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 13:16:17'),(109,31,'user_logout','User logged out','::1','2026-03-29 13:17:05'),(110,1,'return_updated','Return request #10 updated to: approved','::1','2026-03-29 13:42:54'),(111,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:31'),(112,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:40'),(113,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:41'),(114,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:42'),(115,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:42'),(116,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:42'),(117,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:42'),(118,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:43'),(119,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:48'),(120,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:49'),(121,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:50'),(122,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:51'),(123,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 14:08:52'),(124,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-03-29 14:09:33'),(125,1,'user_logout','User logged out','::1','2026-03-29 14:10:20'),(126,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 14:12:01'),(127,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-03-29 14:12:47'),(128,1,'user_logout','User logged out','::1','2026-03-29 14:12:56'),(129,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 14:46:25'),(130,31,'user_logout','User logged out','::1','2026-03-29 16:13:50'),(131,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-03-29 16:15:03'),(132,3,'user_logout','User logged out','::1','2026-03-29 16:28:38'),(133,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-03-29 16:31:48'),(134,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-03-29 16:39:27'),(135,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 16:43:07'),(136,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 16:44:35'),(137,3,'user_login','User logged in: staff@southdev.com','::1','2026-03-29 16:45:18'),(138,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 16:46:27'),(139,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 17:12:12'),(140,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 17:42:41'),(141,31,'user_logout','User logged out','::1','2026-03-29 17:54:31'),(142,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 18:04:31'),(143,1,'user_login','User logged in: admin@southdev.com','::1','2026-03-29 18:28:53'),(144,31,'user_logout','User logged out','::1','2026-03-29 18:30:59'),(145,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 18:35:15'),(146,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 19:23:47'),(147,31,'user_logout','User logged out','::1','2026-03-29 19:23:51'),(148,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 19:29:37'),(149,31,'user_logout','User logged out','::1','2026-03-29 19:31:34'),(150,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-03-29 19:31:47'),(151,31,'user_logout','User logged out','::1','2026-03-29 19:31:52'),(152,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 10:13:30'),(153,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 11:24:31'),(154,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-01 11:26:45'),(155,31,'user_logout','User logged out','::1','2026-04-01 11:57:32'),(156,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 11:57:47'),(157,31,'user_logout','User logged out','::1','2026-04-01 11:57:52'),(158,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 12:06:28'),(159,31,'user_logout','User logged out','::1','2026-04-01 12:10:10'),(160,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 12:14:58'),(161,31,'user_logout','User logged out','::1','2026-04-01 12:17:18'),(162,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 12:22:32'),(163,31,'order_created','Order #4 placed, total: Γé▒4,250.00','::1','2026-04-01 12:24:30'),(164,31,'user_logout','User logged out','::1','2026-04-01 12:35:39'),(165,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-04-01 12:36:18'),(166,3,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 450 ΓåÆ 20. Reason: Manual stock update','::1','2026-04-01 12:36:39'),(167,3,'user_logout','User logged out','::1','2026-04-01 12:50:52'),(168,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 12:50:59'),(169,31,'order_cancelled','Order #4 (SHD-20260401-E0CDF7) cancelled by customer. Stock restored.','::1','2026-04-01 12:52:05'),(170,31,'order_created','Order #5 placed, total: Γé▒65.00','::1','2026-04-01 12:52:46'),(171,1,'order_status_updated','Order #5 status changed to: delivered','::1','2026-04-01 12:55:10'),(172,1,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 20 ΓåÆ 500. Reason: Manual stock update','::1','2026-04-01 13:44:22'),(173,31,'return_requested','Return request submitted for Order #5','::1','2026-04-01 14:28:43'),(174,1,'return_updated','Return request #11 updated to: approved','::1','2026-04-01 14:31:46'),(175,1,'stock_movement','Damaged product recorded: PVC Pipe 1/2\" (10ft) (qty: 1) from Return #11','::1','2026-04-01 14:31:46'),(176,31,'user_logout','User logged out','::1','2026-04-01 14:47:29'),(177,32,'user_login','Staff logged in: bulantoy@burnok.com','::1','2026-04-01 14:47:48'),(178,32,'user_logout','User logged out','::1','2026-04-01 14:50:02'),(179,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-01 14:50:19'),(180,31,'order_created','Order #6 placed, total: Γé▒320.00','::1','2026-04-01 15:12:52'),(181,1,'order_status_updated','Order #6 status changed to: delivered','::1','2026-04-01 15:13:11'),(182,31,'return_requested','Return request submitted for Order #6','::1','2026-04-01 15:13:31'),(183,1,'return_updated','Return request #12 updated to: approved','::1','2026-04-01 15:13:52'),(184,1,'stock_movement','Damaged product recorded: Padlock 50mm Heavy Duty (qty: 1) from Return #12','::1','2026-04-01 15:13:52'),(185,31,'order_created','Order #7 placed via gcash, total: Γé▒65.00','::1','2026-04-01 15:32:04'),(186,31,'order_cancelled','Order #7 (SHD-20260401-4721DD) cancelled by customer. Stock restored.','::1','2026-04-01 15:32:21'),(187,31,'order_created','Order #8 placed via cod, total: Γé▒840.00','::1','2026-04-01 15:33:32'),(188,31,'order_cancelled','Order #8 (SHD-20260401-CEBB50) cancelled by customer. Stock restored.','::1','2026-04-01 15:33:41'),(189,31,'order_created','Order #9 placed via gcash, total: Γé▒1,025.00','::1','2026-04-01 15:34:14'),(190,31,'order_cancelled','Order #9 (SHD-20260401-65D2A8) cancelled by customer. Stock restored.','::1','2026-04-01 15:44:12'),(191,31,'order_created','Order #10 placed via gcash, total: Γé▒4,395.00','::1','2026-04-01 15:45:11'),(192,31,'order_cancelled','Order #10 (SHD-20260401-78DD6F) cancelled by customer. Stock restored.','::1','2026-04-01 15:48:35'),(193,31,'order_created','Order #11 placed via card, total: Γé▒885.00','::1','2026-04-01 15:54:09'),(194,1,'order_status_updated','Order #11 status changed to: processing','::1','2026-04-01 16:02:26'),(195,1,'order_status_updated','Order #11 status changed to: shipped','::1','2026-04-01 16:10:06'),(196,1,'price_updated','Price updated for Porcelain Floor Tile 60x60cm (ID #19): Γé▒850.00 ΓåÆ Γé▒0.00','::1','2026-04-01 16:40:12'),(197,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-01 16:40:12'),(198,1,'price_updated','Price updated for Porcelain Floor Tile 60x60cm (ID #19): Γé▒0.00 ΓåÆ Γé▒375.00','::1','2026-04-01 16:44:24'),(199,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-01 16:44:24'),(200,1,'user_updated','User #29 active status toggled','::1','2026-04-01 17:24:30'),(201,1,'user_updated','User #2 active status toggled','::1','2026-04-01 17:24:41'),(202,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-02 11:20:07'),(203,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-02 11:27:11'),(204,31,'user_login','User logged in: markandreyperez@gmail.com','127.0.0.1','2026-04-02 12:02:43'),(205,31,'user_logout','User logged out','::1','2026-04-02 12:12:49'),(206,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-02 12:14:34'),(207,31,'user_logout','User logged out','::1','2026-04-02 12:33:28'),(208,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-02 13:41:20'),(209,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-02 13:41:35'),(210,1,'category_created','Category created: TEST','::1','2026-04-02 13:42:01'),(211,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-02 13:44:42'),(212,31,'user_logout','User logged out','::1','2026-04-02 13:44:48'),(213,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-02 13:48:18'),(214,31,'user_logout','User logged out','::1','2026-04-02 13:48:25'),(215,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-02 14:04:10'),(216,1,'user_logout','User logged out','::1','2026-04-02 14:04:25'),(217,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-03 07:04:33'),(218,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-03 07:05:37'),(219,3,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 499 ΓåÆ 0. Reason: Manual stock update','::1','2026-04-03 07:05:57'),(220,3,'stock_movement','Inventory updated for Porcelain Floor Tile 60x60cm (ID #19): 270 ΓåÆ 0. Reason: Manual stock update','::1','2026-04-03 07:07:04'),(221,3,'user_logout','User logged out','::1','2026-04-03 07:14:41'),(222,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-03 07:14:56'),(223,31,'user_logout','User logged out','::1','2026-04-03 07:20:19'),(224,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-03 07:23:00'),(225,31,'user_logout','User logged out','::1','2026-04-03 07:24:17'),(226,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-03 07:29:48'),(227,31,'user_logout','User logged out','::1','2026-04-03 07:29:55'),(228,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-04-03 14:43:15'),(229,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-03 14:45:38'),(230,3,'stock_added','Added 50 units to Cabinet Door Hinges (Pair) (ID #2). Reason: Stock purchase/restock','::1','2026-04-03 14:48:05'),(231,1,'user_logout','User logged out','::1','2026-04-03 14:48:30'),(232,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-03 14:48:39'),(233,32,'stock_movement','Inventory updated for Porcelain Floor Tile 60x60cm (ID #19): 0 ΓåÆ 100. Reason: Manual stock update','::1','2026-04-03 14:49:20'),(234,3,'user_logout','User logged out','::1','2026-04-03 14:50:29'),(235,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-03 14:50:39'),(236,31,'order_created','Order #12 placed via card, total: Γé▒375.00','::1','2026-04-03 14:56:50'),(237,32,'user_logout','User logged out','::1','2026-04-03 14:57:25'),(238,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-03 14:57:35'),(239,3,'order_status_updated','Order #12 status changed to: processing','::1','2026-04-03 14:57:57'),(240,31,'cancel_requested','Cancel request submitted for Order #12','::1','2026-04-03 14:58:25'),(241,3,'cancel_rejected','Cancel request #5 rejected.','::1','2026-04-03 15:00:52'),(242,3,'order_status_updated','Order #12 status changed to: delivered','::1','2026-04-03 15:01:42'),(243,3,'order_status_updated','Order #12 status changed to: delivered','::1','2026-04-03 15:02:21'),(244,31,'order_created','Order #13 placed via gcash, total: Γé▒520.00','::1','2026-04-03 15:08:52'),(245,31,'order_created','Order #14 placed via gcash, total: Γé▒375.00','::1','2026-04-03 15:17:00'),(246,3,'order_status_updated','Order #14 status changed to: cancelled','::1','2026-04-03 15:18:27'),(247,3,'order_status_updated','Order #13 status changed to: delivered','::1','2026-04-03 15:18:53'),(248,3,'order_status_updated','Order #13 status changed to: processing','::1','2026-04-03 15:19:02'),(249,31,'cancel_requested','Cancel request submitted for Order #13','::1','2026-04-03 15:19:19'),(250,3,'cancel_rejected','Cancel request #6 rejected.','::1','2026-04-03 15:28:09'),(251,3,'order_status_updated','Order #14 status changed to: pending','::1','2026-04-03 15:30:51'),(252,3,'order_status_updated','Order #14 status changed to: delivered','::1','2026-04-03 15:31:02'),(253,31,'cancel_requested','Cancel request submitted for Order #13','::1','2026-04-03 15:31:19'),(254,3,'cancel_approved','Cancel request #7 approved. Order #13 cancelled, stock restored.','::1','2026-04-03 15:31:34'),(255,31,'order_created','Order #15 placed via cod, total: Γé▒375.00','::1','2026-04-03 15:32:30'),(256,3,'order_status_updated','Order #15 status changed to: processing','::1','2026-04-03 15:32:45'),(257,31,'cancel_requested','Cancel request submitted for Order #15','::1','2026-04-03 15:32:53'),(258,3,'cancel_rejected','Cancel request #8 rejected.','::1','2026-04-03 15:33:33'),(259,3,'order_status_updated','Order #15 status changed to: shipped','::1','2026-04-03 15:33:49'),(260,3,'order_status_updated','Order #15 status changed to: delivered','::1','2026-04-03 15:34:07'),(261,31,'return_requested','Return request submitted for Order #15','::1','2026-04-03 15:35:18'),(262,3,'return_updated','Return request #13 updated to: approved','::1','2026-04-03 15:35:35'),(263,3,'stock_movement','Damaged product recorded: Porcelain Floor Tile 60x60cm (qty: 1) from Return #13','::1','2026-04-03 15:35:35'),(264,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-04 12:17:57'),(265,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-04 12:18:05'),(266,31,'order_created','Order #16 placed via gcash, total: Γé▒375.00','::1','2026-04-04 12:19:24'),(267,3,'order_status_updated','Order #16 status changed to: delivered','::1','2026-04-04 12:19:39'),(268,3,'order_status_updated','Order #16 status changed to: processing','::1','2026-04-04 12:20:04'),(269,3,'order_status_updated','Order #16 status changed to: delivered','::1','2026-04-04 12:24:59'),(270,3,'order_status_updated','Order #16 status changed to: pending','::1','2026-04-04 12:25:21'),(271,3,'order_status_updated','Order #16 status changed to: processing','::1','2026-04-04 12:25:31'),(272,3,'order_status_updated','Order #16 status changed to: shipped','::1','2026-04-04 12:25:39'),(273,3,'order_status_updated','Order #16 status changed to: delivered','::1','2026-04-04 12:25:46'),(274,31,'order_created','Order #17 placed via cod, total: Γé▒520.00','::1','2026-04-04 12:26:13'),(275,3,'order_status_updated','Order #17 status changed to: processing','::1','2026-04-04 12:26:30'),(276,3,'order_status_updated','Order #17 status changed to: shipped','::1','2026-04-04 12:26:38'),(277,3,'order_status_updated','Order #17 status changed to: delivered','::1','2026-04-04 12:26:46'),(278,31,'return_requested','Return request submitted for Order #17','::1','2026-04-04 12:37:42'),(279,3,'return_updated','Return request #14 updated to: approved','::1','2026-04-04 12:37:56'),(280,3,'stock_movement','Stock restored: Ceramic Wall Tile 30x60cm (qty: 1) from Return #14','::1','2026-04-04 12:37:56'),(281,31,'order_created','Order #18 placed via cod, total: Γé▒380.00','::1','2026-04-04 12:38:39'),(282,3,'order_status_updated','Order #18 status changed to: processing','::1','2026-04-04 12:38:51'),(283,3,'order_status_updated','Order #18 status changed to: pending','::1','2026-04-04 12:42:12'),(284,31,'order_cancelled','Order #18 (SHD-20260404-FBF3DB) cancelled by customer. Stock restored.','::1','2026-04-04 12:43:52'),(285,31,'order_created','Order #19 placed via cod, total: Γé▒380.00','::1','2026-04-04 12:44:17'),(286,3,'order_status_updated','Order #19 status changed to: processing','::1','2026-04-04 12:44:37'),(287,31,'cancel_requested','Cancel request submitted for Order #19','::1','2026-04-04 12:44:45'),(288,3,'cancel_approved','Cancel request #9 approved. Order #19 cancelled, stock restored.','::1','2026-04-04 12:45:46'),(289,31,'order_created','Order #20 placed via cod, total: Γé▒1,250.00','::1','2026-04-04 12:46:35'),(290,3,'order_status_updated','Order #20 status changed to: processing','::1','2026-04-04 12:46:51'),(291,3,'order_status_updated','Order #20 status changed to: shipped','::1','2026-04-04 12:47:00'),(292,3,'order_status_updated','Order #20 status changed to: delivered','::1','2026-04-04 12:47:10'),(293,31,'return_requested','Return request submitted for Order #20','::1','2026-04-04 12:47:26'),(294,3,'return_updated','Return request #15 updated to: approved','::1','2026-04-04 12:47:48'),(295,3,'stock_movement','Damaged product recorded: Granite Floor Tile 80x80cm (qty: 1) from Return #15','::1','2026-04-04 12:47:48'),(296,31,'order_created','Order #21 placed via cod, total: Γé▒375.00','::1','2026-04-04 12:48:44'),(297,3,'order_status_updated','Order #21 status changed to: delivered','::1','2026-04-04 12:49:01'),(298,31,'return_requested','Return request submitted for Order #21','::1','2026-04-04 12:49:14'),(299,3,'return_updated','Return request #16 updated to: approved','::1','2026-04-04 12:49:26'),(300,3,'stock_movement','Damaged product recorded: Porcelain Floor Tile 60x60cm (qty: 1) from Return #16','::1','2026-04-04 12:49:26'),(301,31,'order_created','Order #22 placed via gcash, total: Γé▒375.00','::1','2026-04-04 12:50:08'),(302,3,'order_status_updated','Order #22 status changed to: delivered','::1','2026-04-04 12:50:20'),(303,31,'return_requested','Return request submitted for Order #22','::1','2026-04-04 12:50:33'),(304,3,'return_updated','Return request #17 updated to: ','::1','2026-04-04 12:50:42'),(305,3,'return_updated','Return request #17 updated to: approved','::1','2026-04-04 12:54:08'),(306,3,'stock_movement','Stock restored: Porcelain Floor Tile 60x60cm (qty: 1) from Return #17','::1','2026-04-04 12:54:08'),(307,3,'return_updated','Return request #17 updated to: completed','::1','2026-04-04 12:58:11'),(308,3,'return_updated','Payment for Order #22 marked as refunded (Return #17)','::1','2026-04-04 12:58:11'),(309,3,'return_updated','Return request #16 updated to: completed','::1','2026-04-04 12:58:13'),(310,3,'return_updated','Payment for Order #21 marked as refunded (Return #16)','::1','2026-04-04 12:58:13'),(311,3,'return_updated','Return request #15 updated to: completed','::1','2026-04-04 12:58:14'),(312,3,'return_updated','Payment for Order #20 marked as refunded (Return #15)','::1','2026-04-04 12:58:14'),(313,3,'return_updated','Return request #14 updated to: completed','::1','2026-04-04 12:58:15'),(314,3,'return_updated','Payment for Order #17 marked as refunded (Return #14)','::1','2026-04-04 12:58:15'),(315,3,'return_updated','Return request #13 updated to: completed','::1','2026-04-04 12:58:16'),(316,3,'return_updated','Payment for Order #15 marked as refunded (Return #13)','::1','2026-04-04 12:58:16'),(317,3,'return_updated','Return request #12 updated to: completed','::1','2026-04-04 12:58:17'),(318,3,'return_updated','Payment for Order #6 marked as refunded (Return #12)','::1','2026-04-04 12:58:17'),(319,3,'return_updated','Return request #11 updated to: completed','::1','2026-04-04 12:58:18'),(320,3,'return_updated','Payment for Order #5 marked as refunded (Return #11)','::1','2026-04-04 12:58:18'),(321,3,'return_updated','Return request #10 updated to: completed','::1','2026-04-04 12:58:20'),(322,3,'return_updated','Payment for Order #2 marked as refunded (Return #10)','::1','2026-04-04 12:58:20'),(323,31,'order_created','Order #23 placed via gcash, total: Γé▒450.00','::1','2026-04-04 13:01:32'),(324,3,'order_status_updated','Order #23 status changed to: delivered','::1','2026-04-04 13:01:42'),(325,31,'return_requested','Return request submitted for Order #23','::1','2026-04-04 13:01:56'),(326,3,'user_logout','User logged out','::1','2026-04-04 13:04:40'),(327,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-04 13:04:53'),(328,1,'return_updated','Return request #18 updated to: approved','::1','2026-04-04 13:07:02'),(329,1,'stock_movement','Stock restored: Stainless Steel Bolt Set (100pc) (qty: 1) from Return #18','::1','2026-04-04 13:07:02'),(330,1,'return_updated','Return request #18 updated to: ','::1','2026-04-04 13:07:59'),(331,1,'return_updated','Return request #18 updated to: ','::1','2026-04-04 13:09:17'),(332,1,'return_updated','Return request #18 updated to: approved','::1','2026-04-04 13:11:15'),(333,1,'stock_movement','Stock restored: Stainless Steel Bolt Set (100pc) (qty: 1) from Return #18','::1','2026-04-04 13:11:15'),(334,1,'return_updated','Return request #18 updated to: completed','::1','2026-04-04 13:11:31'),(335,1,'return_updated','Payment for Order #23 marked as refunded (Return #18)','::1','2026-04-04 13:11:31'),(336,31,'order_created','Order #24 placed via gcash, total: Γé▒380.00','::1','2026-04-04 13:12:59'),(337,1,'order_status_updated','Order #24 status changed to: processing','::1','2026-04-04 13:13:14'),(338,1,'order_status_updated','Order #24 status changed to: shipped','::1','2026-04-04 13:13:23'),(339,1,'order_status_updated','Order #24 status changed to: delivered','::1','2026-04-04 13:13:51'),(340,31,'return_requested','Return request submitted for Order #24','::1','2026-04-04 13:14:01'),(341,1,'return_updated','Return request #19 updated to: approved','::1','2026-04-04 13:15:00'),(342,1,'stock_movement','Stock restored: Mosaic Glass Tile Sheet (qty: 1) from Return #19','::1','2026-04-04 13:15:00'),(343,31,'order_created','Order #25 placed via gcash, total: Γé▒520.00','::1','2026-04-04 13:16:30'),(344,1,'order_status_updated','Order #25 status changed to: delivered','::1','2026-04-04 13:16:41'),(345,31,'return_requested','Return request submitted for Order #25','::1','2026-04-04 13:16:53'),(346,1,'return_updated','Return request #19 updated to: completed','::1','2026-04-04 13:17:50'),(347,1,'return_updated','Payment for Order #24 marked as refunded (Return #19)','::1','2026-04-04 13:17:50'),(348,1,'return_updated','Return request #20 updated to: approved','::1','2026-04-04 13:17:56'),(349,1,'stock_movement','Damaged product recorded: Ceramic Wall Tile 30x60cm (qty: 1) from Return #20','::1','2026-04-04 13:17:56'),(350,31,'order_created','Order #26 placed via gcash, total: Γé▒380.00','::1','2026-04-04 13:27:45'),(351,1,'order_status_updated','Order #26 status changed to: delivered','::1','2026-04-04 13:28:10'),(352,31,'return_requested','Return request submitted for Order #26','::1','2026-04-04 13:28:24'),(353,1,'return_updated','Return request #20 updated to: completed','::1','2026-04-04 13:28:35'),(354,1,'return_updated','Payment for Order #25 marked as refunded (Return #20)','::1','2026-04-04 13:28:35'),(355,1,'return_updated','Return request #21 updated to: approved','::1','2026-04-04 13:28:56'),(356,1,'stock_movement','Damaged product recorded: Mosaic Glass Tile Sheet (qty: 1) from Return #21 ΓÇö not restored to inventory','::1','2026-04-04 13:28:56'),(357,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-04 15:32:59'),(358,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-06 17:21:36'),(359,31,'user_logout','User logged out','::1','2026-04-06 17:59:19'),(360,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-04-06 17:59:43'),(361,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-07 04:03:10'),(362,32,'user_logout','User logged out','::1','2026-04-07 04:04:07'),(363,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-07 04:04:19'),(364,1,'price_updated','Price updated for Porcelain Floor Tile 60x60cm (ID #19): Γé▒375.00 ΓåÆ Γé▒420.00','::1','2026-04-07 04:04:49'),(365,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-07 04:04:49'),(366,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-07 04:05:07'),(367,32,'user_logout','User logged out','::1','2026-04-07 04:29:59'),(368,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-07 04:30:06'),(369,3,'user_logout','User logged out','::1','2026-04-07 04:34:21'),(370,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-07 04:34:36'),(371,1,'return_updated','Return request #21 updated to: completed','::1','2026-04-07 04:39:01'),(372,1,'return_updated','Payment for Order #26 marked as refunded (Return #21)','::1','2026-04-07 04:39:01'),(373,1,'user_logout','User logged out','::1','2026-04-07 04:56:08'),(374,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-07 04:56:16'),(375,31,'user_logout','User logged out','::1','2026-04-07 05:05:12'),(376,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-07 05:05:19'),(377,32,'user_logout','User logged out','::1','2026-04-07 05:06:43'),(378,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-07 05:06:56'),(379,3,'user_logout','User logged out','::1','2026-04-07 05:23:26'),(380,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-07 05:23:31'),(381,32,'user_logout','User logged out','::1','2026-04-07 05:31:29'),(382,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-07 05:31:36'),(383,1,'user_logout','User logged out','::1','2026-04-07 05:37:33'),(384,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-07 05:38:00'),(385,31,'user_logout','User logged out','::1','2026-04-07 05:38:08'),(386,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-11 13:43:44'),(387,NULL,'user_created','New customer registered: kramdreyan@gmail.com','::1','2026-04-11 13:49:34'),(388,33,'user_login','User logged in: kramdreyan@gmail.com','::1','2026-04-11 13:50:10'),(389,33,'order_created','Order #27 placed via card, total: Γé▒380.00','::1','2026-04-11 13:52:09'),(390,33,'user_logout','User logged out','::1','2026-04-11 14:01:54'),(391,32,'user_login','User logged in: bulantoy@burnok.com','::1','2026-04-11 14:02:31'),(392,32,'stock_movement','Inventory updated for Ceramic Wall Tile 30x60cm (ID #20): 48 ΓåÆ 20. Reason: Manual stock update','::1','2026-04-11 14:06:53'),(393,32,'stock_movement','Inventory updated for Cabinet Door Hinges (Pair) (ID #2): 50 ΓåÆ 10. Reason: Manual stock update','::1','2026-04-11 14:07:02'),(394,32,'stock_movement','Inventory updated for Granite Floor Tile 80x80cm (ID #22): 122 ΓåÆ 0. Reason: Manual stock update','::1','2026-04-11 14:07:17'),(395,3,'user_logout','User logged out','::1','2026-04-11 14:09:29'),(396,1,'user_login','User logged in: admin@southdev.com','::1','2026-04-11 14:09:42'),(397,1,'user_updated','User #29 active status toggled','::1','2026-04-11 14:18:09'),(398,1,'user_updated','User #29 active status toggled','::1','2026-04-11 14:19:20'),(399,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-13 15:23:49'),(400,3,'user_login','User logged in: staff@southdev.com','::1','2026-04-14 13:56:07'),(401,NULL,'user_created','New customer registered: natzumekirito@gmail.com','::1','2026-04-16 07:16:12'),(402,NULL,'user_created','New customer registered: marksxkingsx@gmail.com','::1','2026-04-16 07:17:02'),(403,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-16 07:18:26'),(404,31,'user_logout','User logged out','::1','2026-04-16 07:18:40'),(405,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-16 07:22:51'),(406,1,'user_login','Staff logged in: admin@southdev.com','::1','2026-04-16 15:42:33'),(407,1,'product_updated','Product updated: Porcelain Floor Tile 60x60cm (ID #19)','::1','2026-04-16 15:48:26'),(408,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-16 15:48:38'),(409,31,'user_logout','User logged out','::1','2026-04-16 15:48:55'),(410,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-18 14:45:54'),(411,31,'user_logout','User logged out','::1','2026-04-18 14:47:37'),(412,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-04-18 14:48:11'),(413,3,'order_status_updated','Order #27 status changed to: delivered','::1','2026-04-18 14:49:43'),(414,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-20 02:48:50'),(415,31,'order_created','Order #28 placed via gcash, total: Γé▒3,160.00','::1','2026-04-20 02:58:22'),(416,31,'order_cancelled','Order #28 (SHD-20260420-E8759E) cancelled by customer. Stock restored.','::1','2026-04-20 02:58:31'),(417,31,'order_created','Order #29 placed via gcash, total: Γé▒420.00','::1','2026-04-20 03:02:04'),(418,31,'order_created','Order #30 placed via gcash, total: Γé▒280.00','::1','2026-04-20 03:04:10'),(419,31,'payment_processed','PayMongo gcash source created for Order #SHD-20260420-A66917 (Γé▒280.00)','::1','2026-04-20 03:06:37'),(420,31,'order_created','Order #31 placed via gcash, total: Γé▒280.00','::1','2026-04-20 03:10:28'),(421,31,'payment_processed','PayMongo gcash source created for Order #SHD-20260420-422126 (Γé▒280.00)','::1','2026-04-20 03:10:29'),(422,3,'user_login','Staff logged in: staff@southdev.com','::1','2026-04-20 03:13:36'),(423,3,'return_updated','Return request #9 updated to: completed','::1','2026-04-20 03:13:59'),(424,3,'return_updated','Payment for Order #23 marked as refunded (Return #9)','::1','2026-04-20 03:13:59'),(425,31,'user_logout','User logged out','::1','2026-04-20 03:42:31'),(426,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-20 03:45:37'),(427,31,'user_logout','User logged out','::1','2026-04-20 03:54:52'),(428,31,'user_login','User logged in: markandreyperez@gmail.com','::1','2026-04-20 03:56:04'),(429,31,'order_created','Order #32 placed via card, total: Γé▒380.00','::1','2026-04-20 04:08:10'),(430,31,'payment_processed','PayMongo card Payment Intent created for Order #SHD-20260420-9F1D8B (Γé▒380.00)','::1','2026-04-20 04:08:11'),(431,31,'order_created','Order #33 placed via card, total: Γé▒280.00','::1','2026-04-20 04:12:06'),(432,31,'payment_processed','PayMongo card Payment Intent created for Order #SHD-20260420-604101 (Γé▒280.00)','::1','2026-04-20 04:12:07'),(433,31,'order_created','Order #34 placed via card, total: Γé▒420.00','::1','2026-04-20 04:17:25'),(434,31,'payment_processed','PayMongo card Payment Intent created for Order #SHD-20260420-594E98 (Γé▒420.00)','::1','2026-04-20 04:17:26'),(435,31,'payment_processed','Card payment succeeded for Order #SHD-20260420-594E98','::1','2026-04-20 04:19:37');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,19,1,850.01,850.01),(2,2,20,1,520.00,520.00),(3,3,2,1,185.00,185.00),(4,4,19,5,850.00,4250.00),(5,5,10,1,65.00,65.00),(6,6,3,1,320.00,320.00),(7,7,10,1,65.00,65.00),(8,8,3,1,320.00,320.00),(9,8,20,1,520.00,520.00),(10,9,3,1,320.00,320.00),(11,9,2,1,185.00,185.00),(12,9,20,1,520.00,520.00),(13,10,9,1,4200.00,4200.00),(14,10,8,1,195.00,195.00),(15,11,3,1,320.00,320.00),(16,11,2,1,185.00,185.00),(17,11,21,1,380.00,380.00),(18,12,19,1,375.00,375.00),(19,13,20,1,520.00,520.00),(20,14,19,1,375.00,375.00),(21,15,19,1,375.00,375.00),(22,16,19,1,375.00,375.00),(23,17,20,1,520.00,520.00),(24,18,21,1,380.00,380.00),(25,19,21,1,380.00,380.00),(26,20,22,1,1250.00,1250.00),(27,21,19,1,375.00,375.00),(28,22,19,1,375.00,375.00),(29,23,1,1,450.00,450.00),(30,24,21,1,380.00,380.00),(31,25,20,1,520.00,520.00),(32,26,21,1,380.00,380.00),(33,27,21,1,380.00,380.00),(34,28,23,2,450.00,900.00),(35,28,20,2,520.00,1040.00),(36,28,21,1,380.00,380.00),(37,28,19,2,420.00,840.00),(38,29,19,1,420.00,420.00),(39,30,4,1,280.00,280.00),(40,31,4,1,280.00,280.00),(41,32,21,1,380.00,380.00),(42,33,4,1,280.00,280.00),(43,34,19,1,420.00,420.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,29,'SHD-20260326-98673A',850.01,'delivered','TEST, Brgy. Baguio, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-03-26 05:58:33','2026-03-26 05:58:51'),(2,31,'SHD-20260329-03074D',520.00,'delivered','test, Brgy. Bago Aplaya, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-03-28 16:55:12','2026-03-28 16:55:33'),(3,31,'SHD-20260329-EB5A28',185.00,'cancelled','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','','Order placed by mistake','2026-03-28 16:57:18','2026-03-28 16:58:17'),(4,31,'SHD-20260401-E0CDF7',4250.00,'cancelled','TEST, Brgy. Agdao, Davao City','Davao City','Davao del Sur','8000','','Need to change delivery address','2026-04-01 12:24:30','2026-04-01 12:52:05'),(5,31,'SHD-20260401-EE3BFB',65.00,'delivered','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-01 12:52:46','2026-04-01 12:55:10'),(6,31,'SHD-20260401-4B3D8E',320.00,'delivered','ok, Brgy. Baguio, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-01 15:12:52','2026-04-01 15:13:11'),(7,31,'SHD-20260401-4721DD',65.00,'cancelled','test, Brgy. Bangkas Heights, Davao City','Davao City','Davao del Sur','8000','','Wrong products ordered','2026-04-01 15:32:04','2026-04-01 15:32:21'),(8,31,'SHD-20260401-CEBB50',840.00,'cancelled','test, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','','Wrong products ordered','2026-04-01 15:33:32','2026-04-01 15:33:41'),(9,31,'SHD-20260401-65D2A8',1025.00,'cancelled','test, Brgy. Balengaeng, Davao City','Davao City','Davao del Sur','8000','','Need to change delivery address','2026-04-01 15:34:14','2026-04-01 15:44:12'),(10,31,'SHD-20260401-78DD6F',4395.00,'cancelled','test, Brgy. Bangkas Heights, Davao City','Davao City','Davao del Sur','8000','','Wrong products ordered','2026-04-01 15:45:11','2026-04-01 15:48:35'),(11,31,'SHD-20260401-19AD89',885.00,'shipped','ok, Brgy. Bago Gallera, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-01 15:54:09','2026-04-01 16:10:06'),(12,31,'SHD-20260403-22EBC4',375.00,'delivered','test, Brgy. Baguio, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-03 14:56:50','2026-04-03 15:01:42'),(13,31,'SHD-20260403-463812',520.00,'cancelled','test, Brgy. Baracatan, Davao City','Davao City','Davao del Sur','8000','test','Wrong delivery address','2026-04-03 15:08:52','2026-04-03 15:31:34'),(14,31,'SHD-20260403-C2FDCF',375.00,'delivered','test, Brgy. Bago Oshiro, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-03 15:17:00','2026-04-03 15:31:02'),(15,31,'SHD-20260403-E119CD',375.00,'delivered','test, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-03 15:32:30','2026-04-03 15:34:07'),(16,31,'SHD-20260404-C58537',375.00,'delivered','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 12:19:24','2026-04-04 12:25:46'),(17,31,'SHD-20260404-5B9883',520.00,'delivered','tst, Brgy. Alfonso Angliongto Sr., Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 12:26:13','2026-04-04 12:26:46'),(18,31,'SHD-20260404-FBF3DB',380.00,'cancelled','test, Brgy. Bangkas Heights, Davao City','Davao City','Davao del Sur','8000','','Wrong products ordered','2026-04-04 12:38:39','2026-04-04 12:43:52'),(19,31,'SHD-20260404-162279',380.00,'cancelled','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','','Need to change delivery address','2026-04-04 12:44:17','2026-04-04 12:45:46'),(20,31,'SHD-20260404-B64C55',1250.00,'delivered','test, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 12:46:35','2026-04-04 12:47:10'),(21,31,'SHD-20260404-C6E769',375.00,'delivered','test, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','test',NULL,'2026-04-04 12:48:44','2026-04-04 12:49:01'),(22,31,'SHD-20260404-0D29FE',375.00,'delivered','teest, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 12:50:08','2026-04-04 12:50:20'),(23,31,'SHD-20260404-CB74B9',450.00,'delivered','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 13:01:32','2026-04-04 13:01:42'),(24,31,'SHD-20260404-B4531B',380.00,'delivered','test, Brgy. Baliok, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 13:12:59','2026-04-04 13:13:51'),(25,31,'SHD-20260404-ECDCE5',520.00,'delivered','test, Brgy. Balengaeng, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 13:16:30','2026-04-04 13:16:41'),(26,31,'SHD-20260404-12721D',380.00,'delivered','resr, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-04 13:27:45','2026-04-04 13:28:10'),(27,33,'SHD-20260411-9C0B92',380.00,'delivered','Test, Brgy. Bangkal, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-11 13:52:09','2026-04-18 14:49:43'),(28,31,'SHD-20260420-E8759E',3160.00,'cancelled','Katipunan\r\nKatipunan, Brgy. Balengaeng, Davao City','Davao City','Davao del Sur','','','Need to change delivery address','2026-04-20 02:58:22','2026-04-20 02:58:31'),(29,31,'SHD-20260420-C52375',420.00,'pending','Katipunan\r\nKatipunan, Brgy. Alfonso Angliongto Sr., Davao City','Davao City','Davao del Sur','','',NULL,'2026-04-20 03:02:04','2026-04-20 03:02:04'),(30,31,'SHD-20260420-A66917',280.00,'pending','test, Brgy. Balengaeng, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-20 03:04:10','2026-04-20 04:26:53'),(31,31,'SHD-20260420-422126',280.00,'pending','test, Brgy. Balengaeng, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-20 03:10:28','2026-04-20 04:26:53'),(32,31,'SHD-20260420-9F1D8B',380.00,'pending','test, Brgy. Baguio, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-20 04:08:09','2026-04-20 04:08:09'),(33,31,'SHD-20260420-604101',280.00,'pending','test, Brgy. Alejandro Navarro (Linoan), Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-20 04:12:06','2026-04-20 04:12:06'),(34,31,'SHD-20260420-594E98',420.00,'pending','test, Brgy. Alambre, Davao City','Davao City','Davao del Sur','8000','',NULL,'2026-04-20 04:17:25','2026-04-20 04:26:53');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_unique` (`token`),
  KEY `email_idx` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `source_id` varchar(255) DEFAULT NULL,
  `client_key` varchar(512) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_source_id` (`source_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'cod',NULL,NULL,NULL,850.01,'completed','2026-04-01 15:28:00'),(2,2,'cod',NULL,NULL,NULL,520.00,'refunded','2026-04-01 15:28:00'),(3,3,'cod',NULL,NULL,NULL,185.00,'completed','2026-04-01 15:28:00'),(4,4,'cod',NULL,NULL,NULL,4250.00,'completed','2026-04-01 15:28:00'),(5,5,'cod',NULL,NULL,NULL,65.00,'refunded','2026-04-01 15:28:00'),(6,6,'cod',NULL,NULL,NULL,320.00,'refunded','2026-04-01 15:28:00'),(7,7,'gcash',NULL,NULL,NULL,65.00,'pending','2026-04-01 15:32:04'),(8,8,'cod',NULL,NULL,NULL,840.00,'completed','2026-04-01 15:33:32'),(9,9,'gcash',NULL,NULL,NULL,1025.00,'pending','2026-04-01 15:34:14'),(10,10,'gcash',NULL,NULL,NULL,4395.00,'pending','2026-04-01 15:45:11'),(11,11,'card',NULL,NULL,NULL,885.00,'pending','2026-04-01 15:54:09'),(12,12,'card',NULL,NULL,NULL,375.00,'pending','2026-04-03 14:56:50'),(13,13,'gcash',NULL,NULL,NULL,520.00,'pending','2026-04-03 15:08:52'),(14,14,'gcash',NULL,NULL,NULL,375.00,'pending','2026-04-03 15:17:00'),(15,15,'cod',NULL,NULL,NULL,375.00,'refunded','2026-04-03 15:32:30'),(16,16,'gcash',NULL,NULL,NULL,375.00,'pending','2026-04-04 12:19:24'),(17,17,'cod',NULL,NULL,NULL,520.00,'refunded','2026-04-04 12:26:13'),(18,18,'cod',NULL,NULL,NULL,380.00,'completed','2026-04-04 12:38:39'),(19,19,'cod',NULL,NULL,NULL,380.00,'completed','2026-04-04 12:44:17'),(20,20,'cod',NULL,NULL,NULL,1250.00,'refunded','2026-04-04 12:46:35'),(21,21,'cod',NULL,NULL,NULL,375.00,'refunded','2026-04-04 12:48:44'),(22,22,'gcash',NULL,NULL,NULL,375.00,'refunded','2026-04-04 12:50:08'),(23,23,'gcash',NULL,NULL,NULL,450.00,'refunded','2026-04-04 13:01:32'),(24,24,'gcash',NULL,NULL,NULL,380.00,'refunded','2026-04-04 13:12:59'),(25,25,'gcash',NULL,NULL,NULL,520.00,'refunded','2026-04-04 13:16:30'),(26,26,'gcash',NULL,NULL,NULL,380.00,'refunded','2026-04-04 13:27:45'),(27,27,'card',NULL,NULL,NULL,380.00,'pending','2026-04-11 13:52:09'),(28,28,'gcash',NULL,NULL,NULL,3160.00,'pending','2026-04-20 02:58:22'),(29,29,'gcash',NULL,NULL,NULL,420.00,'pending','2026-04-20 03:02:04'),(30,30,'gcash',NULL,'src_DCfCWPUhzufjbBPvm5Yd18Ji',NULL,280.00,'completed','2026-04-20 03:04:10'),(31,31,'gcash',NULL,'src_eDV84K6DrV7cmUSfaYdgAQ2m',NULL,280.00,'completed','2026-04-20 03:10:28'),(32,32,'card',NULL,'pi_Y5cxRqjf1q7wZxdrMeuM2jnp','pi_Y5cxRqjf1q7wZxdrMeuM2jnp_client_AHrppNPbMYCAHR1wELSv6C7D',380.00,'pending','2026-04-20 04:08:10'),(33,33,'card',NULL,'pi_oAyLL2JM4jSz6VmSGizKVYck','pi_oAyLL2JM4jSz6VmSGizKVYck_client_Pp1zTGbkBEbKx6c2UYT1aaPr',280.00,'pending','2026-04-20 04:12:06'),(34,34,'card','pi_BtVjL9iWXAXVDARc5eV4nzBd','pi_BtVjL9iWXAXVDARc5eV4nzBd','pi_BtVjL9iWXAXVDARc5eV4nzBd_client_Vg6BUq33Qt7tmVB29BEhqMgh',420.00,'completed','2026-04-20 04:17:25');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `price_history`
--

DROP TABLE IF EXISTS `price_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ph_product` (`product_id`),
  KEY `idx_ph_date` (`created_at`),
  CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `price_history`
--

LOCK TABLES `price_history` WRITE;
/*!40000 ALTER TABLE `price_history` DISABLE KEYS */;
INSERT INTO `price_history` VALUES (1,19,850.00,0.00,1,'','2026-04-01 16:40:12'),(2,19,0.00,375.00,1,'','2026-04-01 16:44:24'),(3,19,375.00,420.00,1,'','2026-04-07 04:04:49');
/*!40000 ALTER TABLE `price_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Stainless Steel Bolt Set (100pc)','Heavy-duty stainless steel bolts, assorted sizes M6-M12',450.00,NULL,NULL,'HW-BLT-001',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(2,1,'Cabinet Door Hinges (Pair)','Soft-close cabinet hinges, brushed nickel finish',185.00,NULL,NULL,'HW-HNG-002',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(3,1,'Padlock 50mm Heavy Duty','Weather-resistant laminated steel padlock',320.00,NULL,NULL,'HW-PLK-003',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(4,2,'Portland Cement 40kg','Type I general-purpose Portland cement',280.00,NULL,NULL,'CM-CEM-001',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(5,2,'Plywood 4x8 Marine Grade','3/4 inch marine-grade plywood sheet',1250.00,NULL,NULL,'CM-PLY-002',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(6,2,'GI Corrugated Roof Sheet','Gauge 26, 8ft length galvanized iron roofing',385.00,NULL,NULL,'CM-ROF-003',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(7,3,'Cordless Drill 20V','Lithium-ion cordless drill with 2 batteries and charger',3500.00,NULL,NULL,'TL-DRL-001',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(8,3,'Measuring Tape 7.5m','Professional-grade steel measuring tape with auto-lock',195.00,NULL,NULL,'TL-MSR-002',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(9,3,'Circular Saw 7-1/4\"','1400W circular saw with carbide-tipped blade',4200.00,NULL,NULL,'TL-SAW-003',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(10,4,'PVC Pipe 1/2\" (10ft)','Schedule 40 PVC pressure pipe',65.00,NULL,NULL,'PL-PVC-001',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(11,4,'Kitchen Faucet Single Handle','Chrome-plated brass kitchen faucet with sprayer',1850.00,NULL,NULL,'PL-FCT-002',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(12,4,'Teflon Tape 1/2\"x10m','PTFE thread seal tape for pipe connections',25.00,NULL,NULL,'PL-TFL-003',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(13,5,'THHN Wire #12 (75m)','Stranded copper THHN wire, 75 meters',2800.00,NULL,NULL,'EL-WIR-001',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(14,5,'LED Panel Light 18W','Surface-mount 18W LED panel, daylight 6500K',450.00,NULL,NULL,'EL-LED-002',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(15,5,'Circuit Breaker 30A','Bolt-on type single-pole circuit breaker',380.00,NULL,NULL,'EL-BRK-003',1,'2026-02-25 12:30:02','2026-02-25 12:30:02'),(16,2,'Stainless Steel Bolt Set (100pc)','',3000.00,NULL,NULL,'HW-001',1,'2026-02-28 08:12:53','2026-02-28 08:12:53'),(17,6,'BULL DOG','',0.06,NULL,NULL,'HW-00595',0,'2026-02-28 15:37:22','2026-03-02 14:15:01'),(19,7,'Porcelain Floor Tile 60x60cm','TEST',420.00,NULL,'1773991790_69bcf76e90c5f_cropped_1773991790555.jpg','TL-POR-001',1,'2026-03-04 13:55:35','2026-04-16 15:48:26'),(20,7,'Ceramic Wall Tile 30x60cm','Glossy ceramic wall tile, white subway style, 8pcs per box (1.44 sqm)',520.00,NULL,'1773991920_69bcf7f0d4588_cropped_1773991920817.jpg','TL-CER-002',1,'2026-03-04 13:55:35','2026-03-20 07:32:00'),(21,7,'Mosaic Glass Tile Sheet','Decorative glass mosaic sheet 30x30cm, mixed blue/green tones',380.00,NULL,'1773991991_69bcf837f317f_cropped_1773991991947.jpg','TL-MOS-003',1,'2026-03-04 13:55:35','2026-03-20 07:33:12'),(22,7,'Granite Floor Tile 80x80cm','Heavy-duty polished granite tile, dark grey, 3pcs per box (1.92 sqm)',1250.00,NULL,'1773992156_69bcf8dcc56ff_cropped_1773992156770.jpg','TL-GRN-004',1,'2026-03-04 13:55:35','2026-03-20 07:35:56'),(23,7,'Subway Tile 10x30cm (Box of 20)','Classic matte white subway tiles for kitchen/bathroom backsplash',450.00,NULL,'1773992314_69bcf97ac712b_cropped_1773992314694.jpg','TL-SUB-005',1,'2026-03-04 13:55:35','2026-03-20 07:38:34'),(24,7,'Outdoor Non-Slip Tile 40x40cm','Textured non-slip outdoor tile, terracotta finish, 6pcs per box',680.00,NULL,'1773992400_69bcf9d0a8f6f_cropped_1773992400098.jpg','TL-OUT-006',1,'2026-03-04 13:55:35','2026-03-20 07:40:00'),(25,7,'Vinyl Plank Tile 15x90cm','Wood-look vinyl plank tile, oak finish, click-lock install, 8pcs per box',720.00,NULL,'1773992504_69bcfa38bd656_cropped_1773992504670.jpg','TL-VNL-007',1,'2026-03-04 13:55:35','2026-03-20 07:41:44'),(26,7,'Hexagonal Cement Tile','Handcrafted cement hexagonal tile 20cm, assorted patterns',195.00,NULL,'1773992674_69bcfae220a8d_cropped_1773992674059.jpg','TL-HEX-008',1,'2026-03-04 13:55:35','2026-03-20 07:44:34'),(27,7,'Ceramic Tiles','',850.00,NULL,NULL,'HW-02321',0,'2026-03-04 15:09:06','2026-03-04 15:09:33'),(28,7,'Ceramic Tiles','',850.00,NULL,'1772636995_tiles.jpg','HW-0012',0,'2026-03-04 15:09:55','2026-03-09 03:26:29');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_requests`
--

DROP TABLE IF EXISTS `return_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `return_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `return_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_requests`
--

LOCK TABLES `return_requests` WRITE;
/*!40000 ALTER TABLE `return_requests` DISABLE KEYS */;
INSERT INTO `return_requests` VALUES (3,21,23,'Damaged item','pending','','2026-03-21 08:10:46','2026-04-04 12:53:48'),(4,21,23,'Damaged item','pending','','2026-03-21 08:10:53','2026-04-04 12:53:48'),(5,21,23,'Damaged item','pending','','2026-03-21 08:12:28','2026-04-04 12:53:48'),(6,21,23,'Damaged item','pending','','2026-03-21 08:12:30','2026-04-04 12:53:48'),(7,21,23,'Damaged item','pending','','2026-03-21 08:12:38','2026-04-04 12:53:48'),(8,21,23,'Damaged item','pending','','2026-03-21 08:14:06','2026-04-04 12:53:48'),(9,23,2,'Damaged item','completed','','2026-03-21 08:46:39','2026-04-20 03:13:59'),(10,2,31,'damage item','completed','','2026-03-28 16:55:53','2026-04-04 12:58:20'),(11,5,31,'Item arrived damaged or broken ΓÇö TEST DAMAGE','completed','','2026-04-01 14:28:43','2026-04-04 12:58:18'),(12,6,31,'Item arrived damaged or broken ΓÇö test damage','completed','','2026-04-01 15:13:31','2026-04-04 12:58:17'),(13,15,31,'Item arrived damaged or broken','completed','','2026-04-03 15:35:18','2026-04-04 12:58:16'),(14,17,31,'Received wrong item','completed','','2026-04-04 12:37:42','2026-04-04 12:58:15'),(15,20,31,'Item arrived damaged or broken ΓÇö test','completed','','2026-04-04 12:47:26','2026-04-04 12:58:14'),(16,21,31,'Item arrived damaged or broken ΓÇö test','completed','','2026-04-04 12:49:14','2026-04-04 12:58:13'),(17,22,31,'Received wrong item','completed','','2026-04-04 12:50:33','2026-04-04 12:58:11'),(18,23,31,'Item does not match description or photos','completed','','2026-04-04 13:01:56','2026-04-04 13:11:31'),(19,24,31,'Received wrong item','completed','','2026-04-04 13:14:01','2026-04-04 13:17:50'),(20,25,31,'Item arrived damaged or broken','completed','','2026-04-04 13:16:53','2026-04-04 13:28:35'),(21,26,31,'Item arrived damaged or broken','completed','','2026-04-04 13:28:24','2026-04-07 04:39:01');
/*!40000 ALTER TABLE `return_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (4,10,5,5,31,1,'pangit may damage','2026-04-01 14:53:59'),(5,19,12,18,31,1,'pangit rejected request ko','2026-04-03 15:02:43'),(6,19,15,21,31,5,'labley','2026-04-03 15:34:19');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'customer','2026-02-25 12:30:02'),(2,'staff','2026-02-25 12:30:02'),(3,'super_admin','2026-02-25 12:30:02'),(4,'inventory_incharge','2026-03-16 17:11:35');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` enum('purchase','sale','return','adjustment','initial') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sm_product` (`product_id`),
  KEY `idx_sm_type` (`type`),
  KEY `idx_sm_date` (`created_at`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,2,'adjustment',-20,NULL,'Manual stock update',1,'2026-03-17 07:38:53'),(2,2,'purchase',50,NULL,'Stock purchase/restock',27,'2026-03-17 15:09:38'),(3,20,'adjustment',31,NULL,'Manual stock update',27,'2026-03-17 15:11:00'),(6,2,'purchase',50,NULL,'Stock purchase/restock',27,'2026-03-21 08:21:31'),(7,2,'adjustment',100,NULL,'Manual stock update',1,'2026-03-23 06:11:01'),(8,2,'adjustment',-200,NULL,'Manual stock update',27,'2026-03-26 15:38:55'),(9,2,'adjustment',100,NULL,'Manual stock update',3,'2026-03-26 16:09:45'),(10,2,'adjustment',100,NULL,'Manual stock update',3,'2026-03-26 16:10:31'),(11,2,'adjustment',200,NULL,'Manual stock update',3,'2026-03-26 16:11:29'),(12,2,'purchase',50,NULL,'Stock purchase/restock',3,'2026-03-26 16:13:26'),(13,13,'purchase',300,NULL,'Stock purchase/restock',32,'2026-03-28 14:25:49'),(14,2,'adjustment',-430,NULL,'Manual stock update',3,'2026-04-01 12:36:39'),(15,2,'adjustment',480,NULL,'Manual stock update',1,'2026-04-01 13:44:22'),(16,10,'adjustment',-1,11,'Damaged product from return #11: Item arrived damaged or broken ΓÇö TEST DAMAGE',1,'2026-04-01 14:31:46'),(17,3,'adjustment',-1,12,'Damaged product from return #12: Item arrived damaged or broken ΓÇö test damage',1,'2026-04-01 15:13:52'),(18,2,'adjustment',-499,NULL,'Manual stock update',3,'2026-04-03 07:05:57'),(19,19,'adjustment',-270,NULL,'Manual stock update',3,'2026-04-03 07:07:04'),(20,2,'purchase',50,NULL,'Stock purchase/restock',3,'2026-04-03 14:48:05'),(21,19,'adjustment',100,NULL,'Manual stock update',32,'2026-04-03 14:49:20'),(22,19,'adjustment',-1,13,'Damaged product from return #13: Item arrived damaged or broken',3,'2026-04-03 15:35:35'),(23,20,'return',1,14,'Return approved (non-damaged) from return #14',3,'2026-04-04 12:37:56'),(24,22,'adjustment',-1,15,'Damaged product from return #15: Item arrived damaged or broken ΓÇö test',3,'2026-04-04 12:47:48'),(25,19,'adjustment',-1,16,'Damaged product from return #16: Item arrived damaged or broken ΓÇö test',3,'2026-04-04 12:49:26'),(26,19,'return',1,17,'Return approved (non-damaged) from return #17',3,'2026-04-04 12:54:08'),(27,1,'return',1,18,'Return approved (non-damaged) from return #18',1,'2026-04-04 13:07:02'),(28,1,'return',1,18,'Return approved (non-damaged) from return #18',1,'2026-04-04 13:11:15'),(29,21,'return',1,19,'Return approved (non-damaged) from return #19',1,'2026-04-04 13:15:00'),(30,20,'adjustment',-1,20,'Damaged product from return #20: Item arrived damaged or broken',1,'2026-04-04 13:17:56'),(31,20,'adjustment',-28,NULL,'Manual stock update',32,'2026-04-11 14:06:53'),(32,2,'adjustment',-40,NULL,'Manual stock update',32,'2026-04-11 14:07:02'),(33,22,'adjustment',-122,NULL,'Manual stock update',32,'2026-04-11 14:07:17');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_requests`
--

DROP TABLE IF EXISTS `supplier_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `requested_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','ordered','received','cancelled') DEFAULT 'pending',
  `notes` varchar(500) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `requested_by` (`requested_by`),
  KEY `idx_sr_product` (`product_id`),
  KEY `idx_sr_status` (`status`),
  CONSTRAINT `supplier_requests_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_requests`
--

LOCK TABLES `supplier_requests` WRITE;
/*!40000 ALTER TABLE `supplier_requests` DISABLE KEYS */;
INSERT INTO `supplier_requests` VALUES (1,20,20,'pending','',1,'2026-03-16 18:42:35','2026-03-16 18:42:35'),(2,20,100,'pending','',1,'2026-03-16 18:42:52','2026-03-16 18:42:52'),(3,20,50,'pending','',NULL,'2026-03-17 15:09:53','2026-03-17 15:09:53'),(4,2,100,'pending','',NULL,'2026-03-26 15:46:17','2026-03-26 15:46:17'),(5,2,200,'pending','',3,'2026-03-26 16:09:32','2026-03-26 16:09:32');
/*!40000 ALTER TABLE `supplier_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL DEFAULT 1,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `otp_attempts` int(11) NOT NULL DEFAULT 0,
  `otp_locked_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  KEY `idx_users_verification_token` (`verification_token`),
  KEY `idx_users_username` (`username`(50)),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,3,'Super','Admin','admin','admin@southdev.com','$2y$10$.V0klajRNbmyNoV2/dCapuM74lu5vJDqSmO/k4tuLqlOPlL69a3y.','09123456789',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-25 12:30:11',NULL,NULL,NULL,0,NULL,1,'2026-02-25 12:30:02','2026-03-28 14:14:43'),(2,1,'Demo','Customer','customer','customer@southdev.com','$2y$10$RqM/771TdsvDDgKRLIXCXugesuj/6BJDRaCFsL8O1WH7WikylwIvS','',NULL,'','','','','u2_1772635914.jpg','2026-02-25 12:30:11',NULL,NULL,NULL,0,NULL,1,'2026-02-25 12:30:11','2026-04-01 17:24:41'),(3,2,'Demo','Staff','staff','staff@southdev.com','$2y$10$DwHgI.dsVbPFcH//foA25.D.gPJ7DT.lYhAUD9eyoUb15nJUGuO.C','09123456781',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-25 12:30:11',NULL,NULL,NULL,0,NULL,1,'2026-02-25 12:30:11','2026-03-28 15:52:46'),(29,1,'Mark Andrey','Perez','markandreyperez','','$2y$10$7Iln0y..PEE.gpcLwsMIuuPBUgXuuOEg5iXhBZIBpls0BZGJTk3xG','',NULL,'','','','','u29_1774710692.png','2026-03-23 05:27:14',NULL,NULL,NULL,0,NULL,0,'2026-03-23 05:26:43','2026-04-11 14:19:20'),(31,1,'Kuramu','Doreyan','marksxkingsx','markandreyperez@gmail.com','$2y$10$rJIBEndFLBPQc/nfBUWG5OJY2LLrrJeHnl0.kBGOdwHR3gK5jVfwC','09123456789','1999-06-07','Test','Davao City','Davao Del Sur','8000','u31_1776523592.jpg','2026-03-28 13:49:51',NULL,NULL,NULL,0,NULL,1,'2026-03-28 13:49:08','2026-04-18 14:46:32'),(32,4,'Bulantoy','Burnok','bulantoy','bulantoy@burnok.com','$2y$10$d/cibPlFDZGbhTycg5nuwO.BtzXHmT8LXNGAxHCMDs/y/jhfKvMHG','',NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-28 13:58:52',NULL,NULL,NULL,0,NULL,1,'2026-03-28 13:58:52','2026-03-28 14:14:43'),(33,1,'Mark Andrey','Perez','kramdreyan','kramdreyan@gmail.com','$2y$10$sUOYAtjCnuEsEfN6hab/HuN3YBoMMkuYYILUwy54LB68pamvDp5ly','09123456789','2000-06-07','Test','Davao','Davao Del Sur','1000',NULL,'2026-04-11 13:50:03',NULL,NULL,NULL,0,NULL,1,'2026-04-11 13:49:34','2026-04-11 13:50:40'),(34,1,'Errant','Knight','errantknight','natzumekirito@gmail.com','$2y$10$Lfr4mE8ogwSMJtGLBMscF.XtYfPpEWPGGBhFlOCh/yHQWDd8OJ22C','09123456789','1999-06-07',NULL,NULL,NULL,NULL,NULL,NULL,'b7f78427057d8afa20d8378b70635c5b443f20d67ea88b3dce324991fb142823','100546','2026-04-16 07:21:12',0,NULL,1,'2026-04-16 07:16:12','2026-04-16 07:16:12'),(35,1,'Errant','Knight','kramdeyan','marksxkingsx@gmail.com','$2y$10$ZpTVX8x4WFT1BPLVqm.fL.9BdOTJv9hL9altAhWBk1Bbmm3eBzWue','09123456789','1999-06-07',NULL,NULL,NULL,NULL,NULL,NULL,'5c1ff6f490126ad0fa3301d700ba2220d674a7128fe5351639994be9735c7716','532837','2026-04-16 07:22:02',0,NULL,1,'2026-04-16 07:17:02','2026-04-16 07:17:02');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-20 12:44:33
