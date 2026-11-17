<?php
require '../includes/db.php'; // Kết nối CSDL và các hàm hệ thống
guard('uv'); // Kiểm tra quyền ứng viên

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chk(); // Kiểm tra CSRF token bảo mật

    $ma_ung_vien = $_SESSION['u']['ma_uv']; // Hoặc lấy từ session ứng viên đang đăng nhập
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');

    if (!empty($ho_ten)) {
        // Cập nhật thông tin vào bảng UNG_VIEN
        $stmt = $pdo->prepare("UPDATE UNG_VIEN SET ho_ten = ?, so_dien_thoai = ?, ngay_sinh = ? WHERE ma_ung_vien = ?");
        $stmt->execute([$ho_ten, $so_dien_thoai, $ngay_sinh, $ma_ung_vien]);

        // Ghi nhật ký hệ thống (nếu hệ thống có hàm log_act)
        if (function_exists('log_act')) {
            log_act('UNG_VIEN', $ma_ung_vien, 'CAP_NHAT_THONG_TIN');
        }

        // Chuyển hướng về trang thông tin cá nhân kèm thông báo thành công
        header("Location: ho-so.php");
        exit;
    } else {
        // Báo lỗi nếu thiếu thông tin bắt buộc
        header('Location: sua-ho-so.php?error=empty');
        exit();
    }
} else {
    // Nếu truy cập trực tiếp bằng URL thì đá về trang chủ ứng viên
    header('Location: index.php');
    exit();
}