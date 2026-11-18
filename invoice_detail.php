<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Lấy ID hóa đơn từ URL và kiểm tra
$invoice_id = $_GET['id'] ?? null;
if ($invoice_id === null || !isset($_SESSION['completed_invoices'][$invoice_id])) {
    // Redirect to statistics page if invoice not found, to avoid ugly error messages.
    header('Location: statistics.php');
    exit;
}

// Lấy thông tin chi tiết hóa đơn
$invoice = $_SESSION['completed_invoices'][$invoice_id];

// Tính toán lại tổng phụ để hiển thị
$subtotal = 0;
foreach ($invoice['items'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$total_discount_amount = $subtotal - $invoice['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Hóa đơn #<?= htmlspecialchars($invoice_id) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .toolbar { max-width: 800px; margin: auto; margin-bottom: 20px; text-align: right; }
        .btn { background: #007bff; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 500; border: none; cursor: pointer; margin-left: 10px; font-size: 14px; }
        .btn-back { background: #6c757d; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #333; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; }
        .details { display: flex; justify-content: space-between; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .details div { color: #555; font-size: 14px; line-height: 1.6; }
        .items-table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; font-size: 14px; }
        .items-table th, .items-table td { padding: 8px; vertical-align: top; border-bottom: 1px solid #eee; }
        .items-table th { background: #f8f9fa; font-weight: 500; }
        .items-table .item.last td { border-bottom: none; }
        .items-table .right { text-align: right; }
        .totals { margin-top: 20px; width: 100%; font-size: 14px; }
        .totals-table { width: 50%; margin-left: auto; }
        .totals-table td { padding: 6px 8px; }
        .totals-table .label { text-align: right; color: #555; }
        .totals-table .value { text-align: right; font-weight: 500; }
        .totals-table .grand-total td { border-top: 2px solid #ddd; font-size: 1.3em; font-weight: bold; color: #c82333; }
        @media print {
            body { padding: 0; background-color: #fff; }
            .toolbar { display: none; }
            .invoice-box { box-shadow: none; border: none; margin: 0; max-width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="statistics.php" class="btn btn-back"><i class="fa fa-arrow-left"></i> Quay lại</a>
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> In hóa đơn</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <h1>Hóa đơn bán hàng</h1>
            <p>Ngày: <?= date('d/m/Y H:i:s', $invoice['timestamp']) ?></p>
        </div>

        <div class="details">
            <div>
                <strong>Khách hàng:</strong> <?= htmlspecialchars($invoice['customer']['name']) ?><br>
                <strong>Điện thoại:</strong> <?= htmlspecialchars($invoice['customer']['phone'] ?: 'N/A') ?><br>
                <strong>Email:</strong> <?= htmlspecialchars($invoice['customer']['email'] ?: 'N/A') ?>
            </div>
            <div>
                <strong>Hóa đơn #:</strong> <?= htmlspecialchars($invoice_id) ?><br>
                <strong>Phòng:</strong> <?= htmlspecialchars($invoice['roomName']) ?><br>
                <strong>Nhân viên:</strong> <?= htmlspecialchars($invoice['user']) ?>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Mặt hàng</th>
                    <th class="right">SL</th>
                    <th class="right">Đơn giá</th>
                    <th class="right">Giảm giá</th>
                    <th class="right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoice['items'] as $item): 
                    $line_total = $item['price'] * $item['qty'];
                    $final_line_total = $line_total * (1 - ($item['discount'] ?? 0) / 100);
                ?>
                <tr class="item">
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td class="right"><?= $item['qty'] ?></td>
                    <td class="right"><?= number_format($item['price']) ?></td>
                    <td class="right"><?= htmlspecialchars($item['discount'] ?? 0) ?>%</td>
                    <td class="right"><?= number_format($final_line_total) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td class="label">Cộng tiền hàng:</td>
                    <td class="value"><?= number_format($subtotal) ?></td>
                </tr>
                <tr>
                    <td class="label">Giảm giá trên hóa đơn:</td>
                    <td class="value"><?= number_format($total_discount_amount) ?></td>
                </tr>
                <tr class="grand-total">
                    <td class="label">Tổng thanh toán:</td>
                    <td class="value"><?= number_format($invoice['total']) ?> ₫</td>
                </tr>
                 <tr>
                    <td class="label">Khách đưa:</td>
                    <td class="value"><?= number_format($invoice['given']) ?></td>
                </tr>
                <tr>
                    <td class="label">Tiền thừa:</td>
                    <td class="value"><?= number_format($invoice['change']) ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
