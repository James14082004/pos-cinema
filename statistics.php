<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['username'];

// Đặt múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy danh sách hóa đơn đã hoàn thành từ session
$invoices = $_SESSION['completed_invoices'] ?? [];

// Lấy giá trị tìm kiếm
$search_date = $_GET['date'] ?? date('Y-m-d');
$search_room = isset($_GET['room']) ? trim($_GET['room']) : '';

// Lọc hóa đơn và tính tổng
$filtered_total = 0;
$start_timestamp = strtotime($search_date . ' midnight');
$end_timestamp = strtotime($search_date . ' tomorrow midnight') - 1;
$display_date = date('d/m/Y', $start_timestamp);

$filtered_invoice_keys = [];
if (is_array($invoices)) {
    foreach ($invoices as $key => $invoice) {
        $is_date_match = isset($invoice['timestamp']) && $invoice['timestamp'] >= $start_timestamp && $invoice['timestamp'] <= $end_timestamp;
        $is_room_match = !empty($search_room) ? (stripos($invoice['roomName'], $search_room) !== false) : true;

        if ($is_date_match && $is_room_match) {
            $filtered_invoice_keys[] = $key;
            $filtered_total += $invoice['total'] ?? 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê doanh thu - POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Roboto', sans-serif; margin: 0; color: #333; }
        .header { background: #fff; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header-title { color: #ffc107; font-weight: bold; font-size: 20px; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 35px; height: 35px; background: #00aefd; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .container { padding: 20px; max-width: 1200px; margin: auto; }
        .stat-card { background: #fff; padding: 25px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e0e0e0; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card h2 { margin: 0; font-size: 36px; color: #007bff; }
        .stat-card p { margin: 5px 0 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .invoice-table { width: 100%; background: #fff; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .invoice-table th, .invoice-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .invoice-table th { background: #f8f9fa; font-weight: 500; }
        .invoice-table tr:last-child td { border-bottom: none; }
        .invoice-table td.total-cell { text-align: right; font-weight: bold; color: #c82333; }
        .empty-state { text-align: center; padding: 50px; background: #fff; border-radius: 8px; border: 1px solid #e0e0e0; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-back { background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 500; transition: background-color 0.2s; }
        .btn-back:hover { background: #5a6268; }
        .invoice-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .invoice-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        /* Search Toolbar */
        .search-toolbar { background: #fff; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e0e0e0; }
        .search-toolbar form { display: flex; align-items: center; gap: 15px; }
        .search-toolbar input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .search-toolbar button { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; background: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title"><i class="fa fa-chart-bar"></i> Thống Kê</div>
        <div class="user-info">
            <span><?= htmlspecialchars($currentUser) ?></span>
            <div class="avatar" title="<?= htmlspecialchars($currentUser) ?>"><?= strtoupper(substr($currentUser, 0, 1)) ?></div>
            <a href="logout.php" style="color: #d32f2f; font-size: 1.2em;" title="Đăng xuất"><i class="fa fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="container">
        <div class="toolbar">
             <a href="room.php" class="btn-back"><i class="fa fa-arrow-left"></i> Quay lại Danh sách phòng</a>
             <div style="font-size: 18px;">Ngày: <strong><?= $display_date ?></strong></div>
        </div>

        <!-- Search Form -->
        <div class="search-toolbar">
            <form action="statistics.php" method="GET">
                <input type="date" name="date" value="<?= htmlspecialchars($search_date) ?>">
                <input type="text" name="room" placeholder="Tên phòng..." value="<?= htmlspecialchars($search_room) ?>">
                <button type="submit"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>
       
        <div class="stat-card">
            <p>Tổng doanh thu</p>
            <h2><?= number_format($filtered_total) ?> ₫</h2>
        </div>

        <h3>Chi tiết hóa đơn</h3>

        <?php if (empty($filtered_invoice_keys)): ?>
            <div class="empty-state">
                <i class="fa fa-receipt fa-3x" style="color: #ccc;"></i>
                <p style="margin-top: 15px; font-size: 16px;">Không có hóa đơn nào phù hợp với tìm kiếm.</p>
            </div>
        <?php else: ?>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Thời gian</th>
                        <th>Phòng</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th class="total-cell">Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $reversed_keys = array_reverse($filtered_invoice_keys);
                    foreach ($reversed_keys as $display_index => $original_key):
                        $invoice = $invoices[$original_key];
                    ?>
                    <tr onclick="window.location='invoice_detail.php?id=<?= $original_key ?>';">
                        <td><?= count($reversed_keys) - $display_index ?></td>
                        <td><?= date('H:i:s', $invoice['timestamp']) ?></td>
                        <td><?= htmlspecialchars($invoice['roomName']) ?></td>
                        <td><?= htmlspecialchars($invoice['customer']['name']) ?></td>
                        <td><?= htmlspecialchars($invoice['user']) ?></td>
                        <td class="total-cell"><?= number_format($invoice['total']) ?> ₫</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
