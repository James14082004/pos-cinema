<?php
session_start();

// Basic security checks
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['current_room_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if ($data !== null) {
    $roomId = $_SESSION['current_room_id'];

    if (!isset($_SESSION['orders'])) {
        $_SESSION['orders'] = [];
    }

    // If cart is empty and customer is default, we can remove the order entry for this room
    if (empty($data['cart']) && (!isset($data['customer']) || $data['customer']['id'] === 0)) {
        unset($_SESSION['orders'][$roomId]);
    } else {
        $initialRentedDuration = 0;
        foreach ($data['cart'] as $item) {
            if (isset($item['cat']) && $item['cat'] === 'combo' && isset($item['duration'])) {
                $initialRentedDuration = max($initialRentedDuration, $item['duration']);
            }
        }

        $_SESSION['orders'][$roomId] = [
            'cart' => $data['cart'] ?? [],
            'customer' => $data['customer'] ?? ['id' => 0, 'name' => 'Khách lẻ', 'phone' => '', 'email' => ''],
            'initial_rented_duration' => $initialRentedDuration
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
