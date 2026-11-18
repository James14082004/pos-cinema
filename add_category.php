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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'menu_data.php';

    // --- VALIDATION AND SANITIZATION ---
    $name = trim($_POST['name']);
    $bg = trim($_POST['bg']);
    $id = strtolower(str_replace(' ', '_', $name)); // Generate an ID from the name

    // Basic validation
    if (empty($name) || empty($bg)) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Dữ liệu không hợp lệ. Vui lòng thử lại.'
        ];
        header('Location: category.php');
        exit;
    }

    $sanitized_name = htmlspecialchars($name);

    // --- ADD NEW CATEGORY ---
    $newCategory = [
        'id' => $id,
        'name' => $sanitized_name,
        'bg' => $bg,
    ];

    $categories[] = $newCategory;

    // Write the updated arrays back to the data file
    $result = write_menu_data_robust($categories, $products);

    if ($result['success']) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Đã thêm danh mục "' . $sanitized_name . '" thành công!'
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
        'text' => 'Yêu cầu không hợp lệ.'
    ];
}

header('Location: category.php');
exit;
?>
