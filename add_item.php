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
    $price = filter_var($_POST['price'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $category = isset($_POST['cat']) ? trim($_POST['cat']) : '';

    if (empty($name)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Lỗi: Tên món không được để trống.'];
        header('Location: menu.php');
        exit;
    }
    if ($price === false) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Lỗi: Giá phải là một số nguyên hợp lệ.'];
        header('Location: menu.php');
        exit;
    }
    if (empty($category)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Lỗi: Vui lòng chọn một loại.'];
        header('Location: menu.php');
        exit;
    }

    $category_exists = false;
    foreach ($categories as $cat) {
        if ($cat['id'] === $category) {
            $category_exists = true;
            break;
        }
    }

    if (!$category_exists) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Lỗi: Loại được chọn không hợp lệ.'
        ];
        header('Location: menu.php');
        exit;
    }

    $sanitized_name = htmlspecialchars($name);

    // --- ADD NEW PRODUCT ---
    $newId = 0;
    if (!empty($products)) {
        foreach ($products as $product) {
            if ($product['id'] > $newId) {
                $newId = $product['id'];
            }
        }
    }
    $newId++;

    $border_color = '#ccc'; // Default color
    foreach ($categories as $cat) {
        if ($cat['id'] === $category) {
            $border_color = $cat['bg'];
            break;
        }
    }

    $newProduct = [
        'id' => $newId,
        'cat' => $category,
        'name' => $sanitized_name,
        'price' => $price,
        'type' => 'text', 
        'border' => $border_color,
    ];

    $products[] = $newProduct;

    $result = write_menu_data_robust($categories, $products);

    if ($result['success']) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Đã thêm món "' . $sanitized_name . '" thành công!'
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

header('Location: menu.php');
exit;
?>
