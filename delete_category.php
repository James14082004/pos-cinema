<?php
session_start();

function write_menu_data_robust($categories, $products) {
    if (!is_writable('menu_data.php')) {
        return ['success' => false, 'message' => 'Lỗi: Không có quyền ghi vào file menu_data.php.'];
    }

    $content = "<?php\n\n";

    // Build categories string
    $cat_lines = [];
    $categories = array_values($categories); // Re-index
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

    $categoryIdToDelete = $_GET['id'];
    $categoryNameToDelete = 'Không xác định';
    $found = false;

    // Prevent deleting 'all' category
    if ($categoryIdToDelete === 'all') {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Không thể xóa danh mục mặc định.'
        ];
        header('Location: category.php');
        exit;
    }

    // Check if any product is using this category
    $is_used = false;
    foreach ($products as $product) {
        if ($product['cat'] === $categoryIdToDelete) {
            $is_used = true;
            break;
        }
    }

    if ($is_used) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Không thể xóa danh mục đang được sử dụng bởi một sản phẩm.'
        ];
        header('Location: category.php');
        exit;
    }


    if (!empty($categories)) {
        $updatedCategories = [];
        foreach ($categories as $category) {
            if ($category['id'] === $categoryIdToDelete) {
                $found = true;
                $categoryNameToDelete = htmlspecialchars($category['name']);
                continue; // Skip this item
            }
            $updatedCategories[] = $category;
        }

        if ($found) {
            $result = write_menu_data_robust($updatedCategories, $products);
            if ($result['success']) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Đã xóa danh mục "' . $categoryNameToDelete . '" thành công!'
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
                'text' => 'Không tìm thấy danh mục để xóa.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'ID danh mục không hợp lệ.'
        ];
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Yêu cầu không hợp lệ.'
    ];
}

header('Location: category.php');
exit;
?>
