<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

header('Content-Type: application/json');

// --- Basic Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit;
}

// --- Add Server-Side Timestamp ---
date_default_timezone_set('Asia/Ho_Chi_Minh');
$data['timestamp'] = time();

// --- Get Time-based Cost from Client-side ---
$currentRoomId = $_SESSION['current_room_id'] ?? null;
if (isset($data['time_info']) && $data['time_info']['cost'] > 0) {
    $time_item = [
        'id' => 'TIME',
        'name' => 'Tiền giờ (' . $data['time_info']['duration'] . ')',
        'price' => $data['time_info']['cost'],
        'qty' => 1,
        'discount' => 0
    ];
    // Add to the beginning of the items array for better visibility in invoice
    array_unshift($data['items'], $time_item);
}

// --- 1. Archive the Invoice ---
if (!isset($_SESSION['completed_invoices'])) {
    $_SESSION['completed_invoices'] = [];
}
$_SESSION['completed_invoices'][] = $data;
$archive_success = true;

// --- Clean up session for this room ---
if ($currentRoomId) {
    unset($_SESSION['orders'][$currentRoomId]);
    unset($_SESSION['current_room_id']);
    unset($_SESSION['current_room_name']);
}
// --- 2. Send Email if Requested ---
$email_success = null; // null: not requested, false: failed, true: success
$email_message = '';

if (isset($data['sendEmail']) && $data['sendEmail'] === true) {
    if (!isset($data['customer']['email']) || empty($data['customer']['email'])) {
        $email_success = false;
        $email_message = 'Thiếu email khách hàng.';
    } else {
        $customerName = htmlspecialchars($data['customer']['name']);
        $customerEmail = $data['customer']['email'];
        $roomName = htmlspecialchars($data['roomName']);
        $staffName = htmlspecialchars($data['user']);
        
        // Use the unified timestamp
        $date = date('d/m/Y H:i:s', $data['timestamp']);

        // Build HTML items list
        $itemsHTML = "";
        foreach ($data['items'] as $item) {
            $name = htmlspecialchars($item['name']);
            $qty = $item['qty'];
            $price = number_format($item['price'], 0, ',', '.');
            $discount = ($item['discount'] ?? 0) . '%';
            $totalLine = number_format($item['price'] * $item['qty'] * (1 - ($item['discount'] ?? 0) / 100), 0, ',', '.');
            $itemsHTML .= "<tr><td>$name</td><td>$qty</td><td class='text-right'>$price ₫</td><td class='text-right'>$discount</td><td class='text-right'>$totalLine ₫</td></tr>";
        }

        // Replace placeholders in template
        $template = file_get_contents('invoice_template.html');
        $invoiceHTML = str_replace(
            ['{{customerName}}', '{{shopName}}', '{{roomName}}', '{{staffName}}', '{{date}}', '{{given}}', '{{change}}', '{{total}}', '{{itemsHere}}'],
            [$customerName, SENDER_NAME, $roomName, $staffName, $date, number_format($data['given']), number_format($data['change']), number_format($data['total']), $itemsHTML],
            $template
        );

        $mail = new PHPMailer(true);
        try {
            if (USE_SMTP) {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = (SMTP_SECURE === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = SMTP_PORT;
            }

            $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
            $mail->addAddress($customerEmail, $customerName);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Hóa đơn điện tử từ " . SENDER_NAME;
            $mail->Body = $invoiceHTML;
            $mail->AltBody = strip_tags($invoiceHTML);

            $mail->send();
            $email_success = true;
            $email_message = 'Email đã được gửi thành công.';

        } catch (Exception $e) {
            $email_success = false;
            $email_message = "Lỗi gửi mail: " . $mail->ErrorInfo;
        }
    }
}

// --- 3. Respond to Client ---
echo json_encode([
    'success' => $archive_success,
    'email_status' => $email_success,
    'email_message' => $email_message
]);
?>
