<?php
require_once __DIR__ . '/includes/init.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Bookstore</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
            font-family: Arial, sans-serif;
        }

        .about-hero {
            background: #0d2348;
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .about-content {
            padding: 60px 20px;
        }

        .about-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            height: 100%;
        }

        footer {
            background: #0d2348;
            color: white;
            padding: 25px;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark"
         style="background:#0d2348;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                📖 BOOKSTORE
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">Katalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Hubungi Admin</a>
                    </li>
                     <li class="nav-item">
    <a class="nav-link" href="contact.php">Hubungi Admin</a>
</li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="about-hero">
        <div class="container">
            <h1 class="fw-bold">Tentang Bookstore</h1>
            <p class="lead">
                Temukan dunia melalui setiap halaman buku.
            </p>
        </div>
    </section>

    <section class="about-content">
        <div class="container">

            <div class="row justify-content-center mb-5">
                <div class="col-lg-9 text-center">
                    <h2 class="fw-bold mb-3">Siapa Kami?</h2>

                    <p class="text-secondary">
                        Bookstore merupakan platform toko buku online
                        yang menyediakan berbagai pilihan buku dari
                        berbagai kategori. Kami hadir untuk memudahkan
                        pelanggan menemukan dan membeli buku favorit
                        mereka dengan mudah dan praktis.
                    </p>
                </div>
            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="about-card">
                        <h3 class="fw-bold">🎯 Visi Kami</h3>
                        <p class="text-secondary">
                            Menjadi platform toko buku online yang
                            memudahkan masyarakat mendapatkan akses
                            terhadap berbagai sumber bacaan berkualitas.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="about-card">
                        <h3 class="fw-bold">🚀 Misi Kami</h3>
                        <p class="text-secondary">
                            Menyediakan katalog buku yang beragam,
                            memberikan pengalaman belanja yang mudah,
                            serta meningkatkan minat membaca masyarakat.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer>
        <h5>📖 Bookstore</h5>
        <p class="mb-0">
            © 2026 Bookstore. Project LSP.
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>