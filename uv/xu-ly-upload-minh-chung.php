<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chk(); // Kiểm tra CSRF token

    $ma_ung_vien = $_POST['ma_ung_vien'] ?? '';
    $loai = $_POST['loai'] ?? 'KHAC';

    if (empty($ma_ung_vien)) {
        header('Location: ho-so.php?err=' . urlencode('Lỗi thông tin ứng viên.'));
        exit;
    }

    // 1. Kiểm tra xem ứng viên đã có hồ sơ tuyển sinh trong đợt hiện tại chưa (Ví dụ: DOT2026_1)
    $ma_dot = 'DOT2026_1';
    $ho_so = q("SELECT ma_ho_so, trang_thai FROM HO_SO_TUYEN_SINH WHERE ma_ung_vien = ? AND ma_dot = ?", [$ma_ung_vien, $ma_dot])->fetch();

    if (!$ho_so) {
        // Tự sinh mã hồ sơ tiếp theo (VD: HS07, HS08...)
        $last_hs = q("SELECT ma_ho_so FROM HO_SO_TUYEN_SINH ORDER BY CAST(SUBSTRING(ma_ho_so, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
        $next_num = $last_hs ? (int)substr($last_hs['ma_ho_so'], 2) + 1 : 1;
        $ma_ho_so = 'HS' . str_pad($next_num, 2, '0', STR_PAD_LEFT);

        // Tạo mới hồ sơ và để trạng thái CHO_DUYET luôn khi tải minh chứng
        q("INSERT INTO HO_SO_TUYEN_SINH (ma_ho_so, ma_ung_vien, ma_dot, trang_thai, ngay_tao) VALUES (?, ?, ?, 'CHO_DUYET', NOW())", 
            [$ma_ho_so, $ma_ung_vien, $ma_dot]);
    } else {
        $ma_ho_so = $ho_so['ma_ho_so'];
    }

    // 2. Xử lý upload file
    if (isset($_FILES['file_minh_chung']) && $_FILES['file_minh_chung']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_minh_chung']['tmp_name'];
        $file_name = $_FILES['file_minh_chung']['name'];
        $file_size = $_FILES['file_minh_chung']['size']; // tính bằng bytes
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Kiểm tra định dạng cho phép
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_exts)) {
            header('Location: ho-so.php?err=' . urlencode('Chỉ chấp nhận file định dạng PDF, JPG, JPEG hoặc PNG.'));
            exit;
        }

        // Kiểm tra dung lượng (tối đa 10 MB = 10 * 1024 * 1024 bytes)
        $max_size = 10 * 1024 * 1024;
        if ($file_size > $max_size) {
            header('Location: ho-so.php?err=' . urlencode('Dung lượng file vượt quá giới hạn 10 MB.'));
            exit;
        }

        // Tạo thư mục lưu trữ nếu chưa có (Thư mục: ../uploads/2026/11/)
        $upload_dir = '../uploads/' . date('Y/m') . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Đổi tên file để tránh trùng lặp (dùng timestamp)
        $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file_name);
        $dest_path = $upload_dir . $new_file_name;
        $duong_dan_db = 'uploads/' . date('Y/m') . '/' . $new_file_name; // Lưu vào DB đường dẫn tương đối

        if (move_uploaded_file($file_tmp, $dest_path)) {
            // Chuyển đổi dung lượng ra MB (làm tròn 2 chữ số thập phân)
            $kich_thuoc_mb = round($file_size / (1024 * 1024), 2);

            // Tự sinh mã minh chứng tiếp theo (VD: MC09, MC10...)
            $last_mc = q("SELECT ma_minh_chung FROM MINH_CHUNG ORDER BY CAST(SUBSTRING(ma_minh_chung, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
            $next_mc_num = $last_mc ? (int)substr($last_mc['ma_minh_chung'], 2) + 1 : 1;
            $ma_minh_chung = 'MC' . str_pad($next_mc_num, 2, '0', STR_PAD_LEFT);

            // 3. Lưu thông tin minh chứng vào cơ sở dữ liệu
            q("INSERT INTO MINH_CHUNG (ma_minh_chung, ma_ho_so, loai, ten_file, duong_dan_file, dinh_dang_file, kich_thuoc, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, 'CHUA_XAC_THUC')",
                [$ma_minh_chung, $ma_ho_so, $loai, $file_name, $duong_dan_db, strtoupper($file_ext), $kich_thuoc_mb]);

            // 4. QUAN TRỌNG: Chuyển trạng thái hồ sơ từ 'NHAP' sang 'CHO_DUYET' để Phòng Đào tạo xác thực
            q("UPDATE HO_SO_TUYEN_SINH SET trang_thai = 'CHO_DUYET' WHERE ma_ho_so = ? AND trang_thai = 'NHAP'", [$ma_ho_so]);

            header('Location: ho-so.php?success=1');
            exit;
        } else {
            header('Location: ho-so.php?err=' . urlencode('Không thể di chuyển file tải lên. Vui lòng thử lại.'));
            exit;
        }
    } else {
        header('Location: ho-so.php?err=' . urlencode('Vui lòng chọn file minh chứng hợp lệ.'));
        exit;
    }
} else {
    header('Location: ho-so.php');
    exit;
}