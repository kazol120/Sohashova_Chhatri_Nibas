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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `room_booking_history_id` bigint unsigned DEFAULT NULL,
  `complaint_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0=Pending, 1=Accepted/Resolved',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `is_seen_by_user` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaints_user_id_foreign` (`user_id`),
  CONSTRAINT `complaints_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposits`
--

LOCK TABLES `deposits` WRITE;
/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
INSERT INTO `deposits` VALUES (27,193,10000.00,'2026-09-02',2,NULL,'2026-09-02 01:48:37','2026-09-02 01:48:37'),(28,192,500.00,'2026-09-02',2,NULL,'2026-09-02 01:53:21','2026-09-02 01:53:21');
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
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `districts` VALUES (1,1,'Narsingdi','নরসিংদী'),(2,1,'Gazipur','গাজীপুর'),(3,1,'Shariatpur','শরীয়তপুর'),(4,1,'Narayanganj','নারায়ণগঞ্জ'),(5,1,'Tangail','টাঙ্গাইল'),(6,1,'Kishoreganj','কিশোরগঞ্জ'),(7,1,'Manikganj','মানিকগঞ্জ'),(8,1,'Dhaka','ঢাকা'),(9,1,'Munshiganj','মুন্সিগঞ্জ'),(10,1,'Rajbari','রাজবাড়ী'),(11,1,'Madaripur','মাদারীপুর'),(12,1,'Gopalganj','গোপালগঞ্জ'),(13,1,'Faridpur','ফরিদপুর'),(14,2,'Comilla','কুমিল্লা'),(15,2,'Feni','ফেনী'),(16,2,'Brahmanbaria','ব্রাহ্মণবাড়িয়া'),(17,2,'Rangamati','রাঙ্গামাটি'),(18,2,'Noakhali','নোয়াখালী'),(19,2,'Chandpur','চাঁদপুর'),(20,2,'Lakshmipur','লক্ষ্মীপুর'),(21,2,'Chittagong','চট্টগ্রাম'),(22,2,'Coxsbazar','কক্সবাজার'),(23,2,'Khagrachhari','খাগড়াছড়ি'),(24,2,'Bandarban','বান্দরবান'),(25,3,'Sirajganj','সিরাজগঞ্জ'),(26,3,'Pabna','পাবনা'),(27,3,'Bogra','বগুড়া'),(28,3,'Rajshahi','রাজশাহী'),(29,3,'Natore','নাটোর'),(30,3,'Joypurhat','জয়পুরহাট'),(31,3,'Chapainawabganj','চাঁপাইনবাবগঞ্জ'),(32,3,'Naogaon','নওগাঁ'),(33,4,'Jessore','যশোর'),(34,4,'Satkhira','সাতক্ষীরা'),(35,4,'Meherpur','মেহেরপুর'),(36,4,'Narail','নড়াইল'),(37,4,'Chuadanga','চুয়াডাঙ্গা'),(38,4,'Kushtia','কুষ্টিয়া'),(39,4,'Magura','মাগুরা'),(40,4,'Khulna','খুলনা'),(41,4,'Bagerhat','বাগেরহাট'),(42,4,'Jhenaidah','ঝিনাইদহ'),(43,5,'Jhalakathi','ঝালকাঠি'),(44,5,'Patuakhali','পটুয়াখালী'),(45,5,'Pirojpur','পিরোজপুর'),(46,5,'Barisal','বরিশাল'),(47,5,'Bhola','ভোলা'),(48,5,'Barguna','বরগুনা'),(49,6,'Panchagarh','পঞ্চগড়'),(50,6,'Dinajpur','দিনাজপুর'),(51,6,'Lalmonirhat','লালমনিরহাট'),(52,6,'Nilphamari','নীলফামারী'),(53,6,'Gaibandha','গাইবান্ধা'),(54,6,'Thakurgaon','ঠাকুরগাঁও'),(55,6,'Rangpur','রংপুর'),(56,6,'Kurigram','কুড়িগ্রাম'),(57,7,'Sylhet','সিলেট'),(58,7,'Moulvibazar','মৌলভীবাজার'),(59,7,'Habiganj','হবিগঞ্জ'),(60,7,'Sunamganj','সুনামগঞ্জ'),(61,8,'Sherpur','শেরপুর'),(62,8,'Mymensingh','ময়মনসিংহ'),(63,8,'Jamalpur','জামালপুর'),(64,8,'Netrokona','নেত্রকোণা');
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
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `divisions` VALUES (1,'Dhaka','ঢাকা'),(2,'Chittagong','চট্টগ্রাম'),(3,'Rajshahi','রাজশাহী'),(4,'Khulna','খুলনা'),(5,'Barisal','বরিশাল'),(6,'Rangpur','রংপুর'),(7,'Sylhet','সিলেট'),(8,'Mymensingh','ময়মনসিংহ');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `expense_note` text COLLATE utf8mb4_unicode_ci,
  `expense_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_category_foreign` (`expense_category`),
  CONSTRAINT `expenses_expense_category_foreign` FOREIGN KEY (`expense_category`) REFERENCES `expense_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
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
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `floors`
--

LOCK TABLES `floors` WRITE;
/*!40000 ALTER TABLE `floors` DISABLE KEYS */;
INSERT INTO `floors` VALUES (59,'floor_1788082236_6a93f83c5f32c.jfif','First Floor','2026-08-30 03:30:36','2026-08-30 03:30:36'),(60,'floor_1788084465_6a9400f1b5cba.jpg','Second Floor','2026-08-30 04:07:45','2026-08-30 04:07:45'),(61,'floor_1788084758_6a94021675ecb.jfif','Fourth Floor','2026-08-30 04:12:38','2026-08-30 04:12:38');
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
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `hostel_rules`
--

DROP TABLE IF EXISTS `hostel_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hostel_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hostel_rules`
--

LOCK TABLES `hostel_rules` WRITE;
/*!40000 ALTER TABLE `hostel_rules` DISABLE KEYS */;
INSERT INTO `hostel_rules` VALUES (1,'ti ti-calendar','মেসের ভাড়া ০৭ তারিখের মধ্যে পরিশোধ করতে হবে।','2026-09-05 22:23:47','2026-09-05 22:23:47'),(2,'ti ti-pig-money','অগ্রিম দুই মাসের টাকা জামানত দিতে হবে । মেস ছাড়লে ০২ মাস পূর্বেই মেস কর্তৃপক্ষকে জানাতে হবে। অন্যথায় দুই মাসের ভাড়া দিয়ে মেস ছাড়তে হবে।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(3,'ti ti-sunset','মাগরিবের আযানের পর মেসের বাহিরে থাকলে মেস অভিভাবককে জানিয়ে দিতে হবে।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(4,'ti ti-arrows-exchange','রুম চেঞ্জ করতে চাইলে ৫০০ টাকা প্রদান করতে হবে।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(5,'ti ti-bolt','ওয়াইফাই, ক্লিনার, বিদ্যুৎ - ভাড়ার সাথে ইনক্লুড (সংযুক্ত) থাকবে।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(6,'ti ti-cash','উন্নয়ন ফি 25০০/- (এককালীন) ।','2026-09-05 22:23:48','2026-09-05 22:37:07'),(7,'ti ti-door','একক সিট ভাড়া ৩০০০/- টাকা, দুই সিট ভাড়া ২৫০০/- টাকা।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(8,'ti ti-alert-triangle','সকল কিছু পরিবর্তন, পরিবর্ধন, যে কোন মুহূর্তে কর্তৃপক্ষ সিদ্ধান্ত নিতে পারিবে।','2026-09-05 22:23:48','2026-09-05 22:23:48'),(9,'ti ti-shield-check','মেসের নিয়ম-কানুন মেনে চলতে হবে। কারো বিরুদ্ধে কোনো অভিযোগ আসলে এবং তা প্রমাণিত হলে সিট বাতিলসহ যেকোনো ব্যবস্থা নেয়ার অধিকার কর্তৃপক্ষ রাখে।','2026-09-05 22:23:48','2026-09-05 22:23:48');
/*!40000 ALTER TABLE `hostel_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hotel_managems`
--

DROP TABLE IF EXISTS `hotel_managems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hotel_managems` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `meal_requests`
--

DROP TABLE IF EXISTS `meal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meal_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `total_days` int NOT NULL DEFAULT '1',
  `request_type` enum('off','full','half_day','half_night') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `user_notified` tinyint(1) NOT NULL DEFAULT '0',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meal_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `meal_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meal_requests`
--

