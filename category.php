<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['username'];

// Include the menu data
include 'menu_data.php';

// Get any message from the session
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục - POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Roboto', sans-serif; margin: 0; }
        .header { background: #fff; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header-title { color: #007bff; font-weight: bold; font-size: 20px; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 35px; height: 35px; background: #00aefd; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .container { padding: 20px; }
        .toolbar { background: #fff; padding: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border: 1px solid #e0e0e0; }
        .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .category-card { background: #fff; border-radius: 8px; padding: 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: relative; }
        .category-card .delete-btn { position: absolute; top: 10px; right: 10px; background: #ff4d4d; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 12px; }
        .category-card h3 { margin: 0; font-size: 16px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 30px; border: 1px solid #888; width: 90%; max-width: 450px; border-radius: 10px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .modal h2 { margin-top: 0; }
        .modal form label { display: block; margin-bottom: 8px; font-weight: 500; }
        .modal form input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        .modal form input[type="submit"] { background-color: #28a745; color: white; border: none; cursor: pointer; }
        .notification { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; }
        .notification.success { background-color: #28a745; }
        .notification.error { background-color: #dc3545; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-title"><i class="fa fa-tags"></i> Quản lý Danh mục</div>
        <div class="user-info">
            <a href="index.php" style="text-decoration: none; color: #333; font-weight: 500;">POS</a>
            <span style="margin: 0 10px;">|</span>
            <a href="room.php" style="text-decoration: none; color: #333; font-weight: 500;">Phòng</a>
            <span style="margin: 0 10px;">|</span>
            <a href="menu.php" style="text-decoration: none; color: #333; font-weight: 500;">Menu</a>
            <span style="margin: 0 10px;">|</span>
            <span><?= htmlspecialchars($currentUser) ?></span>
            <div class="avatar" title="<?= htmlspecialchars($currentUser) ?>"><?= strtoupper(substr($currentUser, 0, 1)) ?></div>
            <a href="logout.php" style="color: #d32f2f; font-size: 1.2em;" title="Đăng xuất"><i class="fa fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="notification <?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <div style="font-weight: bold;"><i class="fa fa-list"></i> Danh sách danh mục</div>
            <button id="addCategoryBtn" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-plus"></i> Thêm danh mục mới
            </button>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $category): if ($category['id'] == 'all') continue; ?>
                <div class="category-card" style="border-left: 5px solid <?= $category['bg'] ?>;">
                    <a href="delete_category.php?id=<?= $category['id'] ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này không?');" title="Xóa danh mục"><i class="fa fa-trash"></i></a>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div id="addCategoryModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fa fa-plus-circle"></i> Thêm danh mục mới</h2>
        <form action="add_category.php" method="post">
            <label for="name">Tên danh mục:</label>
            <input type="text" id="name" name="name" placeholder="Ví dụ: Đồ ăn vặt" required>
            
            <label for="bg">Màu sắc:</label>
            <input type="color" id="bg" name="bg" value="#ffcc80" required>
            
            <input type="submit" value="Thêm danh mục">
        </form>
      </div>
    </div>

<script>
    var modal = document.getElementById("addCategoryModal");
    var btn = document.getElementById("addCategoryBtn");
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
      modal.style.display = "block";
    }
    span.onclick = function() {
      modal.style.display = "none";
    }
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
</script>
</body>
</html>
