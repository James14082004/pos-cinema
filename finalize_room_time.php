<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $roomId = $data['roomId'] ?? null;

    if ($roomId !== null && isset($_SESSION['orders'][$roomId])) {
        // Set the end_time for the room in the session
        $_SESSION['orders'][$roomId]['end_time'] = time();
        echo json_encode(['success' => true, 'message' => 'Timer stopped successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid room ID or room not found in session.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>