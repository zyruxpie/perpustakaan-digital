<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

if ($id == 0) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = cleanInput($_POST['judul']);
    $pengarang = cleanInput($_POST['pengarang']);
    $kategori_id = (int)$_POST['kategori_id'];
    $jumlah = (int)$_POST['jumlah'];

    $stmt = $conn->prepare("UPDATE buku SET judul = ?, pengarang = ?, kategori_id = ?, jumlah = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $judul, $pengarang, $kategori_id, $jumlah, $id);

    if ($stmt->execute()) {
        $message = "Buku berhasil diperbarui";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$result = $conn->query("SELECT * FROM buku WHERE id = $id");
$buku = $result->fetch_assoc();

$kategori_result = $conn->query("SELECT id, nama FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan Digital</title>
    <style>
        :root {
            --primary: #4a90e2;
            --primary-dark: #357abd;
            --secondary: #2ecc71;
            --secondary-dark: #27ae60;
            --dark: #2c3e50;
            --light: #f5f6fa;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f1c40f;
            --gray: #95a5a6;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .user-navbar {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .user-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 1rem;
            list-style: none;
        }

        .nav-menu a {
            color: var(--dark);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: var(--transition);
        }

        .nav-menu a:hover {
            background: var(--primary);
            color: var(--white);
        }

        .edit-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            background: var(--success);
            color: var(--white);
        }

        h1 {
            color: var(--dark);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="user-navbar">
        <div class="user-nav">
            <a href="#" class="nav-brand">Perpustakaan Digital</a>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Kembali ke Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="edit-form">
            <h1>Edit Buku</h1>
            
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form action="edit_buku.php?id=<?php echo $id; ?>" method="post">
                <div class="form-group">
                    <label for="judul">Judul:</label>
                    <input type="text" class="form-control" id="judul" name="judul" value="<?php echo $buku['judul']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="pengarang">Pengarang:</label>
                    <input type="text" class="form-control" id="pengarang" name="pengarang" value="<?php echo $buku['pengarang']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kategori_id">Kategori:</label>
                    <select class="form-control" id="kategori_id" name="kategori_id" required>
                        <?php while ($kategori = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo $kategori['id']; ?>" <?php echo ($buku['kategori_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                                <?php echo $kategori['nama']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" class="form-control" id="jumlah" name="jumlah" value="<?php echo $buku['jumlah']; ?>" required min="0">
                </div>
                
                <button type="submit" class="btn btn-primary">Perbarui Buku</button>
            </form>
        </div>
    </div>
</body>
</html>