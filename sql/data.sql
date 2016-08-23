-- MySQL dump 10.13  Distrib 5.6.24, for linux-glibc2.5 (x86_64)
--
-- Host: localhost    Database: calendar
-- ------------------------------------------------------
-- Server version	5.6.31-0ubuntu0.14.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `apps`
--

LOCK TABLES `apps` WRITE;
/*!40000 ALTER TABLE `apps` DISABLE KEYS */;
INSERT INTO `apps` VALUES ('mMRUI7s7Nn0yGq0','Santiago','Simple','gescalante@arkho.tech',1),('mMRUI7s7Nn0yGq1','La_Florida','PHP','gescalante@php.com',1),('mMRUI7s7Nn0yGq1','Providencia','Zend','gescalante@zend.com',1);
/*!40000 ALTER TABLE `apps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `block_schedules`
--

LOCK TABLES `block_schedules` WRITE;
/*!40000 ALTER TABLE `block_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `calendars`
--

LOCK TABLES `calendars` WRITE;
/*!40000 ALTER TABLE `calendars` DISABLE KEYS */;
INSERT INTO `calendars` VALUES (3,'Agenda Geovanni Escalante','1','Geovanni Escalante',0,'Lunes',20,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(4,'Agenda Santiago Escalante','2','Santiago Escalante',0,'Martes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(5,'Agenda Contabilidad','3','Contabilidad',1,'Miercoles',15,3,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(6,'Agenda Mauricio Blanco','4','Mauricio Blanco',0,'Lunes',20,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(7,'Agenda Milena Barrios','1','Milena Barrios',0,'Lunes',10,1,'1',NULL,'mMRUI7s7Nn0yGq1','Providencia',1),(9,'Agenda Contabilidad','1','Contabilidad',1,'Lunes',20,4,'1',NULL,'mMRUI7s7Nn0yGq1','La_Florida',0),(11,'Agenda Hector Perez','1','Hector Perez Martinez',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq1','Providencia',1),(12,'Agenda Sofia Perez','1','Sofia Perez Martinez',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq1','Providencia',1),(13,'Agenda Ana Bacca','1','Sofia Ana Bacca',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(14,'Agenda Sofia Perez','1','Sofia Perez Martinez',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(21,'Agenda Maricela Hernandez','1','Maricela Hernandez',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(24,'Agenda Maricela Hernandez2','1','Maricela Hernandez',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(34,'Agenda Financiera','1','Financiera',1,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',1),(35,'Agenda Luis López','1','Luis López',0,'Lunes, Martes, Miercoles, Jueves, Viernes',30,1,'0',NULL,'mMRUI7s7Nn0yGq0','Santiago',NULL);
/*!40000 ALTER TABLE `calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `non_working_days`
--

LOCK TABLES `non_working_days` WRITE;
/*!40000 ALTER TABLE `non_working_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `non_working_days` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-08-19  9:12:45
