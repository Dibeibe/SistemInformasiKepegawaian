-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Waktu pembuatan: 24 Des 2025 pada 15.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_kepegawaian`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `pegawai_id`, `tanggal`, `jam_masuk`, `jam_keluar`, `status`) VALUES
(1, 6, '2025-10-22', '07:45:38', '07:45:59', 'hadir');

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aksi` varchar(255) NOT NULL,
  `tabel` varchar(100) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cuti`
--

CREATE TABLE `cuti` (
  `id` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `disetujui_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cuti`
--

INSERT INTO `cuti` (`id`, `pegawai_id`, `tanggal_pengajuan`, `tanggal_mulai`, `tanggal_selesai`, `alasan`, `status`, `disetujui_oleh`) VALUES
(1, 6, '0000-00-00', '2025-10-31', '2025-11-02', 'saya mau liburan', '', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `gaji`
--

CREATE TABLE `gaji` (
  `id` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `bulan` varchar(20) NOT NULL,
  `tahun` int(11) NOT NULL,
  `gaji_pokok` decimal(12,2) NOT NULL,
  `potongan` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatan`
--

CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL,
  `nama_jabatan` varchar(100) NOT NULL,
  `gaji_pokok` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jabatan`
--

INSERT INTO `jabatan` (`id`, `nama_jabatan`, `gaji_pokok`) VALUES
(1, 'Bos', 0.00),
(2, 'CEO', 0.00),
(3, 'STAFF', 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pegawai`
--

INSERT INTO `pegawai` (`id`, `user_id`, `nip`, `nama`, `jabatan_id`, `unit_id`, `alamat`, `telp`, `email`, `foto`, `tanggal_masuk`) VALUES
(4, 1, '1234455', 'rino', 3, 1, 'jl.prg', '089765444', 'rino@mail.com', '1761110516_2ee7ee30ee5c2798212973846d7bc5a1.jpg', '2025-10-24'),
(6, 3, '142362642624', 'anton', 2, 2, 'sawangan', '08327462746274', 'anton@gmail.comn', '1761111894_2ee7ee30ee5c2798212973846d7bc5a1.jpg', '2025-10-16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit`
--

CREATE TABLE `unit` (
  `id` int(11) NOT NULL,
  `nama_unit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit`
--

INSERT INTO `unit` (`id`, `nama_unit`) VALUES
(1, 'Office Boy'),
(2, 'Office Girl'),
(3, 'Security');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin1', 'admin123', 'admin', '2025-09-29 08:58:11'),
(2, 'pegawai1', 'pegawai123', 'pegawai', '2025-09-29 08:58:45'),
(3, 'anton', 'anton123', 'pegawai', '2025-10-22 05:44:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pegawai_id` (`pegawai_id`);

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cuti`
--
ALTER TABLE `cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pegawai_id` (`pegawai_id`),
  ADD KEY `disetujui_oleh` (`disetujui_oleh`);

--
-- Indeks untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pegawai_id` (`pegawai_id`);

--
-- Indeks untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `jabatan_id` (`jabatan_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indeks untuk tabel `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cuti`
--
ALTER TABLE `cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jabatan`
--
ALTER TABLE `jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `cuti`
--
ALTER TABLE `cuti`
  ADD CONSTRAINT `cuti_ibfk_1` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cuti_ibfk_2` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD CONSTRAINT `gaji_ibfk_1` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pegawai`
--
ALTER TABLE `pegawai`
  ADD CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pegawai_ibfk_2` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatan` (`id`),
  ADD CONSTRAINT `pegawai_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
