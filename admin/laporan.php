<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Fungsi untuk mendapatkan laporan bulanan
function getLaporanBulanan($conn, $bulan, $tahun) {
    $query = "
        SELECT 
            b.judul,
            COUNT(*) as total_peminjaman,
            SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) as total_dikembalikan,
            SUM(CASE WHEN p.status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat
        FROM 
            peminjaman p
        JOIN 
            buku b ON p.buku_id = b.id
        WHERE 
            MONTH(p.tanggal_pinjam) = ? AND YEAR(p.tanggal_pinjam) = ?
        GROUP BY 
            b.id
        ORDER BY 
            total_peminjaman DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bulan, $tahun);
    $stmt->execute();
    return $stmt->get_result();
}

$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

$laporan_result = getLaporanBulanan($conn, $bulan, $tahun);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/" class="logo">Laporan Bulanan</a>
            <nav class="nav-buttons">
                <button class="auth-btn dashboard-btn" onclick="window.location.href='dashboard.php'">Dashboard</button>
                <button class="auth-btn kategori-btn" onclick="window.location.href='kategori.php'">Kategori</button>
                <button class="auth-btn manajemen-btn" onclick="window.location.href='manajemen-peminjaman.php'">Manajemen Peminjaman</button>
                <button class="auth-btn laporan-btn" onclick="window.location.href='laporan.php'">Laporan</button>
                <button class="auth-btn anggota-btn" onclick="window.location.href='daftar-anggota.php'">Daftar Anggota</button>
                <button class="auth-btn logout-btn" onclick="window.location.href='../logout.php'">Logout</button>
            </nav>
        </div>
        
        <div class="content-section">
            <div class="filter-section">
                <form action="laporan.php" method="get" class="filter-form">
                    <div class="form-group">
                        <select name="bulan" class="select-month">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $bulan == $i ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select name="tahun" class="select-year">
                            <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="auth-btn kategori-btn">Tampilkan Laporan</button>
                    </div>
                </form>
            </div>

            <div class="stats-cards">
                <div class="card">
                    <h3>Total Peminjaman</h3>
                    <?php 
                    $total = 0;
                    $result = $laporan_result->fetch_all(MYSQLI_ASSOC);
                    foreach ($result as $row) {
                        $total += $row['total_peminjaman'];
                    }
                    echo "<p class='stats-number'>$total</p>";
                    $laporan_result->data_seek(0);
                    ?>
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Buku</th>
                            <th>Total Peminjaman</th>
                            <th>Total Dikembalikan</th>
                            <th>Total Terlambat</th>
                            <th>Persentase Keterlambatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $laporan_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['total_peminjaman']; ?></td>
                                <td><?php echo $row['total_dikembalikan']; ?></td>
                                <td><?php echo $row['total_terlambat']; ?></td>
                                <td>
                                    <?php 
                                    $persentase = ($row['total_peminjaman'] > 0) 
                                        ? round(($row['total_terlambat'] / $row['total_peminjaman']) * 100, 1) 
                                        : 0;
                                    echo $persentase . '%';
                                    ?>
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