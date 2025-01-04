<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: buku.php");
    exit();
}

// Ambil detail buku
$query = "SELECT b.*, k.nama as kategori_nama 
          FROM buku b 
          JOIN kategori k ON b.kategori_id = k.id 
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$buku = $result->fetch_assoc();

if (!$buku) {
    header("Location: buku.php");
    exit();
}

// Hitung jumlah buku yang tersedia
$query_tersedia = "SELECT jumlah - (SELECT COUNT(*) FROM peminjaman WHERE buku_id = ? AND status = 'dipinjam') AS tersedia FROM buku WHERE id = ?";
$stmt_tersedia = $conn->prepare($query_tersedia);
$stmt_tersedia->bind_param("ii", $id, $id);
$stmt_tersedia->execute();
$result_tersedia = $stmt_tersedia->get_result();
$tersedia = $result_tersedia->fetch_assoc()['tersedia'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($buku['judul']); ?> - Perpustakaan Digital</title>
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
        <div class="book-detail">
            <div class="book-image">
                <img src="<?php echo htmlspecialchars($buku['gambar']); ?>" alt="<?php echo htmlspecialchars($buku['judul']); ?>">
            </div>
            <div class="book-info">
                <h1><?php echo htmlspecialchars($buku['judul']); ?></h1>
                <p class="author"><i class="fas fa-user"></i> <?php echo htmlspecialchars($buku['pengarang']); ?></p>
                <p class="category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($buku['kategori_nama']); ?></p>
                <p class="stock <?php echo $tersedia > 0 ? 'text-success' : 'text-danger'; ?>">
                    <i class="fas <?php echo $tersedia > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    Stok Tersedia: <?php echo $tersedia; ?>
                </p>
                <?php if ($tersedia > 0): ?>
                    <form action="peminjaman.php" method="post">
                        <input type="hidden" name="buku_id" value="<?php echo $buku['id']; ?>">
                        <button type="submit" name="pinjam" class="borrow-btn">
                            <i class="fas fa-hand-holding-heart"></i> Pinjam Buku
                        </button>
                    </form>
                <?php else: ?>
                    <button class="borrow-btn disabled" disabled>
                        <i class="fas fa-clock"></i> Buku Tidak Tersedia
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .book-detail {
            display: flex;
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-top: 80px;
        }
        .book-image {
            flex: 0 0 300px;
        }
        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .book-info {
            flex: 1;
            padding: 2rem;
        }
        .book-info h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .author, .category, .stock {
            margin-bottom: 0.5rem;
        }
        .description {
            margin-top: 1rem;
        }
        .description h2 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .borrow-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            margin-top: 1rem;
        }
        .borrow-btn:hover {
            background: var(--primary-dark);
        }
        .borrow-btn.disabled {
            background: var(--gray);
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .book-detail {
                flex-direction: column;
            }
            .book-image {
                flex: 0 0 200px;
            }
        }
    </style>
</body>
</html>