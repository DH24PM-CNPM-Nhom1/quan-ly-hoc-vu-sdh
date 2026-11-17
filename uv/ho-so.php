<?php 
$base='../';
$role='uv';
$active='ho-so.php';
$title='Hồ sơ của tôi';
$sub='Đợt 1 - Tuyển sinh Chính quy 2026';
include '../includes/header.php'; 
$ma_tai_khoan = $_SESSION['u']['ma_tai_khoan'] ?? '';

$uv = q("SELECT u.*, t.username FROM UNG_VIEN u JOIN TAI_KHOAN t ON u.ma_tai_khoan = t.ma_tai_khoan WHERE u.ma_tai_khoan = ?", [$ma_tai_khoan])->fetch();

if (!$uv) {
    $uv = ['ma_ung_vien' => '', 'ho_ten' => '', 'ngay_sinh' => '', 'email' => '', 'so_dien_thoai' => '', 'qua_trinh' => ''];
}

// 1. Lấy thông tin hồ sơ hiện tại
$ho_so = q("SELECT ma_ho_so, trang_thai FROM HO_SO_TUYEN_SINH WHERE ma_ung_vien = ? AND ma_dot = 'DOT2026_1'", [$uv['ma_ung_vien']])->fetch();
$trang_thai_hs = $ho_so['trang_thai'] ?? 'NHAP';

// 2. XỬ LÝ XÓA MINH CHỨNG (Nếu ứng viên bấm nút Xóa)
if (isset($_GET['action']) && $_GET['action'] === 'xoa_mc' && isset($_GET['id']) && $ho_so) {
    $ma_mc_xoa = $_GET['id'];
    
    // Kiểm tra minh chứng có thuộc hồ sơ của ứng viên này không
    $mc_can_xoa = q("SELECT * FROM MINH_CHUNG WHERE ma_minh_chung = ? AND ma_ho_so = ?", [$ma_mc_xoa, $ho_so['ma_ho_so']])->fetch();
    
    if ($mc_can_xoa) {
        // Chỉ cho phép xóa nếu chưa được xác thực hợp lệ
        if ($mc_can_xoa['trang_thai'] !== 'DA_XAC_THUC_THU_CONG') {
            // Xóa file vật lý trên ổ cứng nếu tồn tại
            $file_path = '../' . $mc_can_xoa['duong_dan_file'];
            if (!empty($mc_can_xoa['duong_dan_file']) && file_exists($file_path)) {
                @unlink($file_path);
            }
            // Xóa bản ghi trong CSDL
            q("DELETE FROM MINH_CHUNG WHERE ma_minh_chung = ? AND ma_ho_so = ?", [$ma_mc_xoa, $ho_so['ma_ho_so']]);
            header("Location: ho-so.php?del_ok=1");
            exit;
        } else {
            header("Location: ho-so.php?err=" . urlencode("Minh chứng đã được Phòng Đào tạo xác thực, không thể xóa!"));
            exit;
        }
    }
}

// 3. Lấy danh sách minh chứng để hiển thị
$danh_sach_mc = [];
if ($ho_so) {
    $danh_sach_mc = q("SELECT * FROM MINH_CHUNG WHERE ma_ho_so = ?", [$ho_so['ma_ho_so']])->fetchAll();
}
?>

<!-- Thanh tiến trình -->
<div class="steps">
    <div class="done">Tạo hồ sơ</div>
    <div class="<?= !empty($danh_sach_mc) ? 'done' : 'now' ?>">Tải minh chứng</div>
    <div class="<?= $trang_thai_hs !== 'NHAP' ? 'now' : '' ?>">Đang chờ duyệt</div>
    <div>Kết quả xét tuyển</div>
    <div>Nhập học</div>
</div>

