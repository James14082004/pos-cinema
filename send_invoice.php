<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

header('Content-Type: application/json');

// Reject GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Read JSON
$data = json_decode(file_get_contents('php://input'));

if (!$data || !isset($data->customer->email)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu email khách hàng.']);
    exit;
}

$customerName = htmlspecialchars($data->customer->name);
$customerEmail = $data->customer->email;

$total = number_format($data->total, 0, ',', '.');
$given = number_format($data->given, 0, ',', '.');
$change = number_format($data->change, 0, ',', '.');
$roomName = htmlspecialchars($data->roomName);
$staffName = htmlspecialchars($data->user);
$date = date('d/m/Y H:i:s');

// Build HTML
$htmlContent = "<h2>Hóa đơn điện tử</h2>";

// Đọc template
$template = file_get_contents('invoice_template.html');

// Tạo danh sách món hàng
$itemsHTML = "";
foreach ($data->items as $item) {
    $name = htmlspecialchars($item->name);
    $qty = $item->qty;
    $price = number_format($item->price, 0, ',', '.');
    $discount = $item->discount . '%';
    $totalLine = number_format($item->price * $item->qty * (1 - $item->discount / 100), 0, ',', '.');

    $itemsHTML .= "
        <tr>
            <td>$name</td>
            <td>$qty</td>
            <td class='text-right'>$price ₫</td>
            <td class='text-right'>$discount</td>
            <td class='text-right'>$totalLine ₫</td>
        </tr>
    ";
}

// Thay dữ liệu vào template
$invoiceHTML = str_replace(
    [
        '{{customerName}}',
        '{{shopName}}',
        '{{roomName}}',
        '{{staffName}}',
        '{{date}}',
        '{{given}}',
        '{{change}}',
        '{{total}}',
        '{{itemsHere}}'
    ],
    [
        $customerName,
        SENDER_NAME,
        $roomName,
        $staffName,
        $date,
        number_format($data->given, 0, ',', '.'),
        number_format($data->change, 0, ',', '.'),
        number_format($data->total, 0, ',', '.'),
        $itemsHTML
    ],
    $template
);


$mail = new PHPMailer(true);

try {
    if (USE_SMTP) {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;

        // Apply config.php correctly
        if (SMTP_SECURE === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->Port = SMTP_PORT;  // <-- FIX: dùng giá trị config
    }

    $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
    $mail->addAddress($customerEmail, $customerName);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Hóa đơn điện tử từ " . SENDER_NAME;
    $mail->Body = $invoiceHTML;
    $mail->AltBody = strip_tags($invoiceHTML);


    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Email đã được gửi thành công.']);
} 
catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $mail->ErrorInfo
    ]);
    exit;
}

