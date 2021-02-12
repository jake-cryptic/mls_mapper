-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2019 at 12:29 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lte_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `masts`
--

CREATE TABLE `masts2` (
  `mast_id` int(15) NOT NULL,
  `mnc` int(3) NOT NULL,
  `enodeb_id` int(10) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `updated` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors2` (
  `cell_id` int(15) NOT NULL,
  `mnc` smallint(3) NOT NULL,
  `enodeb_id` int(10) NOT NULL,
  `sector_id` smallint(3) NOT NULL,
  `pci` smallint(3) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `samples` int(5) NOT NULL,
  `created` int(11) UNSIGNED NOT NULL,
  `updated` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `masts`
--
ALTER TABLE `masts2`
  ADD PRIMARY KEY (`mast2_id`),
  ADD KEY `mnc2_index` (`mnc`),
  ADD KEY `enb2_index` (`enodeb_id`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors2`
  ADD PRIMARY KEY (`cell2_id`),
  ADD KEY `enodeb2_id` (`enodeb_id`),
  ADD KEY `sector2_id` (`sector_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `masts`
--
ALTER TABLE `masts`
  MODIFY `mast_id` int(15) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
