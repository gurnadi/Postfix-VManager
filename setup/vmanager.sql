-- MySQL dump 10.13  Distrib 5.1.68, for portbld-freebsd9.1 (amd64)
--
-- Host: localhost    Database: vmanager
-- ------------------------------------------------------
-- Server version	5.1.68-log

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
-- Table structure for table `alias_domain`
--

DROP TABLE IF EXISTS `alias_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alias_domain` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `address` varchar(120) NOT NULL,
  `alias_domain` varchar(120) NOT NULL,
  `target_domain` varchar(255) NOT NULL DEFAULT '',
  `isDefault` enum('N','Y') NOT NULL DEFAULT 'N',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`address`),
  UNIQUE KEY `id` (`id`),
  KEY `target_domain` (`target_domain`),
  KEY `alias_domain` (`alias_domain`),
  KEY `isDefault` (`isDefault`),
  KEY `active` (`active`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domain`
--

DROP TABLE IF EXISTS `domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `groups` int(10) NOT NULL DEFAULT '0',
  `mailboxes` int(10) NOT NULL DEFAULT '0',
  `maxquota` bigint(20) NOT NULL DEFAULT '0',
  `transport` varchar(255) NOT NULL,
  `ds_mboxes` enum('N','Y') NOT NULL DEFAULT 'N',
  `ds_groups` enum('N','Y') NOT NULL DEFAULT 'N',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`domain`),
  KEY `active` (`active`),
  KEY `ds_aliases` (`ds_groups`),
  KEY `ds_mboxes` (`ds_mboxes`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COMMENT='Postfix Manage - Virtual Domains';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forwarders`
--

DROP TABLE IF EXISTS `forwarders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forwarders` (
  `address` varchar(255) NOT NULL,
  `goto` text NOT NULL,
  `domain` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  KEY `address` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `address` varchar(255) NOT NULL,
  `goto` text NOT NULL,
  `domain` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailbox`
--

DROP TABLE IF EXISTS `mailbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailbox` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `maildir` varchar(255) NOT NULL,
  `quota` bigint(20) NOT NULL DEFAULT '0',
  `domain` varchar(255) NOT NULL,
  `alias` enum('N','Y') NOT NULL DEFAULT 'N',
  `autoresponse` enum('N','Y') NOT NULL DEFAULT 'N',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `admin` enum('SuperAdmin','DomainAdmin','SubAdmin','Webmaster','User') NOT NULL DEFAULT 'User',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`username`),
  KEY `domain` (`domain`),
  KEY `admin` (`admin`),
  KEY `modified` (`modified`),
  KEY `active` (`active`),
  KEY `autoresponse` (`autoresponse`),
  KEY `alias` (`alias`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COMMENT='Postfix Manage - Virtual Mailboxes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parking_domains`
--

DROP TABLE IF EXISTS `parking_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parking_domains` (
  `domain` varchar(255) NOT NULL,
  `smtp_host` varchar(200) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sender_relay`
--

DROP TABLE IF EXISTS `sender_relay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sender_relay` (
  `email` varchar(255) NOT NULL,
  `transport` varchar(100) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transport`
--

DROP TABLE IF EXISTS `transport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transport` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(128) NOT NULL DEFAULT '',
  `destination` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vacation`
--

DROP TABLE IF EXISTS `vacation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vacation` (
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cache` text NOT NULL,
  `domain` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Manage - Virtual Vacation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vacation_notification`
--

DROP TABLE IF EXISTS `vacation_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vacation_notification` (
  `on_vacation` varchar(255) NOT NULL,
  `notified` varchar(255) NOT NULL,
  `notified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`on_vacation`,`notified`),
  CONSTRAINT `vacation_notification_pkey` FOREIGN KEY (`on_vacation`) REFERENCES `vacation` (`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Manage - Virtual Vacation Notifications';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-17 10:43:31
