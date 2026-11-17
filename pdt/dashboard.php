<?php $base='../';$role='pdt';$active='dashboard.php';$title='Tổng quan';$sub='Tất cả các đợt';include '../includes/header.php';
$c=q("SELECT trang_thai,COUNT(*) n FROM HO_SO_TUYEN_SINH GROUP BY trang_thai")->fetchAll(PDO::FETCH_KEY_PAIR);$g=function($k)use($c){return isset($c[$k])?$c[$k]:0;};
$new=q("SELECT h.ma_ho_so,h.ngay_nop,h.trang_thai,u.ho_ten FROM HO_SO_TUYEN_SINH h JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien WHERE h.trang_thai IN ('CHO_DUYET','CAN_BO_SUNG') ORDER BY h.ngay_nop DESC LIMIT 5")->fetchAll(); ?>
<div class="stats"><div class="stat"><b><?= array_sum($c) ?></b><span>Tổng hồ sơ</span></div><div class="stat"><b><?= $g('CHO_DUYET') ?></b><span>Chờ duyệt</span></div><div class="stat"><b><?= $g('CAN_BO_SUNG') ?></b><span>Cần bổ sung</span></div><div class="stat"><b><?= $g('DA_DUYET')+$g('TRUNG_TUYEN')+$g('DA_XAC_NHAN_NHAP_HOC') ?></b><span>Đã duyệt trở lên</span></div></div>
<div class="card wrap"><h2>Hồ sơ cần xử lý</h2><table><tr><th>Mã</th><th>Ứng viên</th><th>Ngày nộp</th><th>Trạng thái</th></tr>
<?php foreach($new as $r): ?><tr><td><a href="ho-so-chi-tiet.php?id=<?= e($r['ma_ho_so']) ?>"><?= e($r['ma_ho_so']) ?></a></td><td><?= e($r['ho_ten']) ?></td><td><?= e($r['ngay_nop']) ?></td><td><?= badge($r['trang_thai']) ?></td></tr><?php endforeach; ?></table></div>
<?php include '../includes/footer.php'; ?>
