-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Okt 2026 pada 09.37
-- Versi server: 11.8.1-MariaDB
-- Versi PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_resto`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bahan_baku`
--

CREATE TABLE `bahan_baku` (
  `id` bigint(20) NOT NULL,
  `nama_bahan` varchar(150) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `satuan` varchar(20) NOT NULL,
  `harga_satuan` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bahan_baku`
--

INSERT INTO `bahan_baku` (`id`, `nama_bahan`, `stok`, `satuan`, `harga_satuan`, `created_at`, `updated_at`) VALUES
(1, 'Beras', 100, 'kg', '16500.00', '2026-08-31 02:54:32', '2026-09-25 07:13:09'),
(2, 'Daging Ayam', 10, 'kg', '43000.00', '2026-08-31 02:54:32', '2026-09-22 03:39:01'),
(3, 'Telur Ayam', 100, 'butir', '2000.00', '2026-08-31 02:54:32', '2026-09-22 04:21:46'),
(4, 'Ikan Tongkol', 5000, 'gram', '40.00', '2026-08-31 02:54:32', '2026-09-22 04:34:34'),
(5, 'Tempe', 3000, 'gram', '12.00', '2026-08-31 02:54:32', '2026-09-23 04:07:07'),
(6, 'Tahu', 1000, 'gram', '12.00', '2026-08-31 02:54:32', '2026-09-22 04:36:38'),
(7, 'Kentang', 10, 'kg', '16000.00', '2026-08-31 02:54:32', '2026-09-22 04:04:09'),
(8, 'Terong', 1000, 'gram', '12.00', '2026-08-31 02:54:32', '2026-09-22 04:36:26'),
(9, 'Nangka Muda (Gudeg)', 2000, 'gram', '12.50', '2026-08-31 02:54:32', '2026-09-22 04:36:15'),
(10, 'Mie Basah/Kering', 5000, 'gram', '18.50', '2026-08-31 02:54:32', '2026-09-22 04:35:51'),
(11, 'Bawang Merah', 1000, 'gram', '37.50', '2026-08-31 04:55:22', '2026-09-22 04:30:44'),
(12, 'Bawang Putih', 1000, 'gram', '39.50', '2026-08-31 04:55:22', '2026-09-22 04:31:07'),
(13, 'Cabe Merah Besar', 1000, 'gram', '58.00', '2026-08-31 04:55:22', '2026-09-22 04:31:44'),
(14, 'Cabe Rawit Merah', 1000, 'gram', '95.00', '2026-08-31 04:55:22', '2026-09-22 04:31:57'),
(15, 'Kemiri', 5000, 'gram', '44.00', '2026-08-31 04:55:22', '2026-09-22 04:35:11'),
(16, 'Lengkuas', 2000, 'gram', '12.00', '2026-08-31 04:55:22', '2026-09-22 04:35:19'),
(17, 'Jahe ', 1000, 'gram', '25.00', '2026-08-31 04:55:22', '2026-09-22 04:34:43'),
(18, 'Serai', 3000, 'gram', '12.00', '2026-08-31 04:55:22', '2026-09-22 04:36:43'),
(19, 'Daun Salam', 100, 'gram', '15.00', '2026-08-31 04:55:22', '2026-09-23 07:29:06'),
(20, 'Minyak Goreng', 50, 'liter', '20000.00', '2026-08-31 04:55:22', '2026-09-23 02:51:15'),
(21, 'Garam', 2000, 'gram', '23.00', '2026-08-31 04:55:22', '2026-09-23 04:07:07'),
(22, 'Gula Merah', 1000, 'gram', '25.00', '2026-08-31 04:55:22', '2026-09-22 04:34:22'),
(23, 'Santan Kelapa', 5000, 'gram', '35.00', '2026-08-31 04:55:22', '2026-09-22 04:36:51'),
(24, 'Kecap Manis', 5, 'mililiter', '55.00', '2026-08-31 04:55:22', '2026-09-23 07:08:39'),
(28, 'Mie Bihun', 1000, 'gram', '37.15', '2026-09-22 06:48:06', '2026-09-22 06:48:06'),
(29, 'Mie Soun', 2000, 'gram', '35.00', '2026-09-24 03:14:02', '2026-09-24 03:14:02'),
(30, 'Tomat merah', 2000, 'gram', '10.00', '2026-09-24 04:14:57', '2026-09-24 04:14:57'),
(31, 'Cabai merah keriting', 1000, 'gram', '44.00', '2026-09-24 04:22:42', '2026-09-24 06:43:26'),
(32, 'Gula Pasir', 5000, 'gram', '16.00', '2026-09-24 04:23:47', '2026-09-24 06:44:12'),
(33, 'Kaldu bubuk', 1000, 'gram', '37.00', '2026-09-24 04:24:33', '2026-09-24 06:45:44'),
(34, 'Air Kelapa', 1000, 'ml', '26.00', '2026-09-24 06:57:10', '2026-09-28 06:47:54'),
(35, 'Ketumbar bubuk', 1000, 'gram', '35.00', '2026-09-24 07:07:36', '2026-09-24 07:10:48'),
(37, 'Air asam jawa', 1000, 'ml', '25.00', '2026-09-24 08:15:30', '2026-09-28 06:49:11'),
(38, 'Air putih', 5000, 'ml', '0.00', '2026-09-24 08:18:02', '2026-09-24 08:18:02'),
(39, 'Masako', 500, 'gram', '20000.00', '2026-09-25 07:40:16', '2026-09-25 07:53:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_menu`
--

CREATE TABLE `kategori_menu` (
  `id` bigint(20) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_menu`
--

INSERT INTO `kategori_menu` (`id`, `nama_kategori`, `deskripsi`, `gambar`, `created_at`, `updated_at`) VALUES
(1, 'Nasi Olahan / Paket', 'Menu utama berbasis nasi paket', NULL, '2026-08-31 02:54:31', '2026-08-31 02:54:31'),
(2, 'Lauk & Sayur', 'Pilihan lauk dan sayuran ramesan', NULL, '2026-08-31 02:54:31', '2026-08-31 02:54:31'),
(3, 'Pesanan Khusus', 'Pesanan khusus jumlah besar (Nasbox, Tumpeng, dll)', NULL, '2026-08-31 02:54:31', '2026-08-31 02:54:31'),
(6, 'Minuman', 'Minuman yang segar', NULL, '2026-09-29 07:39:03', '2026-09-29 07:39:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` bigint(20) NOT NULL,
  `kategori_id` bigint(20) NOT NULL,
  `nama_menu` varchar(150) NOT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id`, `kategori_id`, `nama_menu`, `harga`, `deskripsi`, `foto`, `status`, `gambar`, `created_at`, `updated_at`) VALUES
(30, 1, 'Nasi Rames', '10000.00', 'Nasi putih hangat disajikan dengan aneka lauk gurih khas rumahan. Dilengkapi setengah butir telur bacem, tongkol balado kemangi, bihun goreng, soun tumis, dan orek tempe manis gurih. Porsi kenyang dan pas untuk makan siang!', NULL, 'tersedia', '1790047319_6ab1f457075c2.png', '2026-09-22 03:21:59', '2026-10-13 05:18:53'),
(33, 1, 'Nasi Liwet', '8000.00', 'Nasi liwet gurih khas sajian Bu Lastri dengan aroma rempah pilihan.', NULL, 'tersedia', '', '2026-09-24 03:59:37', '2026-10-13 05:18:56'),
(34, 1, 'Nasi Gudeg', '10000.00', 'Nasi putih disajikan dengan gudeg khas yang manis dan gurih.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(35, 1, 'Nasi Kuning', '8000.00', 'Nasi kuning rempah gurih disajikan hangat.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(36, 1, 'Nasi Uduk', '8000.00', 'Nasi uduk santan gurih beraroma harum pandan dan serai.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(37, 1, 'Nasi Tongkol', '10000.00', 'Nasi putih disajikan dengan olahan olahan tongkol gurih khas.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(38, 2, 'Gudeg', '10000.00', 'Olahan nangka muda manis gurih khas sajian rumahan.', NULL, 'habis', '1790234890_6ab4d10aa814f.jpeg', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(39, 2, 'Opor Ayam Suwir', '6000.00', 'Ayam suwir empuk dimasak bumbu opor santan gurih.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(40, 2, 'Tahu Opor', '3000.00', 'Potongan tahu lembut dimasak kuah opor santan gurih.', NULL, 'habis', '1790578854_6aba10a6989fb.webp', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(41, 2, 'Sambel Goreng Tempe', '3000.00', 'Tempe potong dadu yang dimasak tumis bumbu sambal goreng gurih.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(42, 2, 'Kering Tempe', '4000.00', 'Irisan tempe renyah dimasak bumbu manis gurih tahan lama.', NULL, 'habis', '1790239831_6ab4e457affcd.png', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(43, 2, 'Telor Pindang', '4000.00', 'Telur rebus yang dimasak bumbu pindang kecokelatan yang meresap.', NULL, 'habis', '1790578955_6aba110b4ac0e.jpg', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(44, 2, 'Oseng Waluh', '3000.00', 'Tumisan labu siam / waluh iris bumbu gurih lezat.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(45, 2, 'Sambel Goreng Kentang', '4000.00', 'Potongan kentang dadu dimasak dengan balutan bumbu sambal goreng.', NULL, 'habis', '1790579784_6aba14482ddae.jpg', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(46, 2, 'Oseng Ceriwis', '3000.00', 'Tumis sayur ceriwis segar bumbu gurih pedas manis.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(47, 2, 'Oseng Buncis', '3000.00', 'Oseng buncis segar ditumis renyah dengan bumbu rumahan.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(48, 2, 'Oseng Gori', '3000.00', 'Tumisan gori / nangka muda bumbu oseng lezat.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(49, 2, 'Oseng Kangkung', '3000.00', 'Kangkung segar ditumis dengan bumbu pedas gurih nikmat.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(50, 2, 'Oseng Gambas', '3000.00', 'Tumis gambas / oyong segar bumbu gurih bening.', NULL, 'habis', '', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(51, 2, 'Balado Telor', '5000.00', 'Telur rebus digoreng lalu dilumuri bumbu balado cabai merah.', NULL, 'habis', '1790222970_6ab4a27af24be.jpg', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(52, 2, 'Sambel Tongkol', '6000.00', 'Potongan ikan tongkol masak tumis bumbu sambal pedas gurih.', NULL, 'habis', '1790579876_6aba14a4dfbce.webp', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(53, 2, 'Tahu Merah', '3000.00', 'Tahu goreng berbumbu balado merah pedas gurih.', NULL, 'habis', '1790578786_6aba106279694.webp', '2026-09-24 03:59:37', '2026-10-13 05:18:49'),
(54, 6, 'es teh', '4000.00', 'es teh enak', NULL, 'habis', '1790668543_6abb6effcde53.jpg', '2026-09-29 07:55:43', '2026-10-13 05:18:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` int(11) NOT NULL,
  `no_faktur` varchar(50) NOT NULL,
  `tgl_pembelian` date NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `no_faktur`, `tgl_pembelian`, `id_supplier`, `total_harga`, `keterangan`) VALUES
(27, 'TRSUP-20260929105221', '2026-09-29', 1, '1500000.00', 'bahan masakan, dan operasional hari itu\r\n'),
(33, 'TRSUP-20260930151753', '2026-09-30', NULL, '100000.00', 'bahan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_detail`
--

CREATE TABLE `pembelian_detail` (
  `id_detail` int(11) NOT NULL,
  `id_pembelian` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `no_nota` varchar(50) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `tipe_pesanan` enum('Ditempat','Dikirim') NOT NULL,
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kembali` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metode_bayar` varchar(30) NOT NULL DEFAULT 'Tunai',
  `id_pengguna` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `no_nota`, `tanggal`, `tipe_pesanan`, `total_harga`, `bayar`, `kembali`, `metode_bayar`, `id_pengguna`) VALUES
(13, 'NOTA-20260929-0001', '2026-09-29 11:42:00', 'Ditempat', '200000.00', '200000.00', '0.00', 'Tunai', NULL),
(17, 'NOTA-20261013-0001', '2026-10-13 12:22:00', 'Ditempat', '6000.00', '6000.00', '0.00', 'Tunai', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan_detail`
--

CREATE TABLE `penjualan_detail` (
  `id_detail` int(11) NOT NULL,
  `no_nota` varchar(50) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `penjualan_detail`
--

INSERT INTO `penjualan_detail` (`id_detail`, `no_nota`, `id_menu`, `harga`, `jumlah`, `subtotal`) VALUES
(12, 'NOTA-20260929-0001', 38, '10000.00', 20, '200000.00'),
(15, 'NOTA-20261013-0001', 49, '3000.00', 1, '3000.00'),
(16, 'NOTA-20261013-0001', 50, '3000.00', 1, '3000.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `resep_detail`
--

CREATE TABLE `resep_detail` (
  `id` bigint(20) NOT NULL,
  `menu_id` bigint(20) NOT NULL,
  `bahan_baku_id` bigint(20) NOT NULL,
  `jumlah_butuh` decimal(10,2) NOT NULL,
  `satuan` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `no_telp`) VALUES
(1, 'Pasar', 'Pasar', '088899993333');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_user`
--

CREATE TABLE `tbl_user` (
  `username` varchar(13) NOT NULL,
  `sandi` varchar(100) NOT NULL,
  `peran` enum('K','A') NOT NULL,
  `pin` char(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_user`
--

INSERT INTO `tbl_user` (`username`, `sandi`, `peran`, `pin`) VALUES
('admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'A', '111111'),
('kasir', '8691e4fc53b99da544ce86e22acba62d13352eff', 'K', '222222');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bahan_baku`
--
ALTER TABLE `bahan_baku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori_menu`
--
ALTER TABLE `kategori_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menu_kategori` (`kategori_id`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `id_supplier` (`id_supplier`);

--
-- Indeks untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `no_nota` (`no_nota`);

--
-- Indeks untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `no_nota` (`no_nota`);

--
-- Indeks untuk tabel `resep_detail`
--
ALTER TABLE `resep_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_resep_menu` (`menu_id`),
  ADD KEY `fk_resep_bahan` (`bahan_baku_id`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indeks untuk tabel `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bahan_baku`
--
ALTER TABLE `bahan_baku`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `kategori_menu`
--
ALTER TABLE `kategori_menu`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `resep_detail`
--
ALTER TABLE `resep_detail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_menu` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pembelian_detail`
--
ALTER TABLE `pembelian_detail`
  ADD CONSTRAINT `pembelian_detail_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penjualan_detail`
--
ALTER TABLE `penjualan_detail`
  ADD CONSTRAINT `penjualan_detail_ibfk_1` FOREIGN KEY (`no_nota`) REFERENCES `penjualan` (`no_nota`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `resep_detail`
--
ALTER TABLE `resep_detail`
  ADD CONSTRAINT `fk_resep_bahan` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resep_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
