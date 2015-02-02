-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Host: mysql.enigmadream.com
-- Generation Time: Feb 01, 2015 at 03:36 PM
-- Server version: 5.1.56
-- PHP Version: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `enigmadream`
--

-- --------------------------------------------------------

--
-- Table structure for table `tg_illustration`
--

DROP TABLE IF EXISTS `tg_illustration`;
CREATE TABLE IF NOT EXISTS `tg_illustration` (
  `id` bigint(20) unsigned NOT NULL,
  `fk_phrase` bigint(20) unsigned DEFAULT NULL COMMENT 'Reference to tg_illustration',
  `format` enum('PNG','GIF','SVG') COLLATE utf8_unicode_ci NOT NULL,
  `illustration` blob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientaddress` varbinary(16) NOT NULL,
  `fk_user` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `predecessor` (`fk_phrase`,`timestamp`,`clientaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_phrase`
--

DROP TABLE IF EXISTS `tg_phrase`;
CREATE TABLE IF NOT EXISTS `tg_phrase` (
  `id` bigint(20) unsigned NOT NULL,
  `fk_illustration` bigint(20) unsigned DEFAULT NULL COMMENT 'Reference to tg_illustration',
  `phrase` varchar(140) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientaddress` varbinary(16) NOT NULL,
  `fk_user` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `predecessor` (`fk_illustration`,`timestamp`,`clientaddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_user`
--

DROP TABLE IF EXISTS `tg_user`;
CREATE TABLE IF NOT EXISTS `tg_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `lastaddress` varbinary(16) NOT NULL,
  `googleid` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `googleid` (`googleid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;
