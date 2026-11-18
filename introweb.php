<?php
// CẤU HÌNH ĐƯỜNG DẪN TỚI FILE LOGIN CỦA BẠN TẠI ĐÂY
$login_url = 'login.php'; // Đổi tên này nếu file login của bạn tên khác
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống POS - Quản lý bán hàng</title>
    <style>
        /* --- CSS VARIABLE & RESET --- */
        :root {
            --primary: #2563eb; /* Xanh dương hiện đại */
            --secondary: #1e40af;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --bg-gradient: linear-gradient(135deg, #f0f9ff 0%, #cbebff 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }

        body {
            height: 100vh;
            background: var(--bg-gradient);
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Tránh thanh cuộn nếu không cần thiết */
        }

        /* --- HEADER (THANH TRÊN CÙNG) --- */
        header {
            display: flex;
            justify-content: space-between; /* Logo trái, Nút phải */
            align-items: center;
            padding: 20px 40px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px); /* Hiệu ứng kính mờ */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Nút đăng nhập góc phải */
        .btn-login-corner {
            text-decoration: none;
            background-color: var(--primary);
            color: var(--white);
            padding: 10px 24px;
            border-radius: 50px; /* Bo tròn mềm mại */
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .btn-login-corner:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        /* --- PHẦN NỘI DUNG CHÍNH (HERO) --- */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            margin-top: 60px; /* Bù trừ cho header cố định */
        }

        .hero-content {
            max-width: 800px;
            animation: fadeIn Up 0.8s ease-out;
        }

        h1 {
            font-size: 3.5rem;
            color: var(--text-dark);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        h1 span {
            color: var(--primary);
            position: relative;
        }
        
        /* Gạch chân dưới chữ POS */
        h1 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(37, 99, 235, 0.2);
            z-index: -1;
        }

        p.subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Hình minh họa đơn giản bằng CSS (Hình tròn trang trí) */
        .circle-decor {
            position: absolute;
            border-radius: 50%;
            z-index: -1;
            opacity: 0.5;
        }
        .c1 { width: 300px; height: 300px; background: #bfdbfe; top: -50px; left: -100px; filter: blur(80px); }
        .c2 { width: 400px; height: 400px; background: #e0e7ff; bottom: -100px; right: -50px; filter: blur(80px); }

        /* Animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive cho mobile */
        @media (max-width: 768px) {
            h1 { font-size: 2.5rem; }
            header { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <div class="circle-decor c1"></div>
    <div class="circle-decor c2"></div>

    <header>
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            QuickPOS
        </div>
        
        <a href="<?php echo $login_url; ?>" class="btn-login-corner">
            Đăng nhập hệ thống →
        </a>
    </header>

    <main>
        <div class="hero-content">
            <h1>Giải pháp thanh toán <br> <span>Dễ dàng & Tốc độ</span></h1>
            <p class="subtitle">
                Hệ thống quản lý bán hàng tối giản. Giúp bạn tập trung vào khách hàng, chúng tôi lo phần số liệu.
            </p>
            
            <div style="margin-top: 30px;">
                <img src="https://cdn-icons-png.flaticon.com/512/2942/2942862.png" alt="POS Illustration" style="width: 150px; opacity: 0.9;">
            </div>
        </div>
    </main>

    <footer style="position: absolute; bottom: 10px; width: 100%; text-align: center; color: #9ca3af; font-size: 0.8rem;">
        &copy; <?php echo date("Y"); ?> QuickPOS System <br>
      <h2> Hotline: 0896624322</h2>
    </footer>

</body>
</html>