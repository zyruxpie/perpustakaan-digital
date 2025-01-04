<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$message = '';

// Fungsi untuk mengubah status peminjaman
function ubahStatusPeminjaman($conn, $id, $status) {
    $stmt = $conn->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        if ($status == 'dikembalikan') {
            $buku_id = $conn->query("SELECT buku_id FROM peminjaman WHERE id = $id")->fetch_assoc()['buku_id'];
        }
        return "Status peminjaman berhasil diubah";
    } else {
        return "Error: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ubah_status'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $message = ubahStatusPeminjaman($conn, $id, $status);
    }
}

// Ambil daftar peminjaman
$peminjaman_result = $conn->query("
    SELECT p.id, u.username, b.judul, p.tanggal_pinjam, p.tanggal_kembali_aktual, p.status 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN buku b ON p.buku_id = b.id 
    ORDER BY p.tanggal_pinjam DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/" class="logo">Manajemen Peminjaman</a>
            <nav class="nav-buttons">
                <button class="auth-btn dashboard-btn" onclick="window.location.href='dashboard.php'">Dashboard</button>
                <button class="auth-btn kategori-btn" onclick="window.location.href='kategori.php'">Kategori</button>
                <button class="auth-btn manajemen-btn" onclick="window.location.href='manajemen-peminjaman.php'">Manajemen Peminjaman</button>
                <button class="auth-btn laporan-btn" onclick="window.location.href='laporan.php'">Laporan</button>
                <button class="auth-btn anggota-btn" onclick="window.location.href='daftar-anggota.php'">Daftar Anggota</button>
                <button class="auth-btn logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </nav>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-error' : 'alert-success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-section">
            <h2>Daftar Peminjaman</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Peminjam</th>
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
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['tanggal_pinjam']; ?></td>
                                <td><?php echo $row['tanggal_kembali_aktual']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="manajemen-peminjaman.php" method="post" class="action-group">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="dipinjam" <?php echo $row['status'] == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                                            <option value="dikembalikan" <?php echo $row['status'] == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                                        </select>
                                        <button type="submit" name="ubah_status" class="action-btn edit-btn">
                                            Update Status
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>