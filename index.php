<?php require 'includes/db.php';
$dots=q("SELECT * FROM DOT_TUYEN_SINH WHERE trang_thai IN ('DANG_MO','CHUA_MO','DANG_DIEN_RA','CHUA_DIEN_RA') ORDER BY ngay_bat_dau")->fetchAll();
$ng=q("SELECT DISTINCT g.ma_nganh,g.ten_nganh,g.chi_tieu_tuyen_sinh FROM NGANH_DAO_TAO g JOIN DOT_NGANH dn ON dn.ma_nganh=g.ma_nganh JOIN DOT_TUYEN_SINH d ON d.ma_dot=dn.ma_dot WHERE d.trang_thai IN ('DANG_MO','DANG_DIEN_RA') ORDER BY g.ma_nganh")->fetchAll(); ?><!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tuyển sinh Sau đại học - Trường Đại học An Giang</title><link rel="stylesheet" href="assets/style.css"></head><body>
<header class="pub-top">
 <div class="brand">Tuyển sinh Sau đại học<small>Trường Đại học An Giang</small></div>
 <nav><a href="#dot">Đợt tuyển sinh</a><a href="#nganh">Ngành đào tạo</a><a href="#quy-trinh">Quy trình</a><a href="#ho-so">Hồ sơ</a><a href="#lien-he">Liên hệ</a></nav>
 <div class="act"><a class="btn ghost sm" href="dang-nhap.php">Đăng nhập</a><a class="btn sm" href="dang-ky.php">Đăng ký</a></div>
</header>
<section class="home-hero"><h1>Tuyển sinh Thạc sĩ và Tiến sĩ năm 2026</h1>
 <p>Nộp hồ sơ trực tuyến, theo dõi kết quả xét tuyển và xác nhận nhập học mà không cần đến trường.</p>
 <a class="btn" href="dang-ky.php">Đăng ký nộp hồ sơ</a> <a class="btn ghost" href="#quy-trinh">Xem quy trình</a></section>
<section class="sec" id="dot"><h2>Đợt tuyển sinh</h2><p class="sub">Hồ sơ chỉ được nộp trong thời gian của đợt đang mở.</p>
 <div class="grid3"><?php foreach($dots as $d): $mo=in_array($d['trang_thai'],['DANG_MO','DANG_DIEN_RA']); ?>
  <div class="card"><span class="badge <?= $mo?'b-ok':'' ?>"><?= $mo?'Đang mở':'Sắp mở' ?></span><h2 style="margin-top:10px"><?= e($d['ten_dot']) ?></h2><p class="sub"><?= date('d/m/Y',strtotime($d['ngay_bat_dau'])) ?> đến <?= date('d/m/Y',strtotime($d['ngay_ket_thuc'])) ?></p></div>
 <?php endforeach; if(!$dots): ?><div class="card">Hiện chưa có đợt tuyển sinh nào đang mở.</div><?php endif; ?></div></section>
<div class="alt"><section class="sec" id="nganh"><h2>Ngành đào tạo và chỉ tiêu</h2><p class="sub">Chỉ tiêu của các đợt đang mở.</p>
 <div class="card wrap" style="margin:0"><table><tr><th>Mã ngành</th><th>Ngành đào tạo</th><th>Chỉ tiêu</th></tr>
 <?php foreach($ng as $g): ?><tr><td><?= e($g['ma_nganh']) ?></td><td><?= e($g['ten_nganh']) ?></td><td><?= (int)$g['chi_tieu_tuyen_sinh'] ?></td></tr><?php endforeach; if(!$ng): ?><tr><td colspan="3">Chưa có ngành nào đang tuyển sinh.</td></tr><?php endif; ?></table></div></section></div>
<section class="sec" id="quy-trinh"><h2>Quy trình tuyển sinh</h2><p class="sub">Toàn bộ các bước thực hiện trực tuyến.</p>
 <ol class="proc">
  <li><b>Đăng ký tài khoản</b><br>Kích hoạt qua email.</li>
  <li><b>Tạo hồ sơ</b><br>Khai thông tin, tải minh chứng, chọn nguyện vọng.</li>
  <li><b>Nộp hồ sơ</b><br>Hệ thống kiểm tra đủ minh chứng và còn hạn đợt.</li>
  <li><b>Phòng Đào tạo xét duyệt</b><br>Kiểm tra minh chứng, duyệt hoặc yêu cầu bổ sung.</li>
  <li><b>Công bố trúng tuyển</b><br>Nhận giấy báo qua email và trên hệ thống.</li>
  <li><b>Xác nhận nhập học</b><br>Xác nhận trong hạn để trở thành học viên.</li>
 </ol></section>
<div class="alt"><section class="sec" id="ho-so"><h2>Hồ sơ cần chuẩn bị</h2>
 <div class="grid3">
  <div class="card"><h2>Văn bằng</h2><p class="sub">Bằng tốt nghiệp đại học (bản scan).</p></div>
  <div class="card"><h2>Bảng điểm</h2><p class="sub">Bảng điểm toàn khóa đại học.</p></div>
  <div class="card"><h2>Chứng chỉ ngoại ngữ</h2><p class="sub">Và các giấy tờ khác nếu có.</p></div>
 </div><p class="hint">Định dạng PDF hoặc JPEG, mỗi file tối đa 10 MB.</p></section></div>
<section class="sec" id="lien-he"><h2>Liên hệ</h2>
 <div class="card"><dl class="kv"><dt>Đơn vị</dt><dd>Phòng Sau đại học, Trường Đại học An Giang</dd><dt>Email</dt><dd>tuyensinh@truong.edu.vn</dd><dt>Điện thoại</dt><dd>0901 111 222</dd><dt>Giờ làm việc</dt><dd>Thứ Hai đến thứ Sáu, 7:30 - 16:30</dd></dl></div></section>
<footer class="pub-foot">© 2026 Trường Đại học An Giang - ĐHQG TP.HCM. Hệ thống Quản lý Tuyển sinh Sau đại học.</footer>
</body></html>