LOCK TABLES `meal_requests` WRITE;
/*!40000 ALTER TABLE `meal_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `meal_requests` ENABLE KEYS */;
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
  `is_off` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `made_by` (`user_id`),
  KEY `meals_made_by_foreign` (`made_by`),
  CONSTRAINT `meals_made_by_foreign` FOREIGN KEY (`made_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meals`
--

LOCK TABLES `meals` WRITE;
/*!40000 ALTER TABLE `meals` DISABLE KEYS */;
INSERT INTO `meals` VALUES (56,192,'2026-09-02',0,1,2,'2026-09-02 01:51:35','2026-09-02 01:53:21','Auto Full Meal',0),(57,193,'2026-09-02',0,1,2,'2026-09-02 01:51:35','2026-09-02 01:51:35','Auto Full Meal',0),(58,192,'2026-09-03',0,1,2,'2026-09-02 01:53:21','2026-09-02 01:53:21','Auto Full Meal',0);
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
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'2025_01_06_070719_create_otps_table',1),(86,'2014_10_12_000000_create_users_table',2),(87,'2014_10_12_100000_create_password_reset_tokens_table',2),(88,'2014_10_12_100000_create_password_resets_table',2),(89,'2019_08_19_000000_create_failed_jobs_table',2),(90,'2019_12_14_000001_create_personal_access_tokens_table',2),(91,'2024_12_12_063036_create_permission_tables',2),(92,'2025_01_01_063809_create_settings_table',2),(104,'2026_01_17_060442_create_floors_table',3),(122,'2026_03_09_063554_create_residence_overviews_table',7),(123,'2026_03_10_053035_create_galleries_table',8),(133,'2026_04_15_065719_create_staff_attendances_table',13),(134,'2026_04_16_060109_create_staff_salary_payments_table',14),(135,'2026_04_16_061047_create_staff_salary_payments_table',15),(136,'2026_04_18_121356_create_expense_types_table',16),(139,'2026_04_19_070131_create_expenses_table',17),(141,'2026_04_21_073109_create_suppliers_table',19),(150,'2026_04_22_053513_create_brand_categories_table',23),(155,'2026_04_22_094327_create_products_table',25),(169,'2026_04_25_083914_create_manage_sales_table',27),(171,'2026_04_23_051506_create_product_purchases_table',28),(172,'2026_01_17_060835_create_rooms_table',29),(174,'2026_04_22_044000_create_brands_table',30),(181,'2026_04_29_095007_create_product_distributions_table',31),(183,'2026_01_26_043313_create_room_booking_histories_table',32),(185,'2026_03_12_081022_create_staffs_table',33),(186,'2026_05_14_071417_create_hotel_managems_table',34),(187,'2026_07_15_055821_add_family_details_to_room_booking_histories_table',35),(188,'2026_07_18_044231_create_notices_table',36),(191,'2026_07_18_080126_add_booking_months_to_room_booking_histories_table',37),(192,'2026_07_18_090254_create_monthly_payments_table',38),(193,'2026_07_18_102718_add_months_to_extend_to_monthly_payments_table',39),(194,'2026_07_18_120000_create_room_seats_table',40),(195,'2026_07_19_110000_add_user_type_to_room_booking_histories_table',41),(196,'2026_07_19_120000_add_paid_and_due_to_monthly_payments_table',42),(197,'2026_07_19_084830_add_carried_forward_due_to_monthly_payments_table',43),(198,'2026_07_21_111201_add_will_leave_to_room_booking_histories_table',44),(199,'2026_07_22_120000_add_education_and_workplace_to_room_booking_histories_table',45),(200,'2026_04_01_060849_create_meals_table',46),(201,'2026_04_01_060909_create_deposits_table',46),(202,'2026_04_01_060916_create_fines_table',46),(203,'2026_06_13_072838_create_member_releases_table',46),(204,'2026_07_23_054132_add_address_to_room_booking_histories_table',47),(205,'2026_07_26_120000_add_seat_id_to_product_distributions_table',48),(206,'2026_07_27_161500_update_room_booking_histories_columns',48),(207,'2026_07_27_180000_add_parent_phones_to_room_booking_histories_table',49),(208,'2026_07_28_130000_add_development_fee_to_room_booking_histories_table',50),(209,'2026_07_28_170000_make_check_out_nullable_in_room_booking_histories_table',51),(210,'2026_07_29_110000_add_notice_date_to_room_booking_histories_table',52),(211,'2026_07_30_000000_create_room_change_histories_table',53),(212,'2026_08_04_180000_create_complaints_table',54),(213,'2026_08_09_130000_add_is_off_to_meals_table',55),(214,'2026_08_09_160000_create_meal_requests_table',55),(215,'2026_08_09_170000_add_end_date_to_meal_requests_table',55),(216,'2026_08_09_182500_add_advance_fee_to_room_booking_histories_table',55),(217,'2026_08_11_160000_add_is_seen_to_room_booking_histories_table',55),(218,'2026_08_16_163800_create_sliders_table',56),(219,'2026_09_06_103000_create_hostel_rules_table',57);
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
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',25),(3,'App\\Models\\User',26),(3,'App\\Models\\User',27),(3,'App\\Models\\User',28),(3,'App\\Models\\User',29),(3,'App\\Models\\User',30),(3,'App\\Models\\User',31),(3,'App\\Models\\User',32),(3,'App\\Models\\User',33),(3,'App\\Models\\User',34),(3,'App\\Models\\User',35),(3,'App\\Models\\User',36),(3,'App\\Models\\User',37),(3,'App\\Models\\User',38),(3,'App\\Models\\User',39),(3,'App\\Models\\User',40),(3,'App\\Models\\User',41),(3,'App\\Models\\User',42),(3,'App\\Models\\User',43),(3,'App\\Models\\User',44),(3,'App\\Models\\User',45),(3,'App\\Models\\User',46),(3,'App\\Models\\User',47),(3,'App\\Models\\User',48),(3,'App\\Models\\User',49),(3,'App\\Models\\User',50),(3,'App\\Models\\User',51),(3,'App\\Models\\User',58),(3,'App\\Models\\User',59),(4,'App\\Models\\User',60),(4,'App\\Models\\User',62),(4,'App\\Models\\User',63),(4,'App\\Models\\User',71),(3,'App\\Models\\User',72),(3,'App\\Models\\User',73),(4,'App\\Models\\User',74),(3,'App\\Models\\User',75),(3,'App\\Models\\User',76),(3,'App\\Models\\User',77),(3,'App\\Models\\User',78),(3,'App\\Models\\User',79),(3,'App\\Models\\User',80),(3,'App\\Models\\User',81),(3,'App\\Models\\User',82),(3,'App\\Models\\User',83),(3,'App\\Models\\User',84),(3,'App\\Models\\User',85),(3,'App\\Models\\User',86),(3,'App\\Models\\User',87),(3,'App\\Models\\User',88),(3,'App\\Models\\User',89),(3,'App\\Models\\User',90),(3,'App\\Models\\User',91),(3,'App\\Models\\User',92),(3,'App\\Models\\User',93),(3,'App\\Models\\User',94),(3,'App\\Models\\User',95),(3,'App\\Models\\User',96),(3,'App\\Models\\User',97),(3,'App\\Models\\User',98),(3,'App\\Models\\User',99),(3,'App\\Models\\User',100),(3,'App\\Models\\User',101),(3,'App\\Models\\User',102),(3,'App\\Models\\User',103),(3,'App\\Models\\User',104),(3,'App\\Models\\User',105),(3,'App\\Models\\User',106),(3,'App\\Models\\User',107),(3,'App\\Models\\User',108),(3,'App\\Models\\User',109),(3,'App\\Models\\User',110),(5,'App\\Models\\User',111),(6,'App\\Models\\User',112),(6,'App\\Models\\User',113),(5,'App\\Models\\User',114),(6,'App\\Models\\User',115),(4,'App\\Models\\User',116),(4,'App\\Models\\User',117),(4,'App\\Models\\User',118),(4,'App\\Models\\User',119),(5,'App\\Models\\User',120),(5,'App\\Models\\User',121),(5,'App\\Models\\User',122),(6,'App\\Models\\User',123),(6,'App\\Models\\User',124),(5,'App\\Models\\User',125),(6,'App\\Models\\User',126),(6,'App\\Models\\User',127),(6,'App\\Models\\User',128),(5,'App\\Models\\User',129),(6,'App\\Models\\User',130),(5,'App\\Models\\User',131),(5,'App\\Models\\User',132),(5,'App\\Models\\User',133),(5,'App\\Models\\User',134),(5,'App\\Models\\User',135),(5,'App\\Models\\User',136),(6,'App\\Models\\User',137),(5,'App\\Models\\User',138),(6,'App\\Models\\User',139),(5,'App\\Models\\User',140),(6,'App\\Models\\User',141),(6,'App\\Models\\User',142),(5,'App\\Models\\User',143),(5,'App\\Models\\User',144),(5,'App\\Models\\User',145),(6,'App\\Models\\User',146),(6,'App\\Models\\User',147),(5,'App\\Models\\User',148),(5,'App\\Models\\User',149),(5,'App\\Models\\User',150),(4,'App\\Models\\User',151),(6,'App\\Models\\User',152),(5,'App\\Models\\User',153),(5,'App\\Models\\User',154),(5,'App\\Models\\User',155),(5,'App\\Models\\User',156),(5,'App\\Models\\User',157),(5,'App\\Models\\User',158),(5,'App\\Models\\User',159),(5,'App\\Models\\User',160),(5,'App\\Models\\User',161),(5,'App\\Models\\User',162),(5,'App\\Models\\User',163),(5,'App\\Models\\User',164),(5,'App\\Models\\User',165),(5,'App\\Models\\User',178),(5,'App\\Models\\User',179),(5,'App\\Models\\User',180),(5,'App\\Models\\User',182),(5,'App\\Models\\User',187),(5,'App\\Models\\User',188),(5,'App\\Models\\User',191),(5,'App\\Models\\User',192),(5,'App\\Models\\User',193),(5,'App\\Models\\User',195);
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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monthly_payments`
--

LOCK TABLES `monthly_payments` WRITE;
/*!40000 ALTER TABLE `monthly_payments` DISABLE KEYS */;
INSERT INTO `monthly_payments` VALUES (157,177,'2026-08',2500.00,0.00,2500.00,0.00,1,'Cash',NULL,'[02-09-2026 1:40 PM] Collected ৳1500 via Cash\n[02-09-2026 1:55 PM] Collected ৳1000 via Cash','paid','admin','2026-09-02 01:29:13','2026-09-02 02:07:50'),(158,178,'2026-07',3000.00,0.00,3000.00,0.00,1,'Cash',NULL,'[02-09-2026 2:05 PM] Collected ৳1500 via Cash\n[02-09-2026 2:07 PM] Collected ৳1500 via Cash','paid','admin','2026-09-02 01:29:13','2026-09-02 02:07:50'),(159,177,'2026-09',2500.00,1000.00,3500.00,0.00,1,'Cash',NULL,'[02-09-2026 1:55 PM] Collected ৳2500 via Cash','paid','admin','2026-09-02 01:34:00','2026-09-02 02:07:50'),(160,178,'2026-09',3000.00,1500.00,2000.00,2500.00,1,'Cash',NULL,'[02-09-2026 2:07 PM] Collected ৳2000 via Cash','partial','admin','2026-09-02 02:04:54','2026-09-02 02:07:50');
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
INSERT INTO `notices` VALUES (1,'টিএসএস ভিলা - বিশেষ বুকিং ডিসকাউন্ট অফার! এখনই সিট বুক করুন এবং বিশেষ ছাড় উপভোগ করুন — সিট সীমিত, আজই আপনার জায়গা নিশ্চিত করুন','2026-07-17 22:45:42','2026-08-05 23:04:32');
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
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
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `memo_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `single_price` decimal(10,2) NOT NULL,
  `total_price_available` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date NOT NULL,
  `customer_quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_distributions`
--

