-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Des 2025 pada 05.32
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petresq`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `adoption_applications`
--

CREATE TABLE `adoption_applications` (
  `id` int(11) NOT NULL,
  `applicant_user_id` int(11) NOT NULL,
  `assigned_admin_user_id` int(11) NOT NULL DEFAULT 1,
  `hewan_id` int(11) DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `has_garden` tinyint(1) NOT NULL DEFAULT 0,
  `living_situation` varchar(200) DEFAULT NULL,
  `story` text DEFAULT NULL,
  `details_json` text DEFAULT NULL,
  `home_photos_json` text DEFAULT NULL,
  `status` enum('submitted','in_review','approved','rejected','withdrawn') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `adoption_applications`
--

INSERT INTO `adoption_applications` (`id`, `applicant_user_id`, `assigned_admin_user_id`, `hewan_id`, `full_name`, `email`, `phone`, `address_line1`, `postcode`, `has_garden`, `living_situation`, `story`, `details_json`, `home_photos_json`, `status`, `submitted_at`, `updated_at`) VALUES
(1, 6, 1, 1, 'alfareza', 'alfareza@gmail.com', '456', 'purwakarta', '1000', 0, 'bagus', 'adwadwa', '{\"telephone\":\"456\",\"household_setting\":\"jelek\",\"household_activity\":\"b aja\",\"adults\":1,\"children\":1,\"children_ages\":\"13-17\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"no\",\"other_animals\":\"Yes\",\"vaccinated\":\"No\",\"experience\":\"adwadwa\"}', NULL, 'approved', '2025-12-12 10:07:17', '2025-12-12 10:32:20'),
(4, 4, 1, 1, 'wawa', 'wawaks@gmail.com', '12345678', 'cibiru', '40625', 1, 'gacor', 'sdyuio', '{\"telephone\":\"12345678\",\"household_setting\":\"gacor\",\"household_activity\":\"gacor\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"Yes\",\"visiting_ages\":\"under5\",\"flatmates\":\"Yes\",\"flatmates_consent\":\"Yes\",\"allergies\":\"yes\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"sdyuio\"}', NULL, 'approved', '2025-12-12 19:41:14', '2025-12-12 19:42:33'),
(5, 7, 1, 4, 'rijal', 'rijal@gmail.com', '125632', 'a', '13457', 1, 'a', 'a', '{\"telephone\":\"125632\",\"household_setting\":\"a\",\"household_activity\":\"a\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"Yes\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"1\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"a\"}', '[]', 'rejected', '2025-12-13 20:36:25', '2025-12-13 21:42:33'),
(6, 7, 1, 4, 'rijal', 'rijal@gmail.com', '324542', 'x', '123456', 0, 'a', 'a', '{\"telephone\":\"324542\",\"household_setting\":\"aa\",\"household_activity\":\"aa\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"1\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"a\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765633733_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765633733_1.jpeg\"]', 'approved', '2025-12-13 20:48:53', '2025-12-13 21:42:33'),
(7, 7, 1, 3, 'rijal', 'rijal@gmail.com', '2345678', 'z', '13456', 0, 'a', 'a', '{\"telephone\":\"2345678\",\"household_setting\":\"a\",\"household_activity\":\"aa\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"a\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"a\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765634570_0.png\",\"uploads\\/adoption\\/home_photos\\/home_7_1765634570_1.png\"]', 'approved', '2025-12-13 21:02:50', '2025-12-13 21:15:54'),
(8, 7, 1, 6, 'rijal', 'rijal@gmail.com', '12345678', 'w', '12345', 1, 'q', 'q', '{\"telephone\":\"12345678\",\"household_setting\":\"q\",\"household_activity\":\"q\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"1\",\"other_animals\":\"No\",\"vaccinated\":\"Yes\",\"experience\":\"q\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765637920_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765637920_1.jpeg\"]', 'approved', '2025-12-13 21:58:40', '2025-12-13 21:58:52'),
(9, 7, 1, 5, 'rijal', 'rijal@gmail.com', '345678946', 'z', '1234567', 0, 'c', 'c', '{\"telephone\":\"345678946\",\"household_setting\":\"c\",\"household_activity\":\"c\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"2\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"c\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765638395_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765638395_1.jpeg\"]', 'approved', '2025-12-13 22:06:35', '2025-12-13 22:11:39'),
(10, 7, 1, 7, 'rijal', 'rijal@gmail.com', '324542', 'e', '123456', 1, 'e', 'w', '{\"telephone\":\"324542\",\"household_setting\":\"e\",\"household_activity\":\"e\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"2\",\"other_animals\":\"No\",\"vaccinated\":\"Yes\",\"experience\":\"w\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765638854_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765638854_1.jpeg\"]', 'approved', '2025-12-13 22:14:14', '2025-12-13 22:14:24'),
(11, 7, 1, 7, 'rijal', 'rijal@gmail.com', '525325', 'd', '123456', 0, 'e', 'w', '{\"telephone\":\"525325\",\"household_setting\":\"e\",\"household_activity\":\"e\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"2\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"w\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765639651_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765639651_1.jpeg\"]', 'approved', '2025-12-13 22:27:31', '2025-12-13 22:27:37'),
(12, 7, 1, 7, 'rijal', 'rijal@gmail.com', '125632', 'a', '13457', 0, 'aa', 'a', '{\"telephone\":\"125632\",\"household_setting\":\"aa\",\"household_activity\":\"a\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"1\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"a\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765639774_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765639774_1.jpeg\"]', 'approved', '2025-12-13 22:29:34', '2025-12-13 22:29:50'),
(13, 7, 1, 7, 'rijal', 'rijal@gmail.com', '3456789', 'r', '234567', 0, 'r', 'r', '{\"telephone\":\"3456789\",\"household_setting\":\"r\",\"household_activity\":\"r\",\"adults\":1,\"children\":0,\"children_ages\":\"no-children\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"2\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"r\"}', '[\"uploads\\/adoption\\/home_photos\\/home_7_1765639971_0.jpeg\",\"uploads\\/adoption\\/home_photos\\/home_7_1765639971_1.jpeg\"]', 'approved', '2025-12-13 22:32:51', '2025-12-13 22:33:09'),
(14, 1, 1, 4, 'bayou', 'bayouali@gmail.com', '0898', 'gegerkalong', '140625', 1, 'gacor', 'its good big house', '{\"telephone\":\"0898\",\"household_setting\":\"jelek\",\"household_activity\":\"rame 5 orang dirumah\",\"adults\":2,\"children\":3,\"children_ages\":\"6-12\",\"visiting_children\":\"No\",\"visiting_ages\":\"under5\",\"flatmates\":\"No\",\"flatmates_consent\":\"Yes\",\"allergies\":\"nope\",\"other_animals\":\"No\",\"vaccinated\":\"No\",\"experience\":\"its good big house\"}', '[\"uploads\\/adoption\\/home_photos\\/home_1_1765680165_0.png\",\"uploads\\/adoption\\/home_photos\\/home_1_1765680165_1.png\"]', 'approved', '2025-12-14 09:42:45', '2025-12-14 09:43:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `hewan`
--

CREATE TABLE `hewan` (
  `id_hewan` int(10) NOT NULL,
  `jenis` enum('Dog','Cat','Rabbit','') NOT NULL,
  `namaHewan` varchar(50) NOT NULL,
  `breed` varchar(50) NOT NULL,
  `gender` enum('Male','Female','','') NOT NULL,
  `age` varchar(20) NOT NULL,
  `color` varchar(20) NOT NULL,
  `weight` varchar(20) NOT NULL,
  `height` varchar(20) NOT NULL,
  `main_photo` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Available','Adopted','Pending','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `hewan`
--

INSERT INTO `hewan` (`id_hewan`, `jenis`, `namaHewan`, `breed`, `gender`, `age`, `color`, `weight`, `height`, `main_photo`, `description`, `status`) VALUES
(1, 'Dog', 'Iqbal', 'British Shorthair', 'Male', '2 years', 'golden', '5', '10', 'uploads/iqbal.jpg\r\n', 'iqbal adalah kucing baik', 'Available'),
(2, 'Cat', 'gifari', 'Beagle', 'Male', '2 Tahun', 'Black', '5', '30', '', 'gifari adalah kucing jahat', 'Available'),
(3, 'Cat', 'tiffany', 'Havana', 'Male', '3 years', 'White', '5', '25', 'uploads/rehome/pet_6_1765533187.jpg', 'dia anjing baik', 'Available'),
(4, 'Cat', 'ucup', 'persia', 'Male', '1 years', 'belang', '0', '20', 'uploads/rehome/pet_7_1765614559.png', 'ok', 'Adopted'),
(5, 'Rabbit', 'a', 'persia', 'Male', '1 years', 'belang', '2', '20', 'uploads/rehome/pet_7_1765634289.png', 'ok', 'Available'),
(6, 'Rabbit', 'z', 'persia', 'Male', '1 years', 'belang', '1', '12', 'uploads/rehome/pet_7_1765637854.png', 'a', 'Adopted'),
(7, 'Cat', 'c', 'persia', 'Female', '1 years', 'belang', '1', '1', 'uploads/rehome/pet_7_1765638793.png', 's', 'Adopted');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_user_id`, `application_id`, `message`, `is_read`, `created_at`) VALUES
(5, 1, 1, 'New adoption application from alfareza', 0, '2025-12-12 10:07:17'),
(9, 1, NULL, 'New rehome submission from bayou', 0, '2025-12-12 16:46:56'),
(10, 1, NULL, 'New rehome submission from tiffany', 0, '2025-12-12 16:53:07'),
(13, 1, 4, 'New adoption application from wawa', 0, '2025-12-12 19:41:14'),
(15, 1, NULL, 'New rehome submission from kucing', 0, '2025-12-13 14:47:51'),
(16, 7, NULL, 'Your rehome submission #3 has been approved!', 0, '2025-12-13 14:49:33'),
(17, 6, NULL, 'Your rehome submission #2 has been approved!', 0, '2025-12-13 15:16:38'),
(18, 6, NULL, 'Your rehome submission #1 has been approved!', 0, '2025-12-13 15:20:41'),
(19, 1, NULL, 'New rehome submission from ucup', 0, '2025-12-13 15:29:19'),
(20, 7, NULL, 'Your rehome submission #4 has been approved!', 0, '2025-12-13 15:29:50'),
(21, 1, 5, 'New adoption application from rijal', 0, '2025-12-13 20:36:25'),
(22, 7, 5, 'New adoption application from rijal', 0, '2025-12-13 20:36:25'),
(23, 1, 6, 'New adoption application from rijal', 0, '2025-12-13 20:48:53'),
(24, 7, 6, 'New adoption application from rijal', 0, '2025-12-13 20:48:53'),
(25, 1, NULL, 'New rehome submission from a', 0, '2025-12-13 20:58:09'),
(26, 1, 7, 'New adoption application from rijal', 0, '2025-12-13 21:02:50'),
(27, 7, 7, 'New adoption application from rijal', 0, '2025-12-13 21:02:50'),
(28, 1, NULL, 'New rehome submission from kucing', 0, '2025-12-13 21:14:02'),
(29, 7, NULL, 'Your rehome submission #5 has been approved!', 0, '2025-12-13 21:23:45'),
(30, 1, NULL, 'New rehome submission from z', 0, '2025-12-13 21:57:34'),
(31, 7, NULL, 'Your rehome submission #7 has been approved!', 0, '2025-12-13 21:57:47'),
(32, 1, 8, 'New adoption application from rijal', 0, '2025-12-13 21:58:40'),
(33, 7, 8, 'New adoption application from rijal', 0, '2025-12-13 21:58:40'),
(34, 1, 9, 'New adoption application from rijal', 0, '2025-12-13 22:06:35'),
(35, 7, 9, 'New adoption application from rijal', 0, '2025-12-13 22:06:35'),
(36, 1, NULL, 'New rehome submission from c', 0, '2025-12-13 22:13:13'),
(37, 7, NULL, 'Your rehome submission #8 has been approved!', 0, '2025-12-13 22:13:25'),
(38, 1, 10, 'New adoption application from rijal', 0, '2025-12-13 22:14:14'),
(39, 7, 10, 'New adoption application from rijal', 0, '2025-12-13 22:14:14'),
(40, 1, 11, 'New adoption application from rijal', 0, '2025-12-13 22:27:31'),
(41, 7, 11, 'New adoption application from rijal', 0, '2025-12-13 22:27:31'),
(42, 1, 12, 'New adoption application from rijal', 0, '2025-12-13 22:29:34'),
(43, 7, 12, 'New adoption application from rijal', 0, '2025-12-13 22:29:34'),
(44, 1, 13, 'New adoption application from rijal', 0, '2025-12-13 22:32:51'),
(45, 7, 13, 'New adoption application from rijal', 0, '2025-12-13 22:32:51'),
(46, 7, NULL, 'Your rehome submission #8 has been approved!', 0, '2025-12-13 23:55:07'),
(47, 7, NULL, 'Your rehome submission #8 has been approved!', 0, '2025-12-14 09:40:47'),
(48, 1, 14, 'New adoption application from bayou', 0, '2025-12-14 09:42:45'),
(49, 7, 14, 'New adoption application from bayou', 0, '2025-12-14 09:42:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rehome_submissions`
--

CREATE TABLE `rehome_submissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_admin_user_id` int(11) NOT NULL DEFAULT 1,
  `pet_name` varchar(120) NOT NULL,
  `pet_type` enum('Dog','Cat','Rabbit') NOT NULL,
  `age_years` int(11) NOT NULL DEFAULT 0,
  `breed` varchar(200) NOT NULL,
  `color` varchar(100) NOT NULL,
  `weight` int(10) NOT NULL,
  `height` int(10) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postcode` varchar(20) NOT NULL,
  `spayed_neutered` enum('Yes','No') NOT NULL,
  `rehome_reason` varchar(200) NOT NULL,
  `pet_story` longtext NOT NULL,
  `pet_image_path` varchar(255) DEFAULT NULL,
  `documents_json` text DEFAULT NULL,
  `status` enum('submitted','in_review','approved','rejected','withdrawn') NOT NULL DEFAULT 'submitted',
  `admin_notes` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rehome_submissions`
--

INSERT INTO `rehome_submissions` (`id`, `user_id`, `assigned_admin_user_id`, `pet_name`, `pet_type`, `age_years`, `breed`, `color`, `weight`, `height`, `gender`, `address_line1`, `city`, `postcode`, `spayed_neutered`, `rehome_reason`, `pet_story`, `pet_image_path`, `documents_json`, `status`, `admin_notes`, `submitted_at`, `updated_at`) VALUES
(1, 6, 1, 'bayou', 'Cat', 1, 'Golden Retriever', 'Black', 2, 20, 'Female', 'Posindo', 'Jakarta', '40624', 'Yes', 'Moving', 'nothing\r\n', NULL, NULL, 'approved', NULL, '2025-12-12 16:46:56', '2025-12-13 15:20:41'),
(2, 6, 1, 'tiffany', 'Cat', 3, 'Havana', 'White', 5, 25, 'Male', 'Griya', 'Purwakarta', '6006', 'Yes', 'Financial', 'dia anjing baik', 'uploads/rehome/pet_6_1765533187.jpg', NULL, 'approved', '\nAnimal created with ID: 3', '2025-12-12 16:53:07', '2025-12-13 15:16:38'),
(3, 7, 1, 'kucing', 'Cat', 1, 'persia', 'belang', 1, 20, 'Male', 'cibiru', 'bandung', '144600', 'No', 'Moving', '21', 'uploads/rehome/pet_7_1765612071.png', NULL, 'approved', NULL, '2025-12-13 14:47:51', '2025-12-13 14:49:33'),
(4, 7, 1, 'ucup', 'Cat', 1, 'persia', 'belang', 0, 20, 'Male', 'cibiru', 'bandung', '144600', 'No', 'Moving', 'ok', 'uploads/rehome/pet_7_1765614559.png', NULL, 'approved', '\nAnimal created with ID: 4', '2025-12-13 15:29:19', '2025-12-13 15:29:50'),
(5, 7, 1, 'a', 'Rabbit', 1, 'persia', 'belang', 2, 20, 'Male', 'cibiru', 'bandung', '144600', 'No', 'Moving', 'ok', 'uploads/rehome/pet_7_1765634289.png', NULL, 'approved', '\nAnimal created with ID: 5', '2025-12-13 20:58:09', '2025-12-13 21:23:45'),
(6, 7, 1, 'kucing', 'Rabbit', 1, 'persia', 'belang', 1, 1, 'Male', 'cibiru', 'bandung', '144600', 'No', 'Moving', 'q', 'uploads/rehome/pet_7_1765635242.png', NULL, 'submitted', NULL, '2025-12-13 21:14:02', '2025-12-13 21:14:02'),
(7, 7, 1, 'z', 'Rabbit', 1, 'persia', 'belang', 1, 12, 'Male', 'cibiru', 'bandung', '144600', 'No', 'Allergy', 'a', 'uploads/rehome/pet_7_1765637854.png', NULL, 'approved', '\nAnimal created with ID: 6', '2025-12-13 21:57:34', '2025-12-13 21:57:47'),
(8, 7, 1, 'c', 'Cat', 1, 'persia', 'belang', 1, 1, 'Female', 'cibiru', 'bandung', '144600', 'Yes', 'Moving', 's', 'uploads/rehome/pet_7_1765638793.png', NULL, 'approved', NULL, '2025-12-13 22:13:13', '2025-12-14 09:40:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_type` enum('Daily','Weekly','Monthly','Notes') NOT NULL DEFAULT 'Daily',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','done','failed') NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reports`
--

INSERT INTO `reports` (`id`, `report_type`, `start_date`, `end_date`, `description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Daily', '2025-12-13', '2025-12-13', '2 laporan', 'done', 7, '2025-12-13 15:04:44', '2025-12-13 19:45:09'),
(2, 'Daily', '2025-12-14', '2025-12-14', 'cek progres', 'pending', 7, '2025-12-13 18:55:31', '2025-12-14 09:40:21'),
(3, 'Daily', '2025-12-14', '2025-12-14', 'cek progres', 'done', 7, '2025-12-13 18:55:31', '2025-12-13 18:55:31'),
(4, 'Daily', '2025-12-15', '2025-12-15', 'cek progres\n', 'done', 7, '2025-12-13 19:08:29', '2025-12-13 19:08:29'),
(5, 'Daily', '2025-12-16', '2025-12-18', 'cek progres', 'done', 7, '2025-12-13 19:08:50', '2025-12-13 19:08:50'),
(6, 'Daily', '2025-12-20', '2025-12-20', 'tes', 'done', 7, '2025-12-13 19:44:47', '2025-12-13 19:45:01'),
(7, 'Notes', '2025-12-13', '2025-12-13', 'notes', 'done', 7, '2025-12-13 20:01:27', '2025-12-13 20:01:44'),
(8, 'Notes', '2025-12-14', '2025-12-14', 'notes', 'pending', 7, '2025-12-13 20:55:31', '2025-12-13 20:55:31'),
(9, 'Daily', '2025-12-22', '2025-12-24', 'progres', 'pending', 7, '2025-12-13 20:55:50', '2025-12-13 20:55:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(10) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `password`, `role`) VALUES
(1, 'bayou', 'bayouali@gmail.com', 'bayou123', 'admin'),
(4, 'wawa', 'wawaks@gmail.com', 'wawa123', 'user'),
(6, 'alfareza', 'alfareza@gmail.com', '$2y$10$60usUG6u9vOspyznh6fnv.blu3PlU6IHSjxWqDUxLjqAzvf4/E642', 'user'),
(7, 'rijal', 'rijal@gmail.com', '$2y$10$uKGjtLnQZLqIj309nowvcOYXpU0luVQUxQwpdPL2PrbdlEKBu0T/C', 'admin'),
(8, 'Nafian', 'rifkinafian@upi.edu', '$2y$10$UY1VNIaJ08Tb0e7ZvEF6ouGWzA.JyrDymnOIShU/z.o7TR59tBZCG', 'admin'),
(9, 'ghifri al ghifari', 'gifri@gmail.com', '$2y$10$GCERpIJxcRd8ApUbMS8N4.D4mVzTkWCU8vMUukcEEikt8.rUOPIS.', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_adoption_applications_user` (`applicant_user_id`),
  ADD KEY `idx_adoption_applications_admin` (`assigned_admin_user_id`),
  ADD KEY `idx_adoption_applications_hewan` (`hewan_id`);

--
-- Indeks untuk tabel `hewan`
--
ALTER TABLE `hewan`
  ADD PRIMARY KEY (`id_hewan`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_recipient` (`recipient_user_id`,`is_read`),
  ADD KEY `idx_notifications_application` (`application_id`);

--
-- Indeks untuk tabel `rehome_submissions`
--
ALTER TABLE `rehome_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rehome_submissions_user` (`user_id`),
  ADD KEY `idx_rehome_submissions_admin` (`assigned_admin_user_id`),
  ADD KEY `idx_rehome_submissions_status` (`status`),
  ADD KEY `idx_rehome_submissions_created` (`submitted_at`),
  ADD KEY `idx_rehome_submissions_pet_type` (`pet_type`);

--
-- Indeks untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `start_date` (`start_date`),
  ADD KEY `end_date` (`end_date`),
  ADD KEY `status` (`status`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `hewan`
--
ALTER TABLE `hewan`
  MODIFY `id_hewan` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `rehome_submissions`
--
ALTER TABLE `rehome_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD CONSTRAINT `fk_adoption_applications_admin` FOREIGN KEY (`assigned_admin_user_id`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_adoption_applications_hewan` FOREIGN KEY (`hewan_id`) REFERENCES `hewan` (`id_hewan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_adoption_applications_user` FOREIGN KEY (`applicant_user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_application` FOREIGN KEY (`application_id`) REFERENCES `adoption_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`recipient_user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `rehome_submissions`
--
ALTER TABLE `rehome_submissions`
  ADD CONSTRAINT `fk_rehome_submissions_admin` FOREIGN KEY (`assigned_admin_user_id`) REFERENCES `user` (`id_user`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rehome_submissions_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
