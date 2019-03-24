SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lte_cell_export`
--

-- --------------------------------------------------------

CREATE TABLE `sectors` (
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


CREATE TABLE `masts` (
  `mast_id` int(15) NOT NULL,
  `mnc` int(3) NOT NULL,
  `enodeb_id` int(10) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `updated` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `masts`
  ADD PRIMARY KEY (`mast_id`),
  ADD KEY `mnc_index` (`mnc`),
  ADD KEY `enb_index` (`enodeb_id`);


ALTER TABLE `masts`
  MODIFY `mast_id` int(15) NOT NULL AUTO_INCREMENT;

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`cell_id`),
  ADD KEY `enodeb_id` (`enodeb_id`),
  ADD KEY `sector_id` (`sector_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
