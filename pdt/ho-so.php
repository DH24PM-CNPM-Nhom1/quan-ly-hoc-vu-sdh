<?php $base='../';$role='pdt';$active='ho-so.php';$title='Hồ sơ tuyển sinh';$sub='Tìm kiếm và lọc hồ sơ';include '../includes/header.php';
$st=isset($_GET['st'])?$_GET['st']:'';$k=isset($_GET['k'])?trim($_GET['k']):'';
$rows=q("SELECT h.*,u.ho_ten,g.ten_nganh FROM HO_SO_TUYEN_SINH h JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien LEFT JOIN NGUYEN_VONG n ON n.ma_ho_so=h.ma_ho_so AND n.thu_tu=1 LEFT JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh WHERE (?='' OR h.trang_thai=?) AND (?='' OR u.ho_ten LIKE ? OR h.ma_ho_so LIKE ?) ORDER BY h.ngay_nop DESC",[$st,$st,$k,"%$k%","%$k%"])->fetchAll();
$ops=['CHO_DUYET','CAN_BO_SUNG','DA_DUYET','TRUNG_TUYEN','KHONG_TRUNG_TUYEN','DA_XAC_NHAN_NHAP_HOC']; ?>
<div class="card"><form class="filter" method="get"><select name="st"><option value="">Mọi trạng thái</option><?php foreach($ops as $o): ?><option value="<?= $o ?>" <?= $st===$o?'selected':'' ?>><?= strip_tags(badge($o)) ?></option><?php endforeach; ?></select>
<input name="k" value="<?= e($k) ?>" placeholder="Tên hoặc mã hồ sơ"><button class="btn">Lọc</button></form>
<div class="wrap"><table><tr><th>Mã</th><th>Ứng viên</th><th>Ngành ưu tiên 1</th><th>Ngày nộp</th><th>Trạng thái</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td><?= e($r['ma_ho_so']) ?></td><td><?= e($r['ho_ten']) ?></td><td><?= e($r['ten_nganh']) ?></td><td><?= e($r['ngay_nop']) ?></td><td><?= badge($r['trang_thai']) ?></td><td><a href="ho-so-chi-tiet.php?id=<?= e($r['ma_ho_so']) ?>">Xem</a></td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="6">Không có hồ sơ phù hợp.</td></tr><?php endif; ?></table></div></div>
<?php include '../includes/footer.php'; ?>
