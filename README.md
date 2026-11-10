# Bookstore - Toko Buku Online

## A. Informasi Proyek

**Nama aplikasi:** Bookstore

**Deskripsi:** Website toko buku online. Pelanggan bisa melihat katalog, mencari buku, memasukkan buku ke keranjang, memesan dengan pembayaran COD (bayar di tempat), dan melihat riwayat pesanan. Administrator mengelola buku, kategori, stok, pelanggan, transaksi, dan laporan penjualan lewat dashboard.

**Tujuan pembuatan:** Tugas praktik ujian sertifikasi kompetensi LSP bidang Rekayasa Perangkat Lunak (RPL).

**Teknologi:**
- Frontend: HTML5, CSS3, JavaScript dasar, Bootstrap 5, Bootstrap Icons
- Backend: PHP Native (tanpa framework), PDO, PHP Session
- Database: MySQL
- Lingkungan: XAMPP (Apache + MySQL), phpMyAdmin, Visual Studio Code

## B. Fitur Aplikasi

**Pelanggan**
- Homepage (hero, kategori, buku terbaru, buku populer)
- Registrasi, login, dan logout
- Katalog buku, filter kategori, pencarian (judul, penulis, kategori)
- Detail buku
- Keranjang belanja (tambah, ubah jumlah, hapus, total, validasi stok)
- Checkout COD (data penerima, ringkasan, simpan pesanan)
- Riwayat pembelian dan detail pesanan
- Profil (ubah nama, email, password)

**Administrator**
- Login admin terpisah dari login pelanggan
- Dashboard statistik (buku, kategori, pelanggan, transaksi, pendapatan)
- Manajemen buku (tambah, edit, hapus, cari, upload cover)
- Manajemen stok (ubah stok langsung dari daftar buku, peringatan stok habis)
- Manajemen kategori (tambah, edit, hapus)
- Manajemen pelanggan (daftar, detail, riwayat transaksi)
- Manajemen transaksi (detail dan ubah status: Pending, Diproses, Dikirim, Selesai, Dibatalkan)
- Laporan penjualan (filter tanggal, rekap per status, per hari, buku terlaris)

## C. Persiapan Instalasi

1. **Instal XAMPP** dari apachefriends.org, pilih versi PHP 8.x. Ikuti penginstal sampai selesai.
2. **Jalankan Apache dan MySQL:** buka XAMPP Control Panel, klik **Start** di baris Apache dan MySQL sampai keduanya berwarna hijau.
3. **Instal Visual Studio Code** dari code.visualstudio.com.

## D. Instalasi Project

1. **Letakkan folder project** di `C:\xampp\htdocs\`. Contoh: `C:\xampp\htdocs\bookstorerafi\`.
2. **Buat database:** buka `http://localhost/phpmyadmin`, klik **New**, isi nama `bookstore`, pilih collation `utf8mb4_general_ci`, klik **Create**.
3. **Import database:** klik database `bookstore`, buka tab **Import**, pilih file `bookstore.sql`, klik **Import**.
4. **Konfigurasi database:** buka `config/database.php` dan pastikan isinya sesuai XAMPP bawaan: host `localhost`, database `bookstore`, user `root`, password kosong.
5. **Buat folder upload:** pastikan ada folder `uploads/covers/` di dalam folder project.
6. **Jalankan website** lewat browser (lihat bagian E).

## E. Alamat Website

Sesuaikan dengan nama folder project di `htdocs`. Untuk folder `bookstorerafi`:

| Halaman | Alamat |
|---|---|
| Website pelanggan | `http://localhost/bookstorerafi/` |
| Login pelanggan | `http://localhost/bookstorerafi/login.php` |
| Login admin | `http://localhost/bookstorerafi/admin/login.php` |

## F. Akun Pengujian

**Akun pelanggan:** buka halaman Daftar (`register.php`), isi formulir, lalu login.

**Akun admin:** admin tidak bisa mendaftar lewat formulir (demi keamanan). Buat dengan skrip sekali pakai berikut:

1. Buat file `buat_admin.php` di folder utama project dengan isi:

```php
<?php
require_once __DIR__ . '/includes/init.php';

$nama     = 'Administrator';
$email    = 'admin@bookstore.com';
$password = 'ISI_PASSWORD_PILIHANMU';   // ganti dulu sebelum dijalankan

$cek = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
if ($cek) { exit('Akun admin sudah ada. Hapus file ini.'); }

$pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')
    ->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);

echo 'Admin dibuat. HAPUS file buat_admin.php sekarang!';
```

2. Buka `http://localhost/bookstorerafi/buat_admin.php` satu kali.
3. **Hapus file `buat_admin.php`** setelah berhasil.
4. Login admin dengan email dan password yang kamu tulis di skrip.

Password disimpan dalam bentuk hash (`password_hash`), tidak pernah teks biasa.

## G. Struktur Folder

```
bookstorerafi/
├── admin/                 Halaman administrator
│   ├── login.php          Login admin
│   ├── logout.php         Logout admin
│   ├── dashboard.php      Statistik
│   ├── books.php          Daftar buku, ubah stok, hapus
│   ├── book_add.php       Tambah buku + upload cover
│   ├── book_edit.php      Edit buku + ganti cover
│   ├── categories.php     Tambah, edit, hapus kategori
│   ├── customers.php      Daftar dan detail pelanggan
│   ├── orders.php         Daftar, detail, ubah status transaksi
│   └── reports.php        Laporan penjualan
├── assets/css/style.css   Gaya tampilan
├── config/database.php    Koneksi database (PDO)
├── includes/              Potongan kode yang dipakai berulang
│   ├── init.php           Pintu masuk: session + database + fungsi
│   ├── header.php, navbar.php, footer.php   Layout pelanggan
│   ├── admin_header.php, admin_footer.php   Layout admin (sidebar)
│   ├── admin_guard.php    Penjaga halaman admin
│   ├── book_card.php      Kartu satu buku
│   └── upload_cover.php   Fungsi upload gambar yang aman
├── uploads/covers/        Tempat gambar cover buku
├── bookstore.sql          Struktur dan data awal database
├── index.php              Beranda
├── register.php, login.php, logout.php
├── books.php, book_detail.php
├── cart.php, checkout.php
├── orders.php, profile.php
└── README.md
```

