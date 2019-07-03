-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2019 at 03:15 PM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 5.6.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reactnativeweb`
--

-- --------------------------------------------------------

--
-- Table structure for table `fruitsnamelisttable`
--

CREATE TABLE `fruitsnamelisttable` (
  `id` int(11) NOT NULL,
  `fruit_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `fruitsnamelisttable`
--

INSERT INTO `fruitsnamelisttable` (`id`, `fruit_name`) VALUES
(1, 'Apples'),
(2, 'Apricots'),
(3, 'Avocados'),
(4, 'Bananas'),
(5, 'Boysenberries'),
(6, 'Blueberries'),
(7, 'Bing Cherry'),
(8, 'Cherries'),
(9, 'Cantaloupe'),
(10, 'Crab apples'),
(11, 'Clementine'),
(12, 'Cucumbers');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fruitsnamelisttable`
--
ALTER TABLE `fruitsnamelisttable`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fruitsnamelisttable`
--
ALTER TABLE `fruitsnamelisttable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
