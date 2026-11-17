<?php require 'includes/db.php';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){chk();
 $u=q("SELECT t.*,COALESCE(n.ho_ten,c.ho_ten,v.ho_ten,h.ho_ten) ho_ten,n.ma_nhan_vien ma_nv,c.ma_nguoi_duyet ma_nq,v.ma_ung_vien ma_uv,h.ma_hoc_vien ma_hv FROM TAI_KHOAN t LEFT JOIN NHAN_VIEN_PDT n ON n.ma_tai_khoan=t.ma_tai_khoan LEFT JOIN NGUOI_CO_THAM_QUYEN c ON c.ma_tai_khoan=t.ma_tai_khoan LEFT JOIN UNG_VIEN v ON v.ma_tai_khoan=t.ma_tai_khoan LEFT JOIN HOC_VIEN h ON h.ma_tai_khoan=t.ma_tai_khoan WHERE t.username=?",[trim($_POST['username'])])->fetch();
 if(!$u||!password_verify($_POST['password'],$u['mat_khau_ma_hoa']))$err='Sai tên đăng nhập hoặc mật khẩu.';
 elseif($u['trang_thai']!=='HOAT_DONG')$err=$u['trang_thai']==='BI_KHOA'?'Tài khoản đã bị khóa.':'Tài khoản chưa kích hoạt. Hãy kiểm tra email.';
 else{session_regenerate_id(true);unset($u['mat_khau_ma_hoa']);$_SESSION['u']=$u;
  $go=['uv'=>'uv/ho-so.php','hv'=>'hv/ho-so.php','pdt'=>'pdt/dashboard.php','ct'=>'ct/phe-duyet.php'];header('Location: '.$go[$ROLE[$u['vai_tro']]]);exit;}}
?><!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập - Tuyển sinh Sau đại học</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="auth"><div class="hero"><h1>Tuyển sinh Thạc sĩ và Tiến sĩ trực tuyến</h1><p>Nộp hồ sơ, theo dõi kết quả xét tuyển và xác nhận nhập học tại một nơi.</p></div>
<div class="box"><h1>Đăng nhập</h1><p class="sub">Dùng tài khoản đã đăng ký với nhà trường.</p><br>
<?php if(isset($_GET['m'])): ?><div class="note">Nhập học thành công. Hãy đăng nhập lại để vào cổng học viên.</div><?php endif; ?>
<?php if($err): ?><div class="note" style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"><?= e($err) ?></div><?php endif; ?>
<form method="post" class="form"><?= hid() ?>
<div><label>Tên đăng nhập</label><input name="username" required></div>
<div><label>Mật khẩu</label><input type="password" name="password" required></div>
<button class="btn">Đăng nhập</button></form>
<p class="hint"><a href="index.php">Về trang chủ</a></p>