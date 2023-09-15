-- MySQL dump 10.13  Distrib 8.0.20, for Linux (x86_64)
--
-- Host: localhost    Database: projects_ghatna_pms
-- ------------------------------------------------------
-- Server version	8.0.20-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_tokens`
--

DROP TABLE IF EXISTS `access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_tokens` (
  `token_id` int NOT NULL AUTO_INCREMENT,
  `token_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_expiry` datetime NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `token_id_UNIQUE` (`token_id`),
  KEY `fk_access_tokens_1_idx` (`user_id`),
  CONSTRAINT `fk_access_tokens_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_tokens`
--

LOCK TABLES `access_tokens` WRITE;
/*!40000 ALTER TABLE `access_tokens` DISABLE KEYS */;
INSERT INTO `access_tokens` VALUES (112,'c743cac4325fa55c55192cd4a0b9e949','2020-06-15 20:13:59',1);
/*!40000 ALTER TABLE `access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_roles`
--

DROP TABLE IF EXISTS `employee_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_roles`
--

LOCK TABLES `employee_roles` WRITE;
/*!40000 ALTER TABLE `employee_roles` DISABLE KEYS */;
INSERT INTO `employee_roles` VALUES (1,'Associate'),(2,'Project Manager');
/*!40000 ALTER TABLE `employee_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_roles_joins`
--

DROP TABLE IF EXISTS `employee_roles_joins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_roles_joins` (
  `join_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`join_id`),
  KEY `fk_employee_roles_joins_2_idx` (`role_id`),
  KEY `fk_employee_roles_joins_1_idx` (`emp_id`),
  CONSTRAINT `fk_employee_roles_joins_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_employee_roles_joins_2` FOREIGN KEY (`role_id`) REFERENCES `employee_roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_roles_joins`
--