LOCK TABLES `product_distributions` WRITE;
/*!40000 ALTER TABLE `product_distributions` DISABLE KEYS */;
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
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `single_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL,
  `available_quantity` int NOT NULL DEFAULT '0',
  `total_price_available` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `memo_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_purchases`
--

LOCK TABLES `product_purchases` WRITE;
/*!40000 ALTER TABLE `product_purchases` DISABLE KEYS */;
INSERT INTO `product_purchases` VALUES (16,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(17,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:47:57'),(18,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(19,7,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 05:48:56'),(20,8,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-11 05:57:15'),(21,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 05:47:57'),(22,7,27,NULL,'Biscuit',60.00,300.00,5,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:23','2026-05-11 05:47:57'),(23,7,24,NULL,'Coffee',200.00,1000.00,5,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:24','2026-05-16 00:26:35'),(24,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:47:57'),(25,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-11 05:48:19'),(26,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:47:57'),(27,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-11 05:48:55'),(28,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 05:49:58'),(29,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:48:19'),(30,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-11 05:50:12'),(31,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:48:19'),(32,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-17 05:35:55'),(33,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(34,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(35,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:48:19'),(36,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(37,7,24,NULL,'Coffee',200.00,2000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-17 05:44:11'),(38,8,24,NULL,'Coffee',200.00,2000.00,10,5,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-06-05 23:04:41'),(39,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(40,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:35:55'),(41,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:48:19'),(42,7,26,NULL,'Bread',100.00,700.00,7,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-17 05:36:18'),(43,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-17 05:43:41'),(44,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:47:33'),(45,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:49:21'),(46,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(47,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-17 05:47:33'),(48,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:49:21'),(49,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-07-26 01:29:02'),(50,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-07-27 00:56:36'),(51,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(52,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-11 05:49:59'),(53,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(54,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(55,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(56,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-07-27 00:56:36'),(57,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-08-03 22:51:46'),(58,8,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-08-03 22:51:46'),(59,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:57:15'),(60,8,26,NULL,'Bread',100.00,1000.00,10,8,800.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-08-03 22:51:46'),(61,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(62,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(63,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(64,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(65,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(66,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(67,7,24,NULL,'Coffee',200.00,1000.00,5,5,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:34:24','2026-05-06 02:34:24'),(68,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(69,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-06 02:41:10'),(70,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-11 05:57:15'),(71,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-11 05:57:15'),(72,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(73,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(74,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(75,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(76,7,27,NULL,'Biscuit',60.00,360.00,6,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:35:29','2026-05-16 03:48:48'),(77,7,26,NULL,'Bread',100.00,700.00,7,7,700.00,'2026-05-06',0.00,NULL,'2026-05-06 02:39:02','2026-05-06 02:39:02'),(78,7,27,NULL,'Biscuit',60.00,60.00,1,0,0.00,'2026-05-06',0.00,NULL,'2026-05-06 02:40:45','2026-05-16 05:25:31'),(79,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-06',0.00,NULL,'2026-05-06 02:41:10','2026-05-06 02:41:10'),(80,8,26,NULL,'Bread',100.00,1000.00,10,2,200.00,'2026-05-06',0.00,NULL,'2026-05-06 02:33:23','2026-05-11 03:09:18'),(81,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:11','2026-05-06 01:32:01'),(82,6,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:49:24','2026-05-17 05:30:03'),(83,9,26,NULL,'Bread',100.00,1000.00,10,0,0.00,'2026-05-03',0.00,NULL,'2026-05-03 03:55:16','2026-05-06 01:37:09'),(84,7,24,NULL,'Coffee',200.00,2000.00,10,7,1400.00,'2026-05-03',0.00,NULL,'2026-05-03 04:02:01','2026-05-11 03:09:18'),(85,8,24,NULL,'Coffee',200.00,2000.00,10,10,2000.00,'2026-05-06',0.00,NULL,'2026-05-06 01:56:40','2026-05-06 01:56:40'),(86,8,27,NULL,'Biscuit',60.00,600.00,10,0,0.00,'2026-05-11',0.00,'4521','2026-05-11 05:44:11','2026-05-17 05:35:20'),(87,8,26,NULL,'Bread',100.00,1000.00,10,10,1000.00,'2026-05-11',0.00,'4521','2026-05-11 05:44:12','2026-05-11 05:44:12'),(88,8,27,NULL,'Biscuit',60.00,6000.00,100,67,4020.00,'2026-07-26',0.00,NULL,'2026-07-26 01:30:08','2026-08-03 22:51:46'),(89,10,29,NULL,'test',200.00,6000.00,30,23,4600.00,'2026-07-29',0.00,'4512','2026-07-29 03:37:35','2026-07-29 03:39:05'),(90,9,24,NULL,'Coffee',200.00,4000.00,20,20,4000.00,'2026-08-04',0.00,NULL,'2026-08-03 22:59:04','2026-08-03 22:59:04');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buy_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sell_price` decimal(10,2) DEFAULT '0.00',
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (24,7,14,'Coffee',200.00,0.00,'2026-04-29','2026-04-29 05:53:56','2026-04-29 05:53:56'),(25,8,14,'tea',50.00,0.00,'2026-04-29','2026-04-29 05:54:27','2026-04-29 05:54:27'),(26,9,15,'Bread',100.00,0.00,'2026-04-29','2026-04-29 05:55:56','2026-04-29 05:55:56'),(27,9,15,'Biscuit',60.00,0.00,'2026-04-29','2026-04-29 05:56:17','2026-04-29 05:56:17'),(28,10,16,'Rice,Fish Curry,Chicken Curry,Vegetables,Dal',120.00,0.00,'2026-04-29','2026-04-29 05:57:37','2026-04-29 05:57:37'),(29,8,15,'test',200.00,0.00,'2026-07-29','2026-07-29 03:36:36','2026-07-29 03:36:36');
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_back` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_front` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `residence_overviews` VALUES (4,'A Premier Girls\' Residence in the Heart of Rangpur.','A premier girls\' residence in the heart of Rangpur. Established in 2023, Sohahova Chhatri Nibas offers a safe, secure, and intellectually stimulating environment for female students. We provide thoughtfully furnished accommodation with comprehensive amenities, including high-speed Wi-Fi, nutritious meal plans, 24/7 security, laundry services, and dedicated study lounges. Our mission is to empower residents to excel academically and personally.','1784102863_xhgXxXvK.png','1786187982_GeP7P7ZR.jpeg','2026-03-09 02:47:55','2026-08-08 05:19:42');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor_number_room_number_roomprice` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workplace_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_nid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_nid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `division_id` bigint unsigned DEFAULT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `thana_id` bigint unsigned DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `pay_cash_in` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_online` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_amount` decimal(12,2) DEFAULT '0.00',
  `development_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `advance_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `check_in` date NOT NULL,
  `check_out` date DEFAULT NULL,
  `booking_months` int DEFAULT '1',
  `today_check_out` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_seen` tinyint NOT NULL DEFAULT '0',
  `will_leave` tinyint NOT NULL DEFAULT '0',
  `notice_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `room_booking_histories_chk_1` CHECK (json_valid(`floor_number_room_number_roomprice`))
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_booking_histories`
--

