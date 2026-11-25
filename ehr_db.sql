-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ehr_system
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
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `duration_minutes` smallint(6) NOT NULL DEFAULT 30,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_doctor_date` (`doctor_id`,`appointment_date`),
  KEY `idx_patient` (`patient_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,2,'2026-08-29','04:16:00',30,'checkup','completed',NULL,3,'2026-08-27 16:17:02','2026-08-27 16:18:28'),(2,1,2,'2026-08-27','18:31:00',30,'','completed',NULL,3,'2026-08-27 16:31:20','2026-08-27 16:33:00'),(3,2,2,'2026-08-28','17:00:00',30,'Magpa checkup sa gamay','completed',NULL,3,'2026-08-28 16:33:29','2026-08-28 16:35:21'),(4,3,2,'2026-08-29','15:00:00',30,'Magpa tanaw sa iyang ka monggol','completed',NULL,3,'2026-08-29 14:41:51','2026-08-29 14:44:23'),(5,4,2,'2026-09-03','17:44:00',30,'Basta','completed',NULL,3,'2026-09-03 17:45:02','2026-09-03 17:46:07'),(6,5,2,'2026-09-26','10:00:00',30,'Checkup','no_show',NULL,3,'2026-09-24 08:29:29','2026-09-30 10:23:33'),(13,7,2,'2026-11-10','10:00:00',30,'Basta','cancelled',NULL,3,'2026-11-09 22:19:56','2026-11-11 22:40:20'),(14,7,2,'2026-11-12','10:40:00',30,'secret','cancelled',NULL,3,'2026-11-11 22:40:51','2026-11-11 22:48:27'),(15,7,2,'2026-11-12','10:50:00',30,'','scheduled',NULL,3,'2026-11-11 22:48:45','2026-11-11 22:48:45');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `audit_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=476 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-23 21:45:07'),(2,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-23 21:49:07'),(3,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-23 22:17:30'),(4,1,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-23 22:19:16'),(5,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:26:14'),(6,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:26:18'),(7,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:45:39'),(8,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:45:43'),(9,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:49:48'),(10,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:49:51'),(11,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-24 08:50:02'),(12,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:50:11'),(13,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:50:15'),(14,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-24 08:50:23'),(15,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:57:36'),(16,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:57:47'),(17,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 08:58:15'),(18,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 08:58:19'),(19,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 09:15:49'),(20,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 09:15:52'),(21,NULL,'login_failed','Authentication',NULL,'Unknown username: jadjsj','::1','2026-08-24 09:24:37'),(22,NULL,'login_failed','Authentication',NULL,'Unknown username: jadjsj','::1','2026-08-24 09:24:48'),(23,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-24 09:25:31'),(24,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 09:25:35'),(25,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 09:26:05'),(26,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 09:26:11'),(27,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 09:27:05'),(28,NULL,'login_failed','Authentication',NULL,'Unknown username: sdadsd','::1','2026-08-24 09:27:10'),(29,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 09:27:20'),(30,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 16:31:19'),(31,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 16:34:28'),(32,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 16:34:43'),(33,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 17:10:33'),(34,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 17:10:42'),(35,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 17:18:40'),(36,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 17:18:45'),(37,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 17:34:37'),(38,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 17:34:41'),(39,NULL,'login_failed','Authentication',NULL,'Unknown username: jairene','::1','2026-08-24 17:35:34'),(40,NULL,'login_failed','Authentication',NULL,'Unknown username: jairene','::1','2026-08-24 17:35:46'),(41,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 17:36:00'),(42,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 17:36:10'),(43,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 17:36:28'),(44,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 17:36:37'),(45,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 21:16:26'),(46,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 21:16:30'),(47,NULL,'login_failed','Authentication',NULL,'Unknown username: docasjdasjd','::1','2026-08-24 21:17:59'),(48,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 21:18:45'),(49,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 21:18:57'),(50,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 21:19:13'),(51,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-24 21:19:20'),(52,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-24 21:33:57'),(53,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 10:59:58'),(54,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:00:03'),(55,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:02:57'),(56,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:03:01'),(57,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:03:08'),(58,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:03:12'),(59,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:03:30'),(60,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:03:43'),(61,NULL,'login_failed','Authentication',NULL,'Unknown username: dasdadas','::1','2026-08-25 11:04:07'),(62,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:06:08'),(63,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:06:13'),(64,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:06:17'),(65,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 11:06:21'),(66,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 11:40:05'),(67,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:39:19'),(68,NULL,'login_failed','Authentication',NULL,'Unknown username: jairene','::1','2026-08-25 17:40:17'),(69,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-25 17:40:45'),(70,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 17:41:10'),(71,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:41:15'),(72,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 17:45:20'),(73,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:45:24'),(74,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 17:46:15'),(75,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:46:27'),(76,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 17:47:31'),(77,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:47:36'),(78,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 17:48:31'),(79,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 17:48:35'),(80,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 18:01:05'),(81,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 18:01:27'),(82,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 18:01:33'),(83,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 18:01:36'),(84,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 18:01:43'),(85,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 23:27:04'),(86,NULL,'login_failed','Authentication',NULL,'Unknown username: ajdajsdad','::1','2026-08-25 23:27:09'),(87,NULL,'login_failed','Authentication',NULL,'Unknown username: ajdajsdad','::1','2026-08-25 23:27:11'),(88,NULL,'login_failed','Authentication',NULL,'Unknown username: ajdajsdad','::1','2026-08-25 23:27:12'),(89,NULL,'login_failed','Authentication',NULL,'Unknown username: jairene','::1','2026-08-25 23:27:23'),(90,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-25 23:28:21'),(91,3,'patient_registered','Patients','1','Registered patient PT-2026-0001','::1','2026-08-25 23:31:26'),(92,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-25 23:56:07'),(93,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 17:51:11'),(94,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 17:51:16'),(95,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 17:51:24'),(96,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:06:43'),(97,NULL,'login_failed','Authentication',NULL,'Unknown username: jadhsdhadhsa','::1','2026-08-26 18:07:38'),(98,NULL,'login_failed','Authentication',NULL,'Unknown username: jadhsdhadhsa','::1','2026-08-26 18:07:43'),(99,NULL,'login_failed','Authentication',NULL,'Unknown username: jadhsdhadhsa','::1','2026-08-26 18:07:48'),(100,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:07:53'),(101,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:08:36'),(102,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:08:44'),(103,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:10:18'),(104,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:16:52'),(105,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:16:57'),(106,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:17:07'),(107,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:17:13'),(108,3,'patient_updated','Patients','1','Updated demographic info','::1','2026-08-26 18:21:58'),(109,3,'patient_updated','Patients','1','Updated demographic info','::1','2026-08-26 18:22:09'),(110,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:26:35'),(111,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:26:41'),(112,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:30:52'),(113,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:31:00'),(114,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:31:55'),(115,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:32:03'),(116,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:32:14'),(117,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:32:22'),(118,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:32:47'),(119,3,'patient_updated','Patients','1','Updated demographic info','::1','2026-08-26 18:36:52'),(120,3,'patient_updated','Patients','1','Updated demographic info','::1','2026-08-26 18:37:17'),(121,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:41:29'),(122,NULL,'login_failed','Authentication',NULL,'Unknown username: ajdsjadja','::1','2026-08-26 18:41:35'),(123,NULL,'login_failed','Authentication',NULL,'Unknown username: ajdsjadja','::1','2026-08-26 18:41:42'),(124,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:41:48'),(125,3,'patient_updated','Patients','1','Updated demographic info','::1','2026-08-26 18:42:15'),(126,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:42:39'),(127,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:42:45'),(128,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:43:13'),(129,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:43:20'),(130,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-26 18:43:27'),(131,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-26 18:43:32'),(132,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:03:00'),(133,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:15:49'),(134,3,'appointment_scheduled','Appointments','1','Scheduled for patient #1 with doctor #2','::1','2026-08-27 16:17:02'),(135,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:17:29'),(136,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-27 16:17:35'),(137,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:17:38'),(138,2,'appointment_status_updated','Appointments','1','Status changed to completed','::1','2026-08-27 16:18:28'),(139,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:18:35'),(140,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-27 16:18:40'),(141,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:18:44'),(142,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:18:50'),(143,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:18:55'),(144,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:30:39'),(145,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:30:45'),(146,3,'appointment_scheduled','Appointments','2','Scheduled for patient #1 with doctor #2','::1','2026-08-27 16:31:20'),(147,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:31:25'),(148,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:31:34'),(149,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:31:39'),(150,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:31:45'),(151,2,'consultation_created','Consultations','1','Consultation for appointment #2','::1','2026-08-27 16:33:00'),(152,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:33:18'),(153,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 16:33:24'),(154,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-27 16:43:48'),(155,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-27 22:21:45'),(156,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:13:54'),(157,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:17:09'),(158,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:17:39'),(159,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:17:46'),(160,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:18:14'),(161,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:28:51'),(162,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:29:46'),(163,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-28 16:29:53'),(164,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:29:57'),(165,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:30:39'),(166,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:30:46'),(167,3,'patient_registered','Patients','2','Registered patient PT-2026-0002','::1','2026-08-28 16:32:06'),(168,3,'patient_updated','Patients','2','Updated demographic info','::1','2026-08-28 16:32:39'),(169,3,'appointment_scheduled','Appointments','3','Scheduled for patient #2 with doctor #2','::1','2026-08-28 16:33:29'),(170,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:33:44'),(171,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:33:55'),(172,2,'consultation_created','Consultations','2','Consultation for appointment #3','::1','2026-08-28 16:35:21'),(173,2,'prescription_created','Prescriptions','1','Prescribed 1 item(s)','::1','2026-08-28 16:36:38'),(174,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:36:47'),(175,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-28 16:36:52'),(176,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-28 16:44:20'),(177,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-29 14:29:14'),(178,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-29 14:29:18'),(179,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-29 14:29:24'),(180,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-29 14:39:14'),(181,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-29 14:39:35'),(182,3,'patient_registered','Patients','3','Registered patient PT-2026-0003','::1','2026-08-29 14:40:58'),(183,3,'appointment_scheduled','Appointments','4','Scheduled for patient #3 with doctor #2','::1','2026-08-29 14:41:51'),(184,3,'vitals_recorded','Vitals','1','Vitals recorded for patient #3','::1','2026-08-29 14:42:53'),(185,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-29 14:43:04'),(186,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-29 14:43:17'),(187,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-29 14:43:21'),(188,2,'consultation_created','Consultations','3','Consultation for appointment #4','::1','2026-08-29 14:44:23'),(189,2,'prescription_created','Prescriptions','2','Prescribed 1 item(s)','::1','2026-08-29 14:45:22'),(190,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-29 14:45:37'),(191,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-29 14:45:44'),(192,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-29 14:46:52'),(193,1,'login_success','Authentication',NULL,NULL,'::1','2026-08-29 14:46:59'),(194,1,'logout','Authentication',NULL,NULL,NULL,'2026-08-29 14:47:04'),(195,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-30 14:12:52'),(196,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-30 14:24:18'),(197,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-30 14:24:23'),(198,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-30 14:24:39'),(199,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-30 14:24:45'),(200,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-30 14:25:06'),(201,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-08-30 14:25:12'),(202,2,'login_success','Authentication',NULL,NULL,'::1','2026-08-30 14:25:16'),(203,2,'logout','Authentication',NULL,NULL,NULL,'2026-08-31 23:12:54'),(204,3,'login_success','Authentication',NULL,NULL,'::1','2026-08-31 23:28:20'),(205,3,'logout','Authentication',NULL,NULL,NULL,'2026-08-31 23:28:32'),(206,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:39:48'),(207,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:39:54'),(208,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:40:00'),(209,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:45:52'),(210,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:45:57'),(211,1,'user_created','User Management','4','Created doctor account: drrene','::1','2026-09-01 16:48:16'),(212,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:48:23'),(213,4,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:48:30'),(214,4,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:48:44'),(215,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:48:49'),(216,1,'user_status_changed','User Management','4','Set drrene to inactive','::1','2026-09-01 16:49:34'),(217,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:49:40'),(218,4,'login_blocked','Authentication',NULL,'Inactive account attempted login','::1','2026-09-01 16:49:46'),(219,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:50:16'),(220,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:50:23'),(221,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:50:31'),(222,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 16:50:41'),(223,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 16:50:54'),(224,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 17:00:49'),(225,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 17:00:56'),(226,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 17:01:30'),(227,1,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-01 17:01:35'),(228,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 17:01:42'),(229,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 22:57:20'),(230,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 22:57:29'),(231,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-01 22:57:46'),(232,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-01 22:57:51'),(233,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:13:16'),(234,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:16:58'),(235,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:17:04'),(236,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:20:44'),(237,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:20:51'),(238,1,'user_edited','User Management','4','Edited drrene — Reason: wrong username','::1','2026-09-02 16:29:38'),(239,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:32:26'),(240,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 16:32:32'),(241,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 16:32:37'),(242,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:32:43'),(243,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:33:09'),(244,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:33:15'),(245,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:34:07'),(246,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:34:12'),(247,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 16:44:59'),(248,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 16:45:04'),(249,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:00:55'),(250,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:01:01'),(251,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:01:27'),(252,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:01:35'),(253,1,'user_status_changed','User Management','4','Set baterbonia to active','::1','2026-09-02 17:01:42'),(254,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:01:50'),(255,4,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 17:01:58'),(256,4,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 17:02:06'),(257,4,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 17:02:08'),(258,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:02:15'),(259,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:02:26'),(260,4,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 17:02:32'),(261,4,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:02:36'),(262,4,'profile_updated','Authentication','4','Updated own profile','::1','2026-09-02 17:02:52'),(263,4,'profile_updated','Authentication','4','Updated own profile','::1','2026-09-02 17:03:25'),(264,4,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:03:38'),(265,4,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:03:44'),(266,4,'password_changed','Authentication','4','User changed their own password','::1','2026-09-02 17:04:35'),(267,4,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:04:48'),(268,4,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-02 17:05:02'),(269,4,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:05:08'),(270,4,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:05:12'),(271,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:05:17'),(272,1,'user_status_changed','User Management','4','Set baterbonia to inactive','::1','2026-09-02 17:05:24'),(273,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:07:22'),(274,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:07:28'),(275,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:07:50'),(276,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 17:07:56'),(277,3,'profile_updated','Authentication','3','Updated own profile','::1','2026-09-02 17:08:10'),(278,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 17:10:29'),(279,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 21:51:43'),(280,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-02 21:51:54'),(281,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-02 22:06:06'),(282,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:14:43'),(283,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:22:13'),(284,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-03 17:22:21'),(285,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:22:28'),(286,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:35:39'),(287,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:35:45'),(288,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:41:16'),(289,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:41:30'),(290,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:42:47'),(291,3,'patient_registered','Patients','4','Registered patient PT-2026-0004','::1','2026-09-03 17:44:17'),(292,3,'appointment_scheduled','Appointments','5','Scheduled for patient #4 with doctor #2','::1','2026-09-03 17:45:02'),(293,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:45:13'),(294,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:45:19'),(295,2,'consultation_created','Consultations','4','Consultation for appointment #5','::1','2026-09-03 17:46:07'),(296,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:46:23'),(297,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-03 17:46:30'),(298,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 17:47:47'),(299,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-03 23:17:37'),(300,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-04 14:33:01'),(301,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-04 14:34:27'),(302,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-05 16:28:26'),(303,NULL,'login_failed','Authentication',NULL,'Unknown username: karil','::1','2026-09-06 22:33:01'),(304,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-06 22:33:13'),(305,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-06 22:34:47'),(306,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-06 22:34:52'),(307,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-06 22:35:37'),(308,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-07 09:16:38'),(309,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-08 17:08:01'),(310,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-08 17:14:47'),(311,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-08 17:14:52'),(312,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-08 17:24:05'),(313,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-08 17:24:13'),(314,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-08 17:24:19'),(315,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-10 18:23:26'),(316,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-10 18:32:05'),(317,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-10 18:32:11'),(318,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-10 18:32:18'),(319,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-10 18:32:22'),(320,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-13 21:26:48'),(321,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-18 21:02:15'),(322,1,'backup_created','Backup & Recovery','ehr_backup_2026-09-18_21-02-27.sql','Created backup (43.32 KB)','::1','2026-09-18 21:02:39'),(323,1,'backup_downloaded','Backup & Recovery','ehr_backup_2026-09-18_21-02-27.sql','Downloaded backup file ehr_backup_2026-09-18_21-02-27.sql','::1','2026-09-18 21:03:05'),(324,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-18 21:11:26'),(325,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-18 21:11:38'),(326,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-18 21:11:54'),(327,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-18 21:12:02'),(328,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-18 21:12:10'),(329,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-20 10:21:01'),(330,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-21 21:06:48'),(331,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-21 21:06:57'),(332,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-24 08:25:56'),(333,3,'patient_registered','Patients','5','Registered patient PT-2026-0005','::1','2026-09-24 08:27:56'),(334,3,'patient_updated','Patients','5','Updated demographic info','::1','2026-09-24 08:28:19'),(335,3,'appointment_scheduled','Appointments','6','Scheduled for patient #5 with doctor #2','::1','2026-09-24 08:29:29'),(336,3,'vitals_recorded','Vitals','2','Vitals recorded for patient #5','::1','2026-09-24 08:30:13'),(337,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-24 08:30:26'),(338,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-24 08:30:33'),(339,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-24 08:31:15'),(340,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-24 08:31:20'),(341,1,'backup_created','Backup & Recovery','ehr_backup_2026-09-24_08-31-55.sql','Created backup (45.48 KB)','::1','2026-09-24 08:31:55'),(342,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-24 08:32:17'),(343,1,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-09-30 10:20:49'),(344,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:20:54'),(345,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:22:00'),(346,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:22:08'),(347,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:23:03'),(348,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:23:12'),(349,2,'appointment_status_updated','Appointments','6','Status changed to no_show','::1','2026-09-30 10:23:33'),(350,2,'profile_updated','Authentication','2','Updated own profile','::1','2026-09-30 10:24:40'),(351,2,'profile_updated','Authentication','2','Updated own profile','::1','2026-09-30 10:25:18'),(352,2,'profile_updated','Authentication','2','Updated own profile','::1','2026-09-30 10:29:05'),(353,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:30:20'),(354,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:30:50'),(355,3,'patient_registered','Patients','6','Registered patient PT-2026-0006','::1','2026-09-30 10:31:34'),(356,3,'patient_updated','Patients','6','Updated demographic info','::1','2026-09-30 10:33:00'),(357,3,'lab_result_recorded','Laboratory','1','Recorded Complete Blood Count (CBC) for patient #6','::1','2026-09-30 10:34:40'),(358,3,'appointment_scheduled','Appointments','7','Scheduled for patient #6 with doctor #2','::1','2026-09-30 10:36:47'),(359,3,'vitals_recorded','Vitals','3','Vitals recorded for patient #6','::1','2026-09-30 10:40:02'),(360,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:40:40'),(361,2,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:40:50'),(362,2,'consultation_created','Consultations','5','Consultation for appointment #7','::1','2026-09-30 10:46:50'),(363,2,'prescription_created','Prescriptions','3','Prescribed 1 item(s)','::1','2026-09-30 10:48:41'),(364,2,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:48:50'),(365,3,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:48:58'),(366,3,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:49:22'),(367,1,'login_success','Authentication',NULL,NULL,'::1','2026-09-30 10:49:28'),(368,1,'profile_updated','Authentication','1','Updated own profile','::1','2026-09-30 10:50:06'),(369,1,'profile_updated','Authentication','1','Updated own profile','::1','2026-09-30 10:50:23'),(370,1,'logout','Authentication',NULL,NULL,NULL,'2026-09-30 10:50:31'),(371,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 08:53:26'),(372,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 08:53:34'),(373,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 08:53:41'),(374,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 08:54:10'),(375,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:26:33'),(376,3,'patient_registered','Patients','7','Registered patient PT-2026-0007','::1','2026-10-01 09:27:44'),(377,3,'appointment_scheduled','Appointments','8','Scheduled for patient #7 with doctor #2','::1','2026-10-01 09:28:57'),(378,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:29:02'),(379,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:29:09'),(380,2,'consultation_created','Consultations','6','Consultation for appointment #8','::1','2026-10-01 09:31:02'),(381,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:31:49'),(382,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:31:54'),(383,3,'appointment_scheduled','Appointments','9','Scheduled for patient #7 with doctor #2','::1','2026-10-01 09:32:34'),(384,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:33:38'),(385,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:33:43'),(386,1,'backup_created','Backup & Recovery','ehr_backup_2026-10-01_09-34-29.sql','Created backup (50.74 KB)','::1','2026-10-01 09:34:30'),(387,1,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:35:58'),(388,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:36:02'),(389,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:36:26'),(390,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-01 09:36:34'),(391,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-01 09:39:56'),(392,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-04 21:20:47'),(393,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-08 19:12:26'),(394,1,'backup_created','Backup & Recovery','ehr_backup_2026-10-08_19-12-34.sql','Created backup (51.48 KB)','::1','2026-10-08 19:12:35'),(395,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-10 21:50:05'),(396,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-10 21:50:38'),(397,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-10 21:50:56'),(398,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-10 21:51:06'),(399,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-10 21:51:12'),(400,1,'logout','Authentication',NULL,NULL,NULL,'2026-10-10 21:51:26'),(401,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-13 22:23:21'),(402,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-13 22:24:08'),(403,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-13 22:24:17'),(404,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-13 22:24:25'),(405,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-13 22:24:31'),(406,3,'appointment_status_updated','Appointments','9','Status changed to cancelled','::1','2026-10-13 22:24:55'),(407,3,'patient_archived','Patients','7','Archived patient PT-2026-0007','::1','2026-10-13 22:24:59'),(408,3,'patient_restored','Patients','7','Restored patient PT-2026-0007','::1','2026-10-13 22:27:30'),(409,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-13 22:27:35'),(410,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-13 22:27:40'),(411,1,'logout','Authentication',NULL,NULL,NULL,'2026-10-13 22:27:59'),(412,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-13 22:31:57'),(413,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-17 20:48:21'),(414,3,'appointment_scheduled','Appointments','10','Scheduled for patient #7 with doctor #2 (30 min)','::1','2026-10-17 20:49:08'),(415,3,'appointment_status_updated','Appointments','10','Status changed to cancelled','::1','2026-10-17 20:50:51'),(416,3,'appointment_scheduled','Appointments','11','Scheduled for patient #4 with doctor #2 (30 min)','::1','2026-10-17 20:50:58'),(417,3,'appointment_status_updated','Appointments','11','Status changed to cancelled','::1','2026-10-17 20:52:37'),(418,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-21 21:07:33'),(419,3,'appointment_scheduled','Appointments','12','Scheduled for patient #7 with doctor #2 (30 min)','::1','2026-10-21 21:08:20'),(420,3,'appointment_status_updated','Appointments','12','Status changed to cancelled','::1','2026-10-21 21:08:32'),(421,3,'patient_archived','Patients','4','Archived patient PT-2026-0004','::1','2026-10-21 21:12:38'),(422,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-21 21:13:53'),(423,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-21 21:14:29'),(424,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-21 21:17:57'),(425,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-21 21:18:06'),(426,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-21 21:23:37'),(427,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-21 21:25:35'),(428,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-21 21:27:00'),(429,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-21 21:27:10'),(430,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-21 21:27:53'),(431,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:09:24'),(432,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:10:07'),(433,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:10:14'),(434,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:10:24'),(435,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:10:30'),(436,1,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:12:13'),(437,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:12:31'),(438,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:13:57'),(439,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:14:25'),(440,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:20:03'),(441,2,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:20:14'),(442,2,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:21:27'),(443,1,'login_success','Authentication',NULL,NULL,'::1','2026-10-26 18:21:34'),(444,1,'logout','Authentication',NULL,NULL,NULL,'2026-10-26 18:21:50'),(445,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-28 15:32:53'),(446,3,'login_success','Authentication',NULL,NULL,'::1','2026-10-31 18:02:49'),(447,3,'patient_summary_viewed','Patients','7','Opened printable summary for PT-2026-0007','::1','2026-10-31 18:18:45'),(448,3,'patient_summary_viewed','Patients','7','Opened printable summary for PT-2026-0007','::1','2026-10-31 18:19:38'),(449,3,'logout','Authentication',NULL,NULL,NULL,'2026-10-31 18:19:52'),(450,3,'login_success','Authentication',NULL,NULL,'::1','2026-11-08 15:14:09'),(451,3,'logout','Authentication',NULL,NULL,NULL,'2026-11-08 15:15:23'),(452,2,'login_failed','Authentication',NULL,'Incorrect password','::1','2026-11-08 15:15:31'),(453,2,'login_success','Authentication',NULL,NULL,'::1','2026-11-08 15:15:36'),(454,2,'logout','Authentication',NULL,NULL,NULL,'2026-11-08 15:21:24'),(455,3,'login_success','Authentication',NULL,NULL,'::1','2026-11-08 15:21:29'),(456,3,'login_success','Authentication',NULL,NULL,'::1','2026-11-09 22:19:03'),(457,3,'appointment_scheduled','Appointments','13','Scheduled for patient #7 with doctor #2 (30 min)','::1','2026-11-09 22:19:56'),(458,3,'doctor_notified','Appointments','13','Email notification to doctor #2','::1','2026-11-09 22:20:03'),(459,3,'logout','Authentication',NULL,NULL,NULL,'2026-11-09 22:20:24'),(460,2,'login_success','Authentication',NULL,NULL,'::1','2026-11-09 22:20:32'),(461,2,'logout','Authentication',NULL,NULL,NULL,'2026-11-09 22:20:51'),(462,3,'login_success','Authentication',NULL,NULL,'::1','2026-11-11 22:40:11'),(463,3,'appointment_status_updated','Appointments','13','Status changed to cancelled','::1','2026-11-11 22:40:20'),(464,3,'appointment_scheduled','Appointments','14','Scheduled for patient #7 with doctor #2 (30 min)','::1','2026-11-11 22:40:51'),(465,3,'doctor_notified','Appointments','14','Email notification to doctor #2','::1','2026-11-11 22:40:58'),(466,3,'logout','Authentication',NULL,NULL,NULL,'2026-11-11 22:44:31'),(467,2,'login_success','Authentication',NULL,NULL,'::1','2026-11-11 22:47:57'),(468,2,'logout','Authentication',NULL,NULL,NULL,'2026-11-11 22:48:07'),(469,3,'login_success','Authentication',NULL,NULL,'::1','2026-11-11 22:48:13'),(470,3,'appointment_status_updated','Appointments','14','Status changed to cancelled','::1','2026-11-11 22:48:27'),(471,3,'appointment_scheduled','Appointments','15','Scheduled for patient #7 with doctor #2 (30 min)','::1','2026-11-11 22:48:46'),(472,3,'doctor_notified','Appointments','15','Email notification to doctor #2','::1','2026-11-11 22:48:52'),(473,3,'logout','Authentication',NULL,NULL,NULL,'2026-11-11 22:49:48'),(474,2,'login_success','Authentication',NULL,NULL,'::1','2026-11-24 21:21:13'),(475,1,'login_success','Authentication',NULL,NULL,'::1','2026-11-25 21:57:25');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_logs` (
  `backup_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`backup_log_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_logs`
--

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
INSERT INTO `backup_logs` VALUES (1,'ehr_backup_2026-09-18_21-02-27.sql','43.32 KB',1,'success',NULL,'2026-09-18 21:02:39'),(2,'ehr_backup_2026-09-24_08-31-55.sql','45.48 KB',1,'success',NULL,'2026-09-24 08:31:55'),(3,'ehr_backup_2026-10-01_09-34-29.sql','50.74 KB',1,'success',NULL,'2026-10-01 09:34:30'),(4,'ehr_backup_2026-10-08_19-12-34.sql','51.48 KB',1,'success',NULL,'2026-10-08 19:12:35');
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultations`
--

DROP TABLE IF EXISTS `consultations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `chief_complaint` varchar(255) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `physical_exam` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`consultation_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `idx_patient` (`patient_id`),
  CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultations`
--

LOCK TABLES `consultations` WRITE;
/*!40000 ALTER TABLE `consultations` DISABLE KEYS */;
INSERT INTO `consultations` VALUES (1,2,1,2,'Lagnat','Init kay','Oks lang','baw','inom tambal','hehe','2026-08-27 16:33:00'),(2,3,2,2,'Sip on','Clogged ag ilong','di okay','unsani','Paimnon og redhorse','gamay nalang naa nay gamay','2026-08-28 16:35:21'),(3,4,3,2,'Kamonngol','Mokalit ra','murag adik','naa juy gamay','paimnon og redhorse','basta mabuhi rani','2026-08-29 14:44:23'),(4,5,4,2,'Ubo','sig ubo','healthy','baw unsa ni','inom tambal','secret','2026-09-03 17:46:07'),(5,NULL,6,2,'Insomia','di ka tug','naay mga eyebgs','faadajdbashdadba','abdfhdusidjaida','','2026-09-30 10:46:50'),(6,NULL,7,2,'Insomia','dikatug','eyebags dako','','patugon paimnon tanduway','','2026-10-01 09:31:02');
/*!40000 ALTER TABLE `consultations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_results`
--

DROP TABLE IF EXISTS `lab_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_results` (
  `lab_result_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `test_name` varchar(150) NOT NULL,
  `test_date` date NOT NULL,
  `result_value` varchar(255) DEFAULT NULL,
  `reference_range` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `recorded_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lab_result_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_patient` (`patient_id`),
  CONSTRAINT `lab_results_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `lab_results_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `lab_results_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_results`
--

LOCK TABLES `lab_results` WRITE;
/*!40000 ALTER TABLE `lab_results` DISABLE KEYS */;
INSERT INTO `lab_results` VALUES (1,6,NULL,'Complete Blood Count (CBC)','2026-09-25','10.0 g/dL','12.0–17.0 g/dL','Delikado','completed',3,'2026-09-30 10:34:40');
/*!40000 ALTER TABLE `lab_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_code` varchar(20) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `middle_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) NOT NULL,
  `date_of_birth` date NOT NULL,
  `sex` enum('male','female') NOT NULL,
  `civil_status` enum('single','married','widowed','separated') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-','unknown') NOT NULL DEFAULT 'unknown',
  `allergies` text DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `patient_code` (`patient_code`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,'PT-2026-0001','Jairene','P','Lumba','2021-02-10','female','single','zone 5 sogod','121312121','Irene','0913213132','B+','haduhsadauda','wala','active',3,'2026-08-25 23:31:26','2026-08-26 18:42:15'),(2,'PT-2026-0002','Lemuel','John','Abrea','2005-06-15','male','married','San Pedro','09123114132131','Ate oms','09123141332','B+','','Naay gamay','active',3,'2026-08-28 16:32:06','2026-08-28 16:32:39'),(3,'PT-2026-0003','Edrian','P.','Palerzkie','1999-03-25','male',NULL,'Bontoc hehehe','091234567788','Iya Daddy','0912312324432','A-','Peanuts','Mokalit og kamonggol','active',3,'2026-08-29 14:40:58','2026-08-29 14:40:58'),(4,'PT-2026-0004','Jai','P','Lumbs','2008-02-19','male',NULL,'Zone3','09192313212','','','A+','','','archived',3,'2026-09-03 17:44:17','2026-10-21 21:12:38'),(5,'PT-2026-0005','Uriel','S.','Serenio','2002-02-22','male',NULL,'San Rouque','09123131445','Mama niya','09123524342','A-','','','active',3,'2026-09-24 08:27:56','2026-09-24 08:28:19'),(6,'PT-2026-0006','Johnny','J.','Yespapa','2003-07-10','male','widowed','Macrohon Southern Leyte','09857472347','Jannah','091231513232','B+','Peanut','','active',3,'2026-09-30 10:31:34','2026-09-30 10:33:00'),(7,'PT-2026-0007','Jeffrey','M.','Itaok','2008-10-17','male',NULL,'','','','','unknown','','','active',3,'2026-10-01 09:27:44','2026-10-13 22:27:30');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescription_items`
--

DROP TABLE IF EXISTS `prescription_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescription_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `frequency` varchar(100) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `prescription_id` (`prescription_id`),
  CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescription_items`
--

LOCK TABLES `prescription_items` WRITE;
/*!40000 ALTER TABLE `prescription_items` DISABLE KEYS */;
INSERT INTO `prescription_items` VALUES (1,1,'Redhorse','5kabaso','10x day','1 day','Nig human og kaon hehe'),(2,2,'Redhorse','2 ka baso','24x a day','7 days','bisan kanus a'),(3,3,'Melatonin','300mg','2x a day','1 mont','after eating');
/*!40000 ALTER TABLE `prescription_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL AUTO_INCREMENT,
  `consultation_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`prescription_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `idx_patient` (`patient_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (1,NULL,2,2,'optional ni?','2026-08-28 16:36:38'),(2,NULL,3,2,'','2026-08-29 14:45:22'),(3,NULL,6,2,'','2026-09-30 10:48:40');
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` enum('admin','doctor','staff') NOT NULL,
  `account_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `avatar_path` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$jQPVg2tExg2goNnSZr1Dx.RPTanGx2sEWzXxuySCiJAHn0mrjga3.','System Administrator',NULL,'admin','active',NULL,'2026-11-25 21:57:25','2026-08-23 21:44:52','2026-11-25 21:57:25'),(2,'doctor','$2y$10$Xb1ciq/7wVF5QJm9lnMijubxddepUWlWBktkYsPXSy24hAFaC8tjK','Dr. Johnny Since','frysnana@gmail.com','doctor','active','assets/uploads/avatars/user_2_1790735342.jpg','2026-11-24 21:21:13','2026-08-23 21:44:52','2026-11-24 21:21:13'),(3,'staff','$2y$10$biVcHI5xsh5e002Aa3bzbeXSi3rXudx9vVPO2xP5L.YkRKT0q74Bu','Karil Cally',NULL,'staff','active','assets/uploads/avatars/user_3_1788340090.jpg','2026-11-11 22:48:13','2026-08-23 21:44:52','2026-11-11 22:48:13'),(4,'baterbonia','$2y$10$QU4RMOa9FBzNtm7QSyNgq.sdazJfYFXte6SePn5J4ffiF0tkZHA9y','Dr. Rene Baterbons',NULL,'doctor','inactive','assets/uploads/avatars/user_4_1788339805.jpg','2026-09-02 17:05:08','2026-09-01 16:48:16','2026-09-02 17:05:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vitals`
--

DROP TABLE IF EXISTS `vitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vitals` (
  `vital_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `pulse_rate` smallint(6) DEFAULT NULL,
  `respiratory_rate` smallint(6) DEFAULT NULL,
  `oxygen_saturation` smallint(6) DEFAULT NULL,
  `height_cm` decimal(5,1) DEFAULT NULL,
  `weight_kg` decimal(5,1) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`vital_id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_appointment` (`appointment_id`),
  CONSTRAINT `vitals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `vitals_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `vitals_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vitals`
--

LOCK TABLES `vitals` WRITE;
/*!40000 ALTER TABLE `vitals` DISABLE KEYS */;
INSERT INTO `vitals` VALUES (1,3,4,3,'110/90',37.0,99,18,100,150.0,60.0,'2026-08-29 14:42:53'),(2,5,6,3,'110/80',35.0,87,13,97,170.0,62.0,'2026-09-24 08:30:13'),(3,6,NULL,3,'130/90',37.1,80,9,91,170.0,65.0,'2026-09-30 10:40:01');
/*!40000 ALTER TABLE `vitals` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25 21:57:34