LOCK TABLES `employee_roles_joins` WRITE;
/*!40000 ALTER TABLE `employee_roles_joins` DISABLE KEYS */;
INSERT INTO `employee_roles_joins` VALUES (3,9,1),(4,9,2),(19,12,2),(21,13,2),(32,14,1);
/*!40000 ALTER TABLE `employee_roles_joins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_target_history`
--

DROP TABLE IF EXISTS `employee_target_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_target_history` (
  `tg_id` int NOT NULL AUTO_INCREMENT,
  `tg_amount` int NOT NULL,
  `tg_startdate` date NOT NULL,
  `emp_id` int NOT NULL,
  PRIMARY KEY (`tg_id`),
  KEY `fk_employee_target_history_1_idx` (`emp_id`),
  CONSTRAINT `fk_employee_target_history_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_target_history`
--

LOCK TABLES `employee_target_history` WRITE;
/*!40000 ALTER TABLE `employee_target_history` DISABLE KEYS */;
INSERT INTO `employee_target_history` VALUES (3,3232,'2020-06-02',14),(4,100,'2020-02-01',14);
/*!40000 ALTER TABLE `employee_target_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_type_history`
--

DROP TABLE IF EXISTS `employee_type_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_type_history` (
  `tp_id` int NOT NULL AUTO_INCREMENT,
  `tp_type` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tp_startdate` date NOT NULL,
  `emp_id` int NOT NULL,
  PRIMARY KEY (`tp_id`),
  KEY `fk_employee_type_history_1_idx` (`emp_id`),
  CONSTRAINT `fk_employee_type_history_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_type_history`
--

LOCK TABLES `employee_type_history` WRITE;
/*!40000 ALTER TABLE `employee_type_history` DISABLE KEYS */;
INSERT INTO `employee_type_history` VALUES (1,'Insider','2020-03-05',9),(9,'Insider','2020-03-19',12),(10,'Insider','2020-03-19',13),(11,'Insider','2020-03-19',14),(12,'Outsider','2020-03-25',9);
/*!40000 ALTER TABLE `employee_type_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `emp_id` int NOT NULL AUTO_INCREMENT,
  `emp_uid` int NOT NULL,
  `emp_fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (9,0,'ankur gupta'),(12,0,'Radhe Shyam'),(13,5,'Krishna Poddar'),(14,4,'Pooja Gupta');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `old_work`
--

DROP TABLE IF EXISTS `old_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `old_work` (
  `work_id` int NOT NULL AUTO_INCREMENT,
  `work_date` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_type` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_amount` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_id` int DEFAULT NULL,
  PRIMARY KEY (`work_id`),
  KEY `fk_old_work_1_idx` (`emp_id`),
  CONSTRAINT `fk_old_work_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `old_work`
--

LOCK TABLES `old_work` WRITE;
/*!40000 ALTER TABLE `old_work` DISABLE KEYS */;
INSERT INTO `old_work` VALUES (1,'2020-05-31','Project Work','33',13),(2,'2020-05-29','Incentive','11',9),(3,'2020-03-18','Project Work','50',14),(4,'2020-03-19','Incentive','10',14);
/*!40000 ALTER TABLE `old_work` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `pmt_id` int NOT NULL AUTO_INCREMENT,
  `pmt_month` date NOT NULL DEFAULT '2019-01-01',
  `pmt_date` date NOT NULL,
  `pmt_amount` decimal(10,2) NOT NULL,
  `pmt_mode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pmt_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_id` int NOT NULL,
  PRIMARY KEY (`pmt_id`),
  KEY `fk_payments_1_idx` (`emp_id`),
  CONSTRAINT `fk_payments_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (4,'2019-01-01','2020-03-31',10.00,'','',9),(30,'2020-05-31','2020-05-31',165.00,'Net Banking','3434343',14);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_costs`
--

DROP TABLE IF EXISTS `project_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_costs` (
  `cost_id` int NOT NULL AUTO_INCREMENT,
  `cost_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_amount` decimal(10,2) NOT NULL,
  `cost_incentive_rate` decimal(10,2) NOT NULL,
  `proj_id` int NOT NULL,
  PRIMARY KEY (`cost_id`),
  KEY `fk_project_costs_1_idx` (`proj_id`),
  CONSTRAINT `fk_project_costs_1` FOREIGN KEY (`proj_id`) REFERENCES `projects` (`proj_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_costs`
--

LOCK TABLES `project_costs` WRITE;
/*!40000 ALTER TABLE `project_costs` DISABLE KEYS */;
INSERT INTO `project_costs` VALUES (5,'Marketing',200.00,10.00,3),(6,'Creation',400.00,20.00,3);
/*!40000 ALTER TABLE `project_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_incentives`
--

DROP TABLE IF EXISTS `project_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_incentives` (
  `inc_id` int NOT NULL AUTO_INCREMENT,
  `inc_date` date NOT NULL,
  `inc_amount` decimal(10,2) NOT NULL,
  `ts_id` int NOT NULL,
  `pr_id` int NOT NULL,
  PRIMARY KEY (`inc_id`),
  KEY `fk_project_incentives_2_idx` (`ts_id`),
  KEY `fk_project_incentives_1_idx` (`pr_id`),
  CONSTRAINT `fk_project_incentives_1` FOREIGN KEY (`pr_id`) REFERENCES `project_printorders` (`pr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_project_incentives_2` FOREIGN KEY (`ts_id`) REFERENCES `project_tasks` (`ts_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_incentives`
--

LOCK TABLES `project_incentives` WRITE;
/*!40000 ALTER TABLE `project_incentives` DISABLE KEYS */;
INSERT INTO `project_incentives` VALUES (89,'2020-03-28',20.00,7,30),(91,'2020-03-28',30.00,6,30),(92,'2020-03-24',20.00,7,31),(93,'2020-03-24',25.00,4,31),(94,'2020-03-24',30.00,6,31);
/*!40000 ALTER TABLE `project_incentives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_printorders`
--

DROP TABLE IF EXISTS `project_printorders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_printorders` (
  `pr_id` int NOT NULL AUTO_INCREMENT,
  `pr_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pr_date` date NOT NULL,
  `proj_id` int NOT NULL,
  PRIMARY KEY (`pr_id`),
  KEY `fk_project_printorders_1_idx` (`proj_id`),
  CONSTRAINT `project_printorders_FK` FOREIGN KEY (`proj_id`) REFERENCES `projects` (`proj_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_printorders`
--

LOCK TABLES `project_printorders` WRITE;
/*!40000 ALTER TABLE `project_printorders` DISABLE KEYS */;
INSERT INTO `project_printorders` VALUES (30,'ddsd','2020-03-28',3),(31,'dsdsd','2020-03-24',3);
/*!40000 ALTER TABLE `project_printorders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tasks`
--

DROP TABLE IF EXISTS `project_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_tasks` (
  `ts_id` int NOT NULL AUTO_INCREMENT,
  `ts_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ts_rate` decimal(10,2) NOT NULL,
  `ts_qty` decimal(10,2) NOT NULL,
  `ts_amount` decimal(10,2) NOT NULL,
  `ts_estimated_date` date NOT NULL,
  `ts_completion_date` date NOT NULL,
  `ts_alotted_by` int NOT NULL,
  `ts_alotted_to` int NOT NULL,
  `ts_status` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`ts_id`),
  KEY `fk_project_tasks_1_idx` (`user_id`),
  KEY `fk_project_tasks_2_idx` (`task_id`),
  KEY `fk_project_tasks_3_idx` (`cost_id`),
  CONSTRAINT `fk_project_tasks_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_project_tasks_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_project_tasks_3` FOREIGN KEY (`cost_id`) REFERENCES `project_costs` (`cost_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tasks`
--

LOCK TABLES `project_tasks` WRITE;
/*!40000 ALTER TABLE `project_tasks` DISABLE KEYS */;
INSERT INTO `project_tasks` VALUES (4,'For design idea and concept',50.00,2.00,100.00,'2020-02-01','2020-02-10',13,14,'Completed',6,13,1),(5,'HTML Conversion',100.00,1.00,100.00,'2020-02-11','2020-02-22',13,12,'Pending',6,2,1),(6,'Water color paint',150.00,1.00,150.00,'2020-03-24','2020-03-06',12,9,'Completed',6,12,1),(7,'Writing Slogas',50.00,4.00,200.00,'2020-03-10','2020-03-14',13,14,'Completed',5,2,1);
/*!40000 ALTER TABLE `project_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `proj_id` int NOT NULL AUTO_INCREMENT,
  `proj_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proj_creation_date` date NOT NULL,
  `proj_completion_date` date NOT NULL,
  `proj_lasttaskdate` date NOT NULL,
  PRIMARY KEY (`proj_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (3,'My Project','2020-03-01','2020-03-27','2020-03-27');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `task_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (2,'Web Designing'),(9,'Writing'),(12,'Painting'),(13,'Thinking'),(14,'Drawing');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','1a1dc91c907325c69271ddf0c944bc72');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workdone`
--

DROP TABLE IF EXISTS `workdone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workdone` (
  `wd_id` int NOT NULL AUTO_INCREMENT,
  `wd_date` date NOT NULL,
  `wd_amount` decimal(10,2) NOT NULL,
  `emp_id` int NOT NULL,
  PRIMARY KEY (`wd_id`),
  KEY `fk_workdone_1_idx` (`emp_id`),
  CONSTRAINT `fk_workdone_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workdone`
--

LOCK TABLES `workdone` WRITE;
/*!40000 ALTER TABLE `workdone` DISABLE KEYS */;
INSERT INTO `workdone` VALUES (4,'2020-01-01',-100.00,9),(5,'2020-02-01',200.00,9),(6,'2020-03-01',10.00,9),(7,'2020-03-01',150.00,14),(8,'2020-02-01',0.00,14),(9,'2020-05-01',-100.00,14),(10,'2020-04-01',-100.00,14),(11,'2019-03-01',0.00,14),(12,'2019-05-01',-100.00,14),(13,'2019-04-01',-100.00,14),(14,'2019-01-01',-100.00,14),(15,'2019-02-01',-100.00,14),(16,'2019-09-01',0.00,14),(17,'2020-06-01',-3232.00,14),(18,'2020-05-01',-300.00,13),(19,'2020-01-01',-100.00,14),(20,'2019-12-01',-100.00,14),(21,'2019-06-01',-100.00,14),(22,'2019-07-01',-100.00,14),(23,'2019-08-01',0.00,14),(24,'2019-10-01',-100.00,14);
/*!40000 ALTER TABLE `workdone` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-06-27 18:29:22
