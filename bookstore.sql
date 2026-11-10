-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Nov 2026 pada 07.58
-- Versi server: 10.11.16-MariaDB
-- Versi PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `bookstore`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cover` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `books`
--

INSERT INTO `books` (`id`, `category_id`, `title`, `author`, `description`, `price`, `stock`, `cover`, `created_at`) VALUES
(7, 1, 'Crows', 'Hiroshi Takahashi', 'Crows (クローズ) adalah sebuah seri manga karya Hiroshi Takahashi yang berlatar belakang tentang kehidupan anak SMA Suzuran. Pengambilan nama Crows sendiri didasarkan pada latar Sekolah Menengah Atas khusus laki-laki yang pada dasarnya berisi banyak berandalan sehingga dijuluki sekolah para Gagak (Crow).', 240000, 20, '8cdf8cd462cd13f9.jpg', '2026-11-10 05:26:06'),
(8, 1, 'Crows The Aftermath', 'Hiroshi Takahashi', 'Crows The Aftermath kurang lebih menceritakan di mana sesudah CROWS dan sebelum WORST jadi bisa di bilang pembukaan untuk komik WORST jadi kurang lebih cerita singkatnya Zetton,Hideyoshi,Gunji,Massa,king joe,Takeda dan Nakajima di sini masih kelas 2 , dan  di suzuran terjadilah freshman war untuk memperebutkan siapa yg terkuat di antara para freshman yg masuk ke suzuran diantaranya ada yg terkuat seperti Kurosawa kazumitsu,kanbe yokihatsu (Bucher),Harada Tokio dan di antra mereka bertiga yg di prediksi bakalan memenagkan freshman war tersebut tetapi terjadi kejadian yg sangat mengejutkan ternyata zetton yg di gadang\" orang terkuat di suzuran pada saat itu berhasil di kalahkan oleh salah seorang freshman yaitu Hanaki Guriko , dan berita itu pun cepat menyebar ke penjuru kota.', 135000, 1, 'f6a1c05c16e1358e.jpg', '2026-11-10 05:27:36'),
(9, 1, 'Worst', 'Hiroshi Takahashi', 'Worst merupakan manga lanjutan dari Crows karya Hiroshi Takahashi. Manga ini masih menceritakan tentang bagaimana konflik di kota Toarushi yang di dalamnya terdapat beberapa sekolah yang memang mempunyai banyak catatan buruk. Inti dari cerita ini sebenarnya adalah perjuangan seorang anak pindahan dari luar kota yang bernama Tsukishima Hana yang ingin menjadi pemimpin salah satu SMA yang terkenal belum pernah memiliki seorang yang dapat memimpin para berandal disana. Tidak lepas dari cerita pada manga sebelumnya (Crows), Hana merupakan murid kelas satu SMA Suzuran saat Hanazawa Saburou menduduki kelas tiga sebagai senior.', 150000, 15, '6e4b66a5b4eef12e.jpg', '2026-11-10 05:29:30'),
(10, 1, 'Crows Dustland', 'Hiroshi Takahashi', 'Di Distrik Utsusemi, Kota Toaru—di mana SMA Khusus Putra Suzuran menjulang tinggi di atas lanskap—seorang anak laki-laki bernama Asada Hibari pindah ke Kompleks Apartemen Nakahara, tempat yang terkenal sebagai bangunan berhantu. Masih terperangkap dalam bayang-bayang kematian ibunya, Hibari tetap tidak mampu melepaskan diri dari kesepiannya yang mencekik. Namun, pertemuan tak sengaja dengan Kobayashi Sanzou, seorang penghuni gedung yang sama, mulai mengarahkan masa mudanya menuju sesuatu yang lebih panas dan tak tergantikan daripada apa pun yang pernah ia ketahui!', 75000, 1000, '949c74dbc2f290a7.jpg', '2026-11-10 05:30:46'),
(11, 3, 'Atomic Habits', 'James Clear', 'Tiny changes remarkable result.', 55000, 55, '544ff37b4e8c8ece.jpg', '2026-11-10 05:36:46'),
(12, 4, 'A Little History of The United States', 'James West Davidson', 'History of nation usa', 95000, 22, '2d783cbe53e9f372.jpg', '2026-11-10 05:38:47'),
(13, 2, 'Harry Potter and the Sorcerer\'s Stone', 'J.K Rowling', 'Sinopsis: Pada usia 11 tahun, Harry mengetahui masa lalunya yang kelam dan mendapat undangan ke Sekolah Sihir Hogwarts. Bersama sahabat barunya, Ron Weasley dan Hermione Granger, Harry masuk asrama Gryffindor. Di sana, mereka mengungkap rahasia Batu Bertuah dan berhasil menggagalkan upaya Lord Voldemort (yang merasuki guru mereka, Profesor Quirrell) untuk kembali bangkit', 350000, 100, '4c028dcb57f80a6c.jpg', '2026-11-10 05:41:11'),
(14, 2, 'Harry Potter and the Deathly Hallows', 'J.K Rowling', 'Harry, Ron, dan Hermione tidak kembali ke Hogwarts, melainkan berpetualang mencari dan menghancurkan benda-benda tempat jiwa Voldemort disembunyikan (Horcrux). Cerita memuncak dalam Pertempuran Hogwarts, di mana Harry mengorbankan diri sejenak demi mengalahkan Voldemort untuk selamanya dan membawa kedamaian kembali ke dunia sihir.', 222000, 12, '852c376d03f6a82a.jpg', '2026-11-10 05:43:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Komik'),
(2, 'Novel'),
(3, 'Pengembangan Diri'),
(4, 'Sejarah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`, `reply`, `replied_at`) VALUES
(1, NULL, 'tes', 'tes@gmail.com', 'eff', 'eeee', 0, '2026-11-10 03:57:28', NULL, NULL),
(2, NULL, 'ff', 'galihjomok@gmail.com', 'dd', 'dddd', 0, '2026-11-10 04:42:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL,
  `payment_method` varchar(20) NOT NULL DEFAULT 'COD',
  `status` enum('Pending','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_title` varchar(200) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `qty` int(10) UNSIGNED NOT NULL,
  `subtotal` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@bookstore.com', '$2y$12$w1LjVBv8e21ylvuqKikKl.umr5wIpWrKuLFU8RlS0w8mafxcDImZS', 'admin', '2026-11-07 06:05:14'),
(2, 'mamamia', 'eustass858@gmail.com', '$2y$10$3KWJW/4c6R0tsnT5XM0hseqe9okVgsNMEPC5xtw3w0EpAbR7F.VPy', 'customer', '2026-11-07 18:04:41');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
