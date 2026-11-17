<?php $base='../';$role='pdt';$active='dot-tuyen-sinh.php';require '../includes/db.php';guard('pdt');
$OPEN=['DANG_MO','DANG_DIEN_RA'];$SHUT=['DA_DONG','DA_KET_THUC'];
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$id=$_POST['id'];$tt=$_POST['tt'];$L=['CHUA_MO'=>'Chưa mở','DANG_MO'=>'Đang mở','DA_DONG'=>'Đã đóng'];
 if(isset($L[$tt])){q("UPDATE DOT_TUYEN_SINH SET trang_thai=? WHERE ma_dot=?",[$tt,$id]);log_act('DOT_TUYEN_SINH',$id,$tt);$_SESSION['f']=['ok','Đã chuyển đợt '.$id.' sang trạng thái "'.$L[$tt].'". Trang chủ đã được cập nhật.'];}
 header('Location: dot-tuyen-sinh.php');exit;}
$title='Đợt tuyển sinh';$sub='Chọn trạng thái bất kỳ trong danh sách, trang chủ tự cập nhật';include '../includes/header.php';
if(!empty($_SESSION['f'])){$f=$_SESSION['f'];unset($_SESSION['f']);echo '<div class="note"'.($f[0]==='bad'?' style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"':'').'>'.e($f[1]).'</div>';}
$r=q("SELECT d.*,(SELECT COUNT(*) FROM HO_SO_TUYEN_SINH h WHERE h.ma_dot=d.ma_dot) so_hs FROM DOT_TUYEN_SINH d ORDER BY d.nam_tuyen DESC,d.ngay_bat_dau DESC")->fetchAll(); ?>
<div class="card wrap"><table><tr><th>Mã đợt</th><th>Tên đợt</th><th>Thời gian</th><th>Hồ sơ</th><th>Trạng thái</th></tr>
<?php foreach($r as $d): $cur=in_array($d['trang_thai'],$OPEN)?'DANG_MO':(in_array($d['trang_thai'],$SHUT)?'DA_DONG':'CHUA_MO'); ?><tr><td><?= e($d['ma_dot']) ?></td><td><?= e($d['ten_dot']) ?></td><td><?= $d['ngay_bat_dau']?date('d/m/Y',strtotime($d['ngay_bat_dau'])):'' ?> - <?= $d['ngay_ket_thuc']?date('d/m/Y',strtotime($d['ngay_ket_thuc'])):'' ?></td><td><?= (int)$d['so_hs'] ?></td>
<td><form method="post" style="display:flex;gap:6px"><?= hid() ?><input type="hidden" name="id" value="<?= e($d['ma_dot']) ?>">
<select name="tt" style="width:auto" onchange="this.form.submit()"><option value="CHUA_MO" <?= $cur==='CHUA_MO'?'selected':'' ?>>Chưa mở</option><option value="DANG_MO" <?= $cur==='DANG_MO'?'selected':'' ?>>Đang mở</option><option value="DA_DONG" <?= $cur==='DA_DONG'?'selected':'' ?>>Đã đóng</option></select>
<noscript><button class="btn sm">Lưu</button></noscript></form></td></tr><?php endforeach; ?></table></div>
<?php include '../includes/footer.php'; ?>
