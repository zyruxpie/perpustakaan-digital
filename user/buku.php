<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

// Inisialisasi variabel pencarian
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Query dasar
$query = "SELECT b.id, b.judul, b.pengarang, k.nama as kategori, b.jumlah, b.gambar 
          FROM buku b 
          JOIN kategori k ON b.kategori_id = k.id";

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $query .= " WHERE b.judul LIKE '%$search%' OR b.pengarang LIKE '%$search%'";
}
if ($kategori > 0) {
    $query .= (strpos($query, 'WHERE') !== false) ? " AND" : " WHERE";
    $query .= " b.kategori_id = $kategori";
}

$query .= " ORDER BY b.judul ASC";

// Eksekusi query
$result = $conn->query($query);

// Ambil daftar kategori untuk filter
$kategori_result = $conn->query("SELECT id, nama FROM kategori ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="user-navbar">
        <div class="user-nav">
            <a href="beranda.php" class="nav-brand">
                <i class="fas fa-book-reader"></i> 
                Perpustakaan Digital
            </a>
            <ul class="nav-menu">
                <li><a href="beranda.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="buku.php"><i class="fas fa-book"></i> Daftar Buku</a></li>
                <li><a href="status-buku.php"><i class="fas fa-info-circle"></i> Status Buku</a></li>
                <li><a href="peminjaman.php"><i class="fas fa-bookmark"></i> Peminjaman</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <section class="hero">
            <h1>Katalog Buku</h1>
            <p>Temukan berbagai koleksi buku yang tersedia</p>
        </section>

        <section class="search-section">
            <form action="buku.php" method="get" class="search-form">
                <div class="input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Cari judul atau pengarang" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="input-group">
                    <i class="fas fa-filter"></i>
                    <select name="kategori" class="search-select">
                        <option value="0">Semua Kategori</option>
                        <?php while ($kat = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo $kat['id']; ?>" 
                                    <?php echo ($kategori == $kat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kat['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </section>

        <?php if ($result->num_rows > 0): ?>
            <div class="book-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-card">
                        <img src="<?php echo htmlspecialchars($row['gambar']); ?>" 
                             alt="<?php echo htmlspecialchars($row['judul']); ?>" 
                             class="book-cover">
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($row['judul']); ?></h3>
                            <p class="book-author">
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($row['pengarang']); ?>
                            </p>
                            <p class="book-category">
                                <i class="fas fa-tag"></i> 
                                <?php echo htmlspecialchars($row['kategori']); ?>
                            </p>
                            <p class="book-stock <?php echo $row['jumlah'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <i class="fas <?php echo $row['jumlah'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                Stok: <?php echo $row['jumlah']; ?>
                            </p>
                            <a href="detail-buku.php?id=<?php echo $row['id']; ?>" class="view-details">
                                <i class="fas fa-info-circle"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search fa-3x"></i>
                <h2>Tidak ada buku yang ditemukan</h2>
                <p>Coba gunakan kata kunci pencarian yang berbeda</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>