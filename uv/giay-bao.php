<?php $base='../';$role='uv';$active='giay-bao.php';require '../includes/db.php';guard('uv');$uv=$_SESSION['u']['ma_uv'];
$r=q("SELECT g.han_xac_nhan,n.ma_nguyen_vong,n.ma_chuong_trinh,h.ma_ho_so,h.trang_thai hs,d.ten_nganh,c.ten_chuong_trinh,u.ho_ten FROM GIAY_BAO_TRUNG_TUYEN g JOIN NGUYEN_VONG n ON n.ma_nguyen_vong=g.ma_nguyen_vong JOIN HO_SO_TUYEN_SINH h ON h.ma_ho_so=n.ma_ho_so JOIN NGANH_DAO_TAO d ON d.ma_nganh=n.ma_nganh LEFT JOIN CHUONG_TRINH_DAO_TAO c ON c.ma_chuong_trinh=n.ma_chuong_trinh JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien WHERE h.ma_ung_vien=? AND n.trang_thai='TRUNG_TUYEN' LIMIT 1",[$uv])->fetch();
$ok=$r&&$r['hs']==='TRUNG_TUYEN'&&$r['han_xac_nhan']>=date('Y-m-d');
if($ok&&$_SERVER['REQUEST_METHOD']==='POST'){chk();$pdo->beginTransaction();
 if($_POST['act']==='ok'){$n=q("SELECT COUNT(*)+1 FROM HOC_VIEN WHERE ma_hoc_vien LIKE ?",['HV'.date('Y').'%'])->fetchColumn();$ma='HV'.date('Y').sprintf('%03d',$n);
  q("INSERT INTO HOC_VIEN(ma_hoc_vien,ma_tai_khoan,ma_ung_vien,ma_chuong_trinh,ho_ten,email,so_dien_thoai,ngay_sinh,ngay_nhap_hoc) SELECT ?,ma_tai_khoan,ma_ung_vien,?,ho_ten,email,so_dien_thoai,ngay_sinh,CURDATE() FROM UNG_VIEN WHERE ma_ung_vien=?",[$ma,$r['ma_chuong_trinh'],$uv]);
  q("UPDATE HO_SO_TUYEN_SINH SET trang_thai='DA_XAC_NHAN_NHAP_HOC' WHERE ma_ho_so=?",[$r['ma_ho_so']]);
  q("UPDATE TAI_KHOAN SET vai_tro='HOC_VIEN' WHERE ma_tai_khoan=?",[$_SESSION['u']['ma_tai_khoan']]);
  q("INSERT INTO THONG_BAO(ma_tai_khoan,tieu_de,noi_dung,ngay_gui) VALUES(?,?,?,NOW())",[$_SESSION['u']['ma_tai_khoan'],'Nhập học thành công','Bạn đã trở thành học viên, mã số '.$ma.'.']);
  $pdo->commit();log_act('HOC_VIEN',$ma,'XAC_NHAN_NHAP_HOC');session_destroy();header('Location: ../dang-nhap.php?m=1');exit;}
 q("UPDATE HO_SO_TUYEN_SINH SET trang_thai='KHONG_TRUNG_TUYEN' WHERE ma_ho_so=?",[$r['ma_ho_so']]);q("UPDATE NGUYEN_VONG SET trang_thai='KHONG_TRUNG_TUYEN' WHERE ma_nguyen_vong=?",[$r['ma_nguyen_vong']]);
 $pdo->commit();log_act('HO_SO_TUYEN_SINH',$r['ma_ho_so'],'TU_CHOI_NHAP_HOC');header('Location: giay-bao.php');exit;}
$title='Giấy báo trúng tuyển';include '../includes/header.php';
if(!$r): ?><div class="card">Bạn chưa có giấy báo trúng tuyển.</div><?php else: ?>
<div class="letter"><p style="text-align:center"><b>TRƯỜNG ĐẠI HỌC AN GIANG</b><br>Phòng Sau đại học</p><h1>GIẤY BÁO TRÚNG TUYỂN</h1>
<p>Kính gửi: <b><?= e($r['ho_ten']) ?></b></p><p>Nhà trường thông báo bạn đã trúng tuyển vào ngành <b><?= e($r['ten_nganh']) ?></b>, <?= e($r['ten_chuong_trinh']) ?>.</p>
<p>Vui lòng xác nhận nhập học trước ngày <b><?= date('d/m/Y',strtotime($r['han_xac_nhan'])) ?></b>.</p>
<?php if($ok): ?><form method="post" style="text-align:center;margin-top:28px"><?= hid() ?><button name="act" value="ok" class="btn ok">Xác nhận nhập học</button> <button name="act" value="no" class="btn bad" onclick="return confirm('Từ chối nhập học?')">Từ chối nhập học</button></form>
<?php else: ?><p style="text-align:center"><?= badge($r['hs']) ?> Hết hạn hoặc đã xử lý, không thể xác nhận.</p><?php endif; ?></div><?php endif; include '../includes/footer.php'; ?>
