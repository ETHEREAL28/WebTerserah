<?php
session_start();

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: customer/home.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TERSERAHMART</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="iconify logo-icon" data-icon="mdi:store" data-width="60"></span>
                <h1>TERSERAHMART</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <form id="login-form" class="login-form">
                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:account"></span>
                        Username
                    </label>
                    <input type="text" name="username" id="username" required placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:lock"></span>
                        Password
                    </label>
                    <input type="password" name="password" id="password" required placeholder="Masukkan password">
                </div>

                <button type="submit" class="btn-login">
                    <span class="iconify" data-icon="mdi:login"></span>
                    Login
                </button>
            </form>

            <div class="login-footer">
                <p>Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            try {
                const response = await fetch('api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    successDiv.textContent = 'Login berhasil! Redirecting...';
                    successDiv.style.display = 'block';
                    
                    setTimeout(() => {
                        if (result.data.role === 'admin') {
                            window.location.href = 'admin/dashboard.php';
                        } else {
                            window.location.href = 'customer/home.php';
                        }
                    }, 1000);
                } else {
                    errorDiv.textContent = result.message;
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan sistem!';
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>