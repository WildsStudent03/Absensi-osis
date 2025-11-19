-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 19, 2025 at 10:16 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_osis`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `jadwal_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') NOT NULL,
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `jadwal_id`, `tanggal`, `status`, `keterangan`, `created_at`) VALUES
(165, 2, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(166, 3, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(167, 4, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(168, 5, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(169, 6, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(170, 7, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(171, 8, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(172, 9, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(173, 10, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(174, 11, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(175, 12, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(176, 13, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(177, 14, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(178, 15, 8, '2025-11-17', 'Izin', 'ffff', '2025-11-17 02:48:30'),
(179, 16, 8, '2025-11-17', 'Alpha', NULL, '2025-11-17 02:48:30'),
(180, 17, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(181, 18, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(182, 19, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30'),
(183, 20, 8, '2025-11-17', 'Hadir', NULL, '2025-11-17 02:48:30');

-- --------------------------------------------------------

--
-- Table structure for table `datasiswa`
--

CREATE TABLE `datasiswa` (
  `idsiswa` int NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(30) NOT NULL,
  `jurusan` enum('RPL','TBSM','ATPH') NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `jabatan_osis` varchar(50) DEFAULT NULL,
  `status_osis` enum('Aktif','Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `datasiswa`
--

INSERT INTO `datasiswa` (`idsiswa`, `nis`, `nama`, `kelas`, `jurusan`, `no_hp`, `jabatan_osis`, `status_osis`) VALUES
(2, '12313', 'Wilds', 'XII', 'RPL', '02131', '12313', 'Aktif'),
(3, '12313312123131', 'Wil123fas', 'XIIadsf', 'TBSM', '02131', '12313', 'Aktif'),
(4, '11312312313', 'biiISD', 'xii rpl 3', 'RPL', '93919319939', 'bendahara', 'Aktif'),
(5, '12313213', 'sunan lutfi', 'XII', 'TBSM', '02131', 'bendahara', 'Aktif'),
(6, '23001', 'Wilds Jrrr', 'XII', 'RPL', '081234567001', 'Ketua OSIS', 'Aktif'),
(7, '23002', 'Rizky Ananda', 'XI', 'RPL', '081234567002', 'Wakil Ketua', 'Aktif'),
(8, '23003', 'Salsabila Putri', 'XI', 'TBSM', '081234567003', 'Sekretaris 1', 'Aktif'),
(9, '23004', 'Ilham Saputra', 'XII', 'ATPH', '081234567004', 'Sekretaris 2', 'Aktif'),
(10, '23005', 'Nadya Fitriani', 'XI', 'RPL', '081234567005', 'Bendahara 1', 'Aktif'),
(11, '23006', 'Reza Alamsyah', 'X', 'TBSM', '081234567006', 'Bendahara 2', 'Aktif'),
(12, '23007', 'Maya Putri', 'X', 'RPL', '081234567007', 'Koordinator Acara', 'Aktif'),
(13, '23008', 'Fadhil Nur', 'XI', 'ATPH', '081234567008', 'Koordinator Humas', 'Aktif'),
(14, '23009', 'Rani Wulandari', 'XII', 'RPL', '081234567009', 'Anggota', 'Aktif'),
(15, '23010', 'Bagus Firmansyah', 'XI', 'TBSM', '081234567010', 'Anggota', 'Aktif'),
(16, '23011', 'Cindy Amelia', 'X', 'RPL', '081234567011', 'Anggota', 'Aktif'),
(17, '23012', 'Rafi Ahmad', 'XI', 'ATPH', '081234567012', 'Anggota', 'Aktif'),
(18, '23013', 'Devi Lestari', 'XI', 'RPL', '081234567013', 'Anggota', 'Aktif'),
(19, '23014', 'Yoga Pratama', 'X', 'TBSM', '081234567014', 'Anggota', 'Aktif'),
(20, '23015', 'Naila Rahma', 'XII', 'ATPH', '081234567015', 'Anggota', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_kegiatan`
--

CREATE TABLE `jadwal_kegiatan` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `deskripsi` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal_kegiatan`
--

INSERT INTO `jadwal_kegiatan` (`id`, `judul`, `tanggal`, `waktu`, `lokasi`, `deskripsi`, `created_at`, `updated_at`) VALUES
(8, 'RAPAT penting', '2025-11-17', '12:12:00', 'lapangan', NULL, '2025-10-13 01:34:21', '2025-11-17 02:39:31'),
(10, 'rapat OSIS', '2025-11-13', '07:00:00', 'KANTOR', NULL, '2025-11-13 06:49:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `role`, `foto`, `created_at`) VALUES
(4, 'Ilham', 'admin@gmail.com', '$2y$10$qU3E6mjxainVcl2Z3i7f0eyMoOFcFzc3bfBte9vqHTmubRoLCHJeC', 'admin', NULL, '2025-09-30 20:53:47'),
(11, 'sunan', 'user@gmail.com', '$2y$10$plbIIu8zVNle9UF16mQFQORi4pkUifLNcqFvgWoHNFyzD0FsKlYtC', 'admin', NULL, '2025-10-20 04:39:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indexes for table `datasiswa`
--
ALTER TABLE `datasiswa`
  ADD PRIMARY KEY (`idsiswa`);

--
-- Indexes for table `jadwal_kegiatan`
--
ALTER TABLE `jadwal_kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT for table `datasiswa`
--
ALTER TABLE `datasiswa`
  MODIFY `idsiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `jadwal_kegiatan`
--
ALTER TABLE `jadwal_kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `datasiswa` (`idsiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_kegiatan` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
