<?php
session_start();
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
    <title>Register - TERSERAHMART</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="iconify logo-icon" data-icon="mdi:store" data-width="60"></span>
                <h1>TERSERAHMART</h1>
                <p>Daftar akun baru</p>
            </div>

            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <form id="register-form" class="login-form">
                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:account"></span>
                        Username *
                    </label>
                    <input type="text" name="username" required placeholder="Masukkan username">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:email"></span>
                        Email *
                    </label>
                    <input type="email" name="email" required placeholder="Masukkan email">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:account-circle"></span>
                        Nama Lengkap *
                    </label>
                    <input type="text" name="full_name" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:lock"></span>
                        Password *
                    </label>
                    <input type="password" name="password" required placeholder="Masukkan password" minlength="6">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:phone"></span>
                        No. Telepon
                    </label>
                    <input type="text" name="phone" placeholder="Masukkan no. telepon (opsional)">
                </div>

                <div class="form-group">
                    <label>
                        <span class="iconify" data-icon="mdi:map-marker"></span>
                        Alamat
                    </label>
                    <textarea name="address" rows="3" placeholder="Masukkan alamat (opsional)"></textarea>
                </div>

                <button type="submit" class="btn-login">
                    <span class="iconify" data-icon="mdi:account-plus"></span>
                    Daftar Sekarang
                </button>
            </form>

            <div class="login-footer">
                <p>Sudah punya akun? <a href="index.php">Login Sekarang</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            try {
                const response = await fetch('api/auth/register.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    successDiv.textContent = result.message + ' Redirecting ke login...';
                    successDiv.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
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