<?php
$base = '../';
$role = 'uv';
$active = 'nguyen-vong.php';
$title = 'Nguyện vọng';
$sub = 'Sắp xếp theo thứ tự ưu tiên 1, 2, 3';
include '../includes/header.php'; // Trong header.php đã có kết nối $pdo và hàm q(), session

// 1. LẤY ĐÚNG ỨNG VIÊN ĐANG ĐĂNG NHẬP (Không gán cứng UV03 nữa)
$ma_tai_khoan = $_SESSION['u']['ma_tai_khoan'] ?? '';
$uv = q("SELECT ma_ung_vien FROM UNG_VIEN WHERE ma_tai_khoan = ?", [$ma_tai_khoan])->fetch();
$ma_ung_vien = $uv['ma_ung_vien'] ?? '';

if (empty($ma_ung_vien)) {
    echo "<div class='card'><p style='color:red;'>Không tìm thấy thông tin ứng viên. Vui lòng cập nhật hồ sơ trước!</p></div>";
    include '../includes/footer.php';
    exit;
}

// 2. LẤY HỒ SƠ CỦA ỨNG VIÊN TRONG ĐỢT ĐANG MỞ (Tự tạo mới nếu chưa có)
$ho_so = q("SELECT hs.ma_ho_so FROM HO_SO_TUYEN_SINH hs 
            JOIN DOT_TUYEN_SINH dt ON hs.ma_dot = dt.ma_dot 
            WHERE hs.ma_ung_vien = ? AND dt.trang_thai = 'DANG_MO' LIMIT 1", [$ma_ung_vien])->fetch();

if (!$ho_so) {
    $dot_mo = q("SELECT ma_dot FROM DOT_TUYEN_SINH WHERE trang_thai = 'DANG_MO' LIMIT 1")->fetch();
    $ma_dot = $dot_mo['ma_dot'] ?? 'DOT2026_1';

    $last_hs = q("SELECT ma_ho_so FROM HO_SO_TUYEN_SINH ORDER BY CAST(SUBSTRING(ma_ho_so, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
    $next_num = $last_hs ? (int)substr($last_hs['ma_ho_so'], 2) + 1 : 1;
    $ma_ho_so = 'HS' . str_pad($next_num, 2, '0', STR_PAD_LEFT);

    q("INSERT INTO HO_SO_TUYEN_SINH (ma_ho_so, ma_ung_vien, ma_dot, trang_thai, ngay_tao) VALUES (?, ?, ?, 'NHAP', NOW())", 
      [$ma_ho_so, $ma_ung_vien, $ma_dot]);
} else {
    $ma_ho_so = $ho_so['ma_ho_so'];
}

$error = '';
$success = '';

// --- 3. XỬ LÝ MỞ KHÓA NGUYỆN VỌNG (Đưa trạng thái về lại CHO_XET để sửa/xóa) ---
if (isset($_GET['action']) && $_GET['action'] === 'mo_khoa' && isset($_GET['id'])) {
    $ma_nv_mk = $_GET['id'];
    q("UPDATE NGUYEN_VONG SET trang_thai = 'CHO_XET' WHERE ma_nguyen_vong = ? AND ma_ho_so = ?", [$ma_nv_mk, $ma_ho_so]);
    $success = "Đã mở khóa nguyện vọng! Bạn có thể đổi ngành hoặc xóa.";
}

// --- 4. XỬ LÝ ĐỔI / CẬP NHẬT NGUYỆN VỌNG ---
if (isset($_POST['btn_cap_nhat'])) {
    $ma_nv_sua = $_POST['ma_nguyen_vong'] ?? '';
    $ma_nganh_moi = trim($_POST['ma_nganh'] ?? '');
    $thu_tu_moi = (int)($_POST['thu_tu'] ?? 0);

    if (!empty($ma_nv_sua) && !empty($ma_nganh_moi) && $thu_tu_moi > 0) {
        // Kiểm tra trùng thứ tự hoặc trùng ngành với các nguyện vọng KHÁC trong cùng hồ sơ
        $check = q("SELECT * FROM NGUYEN_VONG WHERE ma_ho_so = ? AND ma_nguyen_vong != ? AND (thu_tu = ? OR ma_nganh = ?)", 
                   [$ma_ho_so, $ma_nv_sua, $thu_tu_moi, $ma_nganh_moi])->fetch();

        if ($check) {
            $error = "Thứ tự ưu tiên số $thu_tu_moi hoặc Ngành này đã bị trùng với một nguyện vọng khác của bạn!";
        } else {
            $ct = q("SELECT ma_chuong_trinh FROM CHUONG_TRINH_DAO_TAO WHERE ma_nganh = ? LIMIT 1", [$ma_nganh_moi])->fetch();
            $ma_chuong_trinh = $ct ? $ct['ma_chuong_trinh'] : null;

            q("UPDATE NGUYEN_VONG SET ma_nganh = ?, ma_chuong_trinh = ?, thu_tu = ?, trang_thai = 'CHO_XET' WHERE ma_nguyen_vong = ? AND ma_ho_so = ?", 
              [$ma_nganh_moi, $ma_chuong_trinh, $thu_tu_moi, $ma_nv_sua, $ma_ho_so]);
            $success = "Đổi nguyện vọng thành công!";
        }
    }
}

// --- 5. XỬ LÝ THÊM NGUYỆN VỌNG MỚI ---
if (isset($_POST['btn_them'])) {
    $ma_nganh = trim($_POST['ma_nganh'] ?? '');
    $thu_tu = (int)($_POST['thu_tu'] ?? 0);

    if (!empty($ma_nganh) && $thu_tu > 0) {
        $ct = q("SELECT ma_chuong_trinh FROM CHUONG_TRINH_DAO_TAO WHERE ma_nganh = ? LIMIT 1", [$ma_nganh])->fetch();
        $ma_chuong_trinh = $ct ? $ct['ma_chuong_trinh'] : null;

        $check = q("SELECT * FROM NGUYEN_VONG WHERE ma_ho_so = ? AND (thu_tu = ? OR ma_nganh = ?)", [$ma_ho_so, $thu_tu, $ma_nganh])->fetch();
        
        if ($check) {
            $error = "Thứ tự ưu tiên số $thu_tu hoặc Ngành này đã có trong danh sách nguyện vọng của bạn!";
        } else {
            $last_nv = q("SELECT ma_nguyen_vong FROM NGUYEN_VONG ORDER BY CAST(SUBSTRING(ma_nguyen_vong, 3) AS UNSIGNED) DESC LIMIT 1")->fetch();
            $next_nv = $last_nv ? (int)substr($last_nv['ma_nguyen_vong'], 2) + 1 : 1;
            $ma_nguyen_vong = 'NV' . str_pad($next_nv, 2, '0', STR_PAD_LEFT);
            
            q("INSERT INTO NGUYEN_VONG (ma_nguyen_vong, ma_ho_so, ma_nganh, ma_chuong_trinh, thu_tu, trang_thai) VALUES (?, ?, ?, ?, ?, 'CHO_XET')", 
              [$ma_nguyen_vong, $ma_ho_so, $ma_nganh, $ma_chuong_trinh, $thu_tu]);
            $success = "Thêm nguyện vọng thành công!";
        }
    } else {
        $error = "Vui lòng chọn đầy đủ thông tin ngành và thứ tự ưu tiên!";
    }
}

// --- 6. XỬ LÝ XÓA NGUYỆN VỌNG ---
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $ma_nv_xoa = $_GET['id'];
    q("DELETE FROM NGUYEN_VONG WHERE ma_nguyen_vong = ? AND ma_ho_so = ?", [$ma_nv_xoa, $ma_ho_so]);
    header("Location: nguyen-vong.php");
    exit();
}

// --- 7. LẤY DANH SÁCH NGUYỆN VỌNG & NGÀNH ĐÀO TẠO ---
$danh_sach_nv = q("
    SELECT nv.*, nd.ten_nganh, ct.ten_chuong_trinh 
    FROM NGUYEN_VONG nv 
    JOIN NGANH_DAO_TAO nd ON nv.ma_nganh = nd.ma_nganh 
    LEFT JOIN CHUONG_TRINH_DAO_TAO ct ON nv.ma_chuong_trinh = ct.ma_chuong_trinh 
    WHERE nv.ma_ho_so = ? 
    ORDER BY nv.thu_tu ASC
", [$ma_ho_so])->fetchAll();

$tat_ca_nganh = q("SELECT * FROM NGANH_DAO_TAO")->fetchAll();
$id_dang_sua = ($_GET['action'] ?? '') === 'sua' ? ($_GET['id'] ?? '') : '';
?>

<div class="card wrap">
    <h2>Danh sách nguyện vọng đã đăng ký (Hồ sơ: <?= htmlspecialchars($ma_ho_so) ?>)</h2>
    
    <?php if (!empty($success)): ?>
        <div class="note" style="border-color:var(--ok);background:#E6F4EA;color:var(--ok);margin-bottom:15px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="note" style="border-color:var(--bad);background:#FDE7E5;color:var(--bad);margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Thứ tự</th>
            <th>Ngành đào tạo</th>
            <th>Chương trình</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
        <?php if (count($danh_sach_nv) > 0): ?>
            <?php foreach ($danh_sach_nv as $nv): ?>
                <?php if ($id_dang_sua === $nv['ma_nguyen_vong']): ?>
                    <!-- Dòng chế độ sửa trực tiếp nguyện vọng -->
                    <tr style="background: #f8fafc;">
                        <form method="POST" action="nguyen-vong.php">
                            <td>
                                <input type="hidden" name="ma_nguyen_vong" value="<?= htmlspecialchars($nv['ma_nguyen_vong']) ?>">
                                <select name="thu_tu" required style="padding: 6px;">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= $nv['thu_tu'] == $i ? 'selected' : '' ?>>Ưu tiên <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td colspan="2">
                                <select name="ma_nganh" required style="width: 100%; padding: 6px;">
                                    <?php foreach ($tat_ca_nganh as $ng): ?>
                                        <option value="<?= $ng['ma_nganh'] ?>" <?= $nv['ma_nganh'] == $ng['ma_nganh'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ng['ten_nganh']) ?> (<?= htmlspecialchars($ng['bac_dao_tao']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><span class="badge b-warn">Đang sửa</span></td>
                            <td>
                                <button type="submit" name="btn_cap_nhat" class="btn" style="padding: 4px 10px; font-size: 13px;">Lưu</button>
                                <a href="nguyen-vong.php" class="btn ghost" style="padding: 4px 10px; font-size: 13px;">Hủy</a>
                            </td>
                        </form>
                    </tr>
                <?php else: ?>
                    <!-- Dòng hiển thị bình thường -->
                    <tr>
                        <td><strong><?= $nv['thu_tu'] ?></strong></td>
                        <td><?= htmlspecialchars($nv['ten_nganh']) ?></td>
                        <td><?= htmlspecialchars($nv['ten_chuong_trinh'] ?? 'Chương trình chuẩn') ?></td>
                        <td>
                            <?php 
                                $badge_class = 'b-warn';
                                if ($nv['trang_thai'] == 'TRUNG_TUYEN') $badge_class = 'b-ok';
                                elseif ($nv['trang_thai'] == 'KHONG_TRUNG_TUYEN') $badge_class = 'b-bad';
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($nv['trang_thai']) ?></span>
                        </td>
                        <td>
                            <?php if ($nv['trang_thai'] == 'CHO_XET' || empty($nv['trang_thai'])): ?>
                                <a href="nguyen-vong.php?action=sua&id=<?= $nv['ma_nguyen_vong'] ?>" style="margin-right: 10px; font-weight: bold;">Đổi nguyện vọng</a>
                                <a href="nguyen-vong.php?action=xoa&id=<?= $nv['ma_nguyen_vong'] ?>" style="color: var(--bad);" onclick="return confirm('Bạn có chắc chắn muốn xóa nguyện vọng này?')">Xóa</a>
                            <?php else: ?>
                                <span style="color: gray; margin-right: 8px;">Đã khóa</span>
                                <a href="nguyen-vong.php?action=mo_khoa&id=<?= $nv['ma_nguyen_vong'] ?>" class="btn ghost" style="padding: 2px 8px; font-size: 12px;" onclick="return confirm('Bạn muốn mở khóa để chỉnh sửa lại nguyện vọng này?')">🔓 Mở khóa</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align: center;">Bạn chưa đăng ký nguyện vọng nào.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h2>Thêm nguyện vọng mới</h2>
    <form method="POST" action="nguyen-vong.php">
        <div class="form">
            <div>
                <label>Ngành đào tạo</label>
                <select name="ma_nganh" required>
                    <option value="">-- Chọn ngành đào tạo --</option>
                    <?php foreach ($tat_ca_nganh as $ng): ?>
                        <option value="<?= $ng['ma_nganh'] ?>"><?= htmlspecialchars($ng['ten_nganh']) ?> (<?= htmlspecialchars($ng['bac_dao_tao']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Thứ tự ưu tiên</label>
                <select name="thu_tu" required>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>">Ưu tiên số <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <p style="margin-top: 15px;">
            <button type="submit" name="btn_them" class="btn">Thêm nguyện vọng</button>
        </p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>