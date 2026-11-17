<?php $base='../';$role='pdt';$active='ho-so.php';require '../includes/db.php';guard('pdt');
$id=isset($_GET['id'])?$_GET['id']:'';$nv=$_SESSION['u']['ma_nv'];
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 if(isset($_POST['mc'])){q("UPDATE MINH_CHUNG SET trang_thai=?,ma_nhan_vien_kiem_tra=?,ngay_kiem_tra=NOW() WHERE ma_minh_chung=? AND ma_ho_so=?",[$_POST['kq']==='ok'?'DA_XAC_THUC_THU_CONG':'KHONG_HOP_LE',$nv,$_POST['mc'],$id]);log_act('MINH_CHUNG',$_POST['mc'],$_POST['kq']);}
 else{$m=['duyet'=>'DA_DUYET','bo_sung'=>'CAN_BO_SUNG','tu_choi'=>'KHONG_TRUNG_TUYEN'];$a=$_POST['act'];
  if(isset($m[$a])){q("UPDATE HO_SO_TUYEN_SINH SET trang_thai=?,ma_nhan_vien_duyet=?,ngay_duyet=NOW(),ghi_chu=? WHERE ma_ho_so=? AND trang_thai IN ('CHO_DUYET','CAN_BO_SUNG')",[$m[$a],$nv,$_POST['gc'],$id]);log_act('HO_SO_TUYEN_SINH',$id,$m[$a]);}}
 header('Location: ho-so-chi-tiet.php?id='.urlencode($id));exit;}
$h=q("SELECT h.*,u.ho_ten,u.email,u.so_dien_thoai,u.ngay_sinh,d.ten_dot FROM HO_SO_TUYEN_SINH h JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien JOIN DOT_TUYEN_SINH d ON d.ma_dot=h.ma_dot WHERE h.ma_ho_so=?",[$id])->fetch();
if(!$h){http_response_code(404);exit('Không tìm thấy hồ sơ');}
$mc=q("SELECT * FROM MINH_CHUNG WHERE ma_ho_so=?",[$id])->fetchAll();
$nvg=q("SELECT n.thu_tu,g.ten_nganh FROM NGUYEN_VONG n JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh WHERE n.ma_ho_so=? ORDER BY n.thu_tu",[$id])->fetchAll();
$title='Hồ sơ '.$h['ma_ho_so'].' - '.$h['ho_ten'];$sub='<a href="ho-so.php">&larr; Quay lại danh sách</a>';include '../includes/header.php';
$mo=in_array($h['trang_thai'],['CHO_DUYET','CAN_BO_SUNG']); ?>
<div class="cols"><div class="card"><h2>Thông tin ứng viên</h2><dl class="kv"><dt>Họ và tên</dt><dd><?= e($h['ho_ten']) ?></dd><dt>Ngày sinh</dt><dd><?= e($h['ngay_sinh']) ?></dd><dt>Email</dt><dd><?= e($h['email']) ?></dd><dt>Điện thoại</dt><dd><?= e($h['so_dien_thoai']) ?></dd><dt>Đợt</dt><dd><?= e($h['ten_dot']) ?></dd><dt>Trạng thái</dt><dd><?= badge($h['trang_thai']) ?></dd>
<?php foreach($nvg as $n): ?><dt>Nguyện vọng <?= (int)$n['thu_tu'] ?></dt><dd><?= e($n['ten_nganh']) ?></dd><?php endforeach; ?></dl></div>
<div class="card">
    <h2>Minh chứng</h2>
    <?php foreach($mc as $m): ?>
    <form method="post" class="file" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
        <?= hid() ?>
        <input type="hidden" name="mc" value="<?= e($m['ma_minh_chung']) ?>">
        <div>
            <!-- Tên file kèm link mở xem trực tiếp trong tab mới -->
            <a href="<?= e($base . $m['duong_dan_file']) ?>" target="_blank" title="Bấm để xem file minh chứng" style="font-weight: bold; text-decoration: underline;">
                <?= e($m['ten_file']) ?> 👁️
            </a>
            <br>
            <?= badge($m['trang_thai']) ?> 
            <small style="color: gray;">(<?= e($m['dinh_dang_file']) ?> - <?= e($m['kich_thuoc']) ?> MB)</small>
        </div>
        <div>
            <?php if($mo): ?>
                <button name="kq" value="ok" class="btn ok sm">Hợp lệ</button> 
                <button name="kq" value="bad" class="btn bad sm">Không hợp lệ</button>
            <?php endif; ?>
        </div>
    </form>
    <?php endforeach; ?>
</div>
<?php if($mo): ?><form method="post" class="card"><?= hid() ?><h2>Quyết định</h2><label>Ghi chú / lý do</label><input name="gc" value="<?= e($h['ghi_chu']) ?>"><p><button name="act" value="duyet" class="btn ok">Duyệt hồ sơ</button> <button name="act" value="bo_sung" class="btn ghost">Yêu cầu bổ sung</button> <button name="act" value="tu_choi" class="btn bad" onclick="return confirm('Từ chối hồ sơ này?')">Từ chối</button></p></form><?php endif; ?>
<?php include '../includes/footer.php'; ?>
