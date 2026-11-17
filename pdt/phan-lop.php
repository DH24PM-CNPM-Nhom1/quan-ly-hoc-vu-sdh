<?php $base='../';$role='pdt';$active='phan-lop.php';require '../includes/db.php';guard('pdt');
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$hv=isset($_POST['hv'])?array_values($_POST['hv']):[];$lop=isset($_POST['lop'])?$_POST['lop']:'';
 if(!$hv)$_SESSION['f']=['bad','Hãy chọn ít nhất một học viên.'];
 else{$in=implode(',',array_fill(0,count($hv),'?'));
  $n=q("UPDATE HOC_VIEN h JOIN CHUONG_TRINH_DAO_TAO c ON c.ma_chuong_trinh=h.ma_chuong_trinh JOIN LOP_HANH_CHINH l ON l.ma_lop=? AND l.ma_nganh=c.ma_nganh SET h.ma_lop=l.ma_lop WHERE h.ma_lop IS NULL AND h.ma_hoc_vien IN ($in)",array_merge([$lop],$hv))->rowCount();
  if($n)log_act('HOC_VIEN',implode(',',$hv),'GAN_LOP '.$lop);
  $k=count($hv)-$n;$_SESSION['f']=$n?['ok','Đã gán '.$n.' học viên vào lớp '.$lop.'.'.($k?' Có '.$k.' học viên không gán được vì khác ngành của lớp.':'')]:['bad','Không gán được: học viên phải cùng ngành với lớp và chưa có lớp.'];}
 header('Location: phan-lop.php');exit;}
$title='Phân lớp học viên';$sub='Chọn học viên ở cột trái, bấm Gán vào lớp ở cột phải (chỉ gán vào lớp cùng ngành)';include '../includes/header.php';
if(!empty($_SESSION['f'])){$f=$_SESSION['f'];unset($_SESSION['f']);echo '<div class="note"'.($f[0]==='bad'?' style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"':'').'>'.e($f[1]).'</div>';}
$hv=q("SELECT h.ma_hoc_vien,h.ho_ten,g.ten_nganh FROM HOC_VIEN h JOIN CHUONG_TRINH_DAO_TAO c ON c.ma_chuong_trinh=h.ma_chuong_trinh JOIN NGANH_DAO_TAO g ON g.ma_nganh=c.ma_nganh WHERE h.ma_lop IS NULL ORDER BY h.ma_hoc_vien")->fetchAll();
$ds=[];foreach(q("SELECT ma_lop,ma_hoc_vien,ho_ten FROM HOC_VIEN WHERE ma_lop IS NOT NULL ORDER BY ma_hoc_vien")->fetchAll() as $r)$ds[$r['ma_lop']][]=$r;
$lop=q("SELECT l.*,g.ten_nganh,(SELECT COUNT(*) FROM HOC_VIEN h WHERE h.ma_lop=l.ma_lop) si_so FROM LOP_HANH_CHINH l JOIN NGANH_DAO_TAO g ON g.ma_nganh=l.ma_nganh ORDER BY l.nam_nhap_hoc DESC,l.ma_lop")->fetchAll(); ?>
<form method="post" class="cols"><?= hid() ?>
 <div class="card"><h2>Học viên chưa có lớp</h2>
  <?php foreach($hv as $h): ?><label class="pick" style="font-weight:400"><span><b><?= e($h['ma_hoc_vien']) ?></b> <?= e($h['ho_ten']) ?><br><small class="sub"><?= e($h['ten_nganh']) ?></small></span><input type="checkbox" name="hv[]" value="<?= e($h['ma_hoc_vien']) ?>" style="width:auto"></label><?php endforeach; if(!$hv): ?><p class="sub">Tất cả học viên đã có lớp.</p><?php endif; ?></div>
 <div class="card"><h2>Ngành và lớp</h2>
  <?php foreach($lop as $l): ?><div class="lop"><span><?= e($l['ten_nganh']) ?><br><b><?= e($l['ma_lop']) ?></b> <small class="sub"><?= (int)$l['si_so'] ?> học viên</small></span><button type="submit" name="lop" value="<?= e($l['ma_lop']) ?>" class="btn sm">Gán vào lớp</button></div>
  <details><summary>Xem danh sách lớp (<?= isset($ds[$l['ma_lop']])?count($ds[$l['ma_lop']]):0 ?>)</summary><ul><?php if(isset($ds[$l['ma_lop']])): foreach($ds[$l['ma_lop']] as $x): ?><li><b><?= e($x['ma_hoc_vien']) ?></b> - <?= e($x['ho_ten']) ?></li><?php endforeach; else: ?><li>Chưa có học viên.</li><?php endif; ?></ul></details><?php endforeach; ?></div>
</form>
<?php include '../includes/footer.php'; ?>
