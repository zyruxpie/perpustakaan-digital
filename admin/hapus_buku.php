<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: dashboard.php");
    exit();
}

// Periksa apakah buku sedang dipinjam
$check_query = "SELECT COUNT(*) as count FROM peminjaman WHERE buku_id = $id AND status = 'dipinjam'";
$check_result = $conn->query($check_query);
$check_row = $check_result->fetch_assoc();

if ($check_row['count'] > 0) {
    $_SESSION['error_message'] = "Buku tidak dapat dihapus karena sedang dipinjam.";
    header("Location: dashboard.php");
    exit();
}

// Hapus buku jika tidak sedang dipinjam
$delete_query = "DELETE FROM buku WHERE id = $id";
if ($conn->query($delete_query) === TRUE) {
    $_SESSION['success_message'] = "Buku berhasil dihapus.";
} else {
    $_SESSION['error_message'] = "Error: " . $conn->error;
}

header("Location: dashboard.php");
exit();