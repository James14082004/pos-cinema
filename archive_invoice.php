<?php
session_start();

// Security check
if (!isset($_SESSION['user_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data !== null) {
    // Initialize array if it doesn't exist
    if (!isset($_SESSION['completed_invoices'])) {
        $_SESSION['completed_invoices'] = [];
    }

    // Add a server-side timestamp for accuracy
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $data['timestamp'] = time();

    // Add invoice to session
    $_SESSION['completed_invoices'][] = $data;

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Invoice archived.']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
}
?>
