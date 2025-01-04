<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ubah query untuk menambahkan prefix path yang benar
$buku_terbaru_result = $conn->query("SELECT id, judul, pengarang, CONCAT('assets/', gambar) as gambar FROM buku ORDER BY created_at DESC LIMIT 5");

$buku_terpopuler_result = $conn->query("
    SELECT b.id, b.judul, b.pengarang, CONCAT('assets/', b.gambar) as gambar, COUNT(p.id) as total_peminjaman 
    FROM buku b 
    LEFT JOIN peminjaman p ON b.id = p.buku_id 
    GROUP BY b.id 
    ORDER BY total_peminjaman DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Selamat Datang di Perpustakaan Digital</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php">Dashboard Admin</a></li>
                        <?php else: ?>
                            <li><a href="user/beranda.php">Area Pengguna</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section>
                <h2>Tentang Perpustakaan Digital</h2>
                <p>Selamat datang di Perpustakaan Digital kami! Kami menyediakan akses ke ribuan buku digital untuk memenuhi kebutuhan membaca dan belajar Anda.</p>
            </section>

            <section>
                <h2>Buku Terbaru</h2>
                <div class="book-list">
                    <?php while ($row = $buku_terbaru_result->fetch_assoc()): ?>
                        <div class="book-item">
                            <img src="<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                            <h3><?php echo $row['judul']; ?></h3>
                            <p><?php echo $row['pengarang']; ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <section>
                <h2>Buku Terpopuler</h2>
                <div class="book-list">
                    <?php while ($row = $buku_terpopuler_result->fetch_assoc()): ?>
                        <div class="book-item">
                            <img src="<?php echo $row['gambar']; ?>" alt="<?php echo $row['judul']; ?>">
                            <h3><?php echo $row['judul']; ?></h3>
                            <p><?php echo $row['pengarang']; ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Perpustakaan Digital. Hak Cipta Dilindungi.</p>
        </footer>
    </div>
</body>
</html>