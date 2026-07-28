-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: sohashova_chhatri_nibas
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `brand_categories`
--

DROP TABLE IF EXISTS `brand_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brand_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_categories`
--

LOCK TABLES `brand_categories` WRITE;
/*!40000 ALTER TABLE `brand_categories` DISABLE KEYS */;
INSERT INTO `brand_categories` VALUES (14,'Food & Drink','2026-04-29 05:53:33','2026-04-29 05:53:33'),(15,'Breakfast','2026-04-29 05:55:38','2026-04-29 05:55:38'),(16,'Lunch','2026-04-29 05:56:44','2026-04-29 05:56:44');
/*!40000 ALTER TABLE `brand_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (7,'Nescafe','2026-04-29 05:53:46','2026-04-29 05:53:46'),(8,'Lipton','2026-04-29 05:54:18','2026-04-29 05:54:18'),(9,'Olympic','2026-04-29 05:55:48','2026-04-29 05:55:48'),(10,'Lunch','2026-04-29 05:56:40','2026-04-29 05:56:40');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `made_by` bigint unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `made_by` (`user_id`),
  KEY `deposits_made_by_foreign` (`made_by`),
  CONSTRAINT `deposits_made_by_foreign` FOREIGN KEY (`made_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposits`
--

LOCK TABLES `deposits` WRITE;
/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_name_unique` (`name`),
  UNIQUE KEY `districts_bn_name_unique` (`bn_name`),
  KEY `districts_division_id_foreign` (`division_id`),
  CONSTRAINT `districts_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
INSERT INTO `districts` VALUES (1,1,'Narsingdi','αª¿αª░αª╕αª┐αªéαªªαºÇ'),(2,1,'Gazipur','αªùαª╛αª£αºÇαª¬αºüαª░'),(3,1,'Shariatpur','αª╢αª░αºÇαª»αª╝αªñαª¬αºüαª░'),(4,1,'Narayanganj','αª¿αª╛αª░αª╛αºƒαªúαªùαª₧αºìαª£'),(5,1,'Tangail','αªƒαª╛αªÖαºìαªùαª╛αªçαª▓'),(6,1,'Kishoreganj','αªòαª┐αª╢αºïαª░αªùαª₧αºìαª£'),(7,1,'Manikganj','αª«αª╛αª¿αª┐αªòαªùαª₧αºìαª£'),(8,1,'Dhaka','αªóαª╛αªòαª╛'),(9,1,'Munshiganj','αª«αºüαª¿αºìαª╕αª┐αªùαª₧αºìαª£'),(10,1,'Rajbari','αª░αª╛αª£αª¼αª╛αº£αºÇ'),(11,1,'Madaripur','αª«αª╛αªªαª╛αª░αºÇαª¬αºüαª░'),(12,1,'Gopalganj','αªùαºïαª¬αª╛αª▓αªùαª₧αºìαª£'),(13,1,'Faridpur','αª½αª░αª┐αªªαª¬αºüαª░'),(14,2,'Comilla','αªòαºüαª«αª┐αª▓αºìαª▓αª╛'),(15,2,'Feni','αª½αºçαª¿αºÇ'),(16,2,'Brahmanbaria','αª¼αºìαª░αª╛αª╣αºìαª«αªúαª¼αª╛αªíαª╝αª┐αª»αª╝αª╛'),(17,2,'Rangamati','αª░αª╛αªÖαºìαªùαª╛αª«αª╛αªƒαª┐'),(18,2,'Noakhali','αª¿αºïαª»αª╝αª╛αªûαª╛αª▓αºÇ'),(19,2,'Chandpur','αªÜαª╛αªüαªªαª¬αºüαª░'),(20,2,'Lakshmipur','αª▓αªòαºìαª╖αºìαª«αºÇαª¬αºüαª░'),(21,2,'Chittagong','αªÜαªƒαºìαªƒαªùαºìαª░αª╛αª«'),(22,2,'Coxsbazar','αªòαªòαºìαª╕αª¼αª╛αª£αª╛αª░'),(23,2,'Khagrachhari','αªûαª╛αªùαº£αª╛αª¢αº£αª┐'),(24,2,'Bandarban','αª¼αª╛αª¿αºìαªªαª░αª¼αª╛αª¿'),(25,3,'Sirajganj','αª╕αª┐αª░αª╛αª£αªùαª₧αºìαª£'),(26,3,'Pabna','αª¬αª╛αª¼αª¿αª╛'),(27,3,'Bogra','αª¼αªùαºüαº£αª╛'),(28,3,'Rajshahi','αª░αª╛αª£αª╢αª╛αª╣αºÇ'),(29,3,'Natore','αª¿αª╛αªƒαºïαª░'),(30,3,'Joypurhat','αª£αºƒαª¬αºüαª░αª╣αª╛αªƒ'),(31,3,'Chapainawabganj','αªÜαª╛αªüαª¬αª╛αªçαª¿αª¼αª╛αª¼αªùαª₧αºìαª£'),(32,3,'Naogaon','αª¿αªôαªùαª╛αªü'),(33,4,'Jessore','αª»αª╢αºïαª░'),(34,4,'Satkhira','αª╕αª╛αªñαªòαºìαª╖αºÇαª░αª╛'),(35,4,'Meherpur','αª«αºçαª╣αºçαª░αª¬αºüαª░'),(36,4,'Narail','αª¿αªíαª╝αª╛αªçαª▓'),(37,4,'Chuadanga','αªÜαºüαºƒαª╛αªíαª╛αªÖαºìαªùαª╛'),(38,4,'Kushtia','αªòαºüαª╖αºìαªƒαª┐αºƒαª╛'),(39,4,'Magura','αª«αª╛αªùαºüαª░αª╛'),(40,4,'Khulna','αªûαºüαª▓αª¿αª╛'),(41,4,'Bagerhat','αª¼αª╛αªùαºçαª░αª╣αª╛αªƒ'),(42,4,'Jhenaidah','αª¥αª┐αª¿αª╛αªçαªªαª╣'),(43,5,'Jhalakathi','αª¥αª╛αª▓αªòαª╛αªáαª┐'),(44,5,'Patuakhali','αª¬αªƒαºüαºƒαª╛αªûαª╛αª▓αºÇ'),(45,5,'Pirojpur','αª¬αª┐αª░αºïαª£αª¬αºüαª░'),(46,5,'Barisal','αª¼αª░αª┐αª╢αª╛αª▓'),(47,5,'Bhola','αª¡αºïαª▓αª╛'),(48,5,'Barguna','αª¼αª░αªùαºüαª¿αª╛'),(49,6,'Panchagarh','αª¬αª₧αºìαªÜαªùαªíαª╝'),(50,6,'Dinajpur','αªªαª┐αª¿αª╛αª£αª¬αºüαª░'),(51,6,'Lalmonirhat','αª▓αª╛αª▓αª«αª¿αª┐αª░αª╣αª╛αªƒ'),(52,6,'Nilphamari','αª¿αºÇαª▓αª½αª╛αª«αª╛αª░αºÇ'),(53,6,'Gaibandha','αªùαª╛αªçαª¼αª╛αª¿αºìαªºαª╛'),(54,6,'Thakurgaon','αªáαª╛αªòαºüαª░αªùαª╛αªüαªô'),(55,6,'Rangpur','αª░αªéαª¬αºüαª░'),(56,6,'Kurigram','αªòαºüαº£αª┐αªùαºìαª░αª╛αª«'),(57,7,'Sylhet','αª╕αª┐αª▓αºçαªƒ'),(58,7,'Moulvibazar','αª«αºîαª▓αª¡αºÇαª¼αª╛αª£αª╛αª░'),(59,7,'Habiganj','αª╣αª¼αª┐αªùαª₧αºìαª£'),(60,7,'Sunamganj','αª╕αºüαª¿αª╛αª«αªùαª₧αºìαª£'),(61,8,'Sherpur','αª╢αºçαª░αª¬αºüαª░'),(62,8,'Mymensingh','αª«αºƒαª«αª¿αª╕αª┐αªéαª╣'),(63,8,'Jamalpur','αª£αª╛αª«αª╛αª▓αª¬αºüαª░'),(64,8,'Netrokona','αª¿αºçαªñαºìαª░αªòαºïαªúαª╛');
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisions_name_unique` (`name`),
  UNIQUE KEY `divisions_bn_name_unique` (`bn_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divisions`
--

LOCK TABLES `divisions` WRITE;
/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
INSERT INTO `divisions` VALUES (1,'Dhaka','αªóαª╛αªòαª╛'),(2,'Chittagong','αªÜαªƒαºìαªƒαªùαºìαª░αª╛αª«'),(3,'Rajshahi','αª░αª╛αª£αª╢αª╛αª╣αºÇ'),(4,'Khulna','αªûαºüαª▓αª¿αª╛'),(5,'Barisal','αª¼αª░αª┐αª╢αª╛αª▓'),(6,'Rangpur','αª░αªéαª¬αºüαª░'),(7,'Sylhet','αª╕αª┐αª▓αºçαªƒ'),(8,'Mymensingh','αª«αºƒαª«αª¿αª╕αª┐αªéαª╣');
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_types`
--

DROP TABLE IF EXISTS `expense_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_types`
--

LOCK TABLES `expense_types` WRITE;
/*!40000 ALTER TABLE `expense_types` DISABLE KEYS */;
INSERT INTO `expense_types` VALUES (13,'Internet Bill','2026-04-30 00:58:36','2026-04-30 00:58:36'),(14,'Water Bill','2026-04-30 00:58:48','2026-04-30 00:58:48'),(15,'Electricity Bill','2026-04-30 00:58:56','2026-04-30 00:58:56'),(16,'Software Subscription','2026-04-30 00:59:30','2026-04-30 00:59:30'),(17,'Cleaning Expense','2026-04-30 00:59:54','2026-04-30 00:59:54'),(18,'Room Service Expenses','2026-04-30 01:00:31','2026-04-30 01:00:31');
/*!40000 ALTER TABLE `expense_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `expense_category` bigint unsigned NOT NULL,
  `expense_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expense_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_category_foreign` (`expense_category`),
  CONSTRAINT `expenses_expense_category_foreign` FOREIGN KEY (`expense_category`) REFERENCES `expense_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (86,'2026-05-16',15,'electricity bill',250.00,'2026-05-16 00:44:12','2026-05-16 00:44:12'),(87,'2026-05-16',13,'internet bill',1000.00,'2026-05-16 01:01:33','2026-05-16 01:01:33'),(88,'2026-05-17',15,'electricity bill',35000.00,'2026-05-17 05:29:36','2026-05-17 05:29:36'),(89,'2026-07-27',17,'test',2000.00,'2026-07-27 01:26:40','2026-07-27 01:26:40'),(90,'2026-07-27',13,'test',600.00,'2026-07-27 01:27:21','2026-07-27 01:27:21'),(91,'2026-07-27',17,NULL,200.00,'2026-07-27 02:01:25','2026-07-27 02:01:25'),(92,'2026-07-27',13,NULL,600.00,'2026-07-27 02:01:25','2026-07-27 02:01:25');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fines`
--

DROP TABLE IF EXISTS `fines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `imposed_by` bigint unsigned NOT NULL,
  `replace_user_id` bigint unsigned NOT NULL,
  `type` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imposed_by` (`user_id`),
  KEY `fines_imposed_by_foreign` (`imposed_by`),
  CONSTRAINT `fines_imposed_by_foreign` FOREIGN KEY (`imposed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fines_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fines`
--

LOCK TABLES `fines` WRITE;
/*!40000 ALTER TABLE `fines` DISABLE KEYS */;
/*!40000 ALTER TABLE `fines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `floors`
--

DROP TABLE IF EXISTS `floors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `floors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `floors`
--

LOCK TABLES `floors` WRITE;
/*!40000 ALTER TABLE `floors` DISABLE KEYS */;
INSERT INTO `floors` VALUES (33,'floor_1772687876_69a9120466966.jfif','frist floor','2026-03-04 23:17:56','2026-03-09 03:37:52'),(34,'floor_1772693846.jpg','Second Floor','2026-03-04 23:18:36','2026-03-09 03:39:06'),(35,'floor_1772693980_69a929dcc2870.jpeg','Third Floor','2026-03-05 00:59:40','2026-03-09 03:39:44'),(37,'floor_1772958747_69ad341b09823.jpg','Fifth Floor','2026-03-08 02:32:27','2026-03-09 03:41:08'),(38,'floor_1773118649_69afa4b9b7482.webp','six floor','2026-03-09 22:57:29','2026-03-09 22:57:29');
/*!40000 ALTER TABLE `floors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (3,'1773123388_69afb73c5e8e5.webp','2026-03-10 00:16:28','2026-03-10 00:16:28'),(4,'1773123412_69afb754d7e20.jfif','2026-03-10 00:16:52','2026-03-10 00:16:52'),(5,'1784103345_6a5741b1cc2e1.png','2026-03-10 00:17:10','2026-07-15 02:15:45'),(6,'1784103336_6a5741a8a91ce.png','2026-03-10 00:17:32','2026-07-15 02:15:36'),(7,'1784103326_6a57419e5089e.png','2026-03-10 00:17:54','2026-07-15 02:15:26'),(8,'1784103315_6a5741939a4a7.png','2026-03-10 00:18:25','2026-07-15 02:15:15'),(13,'1784103306_6a57418a9c8fc.png','2026-04-21 05:22:36','2026-07-15 02:15:06');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_managems`
--

DROP TABLE IF EXISTS `hotel_managems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_managems` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hotel_managems`
--

LOCK TABLES `hotel_managems` WRITE;
/*!40000 ALTER TABLE `hotel_managems` DISABLE KEYS */;
INSERT INTO `hotel_managems` VALUES (1,'tests','2026-05-14 01:51:27','2026-05-14 02:04:02'),(2,'test twos','2026-05-14 02:02:27','2026-05-14 02:04:06');
/*!40000 ALTER TABLE `hotel_managems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manage_sales`
--

DROP TABLE IF EXISTS `manage_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manage_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `single_price` decimal(10,2) NOT NULL,
  `total_price_available` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date NOT NULL,
  `customer_quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manage_sales`
--

LOCK TABLES `manage_sales` WRITE;
/*!40000 ALTER TABLE `manage_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `manage_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meals`
--

DROP TABLE IF EXISTS `meals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `half_meal` tinyint NOT NULL DEFAULT '0',
  `full_meal` tinyint NOT NULL DEFAULT '0',
  `made_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `made_by` (`user_id`),
  KEY `meals_made_by_foreign` (`made_by`),
  CONSTRAINT `meals_made_by_foreign` FOREIGN KEY (`made_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meals`
--

LOCK TABLES `meals` WRITE;
/*!40000 ALTER TABLE `meals` DISABLE KEYS */;
/*!40000 ALTER TABLE `meals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_releases`
--

DROP TABLE IF EXISTS `member_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_releases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `release_date` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settlement_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `closing_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_releases`
--

LOCK TABLES `member_releases` WRITE;
/*!40000 ALTER TABLE `member_releases` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_releases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'2025_01_06_070719_create_otps_table',1),(86,'2014_10_12_000000_create_users_table',2),(87,'2014_10_12_100000_create_password_reset_tokens_table',2),(88,'2014_10_12_100000_create_password_resets_table',2),(89,'2019_08_19_000000_create_failed_jobs_table',2),(90,'2019_12_14_000001_create_personal_access_tokens_table',2),(91,'2024_12_12_063036_create_permission_tables',2),(92,'2025_01_01_063809_create_settings_table',2),(104,'2026_01_17_060442_create_floors_table',3),(122,'2026_03_09_063554_create_residence_overviews_table',7),(123,'2026_03_10_053035_create_galleries_table',8),(133,'2026_04_15_065719_create_staff_attendances_table',13),(134,'2026_04_16_060109_create_staff_salary_payments_table',14),(135,'2026_04_16_061047_create_staff_salary_payments_table',15),(136,'2026_04_18_121356_create_expense_types_table',16),(139,'2026_04_19_070131_create_expenses_table',17),(141,'2026_04_21_073109_create_suppliers_table',19),(150,'2026_04_22_053513_create_brand_categories_table',23),(155,'2026_04_22_094327_create_products_table',25),(169,'2026_04_25_083914_create_manage_sales_table',27),(171,'2026_04_23_051506_create_product_purchases_table',28),(172,'2026_01_17_060835_create_rooms_table',29),(174,'2026_04_22_044000_create_brands_table',30),(181,'2026_04_29_095007_create_product_distributions_table',31),(183,'2026_01_26_043313_create_room_booking_histories_table',32),(185,'2026_03_12_081022_create_staffs_table',33),(186,'2026_05_14_071417_create_hotel_managems_table',34),(187,'2026_07_15_055821_add_family_details_to_room_booking_histories_table',35),(188,'2026_07_18_044231_create_notices_table',36),(191,'2026_07_18_080126_add_booking_months_to_room_booking_histories_table',37),(192,'2026_07_18_090254_create_monthly_payments_table',38),(193,'2026_07_18_102718_add_months_to_extend_to_monthly_payments_table',39),(194,'2026_07_18_120000_create_room_seats_table',40),(195,'2026_07_19_110000_add_user_type_to_room_booking_histories_table',41),(196,'2026_07_19_120000_add_paid_and_due_to_monthly_payments_table',42),(197,'2026_07_19_084830_add_carried_forward_due_to_monthly_payments_table',43),(198,'2026_07_21_111201_add_will_leave_to_room_booking_histories_table',44),(199,'2026_07_22_120000_add_education_and_workplace_to_room_booking_histories_table',45),(200,'2026_04_01_060849_create_meals_table',46),(201,'2026_04_01_060909_create_deposits_table',46),(202,'2026_04_01_060916_create_fines_table',46),(203,'2026_06_13_072838_create_member_releases_table',46),(204,'2026_07_23_054132_add_address_to_room_booking_histories_table',47),(205,'2026_07_26_120000_add_seat_id_to_product_distributions_table',48),(206,'2026_07_27_161500_update_room_booking_histories_columns',48),(207,'2026_07_27_180000_add_parent_phones_to_room_booking_histories_table',49),(208,'2026_07_28_130000_add_development_fee_to_room_booking_histories_table',50);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` VALUES (17,'App\\Models\\User',25),(17,'App\\Models\\User',26),(17,'App\\Models\\User',27),(18,'App\\Models\\User',60);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',25),(3,'App\\Models\\User',26),(3,'App\\Models\\User',27),(3,'App\\Models\\User',28),(3,'App\\Models\\User',29),(3,'App\\Models\\User',30),(3,'App\\Models\\User',31),(3,'App\\Models\\User',32),(3,'App\\Models\\User',33),(3,'App\\Models\\User',34),(3,'App\\Models\\User',35),(3,'App\\Models\\User',36),(3,'App\\Models\\User',37),(3,'App\\Models\\User',38),(3,'App\\Models\\User',39),(3,'App\\Models\\User',40),(3,'App\\Models\\User',41),(3,'App\\Models\\User',42),(3,'App\\Models\\User',43),(3,'App\\Models\\User',44),(3,'App\\Models\\User',45),(3,'App\\Models\\User',46),(3,'App\\Models\\User',47),(3,'App\\Models\\User',48),(3,'App\\Models\\User',49),(3,'App\\Models\\User',50),(3,'App\\Models\\User',51),(3,'App\\Models\\User',58),(3,'App\\Models\\User',59),(4,'App\\Models\\User',60),(4,'App\\Models\\User',62),(4,'App\\Models\\User',63),(4,'App\\Models\\User',71),(3,'App\\Models\\User',72),(3,'App\\Models\\User',73),(4,'App\\Models\\User',74),(3,'App\\Models\\User',75),(3,'App\\Models\\User',76),(3,'App\\Models\\User',77),(3,'App\\Models\\User',78),(3,'App\\Models\\User',79),(3,'App\\Models\\User',80),(3,'App\\Models\\User',81),(3,'App\\Models\\User',82),(3,'App\\Models\\User',83),(3,'App\\Models\\User',84),(3,'App\\Models\\User',85),(3,'App\\Models\\User',86),(3,'App\\Models\\User',87),(3,'App\\Models\\User',88),(3,'App\\Models\\User',89),(3,'App\\Models\\User',90),(3,'App\\Models\\User',91),(3,'App\\Models\\User',92),(3,'App\\Models\\User',93),(3,'App\\Models\\User',94),(3,'App\\Models\\User',95),(3,'App\\Models\\User',96),(3,'App\\Models\\User',97),(3,'App\\Models\\User',98),(3,'App\\Models\\User',99),(3,'App\\Models\\User',100),(3,'App\\Models\\User',101),(3,'App\\Models\\User',102),(3,'App\\Models\\User',103),(3,'App\\Models\\User',104),(3,'App\\Models\\User',105),(3,'App\\Models\\User',106),(3,'App\\Models\\User',107),(3,'App\\Models\\User',108),(3,'App\\Models\\User',109),(3,'App\\Models\\User',110),(5,'App\\Models\\User',111),(6,'App\\Models\\User',112),(6,'App\\Models\\User',113),(5,'App\\Models\\User',114),(6,'App\\Models\\User',115),(4,'App\\Models\\User',116),(4,'App\\Models\\User',117),(4,'App\\Models\\User',118),(4,'App\\Models\\User',119),(5,'App\\Models\\User',120),(5,'App\\Models\\User',121),(5,'App\\Models\\User',122),(6,'App\\Models\\User',123),(6,'App\\Models\\User',124),(5,'App\\Models\\User',125),(6,'App\\Models\\User',126),(6,'App\\Models\\User',127),(6,'App\\Models\\User',128),(5,'App\\Models\\User',129),(6,'App\\Models\\User',130),(5,'App\\Models\\User',131),(5,'App\\Models\\User',132),(5,'App\\Models\\User',133),(5,'App\\Models\\User',134),(5,'App\\Models\\User',135),(5,'App\\Models\\User',136),(6,'App\\Models\\User',137),(5,'App\\Models\\User',138),(6,'App\\Models\\User',139),(5,'App\\Models\\User',140),(6,'App\\Models\\User',141),(6,'App\\Models\\User',142);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monthly_payments`
--

DROP TABLE IF EXISTS `monthly_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monthly_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_booking_history_id` bigint unsigned NOT NULL,
  `payment_month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `carried_forward_due` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `months_to_extend` int DEFAULT '1',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trx_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `received_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monthly_payments_room_booking_history_id_foreign` (`room_booking_history_id`),
  CONSTRAINT `monthly_payments_room_booking_history_id_foreign` FOREIGN KEY (`room_booking_history_id`) REFERENCES `room_booking_histories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monthly_payments`
--

LOCK TABLES `monthly_payments` WRITE;
/*!40000 ALTER TABLE `monthly_payments` DISABLE KEYS */;
INSERT INTO `monthly_payments` VALUES (48,121,'2026-07',3000.00,0.00,0.00,3000.00,1,'unpaid',NULL,NULL,'pending',NULL,'2026-07-28 01:51:45','2026-07-28 01:51:45'),(49,125,'2026-07',3000.00,0.00,0.00,3000.00,1,'unpaid',NULL,NULL,'pending',NULL,'2026-07-28 01:51:45','2026-07-28 01:51:45'),(50,126,'2026-07',2500.00,0.00,0.00,2500.00,1,'unpaid',NULL,NULL,'pending',NULL,'2026-07-28 01:51:45','2026-07-28 01:51:45'),(51,127,'2026-07',3000.00,0.00,2000.00,1000.00,1,'Cash',NULL,'[28-07-2026 3:09 PM] Collected αº│2000 via Cash','partial','admin (ID: 2)','2026-07-28 01:51:45','2026-07-28 03:09:53');
/*!40000 ALTER TABLE `monthly_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notices`
--

DROP TABLE IF EXISTS `notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notice` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notices`
--

LOCK TABLES `notices` WRITE;
/*!40000 ALTER TABLE `notices` DISABLE KEYS */;
INSERT INTO `notices` VALUES (1,'αª╕αºïαª╣αª╛αª╢αºïαª¡αª╛ αª¢αª╛αªñαºìαª░αºÇ αª¿αª┐αª¼αª╛αª╕ - αª¼αª┐αª╢αºçαª╖ αª¼αºüαªòαª┐αªé αªíαª┐αª╕αªòαª╛αªëαª¿αºìαªƒ αªàαª½αª╛αª░! αªÅαªûαª¿αªç αª╕αª┐αªƒ αª¼αºüαªò αªòαª░αºüαª¿ αªÅαª¼αªé αª¼αª┐αª╢αºçαª╖ αª¢αª╛αº£ αªëαª¬αª¡αºïαªù αªòαª░αºüαª¿ ΓÇö αª╕αª┐αªƒ αª╕αºÇαª«αª┐αªñ, αªåαª£αªç αªåαª¬αª¿αª╛αª░ αª£αª╛αºƒαªùαª╛ αª¿αª┐αª╢αºìαªÜαª┐αªñ αªòαª░αºüαª¿','2026-07-17 22:45:42','2026-07-26 22:18:57');
/*!40000 ALTER TABLE `notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otps`
--

DROP TABLE IF EXISTS `otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otps`
--

LOCK TABLES `otps` WRITE;
/*!40000 ALTER TABLE `otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'role-index','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(2,'role-create','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(3,'role-edit','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(4,'role-delete','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(5,'permission-index','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(6,'permission-create','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(7,'permission-edit','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(8,'permission-delete','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(9,'setting-index','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(10,'setting-create','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(11,'setting-edit','web','2025-01-23 01:19:47','2025-01-23 01:19:47'),(12,'setting-delete','web','2025-01-23 01:19:48','2025-01-23 01:19:48'),(13,'user-index','web','2025-01-23 01:19:48','2025-01-23 01:19:48'),(14,'user-create','web','2025-01-23 01:19:48','2025-01-23 01:19:48'),(15,'user-edit','web','2025-01-23 01:19:48','2025-01-23 01:19:48'),(16,'user-delete','web','2025-01-23 01:19:48','2025-01-23 01:19:48'),(17,'Booking-history','web','2026-04-01 04:08:31','2026-04-01 04:08:31'),(18,'staffs','web','2026-05-10 23:27:05','2026-05-10 23:27:05');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_distributions`
--

DROP TABLE IF EXISTS `product_distributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_distributions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `floor_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `purchase_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `seat_id` int DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `memo_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `single_price` decimal(10,2) NOT NULL,
  `total_price_available` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date NOT NULL,
  `customer_quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_distributions`
--

LOCK TABLES `product_distributions` WRITE;
/*!40000 ALTER TABLE `product_distributions` DISABLE KEYS */;
INSERT INTO `product_distributions` VALUES (163,35,14,43,44,8,NULL,'Bread','4521',100.00,400.00,'2026-05-17',4,'2026-05-17 05:43:41','2026-05-17 05:43:41'),(164,35,14,44,44,8,NULL,'Bread','4521',100.00,100.00,'2026-05-17',1,'2026-05-17 05:43:41','2026-05-17 05:43:41'),(165,33,24,37,44,7,NULL,'Coffee',NULL,200.00,400.00,'2026-05-17',2,'2026-05-17 05:44:11','2026-05-17 05:44:11'),(166,33,24,38,44,8,NULL,'Coffee',NULL,200.00,200.00,'2026-05-17',1,'2026-05-17 05:44:11','2026-05-17 05:44:11'),(167,33,23,44,45,8,NULL,'Bread','4521',100.00,100.00,'2026-05-17',1,'2026-05-17 05:47:33','2026-05-17 05:47:33'),(168,33,23,47,45,8,NULL,'Bread','4521',100.00,200.00,'2026-05-17',2,'2026-05-17 05:47:33','2026-05-17 05:47:33'),(169,33,23,49,45,8,NULL,'Bread','4521',100.00,100.00,'2026-05-17',1,'2026-05-17 05:47:33','2026-05-17 05:47:33'),(170,38,5,49,51,8,NULL,'Bread',NULL,100.00,500.00,'2026-06-06',5,'2026-06-05 23:04:40','2026-06-05 23:04:40'),(171,38,5,38,51,8,NULL,'Coffee',NULL,200.00,800.00,'2026-06-06',4,'2026-06-05 23:04:41','2026-06-05 23:04:41'),(172,38,36,49,NULL,8,NULL,'Bread','4521',100.00,400.00,'2026-07-26',4,'2026-07-26 01:29:02','2026-07-26 01:29:02'),(173,38,33,88,119,8,1,'Biscuit',NULL,60.00,300.00,'2026-07-27',5,'2026-07-27 00:45:15','2026-07-27 00:45:15'),(174,38,33,88,119,8,1,'Biscuit',NULL,60.00,600.00,'2026-07-27',10,'2026-07-27 00:55:45','2026-07-27 00:55:45'),(175,38,33,50,119,8,1,'Bread','4521',100.00,200.00,'2026-07-27',2,'2026-07-27 00:56:36','2026-07-27 00:56:36'),(176,38,33,56,119,8,1,'Bread','4521',100.00,200.00,'2026-07-27',2,'2026-07-27 00:56:36','2026-07-27 00:56:36'),(177,38,33,57,119,8,1,'Bread','4521',100.00,100.00,'2026-07-27',1,'2026-07-27 00:56:36','2026-07-27 00:56:36');
/*!40000 ALTER TABLE `product_distributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_purchases`
--

DROP TABLE IF EXISTS `product_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `single_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL,
  `available_quantity` int NOT NULL DEFAULT '0',
  `total_price_available` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `memo_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_purchases`
--

LOCK TABLES `product_purchases` WRITE;
/*!40000 ALTER TABLE `product_purchases` DISABLE KEYS */;
INSERT INTO `product_purchases` VALUES (16,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(17,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:47:57'),(18,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(19,7,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 05:48:56'),(20,8,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-11 05:57:15'),(21,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 05:47:57'),(22,7,27,NULL,'Biscuit',60.00,300.00,5,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:23','2026-05-11 05:47:57'),(23,7,24,NULL,'Coffee',200.00,1000.00,5,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:24','2026-05-16 00:26:35'),(24,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:47:57'),(25,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-11 05:48:19'),(26,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:47:57'),(27,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-11 05:48:55'),(28,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 05:49:58'),(29,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:48:19'),(30,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-11 05:50:12'),(31,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:48:19'),(32,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-17 05:35:55'),(33,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(34,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(35,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:48:19'),(36,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(37,7,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-17 05:44:11'),(38,8,24,NULL,'Coffee',200.00,2000.00,10,5,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-06-05 23:04:41'),(39,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(40,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(41,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:48:19'),(42,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-17 05:36:18'),(43,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-17 05:43:41'),(44,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:47:33'),(45,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:49:21'),(46,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(47,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:47:33'),(48,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:49:21'),(49,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-07-26 01:29:02'),(50,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-07-27 00:56:36'),(51,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(52,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:49:59'),(53,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(54,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(55,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(56,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-07-27 00:56:36'),(57,8,26,NULL,'Bread',100.00,1000.00,10,1,100.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-07-27 00:56:36'),(58,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(59,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:57:15'),(60,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-06 02:41:10'),(61,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(62,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(63,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(64,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(65,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(66,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(67,7,24,NULL,'Coffee',200.00,1000.00,5,5,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:24','2026-05-06 02:34:24'),(68,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(69,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-06 02:41:10'),(70,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:57:15'),(71,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:57:15'),(72,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(73,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(74,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(75,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(76,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-16 03:48:48'),(77,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(78,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-16 05:25:31'),(79,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-06 02:41:10'),(80,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(81,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(82,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-17 05:30:03'),(83,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(84,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(85,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(86,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-11',0.00,'4521','2026-05-11 05:44:11','2026-05-17 05:35:20'),(87,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-11',0.00,'4521','2026-05-11 05:44:12','2026-05-11 05:44:12'),(88,8,27,NULL,'Biscuit',60.00,6000.00,100,75,4500.00,'2026-07-26',0.00,NULL,'2026-07-26 01:30:08','2026-07-27 02:15:12');
/*!40000 ALTER TABLE `product_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `brand_category_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `buy_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sell_price` decimal(10,2) DEFAULT '0.00',
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (24,7,14,'Coffee',200.00,0.00,'2026-04-29','2026-04-29 05:53:56','2026-04-29 05:53:56'),(25,8,14,'tea',50.00,0.00,'2026-04-29','2026-04-29 05:54:27','2026-04-29 05:54:27'),(26,9,15,'Bread',100.00,0.00,'2026-04-29','2026-04-29 05:55:56','2026-04-29 05:55:56'),(27,9,15,'Biscuit',60.00,0.00,'2026-04-29','2026-04-29 05:56:17','2026-04-29 05:56:17'),(28,10,16,'Rice,Fish Curry,Chicken Curry,Vegetables,Dal',120.00,0.00,'2026-04-29','2026-04-29 05:57:37','2026-04-29 05:57:37');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `residence_overviews`
--

DROP TABLE IF EXISTS `residence_overviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `residence_overviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_back` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_front` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residence_overviews`
--

LOCK TABLES `residence_overviews` WRITE;
/*!40000 ALTER TABLE `residence_overviews` DISABLE KEYS */;
INSERT INTO `residence_overviews` VALUES (4,'A Premier Girls\' Residence in the Heart of Rangpur.','A premier girls\' residence in the heart of Rangpur. Established in 2023, Sohahova Chhatri Nibas offers a safe, secure, and intellectually stimulating environment for female students. We provide thoughtfully furnished accommodation with comprehensive amenities, including high-speed Wi-Fi, nutritious meal plans, 24/7 security, laundry services, and dedicated study lounges. Our mission is to empower residents to excel academically and personally.','1784102863_xhgXxXvK.png','1784102863_9wmnaTxO.png','2026-03-09 02:47:55','2026-07-15 02:07:43');
/*!40000 ALTER TABLE `residence_overviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,3),(18,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(2,'admin','web','2025-01-23 01:19:46','2025-01-23 01:19:46'),(3,'HotelGuest','web','2026-04-01 04:05:37','2026-04-01 04:05:37'),(4,'staffs','web','2026-05-10 23:26:00','2026-05-10 23:26:00'),(5,'Student','web','2026-07-22 23:47:54','2026-07-22 23:47:54'),(6,'Working Professional','web','2026-07-23 00:05:10','2026-07-23 00:05:10');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_booking_histories`
--

DROP TABLE IF EXISTS `room_booking_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_booking_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor_number_room_number_roomprice` json DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workplace_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_nid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_nid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division_id` bigint unsigned DEFAULT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `thana_id` bigint unsigned DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `pay_cash_in` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_online` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_amount` decimal(12,2) DEFAULT '0.00',
  `development_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `booking_months` int DEFAULT '1',
  `today_check_out` date DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `will_leave` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_booking_histories`
--

LOCK TABLES `room_booking_histories` WRITE;
/*!40000 ALTER TABLE `room_booking_histories` DISABLE KEYS */;
INSERT INTO `room_booking_histories` VALUES (121,'01750887855.png','[{\"roomnumber\": \"101-Seat-A\", \"floornumber\": \"frist floor\", \"advance_price\": 6000}]','ratul','student','carmical  Collage Rangpur','College','1st Year (αªÅαªòαª╛αªªαª╢)',NULL,'jabed','begom','ratul@gmail.com','+8801750887855',NULL,'456213547','01745874582','4578961235','01732145678','123456',1,1,1,'belabo','cash',NULL,3000.00,3000.00,'2026-07-28','2026-07-28',1,NULL,'0',0,'2026-07-27 23:08:28','2026-07-28 01:47:35'),(123,'01765750791.jpg','[{\"roomnumber\": \"201-Seat-A\", \"floornumber\": \"Second Floor\", \"advance_price\": 6000}]','hossion','Working Professional',NULL,NULL,NULL,'RFL Company',NULL,NULL,'hossainshohag898@gmail.com','+8801765750791','7485965',NULL,NULL,NULL,NULL,'123456',1,11,72,'shibchar','cash',NULL,3000.00,0.00,'2026-07-28','2026-07-28',1,'2026-07-28','1',0,'2026-07-27 23:36:24','2026-07-27 23:52:30'),(124,'01765750791.jpg','[{\"roomnumber\": \"301-Seat-B\", \"floornumber\": \"Third Floor\", \"advance_price\": 5000}]','hossion','Working Professional',NULL,NULL,NULL,'RFL Company',NULL,NULL,'hossainshohag898@gmail.com','+8801765750791','7485965',NULL,NULL,NULL,NULL,'123456',1,11,72,'shibchar','cash',NULL,2500.00,0.00,'2026-07-28','2026-07-28',1,'2026-07-28','1',0,'2026-07-27 23:48:29','2026-07-27 23:52:28'),(125,'01765750791.jpg','[{\"price\": 3000, \"roomnumber\": \"303-Seat-A\", \"floornumber\": \"Third Floor\", \"advance_price\": 6000}]','hossion','Working Professional',NULL,NULL,NULL,'RFL Company',NULL,NULL,'hossainshohag898@gmail.com','+8801765750791','7485965',NULL,NULL,NULL,NULL,'415263',1,11,72,'shibchar','cash',NULL,3000.00,0.00,'2026-07-28','2026-07-28',1,NULL,'0',0,'2026-07-28 00:01:26','2026-07-28 00:24:59'),(126,'01306526906.png','[{\"roomnumber\": \"503-Seat-B\", \"floornumber\": \"Fifth Floor\", \"advance_price\": 5000}]','roni','student','begom rokeya university','University','2nd Semester',NULL,'Octavia Vargas','Gretchen Bean','roni@gmail.com','+8801306526906',NULL,'41785462','01885761782','01745262','01750212362','123456',1,8,500,'adabor','cash',NULL,2500.00,0.00,'2026-07-28','2026-07-28',1,NULL,'0',0,'2026-07-28 00:32:35','2026-07-28 00:32:35'),(127,'01799360170.jpg','[{\"roomnumber\": \"601-Seat-A\", \"floornumber\": \"six floor\", \"advance_price\": 6000}]','fahim','Working Professional',NULL,NULL,NULL,'RFL Company',NULL,NULL,NULL,'+8801799360170','7485965',NULL,NULL,NULL,NULL,'123456',4,35,276,'gangni','cash',NULL,3000.00,3000.00,'2026-07-29','2026-07-29',1,NULL,'0',0,'2026-07-28 00:34:47','2026-07-28 01:46:39'),(128,'01314121414.png','[{\"roomnumber\": \"602-Seat-A\", \"floornumber\": \"six floor\", \"advance_price\": 5000}]','jahangir','Working Professional',NULL,NULL,NULL,'RFL Company',NULL,NULL,'jahangir@gmail.com','+8801314121414','7485965',NULL,NULL,NULL,NULL,'123456',6,54,399,'haripur','cash',NULL,2500.00,3000.00,'2026-07-28','2026-07-28',1,NULL,'0',0,'2026-07-28 02:43:34','2026-07-28 02:43:34');
/*!40000 ALTER TABLE `room_booking_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_seats`
--

DROP TABLE IF EXISTS `room_seats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_seats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `seat_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `advance_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_seats_room_id_foreign` (`room_id`),
  CONSTRAINT `room_seats_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_seats`
--

LOCK TABLES `room_seats` WRITE;
/*!40000 ALTER TABLE `room_seats` DISABLE KEYS */;
INSERT INTO `room_seats` VALUES (33,46,'Seat-A',3000.00,6000.00,1,'2026-07-27 22:47:08','2026-07-27 23:20:25'),(34,47,'Seat-A',2500.00,5000.00,0,'2026-07-27 22:50:33','2026-07-27 22:50:33'),(35,47,'Seat-B',2500.00,5000.00,0,'2026-07-27 22:50:33','2026-07-27 22:50:33'),(36,48,'Seat-A',3000.00,6000.00,0,'2026-07-27 22:51:39','2026-07-28 00:24:59'),(38,49,'Seat-A',3000.00,6000.00,0,'2026-07-27 22:54:42','2026-07-27 23:52:30'),(39,50,'Seat-A',2500.00,5000.00,0,'2026-07-27 22:55:43','2026-07-28 00:23:25'),(40,50,'Seat-B',2500.00,5000.00,0,'2026-07-27 22:55:43','2026-07-27 22:55:43'),(41,52,'Seat-A',2500.00,5000.00,0,'2026-07-27 22:56:40','2026-07-27 23:23:59'),(42,52,'Seat-B',2500.00,5000.00,0,'2026-07-27 22:56:40','2026-07-27 23:52:28'),(43,53,'Seat-A',2500.00,5000.00,0,'2026-07-27 22:57:25','2026-07-27 22:57:25'),(44,53,'Seat-B',2500.00,5000.00,0,'2026-07-27 22:57:25','2026-07-27 22:57:25'),(45,54,'Seat-A',3000.00,6000.00,1,'2026-07-27 22:58:16','2026-07-28 00:24:59'),(47,55,'Seat-A',3000.00,6000.00,0,'2026-07-27 22:59:45','2026-07-27 22:59:45'),(48,56,'Seat-A',3000.00,6000.00,0,'2026-07-27 23:00:36','2026-07-27 23:00:36'),(49,57,'Seat-A',2500.00,5000.00,0,'2026-07-27 23:01:21','2026-07-27 23:01:21'),(50,57,'Seat-B',2500.00,5000.00,1,'2026-07-27 23:01:21','2026-07-28 00:32:35'),(51,59,'Seat-A',2500.00,5000.00,1,'2026-07-27 23:02:25','2026-07-28 02:43:34'),(52,59,'Seat-B',2500.00,5000.00,0,'2026-07-27 23:02:25','2026-07-27 23:02:25'),(53,58,'Seat-A',3000.00,6000.00,1,'2026-07-27 23:03:02','2026-07-28 00:34:47'),(54,60,'Seat-A',3000.00,6000.00,0,'2026-07-27 23:03:54','2026-07-27 23:03:54'),(55,61,'Seat-A',3000.00,6000.00,0,'2026-07-27 23:15:30','2026-07-27 23:15:30');
/*!40000 ALTER TABLE `room_seats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `floor_id` bigint unsigned NOT NULL,
  `room_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` json DEFAULT NULL,
  `acstatus` int DEFAULT NULL,
  `room_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `breakfast` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attached_bathroom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_people` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balcony` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ac_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `windows` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_discount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (46,33,'101','[\"1785214028_6a68344cb027e.webp\"]',1,'14x13','no','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'1','2026-07-27 22:43:53','2026-07-27 23:20:25'),(47,33,'102','[\"1785214233_6a683519ce51f.avif\"]',1,'14x12','yes','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:43:53','2026-07-27 22:50:33'),(48,33,'103','[\"1785214299_6a68355b558b9.webp\"]',1,'21x22','no','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:43:53','2026-07-28 00:24:59'),(49,34,'201','[\"1785214482_6a683612708ae.jpg\"]',1,'22x21','yes','yes',NULL,'no','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:44:09','2026-07-27 22:54:42'),(50,34,'202','[\"1785214543_6a68364fbb156.jfif\"]',1,'41x32','yes','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:44:09','2026-07-27 22:55:43'),(52,35,'301','[\"1785214600_6a68368883cff.avif\"]',1,'33x22','no','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:44:22','2026-07-27 22:56:40'),(53,35,'302','[\"1785214645_6a6836b5a1aca.jfif\"]',1,'12x32','no','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:44:22','2026-07-27 22:57:25'),(54,35,'303','[\"1785214696_6a6836e82cb1d.avif\"]',1,'12x23','no','yes',NULL,'yes','Ac','3000','no','Singel',NULL,'1','2026-07-27 22:44:22','2026-07-28 00:24:59'),(55,37,'501','[\"1785214785_6a6837415a7d8.jpg\"]',1,'22x22','no','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:44:59','2026-07-27 22:59:45'),(56,37,'502','[\"1785214836_6a683774ee5eb.jpg\"]',1,'32x21','yes','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:44:59','2026-07-27 23:00:36'),(57,37,'503','[\"1785214881_6a6837a17327e.jfif\"]',1,'21x14','yes','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:44:59','2026-07-27 23:01:21'),(58,38,'601','[\"1785214982_6a6838068ab26.webp\"]',1,'23x21','yes','yes',NULL,'no','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:45:11','2026-07-27 23:03:02'),(59,38,'602','[\"1785214945_6a6837e10be7b.avif\"]',1,'14x24','no','yes',NULL,'yes','Ac','2500','yes','Doubel',NULL,'0','2026-07-27 22:45:11','2026-07-27 23:02:25'),(60,38,'603','[\"1785215034_6a68383ac13fc.webp\"]',1,'21x32','no','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'0','2026-07-27 22:45:11','2026-07-27 23:03:54'),(61,34,'203','[\"1785215730_6a683af2c7b75.webp\"]',1,'21x32','no','yes',NULL,'yes','Ac','3000','yes','Singel',NULL,'0','2026-07-27 23:13:37','2026-07-27 23:15:30');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contents` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'Web Setting','web_setting','{\"title\":\"Tss Villa\",\"phone\":\"01977270920\",\"address\":\"\\u0995\\u09b2\\u09c7\\u099c \\u09b0\\u09cb\\u09a1, \\u09a8\\u09c7\\u09b8\\u0995\\u09cb \\u0997\\u09c7\\u099f \\u09b8\\u0982\\u09b2\\u0997\\u09cd\\u09a8, \\u09b0\\u0982\\u09aa\\u09c1\\u09b0\",\"email\":\"tssvilla2026@gmail.com\",\"currency\":\"BDT\"}','2025-01-23 01:19:49','2026-07-28 01:20:12'),(2,'logo setting','logo_setting','{\"logo\":\"logo.png\",\"favicon\":\"favicon.png\"}','2026-03-04 00:44:37','2026-03-04 00:44:37'),(3,'Meal Setting','meal_setting','{\"half\":\"35\",\"full\":\"65\",\"day_rice\":\"200\",\"night_rice\":\"200\",\"morning_rice\":\"100\",\"Chef_take_meal_number\":\"1\",\"meal_change_time\":\"08:00\",\"seat_rent\":\"2000\"}','2026-07-22 01:29:11','2026-07-22 01:29:11'),(4,'app setting','app_setting','{\"development_fee\":\"3000\",\"development_fee_status\":\"1\"}','2026-07-28 01:01:59','2026-07-28 02:41:49');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_attendances`
--

DROP TABLE IF EXISTS `staff_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_attendances_staff_id_foreign` (`staff_id`),
  CONSTRAINT `staff_attendances_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_attendances`
--

LOCK TABLES `staff_attendances` WRITE;
/*!40000 ALTER TABLE `staff_attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_salary_payments`
--

DROP TABLE IF EXISTS `staff_salary_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_salary_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned NOT NULL,
  `salary_month` tinyint unsigned NOT NULL,
  `salary_year` smallint unsigned NOT NULL,
  `payment_type` enum('advance','full','net_payable') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_idx` (`staff_id`,`salary_month`,`salary_year`),
  CONSTRAINT `staff_salary_payments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_salary_payments`
--

LOCK TABLES `staff_salary_payments` WRITE;
/*!40000 ALTER TABLE `staff_salary_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_salary_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staffs`
--

DROP TABLE IF EXISTS `staffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staffs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nid_passport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `permanent_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `designation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `joining_date` date DEFAULT NULL,
  `shift_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffs`
--

LOCK TABLES `staffs` WRITE;
/*!40000 ALTER TABLE `staffs` DISABLE KEYS */;
INSERT INTO `staffs` VALUES (23,'74581','manik','01733221166','manik120@gmail.com','123456','854216','Male','2026-07-23',2,14,99,'bomilla sadar','Manager','Front Desk',20000.00,'2026-07-23','Morning',0,'1784791309_6a61c10d319b1.jpg',NULL,'2026-07-23 01:21:28','2026-07-23 01:21:49'),(26,'451263','hasansd','01455428099','hasansd@gmail.com','123456','12457896','Male','2026-07-24',2,15,106,'rangpurssd','Manager','Front Desk',20000.00,'2026-07-25','Morning',0,'1784971182_6a647fae55d46.jpg',NULL,'2026-07-24 23:00:23','2026-07-25 03:19:42');
/*!40000 ALTER TABLE `staffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (6,'Ratul Hossion','2026-04-21 02:02:22','2026-04-21 23:07:14'),(7,'Rabbi Hossion','2026-04-27 00:56:17','2026-04-27 00:56:17'),(8,'josim hossion','2026-04-27 00:56:32','2026-04-27 00:56:32'),(9,'Ripon Hossion','2026-04-28 22:49:39','2026-04-28 22:49:39');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanas`
--

DROP TABLE IF EXISTS `thanas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thanas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `district_id` int unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thanas_district_id_foreign` (`district_id`)
) ENGINE=InnoDB AUTO_INCREMENT=549 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanas`
--

LOCK TABLES `thanas` WRITE;
/*!40000 ALTER TABLE `thanas` DISABLE KEYS */;
INSERT INTO `thanas` VALUES (1,1,'Belabo','αª¼αºçαª▓αª╛αª¼αºï'),(2,1,'Monohardi','αª«αª¿αºïαª╣αª░αªªαºÇ'),(3,1,'Narsingdi Sadar','αª¿αª░αª╕αª┐αªéαªªαºÇ αª╕αªªαª░'),(4,1,'Palash','αª¬αª▓αª╛αª╢'),(5,1,'Raipura','αª░αª╛αª»αª╝αª¬αºüαª░αª╛'),(6,1,'Shibpur','αª╢αª┐αª¼αª¬αºüαª░'),(7,2,'Kaliganj','αªòαª╛αª▓αºÇαªùαª₧αºìαª£'),(8,2,'Kaliakair','αªòαª╛αª▓αª┐αºƒαª╛αªòαºêαª░'),(9,2,'Kapasia','αªòαª╛αª¬αª╛αª╕αª┐αºƒαª╛'),(10,2,'Gazipur Sadar','αªùαª╛αª£αºÇαª¬αºüαª░ αª╕αªªαª░'),(11,2,'Sreepur','αª╢αºìαª░αºÇαª¬αºüαª░'),(12,3,'Shariatpur Sadar','αª╢αª░αª┐αºƒαªñαª¬αºüαª░ αª╕αªªαª░'),(13,3,'Naria','αª¿αº£αª┐αºƒαª╛'),(14,3,'Zajira','αª£αª╛αª£αª┐αª░αª╛'),(15,3,'Gosairhat','αªùαºïαª╕αª╛αªçαª░αª╣αª╛αªƒ'),(16,3,'Bhedarganj','αª¡αºçαªªαª░αªùαª₧αºìαª£'),(17,3,'Damudya','αªíαª╛αª«αºüαªíαºìαª»αª╛'),(18,4,'Araihazar','αªåαªíαª╝αª╛αªçαª╣αª╛αª£αª╛αª░'),(19,4,'Bandar','αª¼αª¿αºìαªªαª░'),(20,4,'Narayanganj Sadar','αª¿αª╛αª░αª╛αºƒαª¿αªùαª₧αºìαª£ αª╕αªªαª░'),(21,4,'Rupganj','αª░αºéαª¬αªùαª₧αºìαª£'),(22,4,'Sonargaon','αª╕αºïαª¿αª╛αª░αªùαª╛αªü'),(23,5,'Basail','αª¼αª╛αª╕αª╛αªçαª▓'),(24,5,'Bhuapur','αª¡αºüαª»αª╝αª╛αª¬αºüαª░'),(25,5,'Delduar','αªªαºçαª▓αªªαºüαª»αª╝αª╛αª░'),(26,5,'Ghatail','αªÿαª╛αªƒαª╛αªçαª▓'),(27,5,'Gopalpur','αªùαºïαª¬αª╛αª▓αª¬αºüαª░'),(28,5,'Madhupur','αª«αªºαºüαª¬αºüαª░'),(29,5,'Mirzapur','αª«αª┐αª░αºìαª£αª╛αª¬αºüαª░'),(30,5,'Nagarpur','αª¿αª╛αªùαª░αª¬αºüαª░'),(31,5,'Sakhipur','αª╕αªûαª┐αª¬αºüαª░'),(32,5,'Tangail Sadar','αªƒαª╛αªÖαºìαªùαª╛αªçαª▓ αª╕αªªαª░'),(33,5,'Kalihati','αªòαª╛αª▓αª┐αª╣αª╛αªñαºÇ'),(34,5,'Dhanbari','αªºαª¿αª¼αª╛αº£αºÇ'),(35,6,'Itna','αªçαªƒαª¿αª╛'),(36,6,'Katiadi','αªòαªƒαª┐αºƒαª╛αªªαºÇ'),(37,6,'Bhairab','αª¡αºêαª░αª¼'),(38,6,'Tarail','αªñαª╛αº£αª╛αªçαª▓'),(39,6,'Hossainpur','αª╣αºïαª╕αºçαª¿αª¬αºüαª░'),(40,6,'Pakundia','αª¬αª╛αªòαºüαª¿αºìαªªαª┐αºƒαª╛'),(41,6,'Kuliarchar','αªòαºüαª▓αª┐αºƒαª╛αª░αªÜαª░'),(42,6,'Kishoreganj Sadar','αªòαª┐αª╢αºïαª░αªùαª₧αºìαª£ αª╕αªªαª░'),(43,6,'Karimgonj','αªòαª░αª┐αª«αªùαª₧αºìαª£'),(44,6,'Bajitpur','αª¼αª╛αª£αª┐αªñαª¬αºüαª░'),(45,6,'Austagram','αªàαª╖αºìαªƒαªùαºìαª░αª╛αª«'),(46,6,'Mithamoin','αª«αª┐αªáαª╛αª«αªçαª¿'),(47,6,'Nikli','αª¿αª┐αªòαª▓αºÇ'),(48,7,'Harirampur','αª╣αª░αª┐αª░αª╛αª«αª¬αºüαª░'),(49,7,'Saturia','αª╕αª╛αªƒαºüαª░αª┐αºƒαª╛'),(50,7,'Manikganj Sadar','αª«αª╛αª¿αª┐αªòαªùαª₧αºìαª£ αª╕αªªαª░'),(51,7,'Gior','αªÿαª┐αªôαª░'),(52,7,'Shibaloy','αª╢αª┐αª¼αª╛αª▓αºƒ'),(53,7,'Doulatpur','αªªαºîαª▓αªñαª¬αºüαª░'),(54,7,'Singiar','αª╕αª┐αªéαªùαª╛αªçαª░'),(60,9,'Munshiganj Sadar','αª«αºüαª¿αºìαª╕αª┐αªùαª₧αºìαª£ αª╕αªªαª░'),(61,9,'Sreenagar','αª╢αºìαª░αºÇαª¿αªùαª░'),(62,9,'Sirajdikhan','αª╕αª┐αª░αª╛αª£αªªαª┐αªûαª╛αª¿'),(63,9,'Louhajanj','αª▓αºîαª╣αª£αªé'),(64,9,'Gajaria','αªùαª£αª╛αª░αª┐αºƒαª╛'),(65,9,'Tongibari','αªƒαªéαªùαºÇαª¼αª╛αº£αª┐'),(66,10,'Rajbari Sadar','αª░αª╛αª£αª¼αª╛αªíαª╝αºÇ αª╕αªªαª░'),(67,10,'Goalanda','αªùαºïαª»αª╝αª╛αª▓αª¿αºìαªª'),(68,10,'Pangsa','αª¬αª╛αªéαª╢αª╛'),(69,10,'Baliakandi','αª¼αª╛αª▓αª┐αª»αª╝αª╛αªòαª╛αª¿αºìαªªαª┐'),(70,10,'Kalukhali','αªòαª╛αª▓αºüαªûαª╛αª▓αºÇ'),(71,11,'Madaripur Sadar','αª«αª╛αªªαª╛αª░αºÇαª¬αºüαª░ αª╕αªªαª░'),(72,11,'Shibchar','αª╢αª┐αª¼αªÜαª░'),(73,11,'Kalkini','αªòαª╛αª▓αªòαª┐αª¿αª┐'),(74,11,'Rajoir','αª░αª╛αª£αºêαª░'),(75,12,'Gopalganj Sadar','αªùαºïαª¬αª╛αª▓αªùαª₧αºìαª£ αª╕αªªαª░'),(76,12,'Kashiani','αªòαª╛αª╢αª┐αª»αª╝αª╛αª¿αºÇ'),(77,12,'Tungipara','αªƒαºüαªéαªùαºÇαª¬αª╛αªíαª╝αª╛'),(78,12,'Kotalipara','αªòαºïαªƒαª╛αª▓αºÇαª¬αª╛αªíαª╝αª╛'),(79,12,'Muksudpur','αª«αºüαªòαª╕αºüαªªαª¬αºüαª░'),(80,13,'Faridpur Sadar','αª½αª░αª┐αªªαª¬αºüαª░ αª╕αªªαª░'),(81,13,'Alfadanga','αªåαª▓αª½αª╛αªíαª╛αªÖαºìαªùαª╛'),(82,13,'Boalmari','αª¼αºïαºƒαª╛αª▓αª«αª╛αª░αºÇ'),(83,13,'Sadarpur','αª╕αªªαª░αª¬αºüαª░'),(84,13,'Nagarkanda','αª¿αªùαª░αªòαª╛αª¿αºìαªªαª╛'),(85,13,'Bhanga','αª¡αª╛αªÖαºìαªùαª╛'),(86,13,'Charbhadrasan','αªÜαª░αª¡αªªαºìαª░αª╛αª╕αª¿'),(87,13,'Madhukhali','αª«αªºαºüαªûαª╛αª▓αºÇ'),(88,13,'Saltha','αª╕αª╛αª▓αªÑαª╛'),(89,14,'Debidwar','αªªαºçαª¼αª┐αªªαºìαª¼αª╛αª░'),(90,14,'Barura','αª¼αª░αºüαªíαª╝αª╛'),(91,14,'Brahmanpara','αª¼αºìαª░αª╛αª╣αºìαª«αªúαª¬αª╛αªíαª╝αª╛'),(92,14,'Chandina','αªÜαª╛αª¿αºìαªªαª┐αª¿αª╛'),(93,14,'Chauddagram','αªÜαºîαªªαºìαªªαªùαºìαª░αª╛αª«'),(94,14,'Daudkandi','αªªαª╛αªëαªªαªòαª╛αª¿αºìαªªαª┐'),(95,14,'Homna','αª╣αºïαª«αª¿αª╛'),(96,14,'Laksam','αª▓αª╛αªòαª╕αª╛αª«'),(97,14,'Muradnagar','αª«αºüαª░αª╛αªªαª¿αªùαª░'),(98,14,'Nangalkot','αª¿αª╛αªÖαºìαªùαª▓αªòαºïαªƒ'),(99,14,'Comilla Sadar','αªòαºüαª«αª┐αª▓αºìαª▓αª╛ αª╕αªªαª░'),(100,14,'Meghna','αª«αºçαªÿαª¿αª╛'),(101,14,'Monohargonj','αª«αª¿αºïαª╣αª░αªùαª₧αºìαª£'),(102,14,'South Sadar','αªªαªòαºìαª╖αª┐αªú αª╕αªªαª░'),(103,14,'Titas','αªñαª┐αªñαª╛αª╕'),(104,14,'Burichang','αª¼αºüαªíαª╝αª┐αªÜαªé'),(105,14,'Lalmai','αª▓αª╛αª▓αª«αª╛αªç'),(106,15,'Chhagalnaiya','αª¢αª╛αªùαª▓αª¿αª╛αªçαºƒαª╛'),(107,15,'Feni Sadar','αª½αºçαª¿αºÇ αª╕αªªαª░'),(108,15,'Sonagazi','αª╕αºïαª¿αª╛αªùαª╛αª£αºÇ'),(109,15,'Fulgazi','αª½αºüαª▓αªùαª╛αª£αºÇ'),(110,15,'Parshuram','αª¬αª░αª╢αºüαª░αª╛αª«'),(111,15,'Daganbhuiyan','αªªαª╛αªùαª¿αª¡αºéαª₧αª╛'),(112,16,'Brahmanbaria Sadar','αª¼αºìαª░αª╛αª╣αºìαª«αªúαª¼αª╛αº£αª┐αºƒαª╛ αª╕αªªαª░'),(113,16,'Kasba','αªòαª╕αª¼αª╛'),(114,16,'Nasirnagar','αª¿αª╛αª╕αª┐αª░αª¿αªùαª░'),(115,16,'Sarail','αª╕αª░αª╛αªçαª▓'),(116,16,'Ashuganj','αªåαª╢αºüαªùαª₧αºìαª£'),(117,16,'Akhaura','αªåαªûαª╛αªëαº£αª╛'),(118,16,'Nabinagar','αª¿αª¼αºÇαª¿αªùαª░'),(119,16,'Bancharampur','αª¼αª╛αª₧αºìαª¢αª╛αª░αª╛αª«αª¬αºüαª░'),(120,16,'Bijoynagar','αª¼αª┐αª£αºƒαª¿αªùαª░'),(121,17,'Rangamati Sadar','αª░αª╛αªÖαºìαªùαª╛αª«αª╛αªƒαª┐ αª╕αªªαª░'),(122,17,'Kaptai','αªòαª╛αª¬αºìαªñαª╛αªç'),(123,17,'Kawkhali','αªòαª╛αªëαªûαª╛αª▓αºÇ'),(124,17,'Baghaichari','αª¼αª╛αªÿαª╛αªçαª¢αº£αª┐'),(125,17,'Barkal','αª¼αª░αªòαª▓'),(126,17,'Langadu','αª▓αªéαªùαªªαºü'),(127,17,'Rajasthali','αª░αª╛αª£αª╕αºìαªÑαª▓αºÇ'),(128,17,'Belaichari','αª¼αª┐αª▓αª╛αªçαª¢αº£αª┐'),(129,17,'Juraichari','αª£αºüαª░αª╛αª¢αº£αª┐'),(130,17,'Naniarchar','αª¿αª╛αª¿αª┐αºƒαª╛αª░αªÜαª░'),(131,18,'Noakhali Sadar','αª¿αºïαª»αª╝αª╛αªûαª╛αª▓αºÇ αª╕αªªαª░'),(132,18,'Companiganj','αªòαºïαª«αºìαª¬αª╛αª¿αºÇαªùαª₧αºìαª£'),(133,18,'Begumganj','αª¼αºçαªùαª«αªùαª₧αºìαª£'),(134,18,'Hatia','αª╣αª╛αªñαª┐αª»αª╝αª╛'),(135,18,'Subarnachar','αª╕αºüαª¼αª░αºìαªúαªÜαª░'),(136,18,'Kabirhat','αªòαª¼αª┐αª░αª╣αª╛αªƒ'),(137,18,'Senbug','αª╕αºçαª¿αª¼αª╛αªù'),(138,18,'Chatkhil','αªÜαª╛αªƒαªûαª┐αª▓'),(139,18,'Sonaimori','αª╕αºïαª¿αª╛αªçαª«αºüαªíαª╝αºÇ'),(140,19,'Haimchar','αª╣αª╛αªçαª«αªÜαª░'),(141,19,'Kachua','αªòαªÜαºüαª»αª╝αª╛'),(142,19,'Shahrasti','αª╢αª╛αª╣αª░αª╛αª╕αºìαªñαª┐'),(143,19,'Chandpur Sadar','αªÜαª╛αªüαªªαª¬αºüαª░ αª╕αªªαª░'),(144,19,'South Matlab','αªªαªòαºìαª╖αª┐αªú αª«αªñαª▓αª¼'),(145,19,'Hajiganj','αª╣αª╛αª£αºÇαªùαª₧αºìαª£'),(146,19,'North Matlab','αªëαªñαºìαªñαª░ αª«αªñαª▓αª¼'),(147,19,'Faridgonj','αª½αª░αª┐αªªαªùαª₧αºìαª£'),(148,20,'Lakshmipur Sadar','αª▓αªòαºìαª╖αºìαª«αºÇαª¬αºüαª░ αª╕αªªαª░'),(149,20,'Kamalnagar','αªòαª«αª▓αª¿αªùαª░'),(150,20,'Raipur','αª░αª╛αºƒαª¬αºüαª░'),(151,20,'Ramgati','αª░αª╛αª«αªùαªñαª┐'),(152,20,'Ramganj','αª░αª╛αª«αªùαª₧αºìαª£'),(153,21,'Rangunia','αª░αª╛αªÖαºìαªùαºüαª¿αª┐αºƒαª╛'),(154,21,'Sitakunda','αª╕αºÇαªñαª╛αªòαºüαª¿αºìαªí'),(155,21,'Mirsharai','αª«αºÇαª░αª╕αª░αª╛αªç'),(156,21,'Patiya','αª¬αªƒαª┐αºƒαª╛'),(157,21,'Sandwip','αª╕αª¿αºìαªªαºìαª¼αºÇαª¬'),(158,21,'Banshkhali','αª¼αª╛αªüαª╢αªûαª╛αª▓αºÇ'),(159,21,'Boalkhali','αª¼αºïαºƒαª╛αª▓αªûαª╛αª▓αºÇ'),(160,21,'Anwara','αªåαª¿αºïαª»αª╝αª╛αª░αª╛'),(161,21,'Chandanaish','αªÜαª¿αºìαªªαª¿αª╛αªçαª╢'),(162,21,'Satkania','αª╕αª╛αªñαªòαª╛αª¿αª┐αºƒαª╛'),(163,21,'Lohagara','αª▓αºïαª╣αª╛αªùαª╛αº£αª╛'),(164,21,'Hathazari','αª╣αª╛αªƒαª╣αª╛αª£αª╛αª░αºÇ'),(165,21,'Fatikchhari','αª½αªƒαª┐αªòαª¢αº£αª┐'),(166,21,'Raozan','αª░αª╛αªëαª£αª╛αª¿'),(167,21,'Karnafuli','αªòαª░αºìαªúαª½αºüαª▓αºÇ'),(168,22,'Coxsbazar Sadar','αªòαªòαºìαª╕αª¼αª╛αª£αª╛αª░ αª╕αªªαª░'),(169,22,'Chakaria','αªÜαªòαª░αª┐αºƒαª╛'),(170,22,'Kutubdia','αªòαºüαªñαºüαª¼αªªαª┐αºƒαª╛'),(171,22,'Ukhiya','αªëαªûαª┐αºƒαª╛'),(172,22,'Moheshkhali','αª«αª╣αºçαª╢αªûαª╛αª▓αºÇ'),(173,22,'Pekua','αª¬αºçαªòαºüαºƒαª╛'),(174,22,'Ramu','αª░αª╛αª«αºü'),(175,22,'Teknaf','αªƒαºçαªòαª¿αª╛αª½'),(176,23,'Khagrachhari Sadar','αªûαª╛αªùαªíαª╝αª╛αª¢αªíαª╝αª┐ αª╕αªªαª░'),(177,23,'Dighinala','αªªαª┐αªÿαºÇαª¿αª╛αª▓αª╛'),(178,23,'Panchari','αª¬αª╛αª¿αª¢αªíαª╝αª┐'),(179,23,'Laxmichhari','αª▓αªòαºìαª╖αºÇαª¢αªíαª╝αª┐'),(180,23,'Mohalchari','αª«αª╣αª╛αª▓αª¢αªíαª╝αª┐'),(181,23,'Manikchari','αª«αª╛αª¿αª┐αªòαª¢αªíαª╝αª┐'),(182,23,'Ramgarh','αª░αª╛αª«αªùαªíαª╝'),(183,23,'Matiranga','αª«αª╛αªƒαª┐αª░αª╛αªÖαºìαªùαª╛'),(184,23,'Guimara','αªùαºüαªçαª«αª╛αª░αª╛'),(185,24,'Bandarban Sadar','αª¼αª╛αª¿αºìαªªαª░αª¼αª╛αª¿ αª╕αªªαª░'),(186,24,'Alikadam','αªåαª▓αºÇαªòαªªαª«'),(187,24,'Naikhongchhari','αª¿αª╛αªçαªòαºìαª╖αºìαª»αªéαª¢αº£αª┐'),(188,24,'Rowangchhari','αª░αºïαºƒαª╛αªéαª¢αº£αª┐'),(189,24,'Lama','αª▓αª╛αª«αª╛'),(190,24,'Ruma','αª░αºüαª«αª╛'),(191,24,'Thanchi','αªÑαª╛αª¿αªÜαª┐'),(192,25,'Belkuchi','αª¼αºçαª▓αªòαºüαªÜαª┐'),(193,25,'Chauhali','αªÜαºîαª╣αª╛αª▓αª┐'),(194,25,'Kamarkhand','αªòαª╛αª«αª╛αª░αªûαª¿αºìαªª'),(195,25,'Kazipur','αªòαª╛αª£αºÇαª¬αºüαª░'),(196,25,'Raigonj','αª░αª╛αºƒαªùαª₧αºìαª£'),(197,25,'Shahjadpur','αª╢αª╛αª╣αª£αª╛αªªαª¬αºüαª░'),(198,25,'Sirajganj Sadar','αª╕αª┐αª░αª╛αª£αªùαª₧αºìαª£ αª╕αªªαª░'),(199,25,'Tarash','αªñαª╛αº£αª╛αª╢'),(200,25,'Ullapara','αªëαª▓αºìαª▓αª╛αª¬αª╛αº£αª╛'),(201,26,'Sujanagar','αª╕αºüαª£αª╛αª¿αªùαª░'),(202,26,'Ishurdi','αªêαª╢αºìαª¼αª░αªªαºÇ'),(203,26,'Bhangura','αª¡αª╛αªÖαºìαªùαºüαº£αª╛'),(204,26,'Pabna Sadar','αª¬αª╛αª¼αª¿αª╛ αª╕αªªαª░'),(205,26,'Bera','αª¼αºçαº£αª╛'),(206,26,'Atghoria','αªåαªƒαªÿαª░αª┐αºƒαª╛'),(207,26,'Chatmohar','αªÜαª╛αªƒαª«αºïαª╣αª░'),(208,26,'Santhia','αª╕αª╛αªüαªÑαª┐αºƒαª╛'),(209,26,'Faridpur','αª½αª░αª┐αªªαª¬αºüαª░'),(210,27,'Kahaloo','αªòαª╛αª╣αª╛αª▓αºü'),(211,27,'Bogra Sadar','αª¼αªùαºüαº£αª╛ αª╕αªªαª░'),(212,27,'Shariakandi','αª╕αª╛αª░αª┐αºƒαª╛αªòαª╛αª¿αºìαªªαª┐'),(213,27,'Shajahanpur','αª╢αª╛αª£αª╛αª╣αª╛αª¿αª¬αºüαª░'),(214,27,'Dupchanchia','αªªαºüαª¬αªÜαª╛αªÜαª┐αªüαºƒαª╛'),(215,27,'Adamdighi','αªåαªªαª«αªªαª┐αªÿαª┐'),(216,27,'Nondigram','αª¿αª¿αºìαªªαª┐αªùαºìαª░αª╛αª«'),(217,27,'Sonatala','αª╕αºïαª¿αª╛αªñαª▓αª╛'),(218,27,'Dhunot','αªºαºüαª¿αªƒ'),(219,27,'Gabtali','αªùαª╛αª¼αªñαª▓αºÇ'),(220,27,'Sherpur','αª╢αºçαª░αª¬αºüαª░'),(221,27,'Shibganj','αª╢αª┐αª¼αªùαª₧αºìαª£'),(222,28,'Paba','αª¬αª¼αª╛'),(223,28,'Durgapur','αªªαºüαª░αºìαªùαª╛αª¬αºüαª░'),(224,28,'Mohonpur','αª«αºïαª╣αª¿αª¬αºüαª░'),(225,28,'Charghat','αªÜαª╛αª░αªÿαª╛αªƒ'),(226,28,'Puthia','αª¬αºüαªáαª┐αª»αª╝αª╛'),(227,28,'Bagha','αª¼αª╛αªÿαª╛'),(228,28,'Godagari','αªùαºïαªªαª╛αªùαª╛αªíαª╝αºÇ'),(229,28,'Tanore','αªñαª╛αª¿αºïαª░'),(230,28,'Bagmara','αª¼αª╛αªùαª«αª╛αª░αª╛'),(231,29,'Natore Sadar','αª¿αª╛αªƒαºïαª░ αª╕αªªαª░'),(232,29,'Singra','αª╕αª┐αªéαªíαª╝αª╛'),(233,29,'Baraigram','αª¼αªíαª╝αª╛αªçαªùαºìαª░αª╛αª«'),(234,29,'Bagatipara','αª¼αª╛αªùαª╛αªñαª┐αª¬αª╛αªíαª╝αª╛'),(235,29,'Lalpur','αª▓αª╛αª▓αª¬αºüαª░'),(236,29,'Gurudaspur','αªùαºüαª░αºüαªªαª╛αª╕αª¬αºüαª░'),(237,29,'Naldanga','αª¿αª▓αªíαª╛αªÖαºìαªùαª╛'),(238,30,'Akkelpur','αªåαªòαºìαªòαºçαª▓αª¬αºüαª░'),(239,30,'Kalai','αªòαª╛αª▓αª╛αªç'),(240,30,'Khetlal','αªòαºìαª╖αºçαªñαª▓αª╛αª▓'),(241,30,'Panchbibi','αª¬αª╛αªüαªÜαª¼αª┐αª¼αª┐'),(242,30,'Joypurhat Sadar','αª£αºƒαª¬αºüαª░αª╣αª╛αªƒ αª╕αªªαª░'),(243,31,'Chapainawabganj Sadar','αªÜαª╛αªüαª¬αª╛αªçαª¿αª¼αª╛αª¼αªùαª₧αºìαª£ αª╕αªªαª░'),(244,31,'Gomostapur','αªùαºïαª«αª╕αºìαªñαª╛αª¬αºüαª░'),(245,31,'Nachol','αª¿αª╛αªÜαºïαª▓'),(246,31,'Bholahat','αª¡αºïαª▓αª╛αª╣αª╛αªƒ'),(247,31,'Shibganj','αª╢αª┐αª¼αªùαª₧αºìαª£'),(248,32,'Mohadevpur','αª«αª╣αª╛αªªαºçαª¼αª¬αºüαª░'),(249,32,'Badalgachi','αª¼αªªαª▓αªùαª╛αª¢αºÇ'),(250,32,'Patnitala','αª¬αªñαºìαª¿αª┐αªñαª▓αª╛'),(251,32,'Dhamoirhat','αªºαª╛αª«αªçαª░αª╣αª╛αªƒ'),(252,32,'Niamatpur','αª¿αª┐αºƒαª╛αª«αªñαª¬αºüαª░'),(253,32,'Manda','αª«αª╛αª¿αºìαªªαª╛'),(254,32,'Atrai','αªåαªñαºìαª░αª╛αªç'),(255,32,'Raninagar','αª░αª╛αªúαºÇαª¿αªùαª░'),(256,32,'Naogaon Sadar','αª¿αªôαªùαª╛αªü αª╕αªªαª░'),(257,32,'Porsha','αª¬αºïαª░αª╢αª╛'),(258,32,'Sapahar','αª╕αª╛αª¬αª╛αª╣αª╛αª░'),(259,33,'Manirampur','αª«αªúαª┐αª░αª╛αª«αª¬αºüαª░'),(260,33,'Abhaynagar','αªàαª¡αºƒαª¿αªùαª░'),(261,33,'Bagherpara','αª¼αª╛αªÿαª╛αª░αª¬αª╛αº£αª╛'),(262,33,'Chougachha','αªÜαºîαªùαª╛αª¢αª╛'),(263,33,'Jhikargacha','αª¥αª┐αªòαª░αªùαª╛αª¢αª╛'),(264,33,'Keshabpur','αªòαºçαª╢αª¼αª¬αºüαª░'),(265,33,'Jessore Sadar','αª»αª╢αºïαª░ αª╕αªªαª░'),(266,33,'Sharsha','αª╢αª╛αª░αºìαª╢αª╛'),(267,34,'Assasuni','αªåαª╢αª╛αª╢αºüαª¿αª┐'),(268,34,'Debhata','αªªαºçαª¼αª╣αª╛αªƒαª╛'),(269,34,'Kalaroa','αªòαª▓αª╛αª░αºïαºƒαª╛'),(270,34,'Satkhira Sadar','αª╕αª╛αªñαªòαºìαª╖αºÇαª░αª╛ αª╕αªªαª░'),(271,34,'Shyamnagar','αª╢αºìαª»αª╛αª«αª¿αªùαª░'),(272,34,'Tala','αªñαª╛αª▓αª╛'),(273,34,'Kaliganj','αªòαª╛αª▓αª┐αªùαª₧αºìαª£'),(274,35,'Mujibnagar','αª«αºüαª£αª┐αª¼αª¿αªùαª░'),(275,35,'Meherpur Sadar','αª«αºçαª╣αºçαª░αª¬αºüαª░ αª╕αªªαª░'),(276,35,'Gangni','αªùαª╛αªéαª¿αºÇ'),(277,36,'Narail Sadar','αª¿αº£αª╛αªçαª▓ αª╕αªªαª░'),(278,36,'Lohagara','αª▓αºïαª╣αª╛αªùαº£αª╛'),(279,36,'Kalia','αªòαª╛αª▓αª┐αºƒαª╛'),(280,37,'Chuadanga Sadar','αªÜαºüαª»αª╝αª╛αªíαª╛αªÖαºìαªùαª╛ αª╕αªªαª░'),(281,37,'Alamdanga','αªåαª▓αª«αªíαª╛αªÖαºìαªùαª╛'),(282,37,'Damurhuda','αªªαª╛αª«αºüαªíαª╝αª╣αºüαªªαª╛'),(283,37,'Jibannagar','αª£αºÇαª¼αª¿αª¿αªùαª░'),(284,38,'Kushtia Sadar','αªòαºüαª╖αºìαªƒαª┐αºƒαª╛ αª╕αªªαª░'),(285,38,'Kumarkhali','αªòαºüαª«αª╛αª░αªûαª╛αª▓αºÇ'),(286,38,'Khoksa','αªûαºïαªòαª╕αª╛'),(287,38,'Mirpur','αª«αª┐αª░αª¬αºüαª░'),(288,38,'Daulatpur','αªªαºîαª▓αªñαª¬αºüαª░'),(289,38,'Bheramara','αª¡αºçαªíαª╝αª╛αª«αª╛αª░αª╛'),(290,39,'Shalikha','αª╢αª╛αª▓αª┐αªûαª╛'),(291,39,'Sreepur','αª╢αºìαª░αºÇαª¬αºüαª░'),(292,39,'Magura Sadar','αª«αª╛αªùαºüαª░αª╛ αª╕αªªαª░'),(293,39,'Mohammadpur','αª«αª╣αª«αºìαª«αªªαª¬αºüαª░'),(294,40,'Paikgasa','αª¬αª╛αªçαªòαªùαª╛αª¢αª╛'),(295,40,'Fultola','αª½αºüαª▓αªñαª▓αª╛'),(296,40,'Digholia','αªªαª┐αªÿαª▓αª┐αºƒαª╛'),(297,40,'Rupsha','αª░αºéαª¬αª╕αª╛'),(298,40,'Terokhada','αªñαºçαª░αªûαª╛αªªαª╛'),(299,40,'Dumuria','αªíαºüαª«αºüαª░αª┐αºƒαª╛'),(300,40,'Botiaghata','αª¼αªƒαª┐αª»αª╝αª╛αªÿαª╛αªƒαª╛'),(301,40,'Dakop','αªªαª╛αªòαºïαª¬'),(302,40,'Koyra','αªòαºƒαª░αª╛'),(303,41,'Fakirhat','αª½αªòαª┐αª░αª╣αª╛αªƒ'),(304,41,'Bagerhat Sadar','αª¼αª╛αªùαºçαª░αª╣αª╛αªƒ αª╕αªªαª░'),(305,41,'Mollahat','αª«αºïαª▓αºìαª▓αª╛αª╣αª╛αªƒ'),(306,41,'Sarankhola','αª╢αª░αªúαªûαºïαª▓αª╛'),(307,41,'Rampal','αª░αª╛αª«αª¬αª╛αª▓'),(308,41,'Morrelganj','αª«αºïαº£αºçαª▓αªùαª₧αºìαª£'),(309,41,'Kachua','αªòαªÜαºüαºƒαª╛'),(310,41,'Mongla','αª«αºïαªéαª▓αª╛'),(311,41,'Chitalmari','αªÜαª┐αªñαª▓αª«αª╛αª░αºÇ'),(312,42,'Jhenaidah Sadar','αª¥αª┐αª¿αª╛αªçαªªαª╣ αª╕αªªαª░'),(313,42,'Shailkupa','αª╢αºêαª▓αªòαºüαª¬αª╛'),(314,42,'Harinakundu','αª╣αª░αª┐αªúαª╛αªòαºüαª¿αºìαªíαºü'),(315,42,'Kaliganj','αªòαª╛αª▓αºÇαªùαª₧αºìαª£'),(316,42,'Kotchandpur','αªòαºïαªƒαªÜαª╛αªüαªªαª¬αºüαª░'),(317,42,'Moheshpur','αª«αª╣αºçαª╢αª¬αºüαª░'),(318,43,'Jhalakathi Sadar','αª¥αª╛αª▓αªòαª╛αªáαª┐ αª╕αªªαª░'),(319,43,'Kathalia','αªòαª╛αªáαª╛αª▓αª┐αºƒαª╛'),(320,43,'Nalchity','αª¿αª▓αª¢αª┐αªƒαª┐'),(321,43,'Rajapur','αª░αª╛αª£αª╛αª¬αºüαª░'),(322,44,'Bauphal','αª¼αª╛αªëαª½αª▓'),(323,44,'Patuakhali Sadar','αª¬αªƒαºüαºƒαª╛αªûαª╛αª▓αºÇ αª╕αªªαª░'),(324,44,'Dumki','αªªαºüαª«αªòαª┐'),(325,44,'Dashmina','αªªαª╢αª«αª┐αª¿αª╛'),(326,44,'Kalapara','αªòαª▓αª╛αª¬αª╛αªíαª╝αª╛'),(327,44,'Mirzaganj','αª«αª┐αª░αºìαª£αª╛αªùαª₧αºìαª£'),(328,44,'Galachipa','αªùαª▓αª╛αªÜαª┐αª¬αª╛'),(329,44,'Rangabali','αª░αª╛αªÖαºìαªùαª╛αª¼αª╛αª▓αºÇ'),(330,45,'Pirojpur Sadar','αª¬αª┐αª░αºïαª£αª¬αºüαª░ αª╕αªªαª░'),(331,45,'Nazirpur','αª¿αª╛αª£αª┐αª░αª¬αºüαª░'),(332,45,'Kawkhali','αªòαª╛αªëαªûαª╛αª▓αºÇ'),(333,45,'Zianagar','αª£αª┐αºƒαª╛αª¿αªùαª░'),(334,45,'Bhandaria','αª¡αª╛αª¿αºìαªíαª╛αª░αª┐αºƒαª╛'),(335,45,'Mathbaria','αª«αªáαª¼αª╛αº£αºÇαºƒαª╛'),(336,45,'Nesarabad','αª¿αºçαª¢αª╛αª░αª╛αª¼αª╛αªª'),(337,46,'Barisal Sadar','αª¼αª░αª┐αª╢αª╛αª▓ αª╕αªªαª░'),(338,46,'Bakerganj','αª¼αª╛αªòαºçαª░αªùαª₧αºìαª£'),(339,46,'Babuganj','αª¼αª╛αª¼αºüαªùαª₧αºìαª£'),(340,46,'Wazirpur','αªëαª£αª┐αª░αª¬αºüαª░'),(341,46,'Banaripara','αª¼αª╛αª¿αª╛αª░αºÇαª¬αª╛αº£αª╛'),(342,46,'Gournadi','αªùαºîαª░αª¿αªªαºÇ'),(343,46,'Agailjhara','αªåαªùαºêαª▓αª¥αª╛αº£αª╛'),(344,46,'Mehendiganj','αª«αºçαª╣αºçαª¿αºìαªªαª┐αªùαª₧αºìαª£'),(345,46,'Muladi','αª«αºüαª▓αª╛αªªαºÇ'),(346,46,'Hizla','αª╣αª┐αª£αª▓αª╛'),(347,47,'Bhola Sadar','αª¡αºïαª▓αª╛ αª╕αªªαª░'),(348,47,'Borhan Uddin','αª¼αºïαª░αª╣αª╛αª¿ αªëαªªαºìαªªαª┐αª¿'),(349,47,'Charfesson','αªÜαª░αª½αºìαª»αª╛αª╢αª¿'),(350,47,'Doulatkhan','αªªαºîαª▓αªñαªûαª╛αª¿'),(351,47,'Monpura','αª«αª¿αª¬αºüαª░αª╛'),(352,47,'Tazumuddin','αªñαª£αºüαª«αªªαºìαªªαª┐αª¿'),(353,47,'Lalmohan','αª▓αª╛αª▓αª«αºïαª╣αª¿'),(354,48,'Amtali','αªåαª«αªñαª▓αºÇ'),(355,48,'Barguna Sadar','αª¼αª░αªùαºüαª¿αª╛ αª╕αªªαª░'),(356,48,'Betagi','αª¼αºçαªñαª╛αªùαºÇ'),(357,48,'Bamna','αª¼αª╛αª«αª¿αª╛'),(358,48,'Pathorghata','αª¬αª╛αªÑαª░αªÿαª╛αªƒαª╛'),(359,48,'Taltali','αªñαª╛αª▓αªñαª▓αª┐'),(360,49,'Panchagarh Sadar','αª¬αª₧αºìαªÜαªùαªíαª╝ αª╕αªªαª░'),(361,49,'Debiganj','αªªαºçαª¼αºÇαªùαª₧αºìαª£'),(362,49,'Boda','αª¼αºïαªªαª╛'),(363,49,'Atwari','αªåαªƒαºïαª»αª╝αª╛αª░αºÇ'),(364,49,'Tetulia','αªñαºçαªñαºüαª▓αª┐αª»αª╝αª╛'),(365,50,'Nawabganj','αª¿αª¼αª╛αª¼αªùαª₧αºìαª£'),(366,50,'Birganj','αª¼αºÇαª░αªùαª₧αºìαª£'),(367,50,'Ghoraghat','αªÿαºïαº£αª╛αªÿαª╛αªƒ'),(368,50,'Birampur','αª¼αª┐αª░αª╛αª«αª¬αºüαª░'),(369,50,'Parbatipur','αª¬αª╛αª░αºìαª¼αªñαºÇαª¬αºüαª░'),(370,50,'Bochaganj','αª¼αºïαªÜαª╛αªùαª₧αºìαª£'),(371,50,'Kaharol','αªòαª╛αª╣αª╛αª░αºïαª▓'),(372,50,'Fulbari','αª½αºüαª▓αª¼αª╛αº£αºÇ'),(373,50,'Dinajpur Sadar','αªªαª┐αª¿αª╛αª£αª¬αºüαª░ αª╕αªªαª░'),(374,50,'Hakimpur','αª╣αª╛αªòαª┐αª«αª¬αºüαª░'),(375,50,'Khansama','αªûαª╛αª¿αª╕αª╛αª«αª╛'),(376,50,'Birol','αª¼αª┐αª░αª▓'),(377,50,'Chirirbandar','αªÜαª┐αª░αª┐αª░αª¼αª¿αºìαªªαª░'),(378,51,'Lalmonirhat Sadar','αª▓αª╛αª▓αª«αª¿αª┐αª░αª╣αª╛αªƒ αª╕αªªαª░'),(379,51,'Kaliganj','αªòαª╛αª▓αºÇαªùαª₧αºìαª£'),(380,51,'Hatibandha','αª╣αª╛αªñαºÇαª¼αª╛αª¿αºìαªºαª╛'),(381,51,'Patgram','αª¬αª╛αªƒαªùαºìαª░αª╛αª«'),(382,51,'Aditmari','αªåαªªαª┐αªñαª«αª╛αª░αºÇ'),(383,52,'Syedpur','αª╕αºêαª»αª╝αªªαª¬αºüαª░'),(384,52,'Domar','αªíαºïαª«αª╛αª░'),(385,52,'Dimla','αªíαª┐αª«αª▓αª╛'),(386,52,'Jaldhaka','αª£αª▓αªóαª╛αªòαª╛'),(387,52,'Kishorganj','αªòαª┐αª╢αºïαª░αªùαª₧αºìαª£'),(388,52,'Nilphamari Sadar','αª¿αºÇαª▓αª½αª╛αª«αª╛αª░αºÇ αª╕αªªαª░'),(389,53,'Sadullapur','αª╕αª╛αªªαºüαª▓αºìαª▓αª╛αª¬αºüαª░'),(390,53,'Gaibandha Sadar','αªùαª╛αªçαª¼αª╛αª¿αºìαªºαª╛ αª╕αªªαª░'),(391,53,'Palashbari','αª¬αª▓αª╛αª╢αª¼αª╛αº£αºÇ'),(392,53,'Saghata','αª╕αª╛αªÿαª╛αªƒαª╛'),(393,53,'Gobindaganj','αªùαºïαª¼αª┐αª¿αºìαªªαªùαª₧αºìαª£'),(394,53,'Sundarganj','αª╕αºüαª¿αºìαªªαª░αªùαª₧αºìαª£'),(395,53,'Phulchari','αª½αºüαª▓αª¢αº£αª┐'),(396,54,'Thakurgaon Sadar','αªáαª╛αªòαºüαª░αªùαª╛αªüαªô αª╕αªªαª░'),(397,54,'Pirganj','αª¬αºÇαª░αªùαª₧αºìαª£'),(398,54,'Ranisankail','αª░αª╛αªúαºÇαª╢αªéαªòαºêαª▓'),(399,54,'Haripur','αª╣αª░αª┐αª¬αºüαª░'),(400,54,'Baliadangi','αª¼αª╛αª▓αª┐αª»αª╝αª╛αªíαª╛αªÖαºìαªùαºÇ'),(401,55,'Rangpur Sadar','αª░αªéαª¬αºüαª░ αª╕αªªαª░'),(402,55,'Gangachara','αªùαªéαªùαª╛αªÜαªíαª╝αª╛'),(403,55,'Taragonj','αªñαª╛αª░αª╛αªùαª₧αºìαª£'),(404,55,'Badargonj','αª¼αªªαª░αªùαª₧αºìαª£'),(405,55,'Mithapukur','αª«αª┐αªáαª╛αª¬αºüαªòαºüαª░'),(406,55,'Pirgonj','αª¬αºÇαª░αªùαª₧αºìαª£'),(407,55,'Kaunia','αªòαª╛αªëαª¿αª┐αª»αª╝αª╛'),(408,55,'Pirgacha','αª¬αºÇαª░αªùαª╛αª¢αª╛'),(409,56,'Kurigram Sadar','αªòαºüαªíαª╝αª┐αªùαºìαª░αª╛αª« αª╕αªªαª░'),(410,56,'Nageshwari','αª¿αª╛αªùαºçαª╢αºìαª¼αª░αºÇ'),(411,56,'Bhurungamari','αª¡αºüαª░αºüαªÖαºìαªùαª╛αª«αª╛αª░αºÇ'),(412,56,'Phulbari','αª½αºüαª▓αª¼αª╛αº£αºÇ'),(413,56,'Rajarhat','αª░αª╛αª£αª╛αª░αª╣αª╛αªƒ'),(414,56,'Ulipur','αªëαª▓αª┐αª¬αºüαª░'),(415,56,'Chilmari','αªÜαª┐αª▓αª«αª╛αª░αºÇ'),(416,56,'Rowmari','αª░αºîαª«αª╛αª░αºÇ'),(417,56,'Char Rajibpur','αªÜαª░ αª░αª╛αª£αª┐αª¼αª¬αºüαª░'),(418,57,'Balaganj','αª¼αª╛αª▓αª╛αªùαª₧αºìαª£'),(419,57,'Beanibazar','αª¼αª┐αºƒαª╛αª¿αºÇαª¼αª╛αª£αª╛αª░'),(420,57,'Bishwanath','αª¼αª┐αª╢αºìαª¼αª¿αª╛αªÑ'),(421,57,'Companiganj','αªòαºïαª«αºìαª¬αª╛αª¿αºÇαªùαª₧αºìαª£'),(422,57,'Fenchuganj','αª½αºçαª₧αºìαªÜαºüαªùαª₧αºìαª£'),(423,57,'Golapganj','αªùαºïαª▓αª╛αª¬αªùαª₧αºìαª£'),(424,57,'Gowainghat','αªùαºïαºƒαª╛αªçαª¿αªÿαª╛αªƒ'),(425,57,'Jaintiapur','αª£αºêαª¿αºìαªñαª╛αª¬αºüαª░'),(426,57,'Kanaighat','αªòαª╛αª¿αª╛αªçαªÿαª╛αªƒ'),(427,57,'Sylhet Sadar','αª╕αª┐αª▓αºçαªƒ αª╕αªªαª░'),(428,57,'Zakiganj','αª£αªòαª┐αªùαª₧αºìαª£'),(429,57,'Dakshin Surma','αªªαªòαºìαª╖αª┐αªú αª╕αºüαª░αª«αª╛'),(430,57,'Osmani Nagar','αªôαª╕αª«αª╛αª¿αºÇ αª¿αªùαª░'),(431,58,'Barlekha','αª¼αº£αª▓αºçαªûαª╛'),(432,58,'Kamolganj','αªòαª«αª▓αªùαª₧αºìαª£'),(433,58,'Kulaura','αªòαºüαª▓αª╛αªëαº£αª╛'),(434,58,'Moulvibazar Sadar','αª«αºîαª▓αª¡αºÇαª¼αª╛αª£αª╛αª░ αª╕αªªαª░'),(435,58,'Rajnagar','αª░αª╛αª£αª¿αªùαª░'),(436,58,'Sreemangal','αª╢αºìαª░αºÇαª«αªÖαºìαªùαª▓'),(437,58,'Juri','αª£αºüαº£αºÇ'),(438,59,'Nabiganj','αª¿αª¼αºÇαªùαª₧αºìαª£'),(439,59,'Bahubal','αª¼αª╛αª╣αºüαª¼αª▓'),(440,59,'Ajmiriganj','αªåαª£αª«αª┐αª░αºÇαªùαª₧αºìαª£'),(441,59,'Baniachong','αª¼αª╛αª¿αª┐αºƒαª╛αªÜαªé'),(442,59,'Lakhai','αª▓αª╛αªûαª╛αªç'),(443,59,'Chunarughat','αªÜαºüαª¿αª╛αª░αºüαªÿαª╛αªƒ'),(444,59,'Habiganj Sadar','αª╣αª¼αª┐αªùαª₧αºìαª£ αª╕αªªαª░'),(445,59,'Madhabpur','αª«αª╛αªºαª¼αª¬αºüαª░'),(446,60,'Sunamganj Sadar','αª╕αºüαª¿αª╛αª«αªùαª₧αºìαª£ αª╕αªªαª░'),(447,60,'South Sunamganj','αªªαªòαºìαª╖αª┐αªú αª╕αºüαª¿αª╛αª«αªùαª₧αºìαª£'),(448,60,'Bishwambarpur','αª¼αª┐αª╢αºìαª¼αª«αºìαª¡αª░αª¬αºüαª░'),(449,60,'Chhatak','αª¢αª╛αªñαªò'),(450,60,'Jagannathpur','αª£αªùαª¿αºìαª¿αª╛αªÑαª¬αºüαª░'),(451,60,'Dowarabazar','αªªαºïαª»αª╝αª╛αª░αª╛αª¼αª╛αª£αª╛αª░'),(452,60,'Tahirpur','αªñαª╛αª╣αª┐αª░αª¬αºüαª░'),(453,60,'Dharmapasha','αªºαª░αºìαª«αª¬αª╛αª╢αª╛'),(454,60,'Jamalganj','αª£αª╛αª«αª╛αª▓αªùαª₧αºìαª£'),(455,60,'Shalla','αª╢αª╛αª▓αºìαª▓αª╛'),(456,60,'Derai','αªªαª┐αª░αª╛αªç'),(457,61,'Sherpur Sadar','αª╢αºçαª░αª¬αºüαª░ αª╕αªªαª░'),(458,61,'Nalitabari','αª¿αª╛αª▓αª┐αªñαª╛αª¼αª╛αªíαª╝αºÇ'),(459,61,'Sreebordi','αª╢αºìαª░αºÇαª¼αª░αªªαºÇ'),(460,61,'Nokla','αª¿αªòαª▓αª╛'),(461,61,'Jhenaigati','αª¥αª┐αª¿αª╛αªçαªùαª╛αªñαºÇ'),(462,62,'Fulbaria','αª½αºüαª▓αª¼αª╛αº£αºÇαºƒαª╛'),(463,62,'Trishal','αªñαºìαª░αª┐αª╢αª╛αª▓'),(464,62,'Bhaluka','αª¡αª╛αª▓αºüαªòαª╛'),(465,62,'Muktagacha','αª«αºüαªòαºìαªñαª╛αªùαª╛αª¢αª╛'),(466,62,'Mymensingh Sadar','αª«αºƒαª«αª¿αª╕αª┐αªéαª╣ αª╕αªªαª░'),(467,62,'Dhobaura','αªºαºçαª╛αª¼αª╛αªëαº£αª╛'),(468,62,'Phulpur','αª½αºüαª▓αª¬αºüαª░'),(469,62,'Haluaghat','αª╣αª╛αª▓αºüαºƒαª╛αªÿαª╛αªƒ'),(470,62,'Gouripur','αªùαºîαª░αºÇαª¬αºüαª░'),(471,62,'Gafargaon','αªùαª½αª░αªùαª╛αªüαªô'),(472,62,'Iswarganj','αªêαª╢αºìαª¼αª░αªùαª₧αºìαª£'),(473,62,'Nandail','αª¿αª╛αª¿αºìαªªαª╛αªçαª▓'),(474,62,'Tarakanda','αªñαª╛αª░αª╛αªòαª╛αª¿αºìαªªαª╛'),(475,63,'Jamalpur Sadar','αª£αª╛αª«αª╛αª▓αª¬αºüαª░ αª╕αªªαª░'),(476,63,'Melandah','αª«αºçαª▓αª╛αª¿αºìαªªαª╣'),(477,63,'Islampur','αªçαª╕αª▓αª╛αª«αª¬αºüαª░'),(478,63,'Dewangonj','αªªαºçαªôαª»αª╝αª╛αª¿αªùαª₧αºìαª£'),(479,63,'Sarishabari','αª╕αª░αª┐αª╖αª╛αª¼αª╛αªíαª╝αºÇ'),(480,63,'Madarganj','αª«αª╛αªªαª╛αª░αªùαª₧αºìαª£'),(481,63,'Bokshiganj','αª¼αªòαª╢αºÇαªùαª₧αºìαª£'),(482,64,'Barhatta','αª¼αª╛αª░αª╣αª╛αªƒαºìαªƒαª╛'),(483,64,'Durgapur','αªªαºüαª░αºìαªùαª╛αª¬αºüαª░'),(484,64,'Kendua','αªòαºçαª¿αºìαªªαºüαºƒαª╛'),(485,64,'Atpara','αªåαªƒαª¬αª╛αº£αª╛'),(486,64,'Madan','αª«αªªαª¿'),(487,64,'Khaliajuri','αªûαª╛αª▓αª┐αºƒαª╛αª£αºüαª░αºÇ'),(488,64,'Kalmakanda','αªòαª▓αª«αª╛αªòαª╛αª¿αºìαªªαª╛'),(489,64,'Mohongonj','αª«αºïαª╣αª¿αªùαª₧αºìαª£'),(490,64,'Purbadhala','αª¬αºéαª░αºìαª¼αªºαª▓αª╛'),(491,64,'Netrokona Sadar','αª¿αºçαªñαºìαª░αªòαºïαªúαª╛ αª╕αªªαª░'),(495,28,'Boalia','αª¼αºïαª»αª╝αª╛αª▓αª┐αª»αª╝αª╛'),(496,28,'Rajpara','αª░αª╛αª£αª¬αª╛αªíαª╝αª╛'),(500,8,'Adabor','αªåαªªαª╛αª¼αª░'),(501,8,'Badda','αª¼αª╛αªíαºìαªíαª╛'),(502,8,'Bangshal','αª¼αªéαª╢αª╛αª▓'),(503,8,'Baridhara','αª¼αª╛αª░αª┐αªºαª╛αª░αª╛'),(504,8,'Basabo','αª¼αª╛αª╕αª╛αª¼αºï'),(505,8,'Bengali','αª¼αºçαªÖαºìαªùαª╛αª▓αºÇ'),(506,8,'Chandraghona','αªÜαª¿αºìαªªαºìαª░αªÿαºïαª¿αª╛'),(507,8,'Chawkbazar','αªÜαªòαª¼αª╛αª£αª╛αª░'),(508,8,'Danmondi','αªºαª╛αª¿αª«αª¿αºìαªíαª┐'),(509,8,'Demra','αªíαºçαª«αª░αª╛'),(510,8,'Dhaka Cantt','αªóαª╛αªòαª╛ αªòαºìαª»αª╛αª¿αºìαªƒαª¿αª«αºçαª¿αºìαªƒ'),(511,8,'Dhanmondi','αªºαª╛αª¿αª«αª¿αºìαªíαª┐'),(512,8,'East Agargaon','αª¬αºéαª░αºìαª¼ αªåαªùαª╛αª░αªùαª╛αªüαªô'),(513,8,'Gulshan','αªùαºüαª▓αª╢αª╛αª¿'),(514,8,'Hazaribagh','αª╣αª╛αª£αª╛αª░αºÇαª¼αª╛αªù'),(515,8,'Jatrabari','αª»αª╛αªñαºìαª░αª╛αª¼αª╛αªíαª╝αºÇ'),(516,8,'Kafrul','αªòαª╛αª½αª░αºüαª▓'),(517,8,'Kalabagan','αªòαª╛αª▓αª╛αª¼αª╛αªùαª╛αª¿'),(518,8,'Kamrangirchar','αªòαª«αª░αª╛αªÖαºìαªùαª┐αª░αªÜαª░'),(519,8,'Khilgaon','αªûαª┐αª▓αªùαª╛αªüαªô'),(520,8,'Kochukhet','αªòαºïαªÜαºüαªûαºçαªñ'),(521,8,'Kolkata','αªòαª▓αªòαª╛αªñαª╛'),(522,8,'Kotwali','αªòαºïαªƒαªôαª»αª╝αª╛αª▓αºÇ'),(523,8,'Lalbagh','αª▓αª╛αª▓αª¼αª╛αªù'),(524,8,'Lalbagh Thana','αª▓αª╛αª▓αª¼αª╛αªù αªÑαª╛αª¿αª╛'),(525,8,'Mirpur','αª«αª┐αª░αª¬αºüαª░'),(526,8,'Motijheel','αª«αªñαª┐αª¥αª┐αª▓'),(527,8,'Nayapaltan','αª¿αª»αª╝αª╛ αª¬αª▓αºìαªƒαª¿'),(528,8,'New Market','αª¿αª┐αªë αª«αª╛αª░αºìαªòαºçαªƒ'),(529,8,'Nikunja','αª¿αª┐αªòαºüαª₧αºìαª£αª╛'),(530,8,'Paltan','αª¬αª▓αºìαªƒαª¿'),(531,8,'Pahartali','αª¬αª╛αª╣αª╛αª░αªñαª▓αºÇ'),(532,8,'Purarbagh','αª¬αºüαª░αª╛αª░αª¼αª╛αªù'),(533,8,'Rampura','αª░αª╛αª«αª¬αºüαª░αª╛'),(534,8,'Rayerbazar','αª░αª╛αª»αª╝αºçαª░αª¼αª╛αª£αª╛αª░'),(535,8,'Sabujbagh','αª╕αª¼αºüαª£αª¼αª╛αªù'),(536,8,'Sadarghat','αª╕αªªαª░αªÿαª╛αªƒ'),(537,8,'Savar','αª╕αª╛αª¡αª╛αª░'),(538,8,'Shahbagh','αª╢αª╛αª╣αª¼αª╛αªù'),(539,8,'Shantinagar','αª╢αª╛αª¿αºìαªñαª┐αª¿αªùαª░'),(540,8,'Sherebangla Nagar','αª╢αºçαª░αºçαª¼αª╛αªéαª▓αª╛ αª¿αªùαª░'),(541,8,'Shyamoli','αª╢αºìαª»αª╛αª«αª▓αºÇ'),(542,8,'Sutrapur','αª╕αºéαªñαºìαª░αª╛αª¬αºüαª░'),(543,8,'Tejgaon','αªñαºçαª£αªùαª╛αªüαªô'),(544,8,'Tongi','αªƒαªÖαºìαªùαºÇ'),(545,8,'Uttara','αªëαªñαºìαªñαª░αª╛'),(546,8,'Vashan','αª¡αª╛αª╖αª╛αª¿'),(547,8,'Wari','αªôαºƒαª╛αª░αºÇ'),(548,8,'Zigatola','αª£αª┐αªùαª╛αªñαª▓αª╛');
/*!40000 ALTER TABLE `thanas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `temp_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user.png',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'cover.png',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_status_unique` (`email`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','superadmin@gmail.com',NULL,NULL,1,NULL,'$2y$10$UxEyJFREXB2xzDGkEJqEE.yps3n49E6tbk8w5EW0Rr3MP/bha9eXC',NULL,'user.png','cover.png',NULL,'2025-01-23 01:19:45','2025-01-23 01:19:45'),(2,'admin','admin@gmail.com','01745855220','Rangpur Sadar',1,NULL,'$2y$10$qJDzoj7.pzYn4PAoROuQu.Nmjqv.kbUwErJEesa9Q8z5Au7FmP9rK',NULL,'1784972189_6a64839d682d8.png','cover.png',NULL,'2025-01-23 01:19:46','2026-07-25 03:36:45'),(138,'ratul','ratul@gmail.com','01750887855','belabo',1,NULL,'$2y$10$KrOykjdo0f1INO7M5Y1Ew./nChOsBxtuJnqQE5pRuNlVvBC7S5LGa','123456','01750887855.png','cover.png',NULL,'2026-07-27 23:08:28','2026-07-27 23:08:28'),(139,'hossion','hossainshohag898@gmail.com','01765750791','shibchar',1,NULL,'$2y$10$i1uCVHc9/hECR9zBb9ZSC.KlMrmFmtcDCQM65C8ro0r/EHtvW12F6','415263','01765750791.jpg','cover.png',NULL,'2026-07-27 23:22:46','2026-07-28 00:01:26'),(140,'roni','roni@gmail.com','01306526906','adabor',1,NULL,'$2y$10$Y8iwSjIq4.Pxsd2zC8KlNOHWTRGMlYV54P30Vf1ItRB4eQvQj87nO','123456','01306526906.png','cover.png',NULL,'2026-07-28 00:32:35','2026-07-28 00:32:35'),(141,'fahim',NULL,'01799360170','gangni',1,NULL,'$2y$10$d6tXIpg65LcEX9joVS178OB42ZjA9S06tqQc5bjUZXduoKXigcM1e','123456','01799360170.jpg','cover.png',NULL,'2026-07-28 00:34:47','2026-07-28 00:34:47'),(142,'jahangir','jahangir@gmail.com','01314121414','haripur',1,NULL,'$2y$10$AJouOIfgKiGMNNg4aWhF7OLJvF8G4lY3FbaQgYCZ8A3froLK5bVNq','123456','01314121414.png','cover.png',NULL,'2026-07-28 02:43:34','2026-07-28 02:43:34');
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

-- Dump completed on 2026-07-28 15:28:16
