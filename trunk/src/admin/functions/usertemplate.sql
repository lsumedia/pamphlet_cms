-- phpMyAdmin SQL Dump
-- version 4.1.14.8
-- http://www.phpmyadmin.net
--
-- Host: db591766411.db.1and1.com
-- Generation Time: Sep 16, 2015 at 06:04 PM
-- Server version: 5.5.44-0+deb7u1-log
-- PHP Version: 5.4.45-0+deb7u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db591766411`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `fullname` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `bio` longtext COLLATE latin1_general_ci,
  `password` char(64) COLLATE latin1_general_ci NOT NULL,
  `privilege` int(11) NOT NULL,
  `salt` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `dpurl` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `reg_date` date NOT NULL,
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Password in SHA-256';

--
-- Default user account init
--

INSERT INTO `users` (`username`, `fullname`, `bio`, `password`, `privilege`, `salt`, `dpurl`, `email`, `reg_date`) VALUES
('Administrator', 'Administrator', '<p>This is the default administrator account. Please change the password immediately.</p>\n', '30ae0449fde97c290f92d5425629488aec6a907bd7e2e637423b8df40a80e4e6', 1, 'e7b98d9ff0f72cdf287981c8d457df0e4fd52a34', '', '', '2015-09-16');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
