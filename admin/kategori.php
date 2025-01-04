<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$message = '';

// Fungsi untuk menambah kategori
function tambahKategori($conn, $nama) {
    $stmt = $conn->prepare("INSERT INTO kategori (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    
    if ($stmt->execute()) {
        return "Kategori berhasil ditambahkan";
    } else {
        return "Error: " . $stmt->error;
    }
}

// Fungsi untuk menghapus kategori
function hapusKategori($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return "Kategori berhasil dihapus";
    } else {
        return "Error: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tambah_kategori'])) {
        $nama = cleanInput($_POST['nama']);
        $message = tambahKategori($conn, $nama);
    } elseif (isset($_POST['hapus_kategori'])) {
        $id = $_POST['id'];
        $message = hapusKategori($conn, $id);
    }
}

// Ambil daftar kategori
$kategori_result = $conn->query("SELECT id, nama FROM kategori ORDER BY nama");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/" class="logo">Manajemen Kategori</a>
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
            <h2>Tambah Kategori Baru</h2>
            <form action="kategori.php" method="post" class="form">
                <div class="form-group">
                    <label for="nama">Nama Kategori:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <button type="submit" name="tambah_kategori" class="auth-btn kategori-btn">Tambah Kategori</button>
            </form>
            
            <h2>Daftar Kategori</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $kategori_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['nama']; ?></td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn edit-btn" onclick="window.location.href='edit_kategori.php?id=<?php echo $row['id']; ?>'">
                                            Edit
                                        </button>
                                        <form action="kategori.php" method="post" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="hapus_kategori" class="action-btn delete-btn" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
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