## H. Dokumentasi Database

| Tabel | Fungsi |
|---|---|
| `users` | Data akun. Kolom `role` membedakan `admin` dan `customer`. |
| `categories` | Kategori buku. |
| `books` | Data buku: judul, penulis, harga, stok, cover. Terhubung ke kategori. |
| `orders` | Satu baris per pesanan: penerima, alamat, telepon, total, metode bayar, status. |
| `order_details` | Rincian buku dalam pesanan. Menyimpan salinan judul dan harga saat transaksi. |

**Relasi:**
- `categories` 1 ke banyak `books` (`books.category_id`).
- `users` 1 ke banyak `orders` (`orders.user_id`).
- `orders` 1 ke banyak `order_details` (`order_details.order_id`).
- `books` 1 ke banyak `order_details` (`order_details.book_id`).

`order_details` menyimpan `book_title` dan `price` sendiri supaya riwayat pesanan tidak berubah walau harga buku diubah atau buku dihapus.

## I. Alur Sistem

1. **Registrasi:** data divalidasi, email dicek agar tidak kembar, password di-hash dengan `password_hash()`, lalu disimpan dengan role `customer`.
2. **Login:** email dicari di database, password dicocokkan dengan `password_verify()`, lalu data pengguna disimpan di session.
3. **Pencarian buku:** kata kunci dari kolom pencarian dikirim ke `books.php?q=...` dan dicari dengan `LIKE` pada judul, penulis, dan kategori memakai prepared statement.
4. **Keranjang:** isi keranjang disimpan di session dalam bentuk `[id_buku => jumlah]`. Harga selalu diambil dari database.
5. **Checkout:** wajib login. Data penerima divalidasi, lalu transaksi database dimulai.
6. **Penyimpanan transaksi:** pesanan disimpan ke `orders`, rinciannya ke `order_details`.
7. **Pengurangan stok:** stok dikunci (`FOR UPDATE`), dicek ulang, lalu dikurangi. Jika ada yang gagal, `rollBack()` membatalkan semuanya. Jika sukses, `commit()` menyimpan permanen.
8. **Pengelolaan oleh admin:** admin menambah, mengubah, menghapus buku dan kategori, mengubah stok, dan mengubah status pesanan. Pesanan yang dibatalkan otomatis mengembalikan stok.

**Keamanan yang dipakai:** prepared statement (anti SQL Injection), `htmlspecialchars()` (anti XSS), token CSRF di semua form, `password_hash()` dan `password_verify()`, penjaga halaman admin, session admin terpisah dari pelanggan, validasi upload gambar (isi file, ukuran, ekstensi, nama diacak), serta validasi di server.

## J. Catatan Penting (Kesalahan Umum)

**1. Error 404 Not Found**
- Penyebab: nama folder atau file di URL salah, atau file belum dibuat.
- Perbaikan: pastikan folder project ada di `C:\xampp\htdocs\`, cek ejaan URL (`localhost/bookstorerafi/...`), dan pastikan file yang dituju ada.

**2. Database connection failed**
- Penyebab: MySQL belum menyala atau isi `config/database.php` salah.
- Perbaikan: Start MySQL di XAMPP, lalu cek nama database `bookstore`, user `root`, password kosong.

**3. Access denied for user (MySQL)**
- Penyebab: user atau password di `config/database.php` tidak cocok dengan MySQL.
- Perbaikan: gunakan `root` dengan password kosong (bawaan XAMPP), atau samakan dengan akun MySQL kamu.

**4. Gambar cover tidak muncul**
- Penyebab: folder `uploads/covers/` tidak ada, atau nama file di database tidak sama dengan file di folder.
- Perbaikan: buat folder tersebut, upload ulang cover lewat halaman edit buku, lalu refresh dengan Ctrl+F5.

**5. Halaman kosong (blank)**
- Penyebab: ada error PHP yang tidak ditampilkan.
- Perbaikan: buka log di `C:\xampp\apache\logs\error.log`, atau tambahkan sementara `ini_set('display_errors', 1);` di baris atas `includes/init.php`. Hapus lagi setelah selesai.

**6. Session tidak berfungsi (login terus kembali ke login)**
- Penyebab: `includes/init.php` tidak dimuat di baris pertama halaman, atau ada output sebelum `<?php`.
- Perbaikan: pastikan setiap halaman diawali `require_once ... 'includes/init.php'` dan tidak ada spasi sebelum `<?php`.

**7. Halaman admin tidak bisa diakses**
- Penyebab: belum login admin, atau akun admin belum dibuat.
- Perbaikan: login lewat `admin/login.php` dengan akun ber-role `admin`. Jika belum ada, buat dengan skrip pada bagian F. Admin dan pelanggan memakai pintu login berbeda.

**8. File upload gagal**
- Penyebab: ukuran lebih dari 2 MB, format bukan JPG/PNG/WEBP, folder `uploads/covers/` tidak ada, atau form tanpa `enctype="multipart/form-data"`.
- Perbaikan: gunakan gambar JPG/PNG/WEBP di bawah 2 MB dan pastikan foldernya ada.
