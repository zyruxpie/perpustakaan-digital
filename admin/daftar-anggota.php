<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

// Search functionality
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$searchCondition = "WHERE role != 'admin'";
if (!empty($search)) {
    $searchCondition .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
}

// Get total number of users (excluding admins)
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM users " . $searchCondition);
$total = $totalQuery->fetch_assoc()['total'];
$pages = ceil($total / $perPage);

// Get users for current page (excluding admins)
$query = "SELECT id, username, email, role, created_at FROM users " . $searchCondition . " LIMIT $start, $perPage";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - Admin Perpustakaan Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="/" class="logo">Admin Panel</a>
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
            <h1>Daftar Anggota</h1>

            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari username atau email" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Cari</button>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>