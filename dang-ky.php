<?>
<?php 
require 'includes/db.php';
$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chk(); // Kiểm tra CSRF token
    
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sdt = trim($_POST['sdt'] ?? '');
    $password = $_POST['password'] ?? '';
    $re_password = $_POST['re_password'] ?? '';

    if (empty($ho_ten) || empty($email) || empty($password)) {
        $err = 'Vui lòng điền đầy đủ các thông tin bắt buộc.';
    } elseif ($password !== $re_password) {
        $err = 'Mật khẩu nhập lại không khớp.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Email không hợp lệ.';
    } else {
        // 1. Kiểm tra xem email đã được đăng ký làm username chưa
        $check = q("SELECT ma_tai_khoan FROM TAI_KHOAN WHERE username = ?", [$email])->fetch();
        if ($check) {
            $err = 'Email này đã được đăng ký tài khoản trong hệ thống.';
        } else {
            // 2. Tự sinh mã tài khoản tiếp theo (Ví dụ: TK19 -> TK20)
            $last_tk = q("SELECT ma_tai_khoan FROM TAI_KHOAN ORDER BY CAST(SUBSTRING(ma_tai_khoan, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
            $next_num = $last_tk ? (int)substr($last_tk['ma_tai_khoan'], 2) + 1 : 1;
            $ma_tai_khoan = 'TK' . str_pad($next_num, 2, '0', STR_PAD_LEFT);

            // 3. Tự sinh mã ứng viên tiếp theo (Ví dụ: UV07 -> UV08)
            $last_uv = q("SELECT ma_ung_vien FROM UNG_VIEN ORDER BY CAST(SUBSTRING(ma_ung_vien, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
            $next_uv_num = $last_uv ? (int)substr($last_uv['ma_ung_vien'], 2) + 1 : 1;
            $ma_ung_vien = 'UV' . str_pad($next_uv_num, 2, '0', STR_PAD_LEFT);

            // 4. Mã hóa mật khẩu an toàn bằng bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // 5. Thêm tài khoản mới bằng hàm q() có sẵn trong hệ thống
            q("INSERT INTO TAI_KHOAN (ma_tai_khoan, username, mat_khau_ma_hoa, vai_tro, trang_thai) VALUES (?, ?, ?, 'UNG_VIEN', 'HOAT_DONG')", 
                [$ma_tai_khoan, $email, $hashed_password]);
            
            // 6. Thêm thông tin vào bảng UNG_VIEN
            q("INSERT INTO UNG_VIEN (ma_ung_vien, ma_tai_khoan, ho_ten, email, so_dien_thoai, ngay_dang_ky) VALUES (?, ?, ?, ?, ?, NOW())", 
                [$ma_ung_vien, $ma_tai_khoan, $ho_ten, $email, $sdt]);

            // Đăng ký thành công, chuyển hướng về trang đăng nhập
            header('Location: dang-nhap.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng ký - Tuyển sinh Sau đại học</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth">
 <div class="hero">
    <h1>Tạo tài khoản ứng viên</h1>
    <p>Sau khi đăng ký, bạn có thể dùng chính email để đăng nhập và bắt đầu lập hồ sơ.</p>
 </div>
 <div class="box">
  <h1>Đăng ký</h1><br>
  
  <?php if($err): ?>
   <div class="note" style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"><?= e($err) ?></div>
  <?php endif; ?>

  <form method="post" class="form">
   <?= hid() ?>
   <div><label>Họ và tên</label><input type="text" name="ho_ten" value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>" required></div>
   <div><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></div>
   <div><label>Số điện thoại</label><input type="text" name="sdt" value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>"></div>
   <div><label>Mật khẩu</label><input type="password" name="password" required></div>
   <div><label>Nhập lại mật khẩu</label><input type="password" name="re_password" required></div>
   <button type="submit" class="btn" style="width:100%;text-align:center;border:none;cursor:pointer">Đăng ký</button>
  </form>
  <p class="hint">Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập</a></p>
 </div>
</div>
</body>
</html>