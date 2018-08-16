-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2018 at 10:35 PM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `roledemo`
--

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`version`) VALUES
('20160924162137'),
('20161209132215');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE `permission` (
  `id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`id`, `name`, `description`, `date_created`) VALUES
(1, 'user.manage', 'Manage users', '2018-04-25 16:49:47'),
(2, 'permission.manage', 'Manage permissions', '2018-04-25 16:49:47'),
(3, 'role.manage', 'Manage roles', '2018-04-25 16:49:47'),
(4, 'profile.any.view', 'View anyone\'s profile', '2018-04-25 16:49:47'),
(5, 'profile.own.view', 'View own profile', '2018-04-25 16:49:47'),
(6, 'module.fullaccess', 'Complete access to the application', '2018-04-25 17:04:33'),
(7, 'module.create.documents', 'Create documents related to the module', '2018-04-25 17:11:54'),
(8, 'module.update', 'Update documents in the module associated', '2018-04-25 17:14:32'),
(9, 'module.print.report', 'Permission to print reports', '2018-04-25 17:17:19'),
(10, 'module.export.reporte', 'Permission to export some report generate', '2018-04-25 17:19:14'),
(11, 'module.delete.report', 'Permission with, you can delete a report or something else', '2018-04-25 17:21:53'),
(12, 'module.watch.report.only', 'Users can only see reports, but they won\'t be able to print them, etc.', '2018-04-25 17:30:57'),
(13, 'module.entrylevel', 'View only no more', '2018-05-02 15:42:21'),
(16, 'module.menu', 'access to the menu', '2018-05-14 17:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`, `description`, `date_created`) VALUES
(1, 'Administrator', 'A person who manages users, roles, etc.', '2018-04-25 16:49:47'),
(6, 'Entry Level', 'Basic level, with users can only watch info', '2018-04-26 16:20:59'),
(7, 'Purchasing', 'All about purchasing', '2018-05-03 12:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `role_hierarchy`
--

CREATE TABLE `role_hierarchy` (
  `id` int(11) NOT NULL,
  `parent_role_id` int(11) NOT NULL,
  `child_role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_hierarchy`
--

INSERT INTO `role_hierarchy` (`id`, `parent_role_id`, `child_role_id`) VALUES
(8, 6, 7);

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`id`, `role_id`, `permission_id`) VALUES
(47, 1, 12),
(48, 1, 2),
(49, 1, 4),
(50, 1, 5),
(51, 1, 3),
(52, 1, 1),
(68, 7, 13),
(74, 6, 13),
(75, 6, 16);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `pwd_reset_token` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pwd_reset_token_creation_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `full_name`, `password`, `status`, `date_created`, `pwd_reset_token`, `pwd_reset_token_creation_date`) VALUES
(1, 'admin@example.com', 'Admin', '$2y$10$dGOvudXDWXjQ4Vn8NZUyyeK6oAl9fDJhhujBKgEV9ASFB9caEc7ou', 1, '2018-04-25 16:49:47', NULL, NULL),
(2, 'mojeda@costex.com', 'Michel Ojeda', '$2y$10$xF6rzT0Pv6R2JcI0L0qVNuz.K2MZUBpCt/csXREWAA7lghaFj1u0.', 1, '2018-04-25 16:51:24', NULL, NULL),
(5, 'test@example.com', 'test', '$2y$10$TEQHzjQBZLuRgZCyvlH4ZulSXEmP7fH0r.9ytmmypNU1klPCG6PKy', 1, '2018-04-26 22:24:27', NULL, NULL),
(8, 'testpurchasing@costex.com', 'Test', '$2y$10$65s9SzY4ch3Jcm0HoP4BLuoaNLlE/R3Rn8VJju4AZ/1Gs8H.67d5W', 1, '2018-05-07 16:14:46', NULL, NULL),
(9, 'ale@example.com', 'Alejandro', '$2y$10$fsTSftmrQhpAHU76KM68LeWf.TVrnJBmYtE.GJNoCHXdo0u38KFae', 1, '2018-05-08 18:50:33', NULL, NULL),
(10, 'hugo@example.com', 'Hugo Boss', '$2y$10$boVk6ShczcPRAukRVTn3z.M2QZv0og56GnyCwhN7eHqmpgt9C/zPC', 1, '2018-05-08 19:06:40', NULL, NULL),
(11, 'jose@example.com', 'Jose Mercado', '$2y$10$LuZzvsFKlSyuYyoiOocc5.PUJMUp8TYLEeoduLcGSJ4gXdcYFQpDq', 1, '2018-05-08 19:16:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`id`, `user_id`, `role_id`) VALUES
(1, 1, 1),
(4, 2, 1),
(24, 10, 1),
(32, 8, 7),
(34, 9, 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_idx` (`name`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_idx` (`name`);

--
-- Indexes for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_AB8EFB72A44B56EA` (`parent_role_id`),
  ADD KEY `IDX_AB8EFB72B4B76AB7` (`child_role_id`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6F7DF886D60322AC` (`role_id`),
  ADD KEY `IDX_6F7DF886FED90CCA` (`permission_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_idx` (`email`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_2DE8C6A3A76ED395` (`user_id`),
  ADD KEY `IDX_2DE8C6A3D60322AC` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `role_permission`
--
ALTER TABLE `role_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_role`
--
ALTER TABLE `user_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `role_hierarchy`
--
ALTER TABLE `role_hierarchy`
  ADD CONSTRAINT `role_role_child_role_id_fk` FOREIGN KEY (`child_role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_role_parent_role_id_fk` FOREIGN KEY (`parent_role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_fk` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_role_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
