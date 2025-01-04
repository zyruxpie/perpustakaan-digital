<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Fungsi untuk menambah buku
function tambahBuku($conn, $judul, $pengarang, $kategori_id, $jumlah, $gambar) {
    $target_dir = "../assets/images/books/";
    $gambar_path = uploadImage($gambar, $target_dir);
    
    if (is_string($gambar_path) && strpos($gambar_path, 'Error') === false) {
        $stmt = $conn->prepare("INSERT INTO buku (judul, pengarang, kategori_id, jumlah, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $judul, $pengarang, $kategori_id, $jumlah, $gambar_path);
        
        if ($stmt->execute()) {
            return "Buku berhasil ditambahkan";
        } else {
            return "Error: " . $stmt->error;
        }
    } else {
        return $gambar_path;
    }
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tambah_buku'])) {
        $judul = cleanInput($_POST['judul']);
        $pengarang = cleanInput($_POST['pengarang']);
        $kategori_id = $_POST['kategori_id'];
        $jumlah = $_POST['jumlah'];
        $gambar = $_FILES['gambar'];
        
        $message = tambahBuku($conn, $judul, $pengarang, $kategori_id, $jumlah, $gambar);
    }
}

// Ambil daftar kategori
$kategori_result = $conn->query("SELECT id, nama FROM kategori");

// Ambil daftar buku
$buku_result = $conn->query("SELECT b.id, b.judul, b.pengarang, k.nama as kategori, b.jumlah, b.gambar FROM buku b JOIN kategori k ON b.kategori_id = k.id");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/" class="logo">Dashboard Admin</a>
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
            <h2>Tambah Buku Baru</h2>
            <form action="dashboard.php" method="post" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <label for="judul">Judul:</label>
                    <input type="text" id="judul" name="judul" required>
                </div>
                <div class="form-group">
                    <label for="pengarang">Pengarang:</label>
                    <input type="text" id="pengarang" name="pengarang" required>
                </div>
                <div class="form-group">
                    <label for="kategori_id">Kategori:</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <?php while ($row = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" id="jumlah" name="jumlah" required min="1">
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar Sampul:</label>
                    <input type="file" id="gambar" name="gambar" required accept="image/*" class="file-input">
                </div>
                <button type="submit" name="tambah_buku" class="auth-btn kategori-btn">Tambah Buku</button>
            </form>
            
            <h2>Daftar Buku</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Gambar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $buku_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['pengarang']; ?></td>
                                <td><?php echo $row['kategori']; ?></td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td>
                                    <img src="<?php echo $row['gambar']; ?>" 
                                         alt="<?php echo $row['judul']; ?>" 
                                         class="book-thumbnail">
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="edit_buku.php?id=<?php echo $row['id']; ?>" 
                                           class="action-btn edit-btn">
                                            Edit
                                        </a>
                                        <a href="hapus_buku.php?id=<?php echo $row['id']; ?>" 
                                           class="action-btn delete-btn"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                            Hapus
                                        </a>
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