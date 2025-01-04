<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$message = '';

// Fungsi untuk meminjam buku
function pinjamBuku($conn, $user_id, $buku_id) {
    // Cek apakah buku tersedia dengan mempertimbangkan yang sedang dipinjam
    $stok_query = "
        SELECT b.jumlah,
        (SELECT COUNT(*) FROM peminjaman p WHERE p.buku_id = b.id AND p.status = 'dipinjam') as dipinjam
        FROM buku b 
        WHERE b.id = $buku_id";
    $stok_result = $conn->query($stok_query);
    $stok_data = $stok_result->fetch_assoc();
    
    $tersedia = $stok_data['jumlah'] - $stok_data['dipinjam'];
    
    if ($tersedia <= 0) {
        return "Buku tidak tersedia.";
    }

    // Cek apakah pengguna sudah meminjam buku dengan judul yang sama
    $judul_buku_result = $conn->query("SELECT b.judul FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.user_id = $user_id AND p.status = 'dipinjam'");
    $judul_buku_dipinjam = [];
    while ($row = $judul_buku_result->fetch_assoc()) {
        $judul_buku_dipinjam[] = $row['judul'];
    }

    // Ambil judul buku yang ingin dipinjam
    $judul_buku = $conn->query("SELECT judul FROM buku WHERE id = $buku_id")->fetch_assoc()['judul'];

    if (in_array($judul_buku, $judul_buku_dipinjam)) {
        return "Error: Anda sudah meminjam buku dengan judul yang sama.";
    }

    $tanggal_pinjam = date('Y-m-d');
$stmt = $conn->prepare("INSERT INTO peminjaman (user_id, buku_id, tanggal_pinjam, status) VALUES (?, ?, ?, 'dipinjam')");
$stmt->bind_param("iis", $user_id, $buku_id, $tanggal_pinjam);

if ($stmt->execute()) {
    return "Buku berhasil dipinjam";
} else {
    return "Error: " . $stmt->error;
}
}

// Fungsi untuk mengembalikan buku
function kembalikanBuku($conn, $peminjaman_id, $tanggal_kembali_aktual) {
    $stmt = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali_aktual = ? WHERE id = ?");
    $stmt->bind_param("si", $tanggal_kembali_aktual, $peminjaman_id);
    
    if ($stmt->execute()) {
        // Tambah stok buku
        $buku_id = $conn->query("SELECT buku_id FROM peminjaman WHERE id = $peminjaman_id")->fetch_assoc()['buku_id'];
        return "Buku berhasil dikembalikan";
    } else {
        return "Error: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['pinjam'])) {
        $buku_id = $_POST['buku_id'];
        $message = pinjamBuku($conn, $user_id, $buku_id);
    } elseif (isset($_POST['kembalikan'])) {
        $peminjaman_id = $_POST['peminjaman_id'];
        $tanggal_kembali_aktual = $_POST['tanggal_kembali_aktual'];
        $message = kembalikanBuku($conn, $peminjaman_id, $tanggal_kembali_aktual);
    }
}

// Ambil daftar buku yang tersedia
$buku_tersedia = $conn->query("SELECT id, judul FROM buku WHERE jumlah > 0");

// Ambil daftar peminjaman pengguna
$peminjaman_result = $conn->query("
    SELECT p.id, b.judul, p.tanggal_pinjam, p.tanggal_kembali_aktual, p.status
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    WHERE p.user_id = $user_id
    ORDER BY p.tanggal_pinjam DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/peminjaman.css">
    <style>
        .inline-form {
            display: flex;
            align-items: center;
        }
        .inline-form input[type="date"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Peminjaman Buku</h1>
        <nav>
            <ul>
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="buku.php">Daftar Buku</a></li>
                <li><a href="status-buku.php">Status Buku</a></li>
                <li><a href="peminjaman.php">Peminjaman</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <h2>Pinjam Buku Baru</h2>
        <form action="peminjaman.php" method="post">
            <select name="buku_id" required>
                <option value="">Pilih Buku</option>
                <?php while ($buku = $buku_tersedia->fetch_assoc()): ?>
                    <option value="<?php echo $buku['id']; ?>"><?php echo $buku['judul']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="pinjam">Pinjam Buku</button>
        </form>

        <h2>Daftar Peminjaman</h2>
        <table>
        <thead>
            <tr>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $peminjaman_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['judul']; ?></td>
                    <td><?php echo $row['tanggal_pinjam']; ?></td>
                    <td><?php echo $row['tanggal_kembali_aktual']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if ($row['status'] == 'dipinjam'): ?>
                            <form action="peminjaman.php" method="post" class="inline-form">
                                <input type="hidden" name="peminjaman_id" value="<?php echo $row['id']; ?>">
                                <input type="date" name="tanggal_kembali_aktual" required min="<?php echo $row['tanggal_pinjam']; ?>">
                                <button type="submit" name="kembalikan">Kembalikan</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</body>
</html>