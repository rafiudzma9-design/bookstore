<?php
// Jumlah item di keranjang (keranjang disimpan di session: [id_buku => jumlah])
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-navy sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-book"></i> Bookstore
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="books.php">Katalog</a></li>
                <li class="nav-item">
    <a class="nav-link" href="about.php">About Us</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="contact.php">Hubungi Admin</a>
</li>
            </ul>

            <form class="d-flex me-lg-3 my-2 my-lg-0" action="books.php" method="get">
                <input class="form-control form-control-sm me-2" type="search" name="q"
                       placeholder="Cari judul, penulis..."
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-sm btn-outline-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3"></i> Keranjang
                        <?php if ($cartCount > 0): ?>
                            <span class="badge bg-primary"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="orders.php">Pesanan Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-light ms-lg-2" href="register.php">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>