<div class="card">
    <h2>Thông tin cá nhân <small style="font-size:14px; font-weight:normal; color:#666;">(Trạng thái: <?= htmlspecialchars($trang_thai_hs) ?>)</small></h2>
    <form method="post" action="xu-ly-cap-nhat-ho-so.php" class="form">
        <?= function_exists('hid') ? hid() : '' ?>
        <div>
            <label>Họ và tên</label>
            <input type="text" name="ho_ten" value="<?= htmlspecialchars($uv['ho_ten']) ?>" required>
        </div>
        <div>
            <label>Ngày sinh</label>
            <input type="date" name="ngay_sinh" value="<?= htmlspecialchars($uv['ngay_sinh'] ?? '') ?>" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($uv['email'] ?? $uv['username']) ?>" readonly style="background:#f9f9f9; color:#666;" title="Email dùng làm tên đăng nhập không thể thay đổi">
        </div>
        <div>
            <label>Số điện thoại</label>
            <input type="text" name="so_dien_thoai" value="<?= htmlspecialchars($uv['so_dien_thoai'] ?? '') ?>" required>
        </div>
        <div class="full">
            <label>Quá trình học tập và công tác</label>
            <textarea name="qua_trinh" rows="3"><?= htmlspecialchars($uv['qua_trinh'] ?? '') ?></textarea>
        </div>
        
        <p class="full">
            <button type="submit" name="hanh_dong" value="luu_nhap" class="btn ghost">Lưu nháp</button>
            <button type="submit" name="hanh_dong" value="gui_xac_thuc" class="btn" style="margin-left:8px;">Nộp hồ sơ xác thực</button>
        </p>
    </form>
</div>

<div class="card">
    <h2>Minh chứng</h2>

    <?php if(isset($_GET['err'])): ?>
        <div class="note" style="border-color:var(--bad);background:#FDE7E5;color:var(--bad);margin-bottom:15px;"><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['success'])): ?>
        <div class="note" style="border-color:var(--ok);background:#E6F4EA;color:var(--ok);margin-bottom:15px;">Tải lên minh chứng thành công!</div>
    <?php endif; ?>
    <?php if(isset($_GET['del_ok'])): ?>
        <div class="note" style="border-color:var(--ok);background:#E6F4EA;color:var(--ok);margin-bottom:15px;">Đã xóa minh chứng thành công!</div>
    <?php endif; ?>

    <?php if(empty($danh_sach_mc)): ?>
        <p style="color:#666; font-style:italic; margin-bottom:15px;">Chưa có minh chứng nào được tải lên.</p>
    <?php else: ?>
        <?php foreach($danh_sach_mc as $mc): ?>
            <?php 
                $badge_class = 'b-warn';
                if($mc['trang_thai'] === 'DA_XAC_THUC_THU_CONG') $badge_class = 'b-ok';
                elseif($mc['trang_thai'] === 'KHONG_HOP_LE') $badge_class = 'b-bad';
            ?>
            <div class="file" style="display:flex; justify-content:space-between; align-items:center;">
                <span>
                    <a href="../<?= htmlspecialchars($mc['duong_dan_file']) ?>" target="_blank" style="text-decoration:none; color:inherit;">
                        <?= htmlspecialchars($mc['ten_file']) ?> 
                    </a>
                    <small class="sub">(<?= $mc['kich_thuoc'] ?> MB - Loại: <?= htmlspecialchars($mc['loai']) ?>)</small>
                </span>
                
                <span style="display:flex; align-items:center; gap:10px;">
                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($mc['trang_thai']) ?></span>
                    
                    <!-- Nút Xóa minh chứng (Ẩn đi nếu minh chứng đã được Phòng Đào tạo xác thực) -->
                    <?php if($mc['trang_thai'] !== 'DA_XAC_THUC_THU_CONG'): ?>
                        <a href="ho-so.php?action=xoa_mc&id=<?= urlencode($mc['ma_minh_chung']) ?>" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa file minh chứng này không?');" 
                           style="color:#d93025; text-decoration:none; font-size:13px; font-weight:bold; padding:2px 6px; border:1px solid #f5c2c7; background:#f8d7da; border-radius:4px;">
                           ✕ Xóa
                        </a>
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form action="xu-ly-upload-minh-chung.php" method="post" enctype="multipart/form-data" style="margin-top: 20px; border-top: 1px dashed #ddd; padding-top: 15px;">
        <?= hid() ?>
        <input type="hidden" name="ma_ung_vien" value="<?= $uv['ma_ung_vien'] ?>">
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Loại minh chứng</label>
                <select name="loai" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                    <option value="VAN_BANG">Văn bằng</option>
                    <option value="BANG_DIEM">Bảng điểm</option>
                    <option value="CHUNG_CHI_NGOAI_NGU">Chứng chỉ ngoại ngữ</option>
                    <option value="KHAC">Khác</option>
                </select>
            </div>
            <div style="flex: 2; min-width: 250px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Chọn file (PDF, JPG, PNG - tối đa 10MB)</label>
                <input type="file" name="file_minh_chung" accept=".pdf, .jpg, .jpeg, .png" required style="width:100%;">
            </div>
        </div>
        <button type="submit" class="btn ghost" style="cursor:pointer;">Tải lên minh chứng</button>
    </form>
</div>