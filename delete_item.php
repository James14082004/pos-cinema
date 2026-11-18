<?php
session_start();

function write_menu_data_robust($categories, $products) {
    if (!is_writable('menu_data.php')) {
        return ['success' => false, 'message' => 'Lỗi: Không có quyền ghi vào file menu_data.php.'];
    }

    $content = "<?php\n\n";

    // Build categories string
    $cat_lines = [];
    foreach ($categories as $cat) {
        $cat_lines[] = "    ['id' => '" . addslashes($cat['id']) . "', 'name' => '" . addslashes($cat['name']) . "', 'bg' => '" . addslashes($cat['bg']) . "']";
    }
    $content .= "\$categories = [\n" . implode(",\n", $cat_lines) . "\n];\n\n";

    // Build products string
    $prod_lines = [];
    $products = array_values($products); // Re-index
    foreach ($products as $prod) {
        $line = "    ['id' => " . $prod['id'] . ", 'cat' => '" . addslashes($prod['cat']) . "', 'name' => '" . addslashes($prod['name']) . "', 'price' => " . $prod['price'] . ", 'type' => '" . addslashes($prod['type']) . "', 'border' => '" . addslashes($prod['border']) . "'";
        if (isset($prod['duration'])) {
            $line .= ", 'duration' => " . $prod['duration'];
        }
        $line .= "]";
        $prod_lines[] = $line;
    }
    $content .= "\$products = [\n" . implode(",\n", $prod_lines) . "\n];\n\n";

    $content .= "?>";

    if (file_put_contents('menu_data.php', $content) === false) {
        return ['success' => false, 'message' => 'Lỗi: Không thể ghi vào file menu_data.php.'];
    }

    return ['success' => true];
}

if (isset($_GET['id'])) {
    include 'menu_data.php';

    $itemIdToDelete = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $itemNameToDelete = 'Không xác định';
    $found = false;

    if ($itemIdToDelete !== false && !empty($products)) {
        $updatedProducts = [];
        foreach ($products as $product) {
            if ($product['id'] === $itemIdToDelete) {
                $found = true;
                $itemNameToDelete = htmlspecialchars($product['name']);
                continue; // Skip this item
            }
            $updatedProducts[] = $product;
        }

        if ($found) {
            $result = write_menu_data_robust($categories, $updatedProducts);
            if ($result['success']) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã xóa món "' . $itemNameToDelete . '" thành công!'
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
                'text' => 'Không tìm thấy món để xóa.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'ID món không hợp lệ.'
        ];
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Yêu cầu không hợp lệ.'
    ];
}

header('Location: menu.php');
exit;
?>
