<?php
session_start();

// Function to write data to data.php
function write_data($data) {
    if (!is_writable('data.php')) {
        return ['success' => false, 'message' => 'Lỗi nghiêm trọng: Không có quyền ghi vào file data.php. Vui lòng kiểm tra quyền của file trên server.'];
    }
    // Re-index the array to prevent issues with non-consecutive keys after deletion
    $reindexed_data = array_values($data);
    $dataContent = "<?php\n// In a real application, this would come from a database.\n\$rooms = " . var_export($reindexed_data, true) . ";\n?>";
    if (file_put_contents('data.php', $dataContent) === false) {
        return ['success' => false, 'message' => 'Đã xảy ra lỗi khi ghi dữ liệu vào file.'];
    }
    return ['success' => true];
}

if (isset($_GET['id'])) {
    include 'data.php';

    $roomIdToDelete = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $roomNameToDelete = 'Không xác định';
    $found = false;

    if ($roomIdToDelete !== false && !empty($rooms)) {
        // Keep only the rooms that do not match the ID to delete
        $updatedRooms = [];
        foreach ($rooms as $room) {
            if ($room['id'] === $roomIdToDelete) {
                $found = true;
                $roomNameToDelete = htmlspecialchars($room['name']);
                // Don't add this room to the new array
                continue;
            }
            $updatedRooms[] = $room;
        }

        if ($found) {
            // Write the updated array back to the data file
            $result = write_data($updatedRooms);
            if ($result['success']) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã xóa phòng "' . $roomNameToDelete . '" thành công!'
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => $result['message']
                ];
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Không tìm thấy phòng để xóa.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'ID phòng không hợp lệ.'
        ];
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Yêu cầu không hợp lệ.'
    ];
}

header('Location: room.php');
exit;
?>