LOCK TABLES `room_booking_histories` WRITE;
/*!40000 ALTER TABLE `room_booking_histories` DISABLE KEYS */;
INSERT INTO `room_booking_histories` VALUES (177,'01750887855.png','[{\"floornumber\":\"First Floor\",\"roomnumber\":\"A-1-Block A\",\"advance_price\":3000,\"room_id\":147,\"seat_id\":148}]','ratul','student','carmical collage ragnpur','College','2nd Year (দ্বাদশ)',NULL,'Zelenia Shannon','benubala','ratul@gmail.com','+8801750887855',NULL,'744521','01875426552','1454411','01765847114','123456',1,8,502,'Mahigonj','cash',NULL,2500.00,3000.00,0.00,'2026-09-02',NULL,1,NULL,'1',0,0,NULL,'2026-09-02 01:05:24','2026-09-02 01:21:46'),(178,'01765750791.png','[{\"floornumber\":\"Second Floor\",\"roomnumber\":\"C-10-Block C\",\"advance_price\":6000,\"room_id\":175,\"seat_id\":180}]','mahin','student','carmical collage ragnpur','Diploma / Polytechnic','6th Semester',NULL,'jabed','benubala',NULL,'+8801765750791',NULL,'744521','+1 (472) 29','Odio irure incididun','01885761782','123456',2,21,160,'Mahigonj','cash',NULL,3000.00,3000.00,0.00,'2026-09-02',NULL,1,NULL,'1',0,0,NULL,'2026-09-02 01:06:28','2026-09-02 01:12:51'),(180,'01306526906.png','[{\"floornumber\":\"Second Floor\",\"roomnumber\":\"D-10-Block D\",\"advance_price\":0,\"room_id\":185,\"seat_id\":190,\"original_advance_price\":6000}]','sohag','student','carmical collage ragnpur','University','3rd Semester',NULL,'Zelenia Shannon','begom','sohage@gmail.com','+8801306526906',NULL,'744521','01875426552','1454411','01885761782','123456',4,34,267,'Mahigonj','cash',NULL,3000.00,3000.00,0.00,'2026-09-02','2026-09-02',1,'2026-09-02','1',0,0,NULL,'2026-09-02 01:15:02','2026-09-02 01:26:03');
/*!40000 ALTER TABLE `room_booking_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_change_histories`
--

DROP TABLE IF EXISTS `room_change_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_change_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_booking_history_id` bigint unsigned NOT NULL,
  `resident_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_floor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_room_seat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_floor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_room_seat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_monthly_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `new_monthly_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee_amount` decimal(10,2) NOT NULL DEFAULT '500.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Cash',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_change_histories_room_booking_history_id_index` (`room_booking_history_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_change_histories`
--

LOCK TABLES `room_change_histories` WRITE;
/*!40000 ALTER TABLE `room_change_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_change_histories` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_seats`
--

LOCK TABLES `room_seats` WRITE;
/*!40000 ALTER TABLE `room_seats` DISABLE KEYS */;
INSERT INTO `room_seats` VALUES (148,147,'Block A',2500.00,3000.00,1,'2026-08-30 04:23:40','2026-09-02 01:21:46'),(149,148,'Block A',3000.00,2500.00,0,'2026-08-30 04:25:22','2026-08-31 02:02:55'),(150,149,'Block A',3000.00,2500.00,0,'2026-08-30 04:25:45','2026-08-31 05:50:55'),(151,150,'Block A',3000.00,2200.00,0,'2026-08-30 04:26:19','2026-08-31 23:14:56'),(152,151,'Block B',3000.00,6000.00,0,'2026-09-01 01:21:50','2026-09-01 01:21:50'),(153,152,'Block B',3000.00,6000.00,0,'2026-09-01 01:23:10','2026-09-01 01:23:10'),(154,153,'Block B',3000.00,6000.00,0,'2026-09-01 01:23:40','2026-09-01 01:23:40'),(155,154,'Block B',3000.00,6000.00,0,'2026-09-01 01:24:24','2026-09-01 01:24:24'),(156,155,'Block B',3000.00,6000.00,0,'2026-09-01 01:24:47','2026-09-01 01:24:47'),(157,156,'Block B',3000.00,6000.00,0,'2026-09-01 01:25:18','2026-09-01 01:25:18'),(158,157,'Block B-1',2500.00,5000.00,0,'2026-09-01 01:26:46','2026-09-02 00:51:23'),(159,157,'Block B-2',2500.00,5000.00,0,'2026-09-01 01:26:46','2026-09-01 01:26:46'),(160,158,'Block B',3000.00,6000.00,0,'2026-09-01 01:29:52','2026-09-01 01:29:52'),(161,159,'Block B',3000.00,6000.00,0,'2026-09-01 01:30:18','2026-09-01 01:30:18'),(162,160,'Block B',3000.00,6000.00,0,'2026-09-01 01:30:53','2026-09-01 01:30:53'),(163,161,'Block B',3000.00,6000.00,0,'2026-09-01 01:31:23','2026-09-01 01:31:23'),(166,162,'Block B',3000.00,6000.00,0,'2026-09-01 01:32:44','2026-09-01 01:32:44'),(167,163,'Block B-1',2500.00,5000.00,0,'2026-09-01 01:33:20','2026-09-01 01:33:20'),(168,163,'Block B-2',2500.00,5000.00,0,'2026-09-01 01:33:20','2026-09-01 01:33:20'),(169,164,'Block B',3000.00,6000.00,0,'2026-09-01 01:35:14','2026-09-01 01:35:14'),(170,165,'Block B',3000.00,6000.00,0,'2026-09-01 01:35:42','2026-09-01 01:35:42'),(171,166,'Block C',3500.00,6000.00,0,'2026-09-01 01:47:34','2026-09-01 01:47:34'),(172,167,'Block C',3000.00,6000.00,0,'2026-09-01 01:49:08','2026-09-01 01:49:08'),(173,168,'Block C',3000.00,6000.00,0,'2026-09-01 01:49:54','2026-09-01 01:49:54'),(174,169,'Block C',3000.00,6000.00,0,'2026-09-01 01:50:18','2026-09-01 23:33:04'),(175,170,'Block C',3000.00,6000.00,0,'2026-09-01 01:50:45','2026-09-01 01:50:45'),(176,171,'Block C',3000.00,6000.00,0,'2026-09-01 01:51:09','2026-09-01 01:51:09'),(177,172,'Block C',3000.00,6000.00,0,'2026-09-01 01:51:30','2026-09-01 01:51:30'),(178,173,'Block C',3000.00,6000.00,0,'2026-09-01 01:51:52','2026-09-01 01:51:52'),(179,174,'Block C',3000.00,6000.00,0,'2026-09-01 01:52:11','2026-09-01 01:52:11'),(180,175,'Block C',3000.00,6000.00,1,'2026-09-01 01:52:34','2026-09-02 01:12:51'),(181,176,'Block D',3500.00,6000.00,0,'2026-09-01 01:54:24','2026-09-01 01:54:24'),(182,177,'Block D',3000.00,6000.00,0,'2026-09-01 01:54:57','2026-09-01 01:54:57'),(183,178,'Block D',3000.00,6000.00,0,'2026-09-01 01:55:20','2026-09-01 01:55:20'),(184,179,'Block D',3000.00,6000.00,0,'2026-09-01 01:55:39','2026-09-01 01:55:39'),(185,180,'Block D',3000.00,6000.00,0,'2026-09-01 01:55:58','2026-09-01 01:55:58'),(186,181,'Block D',3000.00,6000.00,0,'2026-09-01 01:56:16','2026-09-01 01:56:16'),(187,182,'Block D',3000.00,6000.00,0,'2026-09-01 01:56:38','2026-09-02 01:10:42'),(188,183,'Block D',3000.00,6000.00,0,'2026-09-01 01:56:57','2026-09-01 01:56:57'),(189,184,'Block D',3000.00,6000.00,0,'2026-09-01 01:57:17','2026-09-01 01:57:17'),(190,185,'Block D',3000.00,6000.00,0,'2026-09-01 01:57:36','2026-09-02 01:26:03'),(191,186,'Block D',3000.00,6000.00,0,'2026-09-01 01:57:56','2026-09-01 01:58:13'),(192,187,'Block D',3000.00,6000.00,0,'2026-09-01 01:58:40','2026-09-01 01:58:40'),(193,188,'Block D-1',2500.00,5000.00,0,'2026-09-01 01:59:13','2026-09-01 23:33:01'),(194,188,'Block D-2',2500.00,5000.00,0,'2026-09-01 01:59:13','2026-09-01 01:59:13'),(195,189,'Block D',3000.00,6000.00,0,'2026-09-01 01:59:34','2026-09-01 01:59:34'),(196,190,'Block D',3000.00,6000.00,0,'2026-09-01 01:59:58','2026-09-01 01:59:58'),(197,191,'Block E',3500.00,6000.00,0,'2026-09-01 02:02:27','2026-09-01 02:02:27'),(198,192,'Block E',3000.00,6000.00,0,'2026-09-01 02:02:53','2026-09-01 02:02:53'),(199,193,'Block E',3000.00,6000.00,0,'2026-09-01 02:03:20','2026-09-01 02:03:20'),(200,194,'Block E',3000.00,6000.00,0,'2026-09-01 02:03:43','2026-09-01 02:03:43'),(201,195,'Block E',3000.00,6000.00,0,'2026-09-01 02:04:04','2026-09-01 02:04:04'),(202,196,'Block E',3000.00,6000.00,0,'2026-09-01 02:04:22','2026-09-01 02:04:22'),(203,197,'Block E',3000.00,6000.00,0,'2026-09-01 02:04:51','2026-09-01 23:32:55'),(204,198,'Block E',3000.00,6000.00,0,'2026-09-01 02:05:15','2026-09-01 23:32:58'),(205,199,'Block E',3000.00,6000.00,0,'2026-09-01 02:05:35','2026-09-01 02:05:35'),(206,200,'Block E',3000.00,6000.00,0,'2026-09-01 02:05:56','2026-09-01 02:05:56'),(207,201,'Block F',3500.00,6000.00,0,'2026-09-01 02:08:32','2026-09-01 02:08:32'),(208,202,'Block F-1',3500.00,5000.00,0,'2026-09-01 02:10:10','2026-09-01 02:10:10'),(209,202,'Block F-2',3000.00,5000.00,0,'2026-09-01 02:10:10','2026-09-01 02:10:10'),(210,203,'Block F',3000.00,6000.00,0,'2026-09-01 02:12:10','2026-09-01 02:12:10'),(211,204,'Block F',3000.00,6000.00,0,'2026-09-01 02:12:41','2026-09-01 02:12:41'),(212,205,'Block F',3000.00,6000.00,0,'2026-09-01 02:13:33','2026-09-01 02:13:33'),(213,206,'Block F',3000.00,6000.00,0,'2026-09-01 02:13:53','2026-09-01 02:13:53'),(214,207,'Block F-1',3000.00,6000.00,0,'2026-09-01 02:39:41','2026-09-01 02:39:41'),(215,207,'Block F-2',3000.00,6000.00,0,'2026-09-01 02:39:41','2026-09-01 02:39:41'),(216,208,'Block F',3000.00,6000.00,0,'2026-09-01 02:40:10','2026-09-01 02:40:10'),(217,209,'Block F',3000.00,6000.00,0,'2026-09-01 02:41:19','2026-09-01 02:41:19'),(218,210,'Block F',3000.00,6000.00,0,'2026-09-01 02:41:43','2026-09-01 02:41:43'),(219,211,'Block F',3000.00,6000.00,0,'2026-09-01 02:42:32','2026-09-01 02:42:32'),(220,212,'Block F',3000.00,6000.00,0,'2026-09-01 02:45:21','2026-09-01 02:45:21'),(221,213,'Block F-1',2500.00,5000.00,0,'2026-09-01 02:45:55','2026-09-02 01:10:42'),(222,213,'Block F-2',2500.00,5000.00,0,'2026-09-01 02:45:55','2026-09-01 02:45:55'),(223,214,'Block F',3000.00,6000.00,0,'2026-09-01 02:47:24','2026-09-01 02:47:24'),(224,215,'Block F',3000.00,6000.00,0,'2026-09-01 02:47:46','2026-09-01 02:47:46');
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
  `room_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `acstatus` int DEFAULT NULL,
  `room_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `breakfast` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attached_bathroom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_people` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balcony` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ac_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `windows` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_discount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `rooms_chk_1` CHECK (json_valid(`image`))
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (147,59,'A-1','[\"1788085420_6a9404ac70f3a.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block A - Single Room',NULL,'1','2026-08-30 03:30:36','2026-09-02 01:21:46'),(148,59,'A-2','[\"1788085522_6a94051274266.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block A - Single Room',NULL,'0','2026-08-30 03:30:36','2026-08-31 02:02:55'),(149,59,'A-3','[\"1788085545_6a940529cbe22.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block A - Single Room',NULL,'0','2026-08-30 03:30:36','2026-08-31 05:50:55'),(150,59,'A-4','[\"1788085579_6a94054bc4834.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block A - Single Room',NULL,'0','2026-08-30 03:30:36','2026-08-31 23:14:56'),(151,59,'B-1','[\"1788247310_6a967d0e74fcc.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:21:50'),(152,59,'B-2','[\"1788247390_6a967d5e50221.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:23:10'),(153,59,'B-3','[\"1788247420_6a967d7c60265.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:23:40'),(154,59,'B-4','[\"1788247464_6a967da8b9b2b.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:24:24'),(155,59,'B-5','[\"1788247487_6a967dbf9f5ab.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:24:47'),(156,59,'B-6','[\"1788247518_6a967ddedab64.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:25:18'),(157,59,'B-7','[\"1788247606_6a967e36300d7.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2500','yes','Block B - Double Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:26:46'),(158,59,'B-8','[\"1788247792_6a967ef07add4.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:29:52'),(159,59,'B-9','[\"1788247818_6a967f0a17240.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:30:18'),(160,59,'B-10','[\"1788247853_6a967f2d9d59b.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:30:53'),(161,59,'B-11','[\"1788247883_6a967f4bcd1f6.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:31:23'),(162,59,'B-12','[\"1788247922_6a967f7260928.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:32:44'),(163,59,'B-13','[\"1788248000_6a967fc0a8b90.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2500','yes','Block B - Double Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:33:20'),(164,59,'B-14','[\"1788248114_6a96803250e76.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:35:14'),(165,59,'B-15','[\"1788248142_6a96804e76469.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block B - Single Room',NULL,'0','2026-08-30 03:30:36','2026-09-01 01:35:42'),(166,60,'C-1','[\"1788248854_6a9683166d697.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3500','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:48:34'),(167,60,'C-2','[\"1788248948_6a96837431445.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:49:08'),(168,60,'C-3','[\"1788248994_6a9683a2a952e.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:49:54'),(169,60,'C-4','[\"1788249018_6a9683baa9576.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 23:33:04'),(170,60,'C-5','[\"1788249045_6a9683d5e2055.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:50:45'),(171,60,'C-6','[\"1788249069_6a9683eda5fe0.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:51:09'),(172,60,'C-7','[\"1788249090_6a96840274930.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:51:30'),(173,60,'C-8','[\"1788249112_6a968418247ca.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:51:52'),(174,60,'C-9','[\"1788249131_6a96842be1e60.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:52:11'),(175,60,'C-10','[\"1788249154_6a96844295ce2.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block C - Single Room',NULL,'1','2026-08-30 04:07:45','2026-09-02 01:12:51'),(176,60,'D-1','[\"1788249264_6a9684b06710d.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3500','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:54:24'),(177,60,'D-2','[\"1788249297_6a9684d199d3a.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:54:57'),(178,60,'D-3','[\"1788249320_6a9684e84383e.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:55:20'),(179,60,'D-4','[\"1788249339_6a9684fb834ed.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:55:39'),(180,60,'D-5','[\"1788249358_6a96850e177b3.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:55:58'),(181,60,'D-6','[\"1788249376_6a968520c7a58.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:56:16'),(182,60,'D-7','[\"1788249398_6a96853698b11.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-02 01:10:42'),(183,60,'D-8','[\"1788249417_6a968549b1c7f.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:56:57'),(184,60,'D-9','[\"1788249437_6a96855d17b72.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:57:17'),(185,60,'D-10','[\"1788249456_6a9685708fcb8.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-02 01:26:03'),(186,60,'D-11','[\"1788249476_6a968584c6a49.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:57:56'),(187,60,'D-12','[\"1788249520_6a9685b0da424.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:58:40'),(188,60,'D-13','[\"1788249553_6a9685d14e66f.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2500','yes','Block D - Double Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:59:13'),(189,60,'D-14','[\"1788249574_6a9685e6a0c1e.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:59:34'),(190,60,'D-15','[\"1788249598_6a9685fe52da8.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block D - Single Room',NULL,'0','2026-08-30 04:07:45','2026-09-01 01:59:58'),(191,61,'E-1','[\"1788249747_6a9686939bb9b.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3500','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:02:27'),(192,61,'E-2','[\"1788249773_6a9686ada87f0.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:02:53'),(193,61,'E-3','[\"1788249800_6a9686c826eea.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:03:20'),(194,61,'E-4','[\"1788249823_6a9686dfb4347.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:03:43'),(195,61,'E-5','[\"1788249844_6a9686f4af1b2.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:04:04'),(196,61,'E-6','[\"1788249862_6a968706e614a.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:04:22'),(197,61,'E-7','[\"1788249891_6a96872396cfb.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 23:32:55'),(198,61,'E-8','[\"1788249915_6a96873b54196.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 23:32:58'),(199,61,'E-9','[\"1788249935_6a96874fc44c1.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:05:35'),(200,61,'E-10','[\"1788249956_6a968764e1965.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block E - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:05:56'),(201,61,'F-1','[\"1788250112_6a96880003acb.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3500','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:08:32'),(202,61,'F-2','[\"1788250210_6a9688626513f.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3500','yes','Block F - Double Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:10:10'),(203,61,'F-3','[\"1788250330_6a9688da377a2.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:12:10'),(204,61,'F-4','[\"1788250361_6a9688f995df6.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:12:41'),(205,61,'F-5','[\"1788250413_6a96892d2e1a0.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:13:33'),(206,61,'F-6','[\"1788250433_6a96894185dea.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:13:53'),(207,61,'F-7','[\"1788251980_6a968f4cefba2.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Double Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:39:41'),(208,61,'F-8','[\"1788252010_6a968f6ac74fc.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:40:10'),(209,61,'F-9','[\"1788252079_6a968fafef932.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:41:19'),(210,61,'F-10','[\"1788252103_6a968fc7245ae.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:41:43'),(211,61,'F-11','[\"1788252152_6a968ff8109b6.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:42:32'),(212,61,'F-12','[\"1788252321_6a9690a1984ad.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:45:21'),(213,61,'F-13','[\"1788252355_6a9690c35d08d.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2500','yes','Block F - Double Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:45:55'),(214,61,'F-14','[\"1788252444_6a96911c93068.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:47:24'),(215,61,'F-15','[\"1788252466_6a969132f153f.webp\"]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'3000','yes','Block F - Single Room',NULL,'0','2026-08-30 04:12:38','2026-09-01 02:47:46');
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contents` text COLLATE utf8mb4_unicode_ci,
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
INSERT INTO `settings` VALUES (1,'Web Setting','web_setting','{\"title\":\"Tss Villa\",\"phone\":\"01977270920\",\"address\":\"\\u0995\\u09b2\\u09c7\\u099c \\u09b0\\u09cb\\u09a1, \\u09a8\\u09c7\\u09b8\\u0995\\u09cb \\u0997\\u09c7\\u099f \\u09b8\\u0982\\u09b2\\u0997\\u09cd\\u09a8, \\u09b0\\u0982\\u09aa\\u09c1\\u09b0\",\"email\":\"tssvilla2026@gmail.com\",\"currency\":\"BDT\"}','2025-01-23 01:19:49','2026-07-28 01:20:12'),(2,'logo setting','logo_setting','{\"logo\":\"logo.png\",\"favicon\":\"favicon.png\"}','2026-03-04 00:44:37','2026-03-04 00:44:37'),(3,'Meal Setting','meal_setting','{\"half\":\"20\",\"full\":\"30\",\"day_rice\":\"200\",\"night_rice\":\"200\",\"morning_rice\":\"100\",\"Chef_take_meal_number\":\"1\",\"meal_change_time\":\"08:00\",\"seat_rent\":\"2000\"}','2026-07-22 01:29:11','2026-09-02 01:52:15'),(4,'app setting','app_setting','{\"development_fee\":\"3000\",\"development_fee_status\":\"1\",\"booking_button_status\":\"1\"}','2026-07-28 01:01:59','2026-09-01 22:44:28');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sliders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sliders`
--

LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;
INSERT INTO `sliders` VALUES (1,'1786877533_6a81965d71d51.png','tss villa','tss villa best',1,'2026-08-16 04:52:13','2026-08-16 04:52:13'),(2,'1786877604_6a8196a41ccb5.jpeg','tss villa','the best description',1,'2026-08-16 04:53:24','2026-08-16 04:53:24'),(3,'1786878898_6a819bb2a596b.png','tss villa','test description',1,'2026-08-16 05:14:58','2026-08-16 05:14:58');
/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
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
  `payment_type` enum('advance','full','net_payable') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_idx` (`staff_id`,`salary_month`,`salary_year`),
  CONSTRAINT `staff_salary_payments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nid_passport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `permanent_address` text COLLATE utf8mb4_unicode_ci,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `joining_date` date DEFAULT NULL,
  `shift_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staffs`
--

LOCK TABLES `staffs` WRITE;
/*!40000 ALTER TABLE `staffs` DISABLE KEYS */;
INSERT INTO `staffs` VALUES (23,'74581','manik','01733221166','manik120@gmail.com','123456','854216','Male','2026-07-23',2,14,99,'bomilla sadar','Accountant / Cashier','Front Desk',20000.00,'2026-07-23','Morning',0,'1784791309_6a61c10d319b1.jpg',NULL,'2026-07-23 01:21:28','2026-08-04 02:54:49'),(26,'451263','hasansd','01455428099','hasansd@gmail.com','123456','12457896','Male','2026-07-24',2,15,106,'rangpurssd','Manager','Front Desk',20000.00,'2026-07-25','Morning',0,'1784971182_6a647fae55d46.jpg',NULL,'2026-07-24 23:00:23','2026-07-25 03:19:42'),(27,'74521','rakibul islam','01745784965','rakibulislam@gmail.com','36903690','749843258','Male','2000-06-07',1,2,9,'kapasia','Accountant / Cashier',NULL,20000.00,'2026-08-04','Morning',0,'1785833309_6a71a75d6ed8c.jpg',NULL,'2026-08-04 02:48:29','2026-08-04 02:48:40');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (6,'Ratul Hossion','2026-04-21 02:02:22','2026-04-21 23:07:14'),(7,'Rabbi Hossion','2026-04-27 00:56:17','2026-04-27 00:56:17'),(8,'josim hossion','2026-04-27 00:56:32','2026-04-27 00:56:32'),(9,'Ripon Hossion','2026-04-28 22:49:39','2026-04-28 22:49:39'),(10,'test','2026-07-29 03:36:48','2026-07-29 03:36:48');
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
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bn_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `thanas_district_id_foreign` (`district_id`)
) ENGINE=InnoDB AUTO_INCREMENT=549 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanas`
--

LOCK TABLES `thanas` WRITE;
/*!40000 ALTER TABLE `thanas` DISABLE KEYS */;
INSERT INTO `thanas` VALUES (1,1,'Belabo','বেলাবো'),(2,1,'Monohardi','মনোহরদী'),(3,1,'Narsingdi Sadar','নরসিংদী সদর'),(4,1,'Palash','পলাশ'),(5,1,'Raipura','রায়পুরা'),(6,1,'Shibpur','শিবপুর'),(7,2,'Kaliganj','কালীগঞ্জ'),(8,2,'Kaliakair','কালিয়াকৈর'),(9,2,'Kapasia','কাপাসিয়া'),(10,2,'Gazipur Sadar','গাজীপুর সদর'),(11,2,'Sreepur','শ্রীপুর'),(12,3,'Shariatpur Sadar','শরিয়তপুর সদর'),(13,3,'Naria','নড়িয়া'),(14,3,'Zajira','জাজিরা'),(15,3,'Gosairhat','গোসাইরহাট'),(16,3,'Bhedarganj','ভেদরগঞ্জ'),(17,3,'Damudya','ডামুড্যা'),(18,4,'Araihazar','আড়াইহাজার'),(19,4,'Bandar','বন্দর'),(20,4,'Narayanganj Sadar','নারায়নগঞ্জ সদর'),(21,4,'Rupganj','রূপগঞ্জ'),(22,4,'Sonargaon','সোনারগাঁ'),(23,5,'Basail','বাসাইল'),(24,5,'Bhuapur','ভুয়াপুর'),(25,5,'Delduar','দেলদুয়ার'),(26,5,'Ghatail','ঘাটাইল'),(27,5,'Gopalpur','গোপালপুর'),(28,5,'Madhupur','মধুপুর'),(29,5,'Mirzapur','মির্জাপুর'),(30,5,'Nagarpur','নাগরপুর'),(31,5,'Sakhipur','সখিপুর'),(32,5,'Tangail Sadar','টাঙ্গাইল সদর'),(33,5,'Kalihati','কালিহাতী'),(34,5,'Dhanbari','ধনবাড়ী'),(35,6,'Itna','ইটনা'),(36,6,'Katiadi','কটিয়াদী'),(37,6,'Bhairab','ভৈরব'),(38,6,'Tarail','তাড়াইল'),(39,6,'Hossainpur','হোসেনপুর'),(40,6,'Pakundia','পাকুন্দিয়া'),(41,6,'Kuliarchar','কুলিয়ারচর'),(42,6,'Kishoreganj Sadar','কিশোরগঞ্জ সদর'),(43,6,'Karimgonj','করিমগঞ্জ'),(44,6,'Bajitpur','বাজিতপুর'),(45,6,'Austagram','অষ্টগ্রাম'),(46,6,'Mithamoin','মিঠামইন'),(47,6,'Nikli','নিকলী'),(48,7,'Harirampur','হরিরামপুর'),(49,7,'Saturia','সাটুরিয়া'),(50,7,'Manikganj Sadar','মানিকগঞ্জ সদর'),(51,7,'Gior','ঘিওর'),(52,7,'Shibaloy','শিবালয়'),(53,7,'Doulatpur','দৌলতপুর'),(54,7,'Singiar','সিংগাইর'),(60,9,'Munshiganj Sadar','মুন্সিগঞ্জ সদর'),(61,9,'Sreenagar','শ্রীনগর'),(62,9,'Sirajdikhan','সিরাজদিখান'),(63,9,'Louhajanj','লৌহজং'),(64,9,'Gajaria','গজারিয়া'),(65,9,'Tongibari','টংগীবাড়ি'),(66,10,'Rajbari Sadar','রাজবাড়ী সদর'),(67,10,'Goalanda','গোয়ালন্দ'),(68,10,'Pangsa','পাংশা'),(69,10,'Baliakandi','বালিয়াকান্দি'),(70,10,'Kalukhali','কালুখালী'),(71,11,'Madaripur Sadar','মাদারীপুর সদর'),(72,11,'Shibchar','শিবচর'),(73,11,'Kalkini','কালকিনি'),(74,11,'Rajoir','রাজৈর'),(75,12,'Gopalganj Sadar','গোপালগঞ্জ সদর'),(76,12,'Kashiani','কাশিয়ানী'),(77,12,'Tungipara','টুংগীপাড়া'),(78,12,'Kotalipara','কোটালীপাড়া'),(79,12,'Muksudpur','মুকসুদপুর'),(80,13,'Faridpur Sadar','ফরিদপুর সদর'),(81,13,'Alfadanga','আলফাডাঙ্গা'),(82,13,'Boalmari','বোয়ালমারী'),(83,13,'Sadarpur','সদরপুর'),(84,13,'Nagarkanda','নগরকান্দা'),(85,13,'Bhanga','ভাঙ্গা'),(86,13,'Charbhadrasan','চরভদ্রাসন'),(87,13,'Madhukhali','মধুখালী'),(88,13,'Saltha','সালথা'),(89,14,'Debidwar','দেবিদ্বার'),(90,14,'Barura','বরুড়া'),(91,14,'Brahmanpara','ব্রাহ্মণপাড়া'),(92,14,'Chandina','চান্দিনা'),(93,14,'Chauddagram','চৌদ্দগ্রাম'),(94,14,'Daudkandi','দাউদকান্দি'),(95,14,'Homna','হোমনা'),(96,14,'Laksam','লাকসাম'),(97,14,'Muradnagar','মুরাদনগর'),(98,14,'Nangalkot','নাঙ্গলকোট'),(99,14,'Comilla Sadar','কুমিল্লা সদর'),(100,14,'Meghna','মেঘনা'),(101,14,'Monohargonj','মনোহরগঞ্জ'),(102,14,'South Sadar','দক্ষিণ সদর'),(103,14,'Titas','তিতাস'),(104,14,'Burichang','বুড়িচং'),(105,14,'Lalmai','লালমাই'),(106,15,'Chhagalnaiya','ছাগলনাইয়া'),(107,15,'Feni Sadar','ফেনী সদর'),(108,15,'Sonagazi','সোনাগাজী'),(109,15,'Fulgazi','ফুলগাজী'),(110,15,'Parshuram','পরশুরাম'),(111,15,'Daganbhuiyan','দাগনভূঞা'),(112,16,'Brahmanbaria Sadar','ব্রাহ্মণবাড়িয়া সদর'),(113,16,'Kasba','কসবা'),(114,16,'Nasirnagar','নাসিরনগর'),(115,16,'Sarail','সরাইল'),(116,16,'Ashuganj','আশুগঞ্জ'),(117,16,'Akhaura','আখাউড়া'),(118,16,'Nabinagar','নবীনগর'),(119,16,'Bancharampur','বাঞ্ছারামপুর'),(120,16,'Bijoynagar','বিজয়নগর'),(121,17,'Rangamati Sadar','রাঙ্গামাটি সদর'),(122,17,'Kaptai','কাপ্তাই'),(123,17,'Kawkhali','কাউখালী'),(124,17,'Baghaichari','বাঘাইছড়ি'),(125,17,'Barkal','বরকল'),(126,17,'Langadu','লংগদু'),(127,17,'Rajasthali','রাজস্থলী'),(128,17,'Belaichari','বিলাইছড়ি'),(129,17,'Juraichari','জুরাছড়ি'),(130,17,'Naniarchar','নানিয়ারচর'),(131,18,'Noakhali Sadar','নোয়াখালী সদর'),(132,18,'Companiganj','কোম্পানীগঞ্জ'),(133,18,'Begumganj','বেগমগঞ্জ'),(134,18,'Hatia','হাতিয়া'),(135,18,'Subarnachar','সুবর্ণচর'),(136,18,'Kabirhat','কবিরহাট'),(137,18,'Senbug','সেনবাগ'),(138,18,'Chatkhil','চাটখিল'),(139,18,'Sonaimori','সোনাইমুড়ী'),(140,19,'Haimchar','হাইমচর'),(141,19,'Kachua','কচুয়া'),(142,19,'Shahrasti','শাহরাস্তি'),(143,19,'Chandpur Sadar','চাঁদপুর সদর'),(144,19,'South Matlab','দক্ষিণ মতলব'),(145,19,'Hajiganj','হাজীগঞ্জ'),(146,19,'North Matlab','উত্তর মতলব'),(147,19,'Faridgonj','ফরিদগঞ্জ'),(148,20,'Lakshmipur Sadar','লক্ষ্মীপুর সদর'),(149,20,'Kamalnagar','কমলনগর'),(150,20,'Raipur','রায়পুর'),(151,20,'Ramgati','রামগতি'),(152,20,'Ramganj','রামগঞ্জ'),(153,21,'Rangunia','রাঙ্গুনিয়া'),(154,21,'Sitakunda','সীতাকুন্ড'),(155,21,'Mirsharai','মীরসরাই'),(156,21,'Patiya','পটিয়া'),(157,21,'Sandwip','সন্দ্বীপ'),(158,21,'Banshkhali','বাঁশখালী'),(159,21,'Boalkhali','বোয়ালখালী'),(160,21,'Anwara','আনোয়ারা'),(161,21,'Chandanaish','চন্দনাইশ'),(162,21,'Satkania','সাতকানিয়া'),(163,21,'Lohagara','লোহাগাড়া'),(164,21,'Hathazari','হাটহাজারী'),(165,21,'Fatikchhari','ফটিকছড়ি'),(166,21,'Raozan','রাউজান'),(167,21,'Karnafuli','কর্ণফুলী'),(168,22,'Coxsbazar Sadar','কক্সবাজার সদর'),(169,22,'Chakaria','চকরিয়া'),(170,22,'Kutubdia','কুতুবদিয়া'),(171,22,'Ukhiya','উখিয়া'),(172,22,'Moheshkhali','মহেশখালী'),(173,22,'Pekua','পেকুয়া'),(174,22,'Ramu','রামু'),(175,22,'Teknaf','টেকনাফ'),(176,23,'Khagrachhari Sadar','খাগড়াছড়ি সদর'),(177,23,'Dighinala','দিঘীনালা'),(178,23,'Panchari','পানছড়ি'),(179,23,'Laxmichhari','লক্ষীছড়ি'),(180,23,'Mohalchari','মহালছড়ি'),(181,23,'Manikchari','মানিকছড়ি'),(182,23,'Ramgarh','রামগড়'),(183,23,'Matiranga','মাটিরাঙ্গা'),(184,23,'Guimara','গুইমারা'),(185,24,'Bandarban Sadar','বান্দরবান সদর'),(186,24,'Alikadam','আলীকদম'),(187,24,'Naikhongchhari','নাইক্ষ্যংছড়ি'),(188,24,'Rowangchhari','রোয়াংছড়ি'),(189,24,'Lama','লামা'),(190,24,'Ruma','রুমা'),(191,24,'Thanchi','থানচি'),(192,25,'Belkuchi','বেলকুচি'),(193,25,'Chauhali','চৌহালি'),(194,25,'Kamarkhand','কামারখন্দ'),(195,25,'Kazipur','কাজীপুর'),(196,25,'Raigonj','রায়গঞ্জ'),(197,25,'Shahjadpur','শাহজাদপুর'),(198,25,'Sirajganj Sadar','সিরাজগঞ্জ সদর'),(199,25,'Tarash','তাড়াশ'),(200,25,'Ullapara','উল্লাপাড়া'),(201,26,'Sujanagar','সুজানগর'),(202,26,'Ishurdi','ঈশ্বরদী'),(203,26,'Bhangura','ভাঙ্গুড়া'),(204,26,'Pabna Sadar','পাবনা সদর'),(205,26,'Bera','বেড়া'),(206,26,'Atghoria','আটঘরিয়া'),(207,26,'Chatmohar','চাটমোহর'),(208,26,'Santhia','সাঁথিয়া'),(209,26,'Faridpur','ফরিদপুর'),(210,27,'Kahaloo','কাহালু'),(211,27,'Bogra Sadar','বগুড়া সদর'),(212,27,'Shariakandi','সারিয়াকান্দি'),(213,27,'Shajahanpur','শাজাহানপুর'),(214,27,'Dupchanchia','দুপচাচিঁয়া'),(215,27,'Adamdighi','আদমদিঘি'),(216,27,'Nondigram','নন্দিগ্রাম'),(217,27,'Sonatala','সোনাতলা'),(218,27,'Dhunot','ধুনট'),(219,27,'Gabtali','গাবতলী'),(220,27,'Sherpur','শেরপুর'),(221,27,'Shibganj','শিবগঞ্জ'),(222,28,'Paba','পবা'),(223,28,'Durgapur','দুর্গাপুর'),(224,28,'Mohonpur','মোহনপুর'),(225,28,'Charghat','চারঘাট'),(226,28,'Puthia','পুঠিয়া'),(227,28,'Bagha','বাঘা'),(228,28,'Godagari','গোদাগাড়ী'),(229,28,'Tanore','তানোর'),(230,28,'Bagmara','বাগমারা'),(231,29,'Natore Sadar','নাটোর সদর'),(232,29,'Singra','সিংড়া'),(233,29,'Baraigram','বড়াইগ্রাম'),(234,29,'Bagatipara','বাগাতিপাড়া'),(235,29,'Lalpur','লালপুর'),(236,29,'Gurudaspur','গুরুদাসপুর'),(237,29,'Naldanga','নলডাঙ্গা'),(238,30,'Akkelpur','আক্কেলপুর'),(239,30,'Kalai','কালাই'),(240,30,'Khetlal','ক্ষেতলাল'),(241,30,'Panchbibi','পাঁচবিবি'),(242,30,'Joypurhat Sadar','জয়পুরহাট সদর'),(243,31,'Chapainawabganj Sadar','চাঁপাইনবাবগঞ্জ সদর'),(244,31,'Gomostapur','গোমস্তাপুর'),(245,31,'Nachol','নাচোল'),(246,31,'Bholahat','ভোলাহাট'),(247,31,'Shibganj','শিবগঞ্জ'),(248,32,'Mohadevpur','মহাদেবপুর'),(249,32,'Badalgachi','বদলগাছী'),(250,32,'Patnitala','পত্নিতলা'),(251,32,'Dhamoirhat','ধামইরহাট'),(252,32,'Niamatpur','নিয়ামতপুর'),(253,32,'Manda','মান্দা'),(254,32,'Atrai','আত্রাই'),(255,32,'Raninagar','রাণীনগর'),(256,32,'Naogaon Sadar','নওগাঁ সদর'),(257,32,'Porsha','পোরশা'),(258,32,'Sapahar','সাপাহার'),(259,33,'Manirampur','মণিরামপুর'),(260,33,'Abhaynagar','অভয়নগর'),(261,33,'Bagherpara','বাঘারপাড়া'),(262,33,'Chougachha','চৌগাছা'),(263,33,'Jhikargacha','ঝিকরগাছা'),(264,33,'Keshabpur','কেশবপুর'),(265,33,'Jessore Sadar','যশোর সদর'),(266,33,'Sharsha','শার্শা'),(267,34,'Assasuni','আশাশুনি'),(268,34,'Debhata','দেবহাটা'),(269,34,'Kalaroa','কলারোয়া'),(270,34,'Satkhira Sadar','সাতক্ষীরা সদর'),(271,34,'Shyamnagar','শ্যামনগর'),(272,34,'Tala','তালা'),(273,34,'Kaliganj','কালিগঞ্জ'),(274,35,'Mujibnagar','মুজিবনগর'),(275,35,'Meherpur Sadar','মেহেরপুর সদর'),(276,35,'Gangni','গাংনী'),(277,36,'Narail Sadar','নড়াইল সদর'),(278,36,'Lohagara','লোহাগড়া'),(279,36,'Kalia','কালিয়া'),(280,37,'Chuadanga Sadar','চুয়াডাঙ্গা সদর'),(281,37,'Alamdanga','আলমডাঙ্গা'),(282,37,'Damurhuda','দামুড়হুদা'),(283,37,'Jibannagar','জীবননগর'),(284,38,'Kushtia Sadar','কুষ্টিয়া সদর'),(285,38,'Kumarkhali','কুমারখালী'),(286,38,'Khoksa','খোকসা'),(287,38,'Mirpur','মিরপুর'),(288,38,'Daulatpur','দৌলতপুর'),(289,38,'Bheramara','ভেড়ামারা'),(290,39,'Shalikha','শালিখা'),(291,39,'Sreepur','শ্রীপুর'),(292,39,'Magura Sadar','মাগুরা সদর'),(293,39,'Mohammadpur','মহম্মদপুর'),(294,40,'Paikgasa','পাইকগাছা'),(295,40,'Fultola','ফুলতলা'),(296,40,'Digholia','দিঘলিয়া'),(297,40,'Rupsha','রূপসা'),(298,40,'Terokhada','তেরখাদা'),(299,40,'Dumuria','ডুমুরিয়া'),(300,40,'Botiaghata','বটিয়াঘাটা'),(301,40,'Dakop','দাকোপ'),(302,40,'Koyra','কয়রা'),(303,41,'Fakirhat','ফকিরহাট'),(304,41,'Bagerhat Sadar','বাগেরহাট সদর'),(305,41,'Mollahat','মোল্লাহাট'),(306,41,'Sarankhola','শরণখোলা'),(307,41,'Rampal','রামপাল'),(308,41,'Morrelganj','মোড়েলগঞ্জ'),(309,41,'Kachua','কচুয়া'),(310,41,'Mongla','মোংলা'),(311,41,'Chitalmari','চিতলমারী'),(312,42,'Jhenaidah Sadar','ঝিনাইদহ সদর'),(313,42,'Shailkupa','শৈলকুপা'),(314,42,'Harinakundu','হরিণাকুন্ডু'),(315,42,'Kaliganj','কালীগঞ্জ'),(316,42,'Kotchandpur','কোটচাঁদপুর'),(317,42,'Moheshpur','মহেশপুর'),(318,43,'Jhalakathi Sadar','ঝালকাঠি সদর'),(319,43,'Kathalia','কাঠালিয়া'),(320,43,'Nalchity','নলছিটি'),(321,43,'Rajapur','রাজাপুর'),(322,44,'Bauphal','বাউফল'),(323,44,'Patuakhali Sadar','পটুয়াখালী সদর'),(324,44,'Dumki','দুমকি'),(325,44,'Dashmina','দশমিনা'),(326,44,'Kalapara','কলাপাড়া'),(327,44,'Mirzaganj','মির্জাগঞ্জ'),(328,44,'Galachipa','গলাচিপা'),(329,44,'Rangabali','রাঙ্গাবালী'),(330,45,'Pirojpur Sadar','পিরোজপুর সদর'),(331,45,'Nazirpur','নাজিরপুর'),(332,45,'Kawkhali','কাউখালী'),(333,45,'Zianagar','জিয়ানগর'),(334,45,'Bhandaria','ভান্ডারিয়া'),(335,45,'Mathbaria','মঠবাড়ীয়া'),(336,45,'Nesarabad','নেছারাবাদ'),(337,46,'Barisal Sadar','বরিশাল সদর'),(338,46,'Bakerganj','বাকেরগঞ্জ'),(339,46,'Babuganj','বাবুগঞ্জ'),(340,46,'Wazirpur','উজিরপুর'),(341,46,'Banaripara','বানারীপাড়া'),(342,46,'Gournadi','গৌরনদী'),(343,46,'Agailjhara','আগৈলঝাড়া'),(344,46,'Mehendiganj','মেহেন্দিগঞ্জ'),(345,46,'Muladi','মুলাদী'),(346,46,'Hizla','হিজলা'),(347,47,'Bhola Sadar','ভোলা সদর'),(348,47,'Borhan Uddin','বোরহান উদ্দিন'),(349,47,'Charfesson','চরফ্যাশন'),(350,47,'Doulatkhan','দৌলতখান'),(351,47,'Monpura','মনপুরা'),(352,47,'Tazumuddin','তজুমদ্দিন'),(353,47,'Lalmohan','লালমোহন'),(354,48,'Amtali','আমতলী'),(355,48,'Barguna Sadar','বরগুনা সদর'),(356,48,'Betagi','বেতাগী'),(357,48,'Bamna','বামনা'),(358,48,'Pathorghata','পাথরঘাটা'),(359,48,'Taltali','তালতলি'),(360,49,'Panchagarh Sadar','পঞ্চগড় সদর'),(361,49,'Debiganj','দেবীগঞ্জ'),(362,49,'Boda','বোদা'),(363,49,'Atwari','আটোয়ারী'),(364,49,'Tetulia','তেতুলিয়া'),(365,50,'Nawabganj','নবাবগঞ্জ'),(366,50,'Birganj','বীরগঞ্জ'),(367,50,'Ghoraghat','ঘোড়াঘাট'),(368,50,'Birampur','বিরামপুর'),(369,50,'Parbatipur','পার্বতীপুর'),(370,50,'Bochaganj','বোচাগঞ্জ'),(371,50,'Kaharol','কাহারোল'),(372,50,'Fulbari','ফুলবাড়ী'),(373,50,'Dinajpur Sadar','দিনাজপুর সদর'),(374,50,'Hakimpur','হাকিমপুর'),(375,50,'Khansama','খানসামা'),(376,50,'Birol','বিরল'),(377,50,'Chirirbandar','চিরিরবন্দর'),(378,51,'Lalmonirhat Sadar','লালমনিরহাট সদর'),(379,51,'Kaliganj','কালীগঞ্জ'),(380,51,'Hatibandha','হাতীবান্ধা'),(381,51,'Patgram','পাটগ্রাম'),(382,51,'Aditmari','আদিতমারী'),(383,52,'Syedpur','সৈয়দপুর'),(384,52,'Domar','ডোমার'),(385,52,'Dimla','ডিমলা'),(386,52,'Jaldhaka','জলঢাকা'),(387,52,'Kishorganj','কিশোরগঞ্জ'),(388,52,'Nilphamari Sadar','নীলফামারী সদর'),(389,53,'Sadullapur','সাদুল্লাপুর'),(390,53,'Gaibandha Sadar','গাইবান্ধা সদর'),(391,53,'Palashbari','পলাশবাড়ী'),(392,53,'Saghata','সাঘাটা'),(393,53,'Gobindaganj','গোবিন্দগঞ্জ'),(394,53,'Sundarganj','সুন্দরগঞ্জ'),(395,53,'Phulchari','ফুলছড়ি'),(396,54,'Thakurgaon Sadar','ঠাকুরগাঁও সদর'),(397,54,'Pirganj','পীরগঞ্জ'),(398,54,'Ranisankail','রাণীশংকৈল'),(399,54,'Haripur','হরিপুর'),(400,54,'Baliadangi','বালিয়াডাঙ্গী'),(401,55,'Rangpur Sadar','রংপুর সদর'),(402,55,'Gangachara','গংগাচড়া'),(403,55,'Taragonj','তারাগঞ্জ'),(404,55,'Badargonj','বদরগঞ্জ'),(405,55,'Mithapukur','মিঠাপুকুর'),(406,55,'Pirgonj','পীরগঞ্জ'),(407,55,'Kaunia','কাউনিয়া'),(408,55,'Pirgacha','পীরগাছা'),(409,56,'Kurigram Sadar','কুড়িগ্রাম সদর'),(410,56,'Nageshwari','নাগেশ্বরী'),(411,56,'Bhurungamari','ভুরুঙ্গামারী'),(412,56,'Phulbari','ফুলবাড়ী'),(413,56,'Rajarhat','রাজারহাট'),(414,56,'Ulipur','উলিপুর'),(415,56,'Chilmari','চিলমারী'),(416,56,'Rowmari','রৌমারী'),(417,56,'Char Rajibpur','চর রাজিবপুর'),(418,57,'Balaganj','বালাগঞ্জ'),(419,57,'Beanibazar','বিয়ানীবাজার'),(420,57,'Bishwanath','বিশ্বনাথ'),(421,57,'Companiganj','কোম্পানীগঞ্জ'),(422,57,'Fenchuganj','ফেঞ্চুগঞ্জ'),(423,57,'Golapganj','গোলাপগঞ্জ'),(424,57,'Gowainghat','গোয়াইনঘাট'),(425,57,'Jaintiapur','জৈন্তাপুর'),(426,57,'Kanaighat','কানাইঘাট'),(427,57,'Sylhet Sadar','সিলেট সদর'),(428,57,'Zakiganj','জকিগঞ্জ'),(429,57,'Dakshin Surma','দক্ষিণ সুরমা'),(430,57,'Osmani Nagar','ওসমানী নগর'),(431,58,'Barlekha','বড়লেখা'),(432,58,'Kamolganj','কমলগঞ্জ'),(433,58,'Kulaura','কুলাউড়া'),(434,58,'Moulvibazar Sadar','মৌলভীবাজার সদর'),(435,58,'Rajnagar','রাজনগর'),(436,58,'Sreemangal','শ্রীমঙ্গল'),(437,58,'Juri','জুড়ী'),(438,59,'Nabiganj','নবীগঞ্জ'),(439,59,'Bahubal','বাহুবল'),(440,59,'Ajmiriganj','আজমিরীগঞ্জ'),(441,59,'Baniachong','বানিয়াচং'),(442,59,'Lakhai','লাখাই'),(443,59,'Chunarughat','চুনারুঘাট'),(444,59,'Habiganj Sadar','হবিগঞ্জ সদর'),(445,59,'Madhabpur','মাধবপুর'),(446,60,'Sunamganj Sadar','সুনামগঞ্জ সদর'),(447,60,'South Sunamganj','দক্ষিণ সুনামগঞ্জ'),(448,60,'Bishwambarpur','বিশ্বম্ভরপুর'),(449,60,'Chhatak','ছাতক'),(450,60,'Jagannathpur','জগন্নাথপুর'),(451,60,'Dowarabazar','দোয়ারাবাজার'),(452,60,'Tahirpur','তাহিরপুর'),(453,60,'Dharmapasha','ধর্মপাশা'),(454,60,'Jamalganj','জামালগঞ্জ'),(455,60,'Shalla','শাল্লা'),(456,60,'Derai','দিরাই'),(457,61,'Sherpur Sadar','শেরপুর সদর'),(458,61,'Nalitabari','নালিতাবাড়ী'),(459,61,'Sreebordi','শ্রীবরদী'),(460,61,'Nokla','নকলা'),(461,61,'Jhenaigati','ঝিনাইগাতী'),(462,62,'Fulbaria','ফুলবাড়ীয়া'),(463,62,'Trishal','ত্রিশাল'),(464,62,'Bhaluka','ভালুকা'),(465,62,'Muktagacha','মুক্তাগাছা'),(466,62,'Mymensingh Sadar','ময়মনসিংহ সদর'),(467,62,'Dhobaura','ধোবাউড়া'),(468,62,'Phulpur','ফুলপুর'),(469,62,'Haluaghat','হালুয়াঘাট'),(470,62,'Gouripur','গৌরীপুর'),(471,62,'Gafargaon','গফরগাঁও'),(472,62,'Iswarganj','ঈশ্বরগঞ্জ'),(473,62,'Nandail','নান্দাইল'),(474,62,'Tarakanda','তারাকান্দা'),(475,63,'Jamalpur Sadar','জামালপুর সদর'),(476,63,'Melandah','মেলান্দহ'),(477,63,'Islampur','ইসলামপুর'),(478,63,'Dewangonj','দেওয়ানগঞ্জ'),(479,63,'Sarishabari','সরিষাবাড়ী'),(480,63,'Madarganj','মাদারগঞ্জ'),(481,63,'Bokshiganj','বকশীগঞ্জ'),(482,64,'Barhatta','বারহাট্টা'),(483,64,'Durgapur','দুর্গাপুর'),(484,64,'Kendua','কেন্দুয়া'),(485,64,'Atpara','আটপাড়া'),(486,64,'Madan','মদন'),(487,64,'Khaliajuri','খালিয়াজুরী'),(488,64,'Kalmakanda','কলমাকান্দা'),(489,64,'Mohongonj','মোহনগঞ্জ'),(490,64,'Purbadhala','পূর্বধলা'),(491,64,'Netrokona Sadar','নেত্রকোণা সদর'),(495,28,'Boalia','বোয়ালিয়া'),(496,28,'Rajpara','রাজপাড়া'),(500,8,'Adabor','আদাবর'),(501,8,'Badda','বাড্ডা'),(502,8,'Bangshal','বংশাল'),(503,8,'Baridhara','বারিধারা'),(504,8,'Basabo','বাসাবো'),(505,8,'Bengali','বেঙ্গালী'),(506,8,'Chandraghona','চন্দ্রঘোনা'),(507,8,'Chawkbazar','চকবাজার'),(508,8,'Danmondi','ধানমন্ডি'),(509,8,'Demra','ডেমরা'),(510,8,'Dhaka Cantt','ঢাকা ক্যান্টনমেন্ট'),(511,8,'Dhanmondi','ধানমন্ডি'),(512,8,'East Agargaon','পূর্ব আগারগাঁও'),(513,8,'Gulshan','গুলশান'),(514,8,'Hazaribagh','হাজারীবাগ'),(515,8,'Jatrabari','যাত্রাবাড়ী'),(516,8,'Kafrul','কাফরুল'),(517,8,'Kalabagan','কালাবাগান'),(518,8,'Kamrangirchar','কমরাঙ্গিরচর'),(519,8,'Khilgaon','খিলগাঁও'),(520,8,'Kochukhet','কোচুখেত'),(521,8,'Kolkata','কলকাতা'),(522,8,'Kotwali','কোটওয়ালী'),(523,8,'Lalbagh','লালবাগ'),(524,8,'Lalbagh Thana','লালবাগ থানা'),(525,8,'Mirpur','মিরপুর'),(526,8,'Motijheel','মতিঝিল'),(527,8,'Nayapaltan','নয়া পল্টন'),(528,8,'New Market','নিউ মার্কেট'),(529,8,'Nikunja','নিকুঞ্জা'),(530,8,'Paltan','পল্টন'),(531,8,'Pahartali','পাহারতলী'),(532,8,'Purarbagh','পুরারবাগ'),(533,8,'Rampura','রামপুরা'),(534,8,'Rayerbazar','রায়েরবাজার'),(535,8,'Sabujbagh','সবুজবাগ'),(536,8,'Sadarghat','সদরঘাট'),(537,8,'Savar','সাভার'),(538,8,'Shahbagh','শাহবাগ'),(539,8,'Shantinagar','শান্তিনগর'),(540,8,'Sherebangla Nagar','শেরেবাংলা নগর'),(541,8,'Shyamoli','শ্যামলী'),(542,8,'Sutrapur','সূত্রাপুর'),(543,8,'Tejgaon','তেজগাঁও'),(544,8,'Tongi','টঙ্গী'),(545,8,'Uttara','উত্তরা'),(546,8,'Vashan','ভাষান'),(547,8,'Wari','ওয়ারী'),(548,8,'Zigatola','জিগাতলা');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `temp_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'user.png',
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'cover.png',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_status_unique` (`email`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','superadmin@gmail.com',NULL,NULL,1,NULL,'$2y$10$UxEyJFREXB2xzDGkEJqEE.yps3n49E6tbk8w5EW0Rr3MP/bha9eXC',NULL,'user.png','cover.png',NULL,'2025-01-23 01:19:45','2025-01-23 01:19:45'),(2,'admin','admin@gmail.com','01745855220','Rangpur Sadar',1,NULL,'$2y$10$qJDzoj7.pzYn4PAoROuQu.Nmjqv.kbUwErJEesa9Q8z5Au7FmP9rK',NULL,'1786179036_6a76eddc11799.png','cover.png',NULL,'2025-01-23 01:19:46','2026-08-08 02:50:36'),(192,'ratul','ratul@gmail.com','01750887855','Mahigonj',1,NULL,'$2y$10$8h67OANl0r.B9FhT6f4PPO2bnH3KaVpthxTnNLQO6vQURklhGSHk6','123456','01750887855.png','cover.png',NULL,'2026-09-02 01:05:24','2026-09-02 01:21:46'),(193,'mahin',NULL,'01765750791','Mahigonj',1,NULL,'$2y$10$ZEssbA7OTeXioTCfH5Xn4ekGmihmhb0IDVl8bVldQ61wmvhkxCoYe','123456','01765750791.png','cover.png',NULL,'2026-09-02 01:06:28','2026-09-02 01:12:51'),(195,'sohag','sohage@gmail.com','01306526906','Mahigonj',1,NULL,'$2y$10$kpanItxzibB4G.VSC1nViu2BasM4UlU0Z50UKj8K8KuaHzmbhceVy','123456','01306526906.png','cover.png',NULL,'2026-09-02 01:15:02','2026-09-02 01:21:44');
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

-- Dump completed on 2026-09-06 11:30:53
