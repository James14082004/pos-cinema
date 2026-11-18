<?php
session_start();

// --- DỮ LIỆU NHÂN VIÊN (MOCK DATA) ---
// Tên đăng nhập => Mật khẩu
$users = [
    'tuan'    => '1111'     // Tài khoản nhân viên Tuấn
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra đăng nhập
    if (isset($users[$username]) && $users[$username] === $password) {
        // Đăng nhập thành công -> Lưu session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;
        
        // Chuyển hướng sang trang POS 
        header('Location: room.php'); 
        exit;
    } else {
        $error = 'Sai tên đăng nhập hoặc mật khẩu!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #22252b; /* Màu nền giống POS */
            color: #fff;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: #2e323d;
            padding: 40px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            text-align: center;
            border-top: 4px solid #26a69a; /* Màu xanh đặc trưng */
        }

        .logo-area {
            margin-bottom: 30px;
        }
        .logo-area i {
            font-size: 50px;
            color: #26a69a;
            margin-bottom: 10px;
        }
        .logo-area h2 { margin: 0; font-weight: bold; letter-spacing: 1px; }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #aaa;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        .form-input {
            width: 100%;
            padding: 12px 15px 12px 40px; /* Chừa chỗ cho icon */
            background: #1a1c23;
            border: 1px solid #3f4452;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.2s;
        }
        .form-input:focus {
            border-color: #26a69a;
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #26a69a;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #2bbbad;
        }

        .error-msg {
            background: rgba(211, 47, 47, 0.2);
            color: #ff5252;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid rgba(211, 47, 47, 0.5);
        }
        
        .hint {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-area">
            <i class="fa fa-cash-register"></i>
            <h2>POS</h2>
            <p style="color: #888; font-size: 13px;">Hệ thống quản lý bán hàng</p>
        </div>

        <?php if($error): ?>
            <div class="error-msg"><i class="fa fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Tên đăng nhập</label>
                <div class="input-wrapper">
                    <i class="fa fa-user"></i>
                    <input type="text" name="username" class="form-input" placeholder="admin" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu</label>
                <div class="input-wrapper">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-input" placeholder="••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
        </form>

        <div class="hint">
            Demo User: <b>tuan</b> / Pass: <b>1111</b>
        </div>
    </div>
<script>
    document.addEventListener('keydown', function(event) { 
        if (event.key === 'Escape') { 
            event.preventDefault(); 
            window.location.href = 'introweb.php';
        }
        });
</script>
</body>
</html>