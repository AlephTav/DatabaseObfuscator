SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

DROP TABLE IF EXISTS `table_without_key`;
CREATE TABLE `table_without_key` (
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `table_without_key` (`name`, `email`, `text`) VALUES
('Name 1', 'email1@domain.com', 'Text 1'),
('Name 2', 'email2@domain.com', 'Text 2'),
('Name 3', 'email3@domain.com', 'Text 3');

DROP TABLE IF EXISTS `table_with_key`;
CREATE TABLE `table_with_key` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `table_with_key` (`id`, `name`, `email`, `text`) VALUES
(1, 'Name 1', 'email1@domain.com', 'Text 1'),
(2, 'Name 2', 'email2@domain.com', 'Text 2'),
(3, 'Name 3', 'email3@domain.com', 'Text 3');

ALTER TABLE `table_with_key`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `table_with_key`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;