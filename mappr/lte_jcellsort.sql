-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2020 at 06:21 PM
-- Server version: 10.5.5-MariaDB
-- PHP Version: 7.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lte_jcellsort`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `bookmark_id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` smallint(6) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `masts`
--

CREATE TABLE `masts` (
  `id` int(15) NOT NULL,
  `mcc` int(3) NOT NULL,
  `mnc` int(3) NOT NULL,
  `enodeb_id` int(10) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `updated` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mast_locations`
--

CREATE TABLE `mast_locations` (
  `id` int(11) NOT NULL,
  `mcc` smallint(4) NOT NULL,
  `mnc` smallint(3) NOT NULL,
  `enodeb_id` int(10) NOT NULL,
  `updated` int(11) NOT NULL,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `user_id` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `id` int(15) NOT NULL,
  `mcc` smallint(3) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` smallint(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT 'Billy NoName',
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(1024) NOT NULL,
  `user_level` tinyint(4) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`bookmark_id`);

--
-- Indexes for table `masts`
--
ALTER TABLE `masts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mnc_index` (`mnc`),
  ADD KEY `enb_index` (`enodeb_id`);

--
-- Indexes for table `mast_locations`
--
ALTER TABLE `mast_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enb_index` (`enodeb_id`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enodeb_id` (`enodeb_id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `mnc` (`mnc`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `bookmark_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `masts`
--
ALTER TABLE `masts`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mast_locations`
--
ALTER TABLE `mast_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` smallint(6) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
