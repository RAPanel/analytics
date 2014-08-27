-- MySQL dump 10.13  Distrib 5.5.36, for Linux (x86_64)
--
-- Host: localhost    Database: semyon_dutyfree
-- ------------------------------------------------------
-- Server version	5.5.36-cll

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
-- Table structure for table `log_visit`
--

DROP TABLE IF EXISTS `log_visit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_visit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` binary(8) NOT NULL,
  `last_action_time` datetime NOT NULL,
  `first_action_time` datetime NOT NULL,
  `location_ip` varbinary(16) NOT NULL,
  `total_time` smallint(6) unsigned NOT NULL,
  `total_actions` smallint(6) unsigned NOT NULL,
  `os` char(3) NOT NULL,
  `browser` varchar(10) NOT NULL,
  `browser_version` varchar(20) NOT NULL,
  `action_id_ref` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=166853 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_action`
--

DROP TABLE IF EXISTS `log_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text,
  `hash` int(11) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=22247 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_hit`
--

DROP TABLE IF EXISTS `log_hit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_hit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` binary(8) NOT NULL,
  `visit_id` int(11) unsigned NOT NULL,
  `action_id_name` int(11) unsigned NOT NULL,
  `action_id_url` int(11) unsigned NOT NULL,
  `action_id_event` int(11) unsigned NOT NULL,
  `time_cpu` smallint(6) unsigned NOT NULL,
  `time_exec` smallint(6) unsigned NOT NULL,
  `ram` smallint(6) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vi_log_visit_lohi_fk` (`visit_id`),
  KEY `aiu_log_action_lohi_fk` (`action_id_url`),
  CONSTRAINT `aiu_log_action_lohi_fk` FOREIGN KEY (`action_id_url`) REFERENCES `log_action` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vi_log_visit_lohi_fk` FOREIGN KEY (`visit_id`) REFERENCES `log_visit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=332269 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-08-27  8:04:37
