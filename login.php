<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if (isLoggedIn()) {
    redirectBasedOnRole();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                $success = "Login berhasil! Mengalihkan...";
                redirectBasedOnRole();
            } else {
                $error = "Password salah";
            }
        } else {
            $error = "Username tidak ditemukan";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Perpustakaan Digital</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="login-alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="login-alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">
                <div class="input-group">
                    <input type="text" 
                           name="username" 
                           placeholder="Username" 
                           required 
                           autocomplete="username">
                    <i class="fas fa-user"></i>
                </div>

                <div class="input-group">
                    <input type="password" 
                           name="password" 
                           placeholder="Password" 
                           required 
                           autocomplete="current-password">
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="login-button">
                    Masuk
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>Belum punya akun? <a href="register.php">Daftar</a></p>
            </div>
        </div>
    </div>
</body>
</html>