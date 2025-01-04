<?php
require_once 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {
        // Cek username sudah digunakan atau belum
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Username sudah digunakan";
        } else {
            // Cek email sudah digunakan atau belum
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email sudah digunakan";
            } else {
                // Hash password dan simpan user baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user'; // Default role
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                
                if ($stmt->execute()) {
                    $success = "Registrasi berhasil! Silakan login.";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Terjadi kesalahan. Silakan coba lagi.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Perpustakaan Digital</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Daftar Akun</h1>
                <p>Silakan lengkapi data diri Anda</p>
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

            <form method="POST" action="register.php" class="login-form">
                <div class="input-group">
                    <input type="text" 
                           name="username" 
                           placeholder="Username" 
                           required 
                           autocomplete="username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fas fa-user"></i>
                </div>

                <div class="input-group">
                    <input type="email" 
                           name="email" 
                           placeholder="Email" 
                           required 
                           autocomplete="email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="input-group">
                    <input type="password" 
                           name="password" 
                           placeholder="Password" 
                           required 
                           autocomplete="new-password">
                    <i class="fas fa-lock"></i>
                </div>

                <div class="input-group">
                    <input type="password" 
                           name="confirm_password" 
                           placeholder="Konfirmasi Password" 
                           required 
                           autocomplete="new-password">
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="login-button">
                    Daftar
                    <i class="fas fa-user-plus"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>Sudah punya akun? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <script>
    // Tambahkan validasi realtime untuk password match
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    const form = document.querySelector('form');

    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Password tidak cocok');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.onchange = validatePassword;
    confirmPassword.onkeyup = validatePassword;
    </script>
</body>
</html>