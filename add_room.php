<?php
session_start();

// Function to write data to data.php
function write_data($data) {
    if (!is_writable('data.php')) {
        return ['success' => false, 'message' => 'Lỗi nghiêm trọng: Không có quyền ghi vào file data.php. Vui lòng kiểm tra quyền của file trên server.'];
    }
    $dataContent = "<?php\n// In a real application, this would come from a database.\n\$rooms = " . var_export($data, true) . ";\n?>";
    if (file_put_contents('data.php', $dataContent) === false) {
        return ['success' => false, 'message' => 'Đã xảy ra lỗi khi ghi dữ liệu vào file.'];
    }
    return ['success' => true];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'data.php';

    // --- VALIDATION AND SANITIZATION ---
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (empty($name) || empty($type) || $price === false) {
        // If validation fails, store an error message and redirect
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Dữ liệu không hợp lệ. Vui lòng thử lại.'
        ];
        header('Location: room.php');
        exit;
    }

    // Sanitize for output
    $sanitized_name = htmlspecialchars($name);
    $sanitized_type = htmlspecialchars($type);

    // --- ADD NEW ROOM ---
    // Get the highest existing ID and add 1 to create a new unique ID
    $newId = 0;
    if (!empty($rooms)) {
        foreach ($rooms as $room) {
            if ($room['id'] > $newId) {
                $newId = $room['id'];
            }
        }
    }
    $newId++;

    $newRoom = [
        'id' => $newId,
        'name' => $sanitized_name,
        'type' => $sanitized_type,
        'status' => 'empty', // Default status
        'price' => $price
    ];

    $rooms[] = $newRoom;

    // Write the updated array back to the data file
    $result = write_data($rooms);

    if ($result['success']) {
        // Store a success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Đã thêm phòng "' . $sanitized_name . '" thành công!'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => $result['message']
        ];
    }

} else {
    // If not a POST request, just redirect
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Yêu cầu không hợp lệ.'
    ];
}

header('Location: room.php');
exit;
?>

