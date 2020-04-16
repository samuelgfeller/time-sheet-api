-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.11-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for time-sheet
CREATE DATABASE IF NOT EXISTS `time-sheet` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `time-sheet`;

-- Dumping structure for table time-sheet.time_sheet
CREATE TABLE IF NOT EXISTS `time_sheet` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
                                            `user_id` int(11) NOT NULL,
                                            `start` datetime DEFAULT current_timestamp(),
                                            `stop` datetime DEFAULT NULL,
                                            `activity` varchar(400) DEFAULT NULL,
                                            `deleted_at` datetime DEFAULT NULL,
                                            PRIMARY KEY (`id`),
                                            KEY `fk_time_sheet_user_idx` (`user_id`),
                                            CONSTRAINT `fk_time_sheet_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

-- Dumping data for table time-sheet.time_sheet: ~13 rows (approximately)
/*!40000 ALTER TABLE `time_sheet` DISABLE KEYS */;
INSERT INTO `time_sheet` (`id`, `user_id`, `start`, `stop`, `activity`, `deleted_at`) VALUES
(55, 1, '2020-04-09 12:26:19', '2020-04-09 14:22:22', NULL, NULL),
(56, 1, '2020-04-09 14:23:54', '2020-04-09 14:24:13', 'asdf', NULL),
(57, 1, '2020-04-09 14:24:48', '2020-04-09 14:24:54', 'ttee', NULL),
(58, 1, '2020-04-09 14:27:21', '2020-04-09 14:29:58', 'test', NULL),
(59, 1, '2020-04-09 14:30:04', '2020-04-09 14:30:30', 'Hey B', NULL),
(61, 1, '2020-04-09 14:57:22', '2020-04-09 15:04:48', '', NULL),
(62, 1, '2020-04-09 15:06:03', '2020-04-09 15:06:05', '', NULL),
(63, 1, '2020-04-09 15:07:17', '2020-04-09 15:14:28', 'Test activity', NULL),
(64, 1, '2020-04-09 15:44:03', '2020-04-09 15:44:53', 'gfdsagfdsgfd', NULL),
(65, 1, '2020-04-09 15:45:09', '2020-04-14 11:28:13', '<script>alert(\'hacked\')</script>', NULL),
(66, 3, '2020-04-09 17:08:41', '2020-04-09 17:08:45', 'rudi zeit test', NULL),
(68, 1, '2020-04-15 15:21:15', '2020-04-15 15:22:14', 'tEST', NULL),
(69, 1, '2020-04-15 15:22:31', '2020-04-16 08:47:58', 'sasdf', NULL),
(70, 1, '2020-04-16 08:48:12', '2020-04-16 08:49:40', 'test', NULL),
(71, 1, '2020-04-16 08:49:40', '2020-04-16 08:49:43', '', NULL),
(72, 1, '2020-04-16 10:57:50', '2020-04-16 11:55:47', '<body>\n<div class="wrapper">\n    <div id="header">\n        <h1>Zeiterfassungstool</h1>\n\n        <button id="registerNavBtn">Benutzer erstellen</button>\n        <button id="allTimeSheetNavBtn">Alle erfassten Zeiten</button>\n        <button id="loginNavBtn">Login</button>\n        <button id="trackTimeNavBtn">Zeit erfassen</button>\n    </div>\n\n    <div id="pageContent">\n        <!--    Display all us', NULL);
/*!40000 ALTER TABLE `time_sheet` ENABLE KEYS */;

-- Dumping structure for table time-sheet.user
CREATE TABLE IF NOT EXISTS `user` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `name` varchar(200) NOT NULL,
                                      `email` varchar(254) NOT NULL,
                                      `password` varchar(300) NOT NULL,
                                      `role` varchar(50) NOT NULL DEFAULT 'user',
                                      `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                                      `created_at` datetime DEFAULT current_timestamp(),
                                      `deleted_at` datetime DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Dumping data for table time-sheet.user: ~3 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `name`, `email`, `password`, `role`, `updated_at`, `created_at`, `deleted_at`) VALUES
(1, 'Samuel Gfeller', 'samuelgfeller@bluewin.ch', '$2y$10$jfSGQtEQwVtvJACgHG8aXOU6Pjpv5ogfw2.3Lsd9CB32bgAPbCmGu', 'admin', '2020-04-09 17:07:21', '2020-04-07 10:34:12', NULL),
(2, 'Admin', 'samuelgfeller143@gmail.com', '$2y$10$0E7XpPcOAc8y8h.IwGB4ZOY0ZXrpkk5vyUPLafOdhxaXGIgIf1WOC', 'admin', '2020-04-09 17:09:42', '2020-04-09 17:05:07', NULL),
(3, 'Rudolf', 'hu.rudoladsfadsff@bluewin.ch', '$2y$10$G9MIjCaoT0u.aE1ZWiXAg.ngYPyPsXpnoHeB8tt2PT2fyPBaYNvHa', 'user', '2020-04-09 17:08:22', '2020-04-09 17:08:22', NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
