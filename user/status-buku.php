<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

// Inisialisasi variabel pencarian
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Query dasar
$query = "
    SELECT b.id, b.judul, b.pengarang, k.nama as kategori, b.jumlah, 
    (SELECT COUNT(*) FROM peminjaman p WHERE p.buku_id = b.id AND p.status = 'dipinjam') as dipinjam
    FROM buku b 
    JOIN kategori k ON b.kategori_id = k.id
";

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $query .= " WHERE b.judul LIKE '%$search%' OR b.pengarang LIKE '%$search%'";
}

// Tambahkan ordering
$query .= " ORDER BY b.judul ASC";

// Eksekusi query
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/status.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Status Buku</h1>
        <nav>
            <ul>
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="buku.php">Daftar Buku</a></li>
                <li><a href="status-buku.php">Status Buku</a></li>
                <li><a href="peminjaman.php">Peminjaman</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <form action="status-buku.php" method="get">
            <input type="text" name="search" placeholder="Cari judul atau pengarang" value="<?php echo $search; ?>">
            <button type="submit">Cari</button>
        </form>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Kategori</th>
                    <th>Total Stok</th>
                    <th>Dipinjam</th>
                    <th>Tersedia</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            // Menghitung sisa stok
            $tersedia = $row['jumlah'] - $row['dipinjam'];
            $status = ($tersedia > 0) ? 'Tersedia' : 'Tidak Tersedia';
            ?>
            <tr>
                <td><?php echo $row['judul']; ?></td>
                <td><?php echo $row['pengarang']; ?></td>
                <td><?php echo $row['kategori']; ?></td>
                <td><?php echo $row['jumlah']; ?></td> <!-- Total stok tidak berubah -->
                <td><?php echo $row['dipinjam']; ?></td>
                <td><?php echo $tersedia; ?></td> <!-- Menampilkan sisa stok -->
                <td class="status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                    <?php echo $status; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">Tidak ada buku yang ditemukan.</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
    </div>
</body>
</html>