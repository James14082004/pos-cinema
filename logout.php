<?php
session_start();
session_destroy(); // Hủy toàn bộ session
header('Location: introweb.php'); // Quay về trang đăng nhập
exit;
?>