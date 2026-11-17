<?php $base='../';$role='ct';$active='phe-duyet.php';require '../includes/db.php';guard('ct');
function fl($t,$m){$_SESSION['f']=[$t,$m];}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$i=$_POST['id'];
 if($_POST['act']==='ok'){$pdo->beginTransaction();
  $n=q("UPDATE DANH_SACH_TRUNG_TUYEN SET trang_thai='DA_PHE_DUYET',ma_nguoi_duyet=?,ngay_phe_duyet=NOW(),ly_do_tu_choi=NULL WHERE ma_danh_sach=? AND trang_thai='CHO_PHE_DUYET'",[$_SESSION['u']['ma_nq'],$i])->rowCount();
  if($n){q("UPDATE NGUYEN_VONG SET trang_thai='TRUNG_TUYEN' WHERE ma_danh_sach=?",[$i]);
   q("UPDATE HO_SO_TUYEN_SINH SET trang_thai='TRUNG_TUYEN' WHERE ma_ho_so IN (SELECT ma_ho_so FROM NGUYEN_VONG WHERE ma_danh_sach=?)",[$i]);$pdo->commit();log_act('DANH_SACH_TRUNG_TUYEN',$i,'DA_PHE_DUYET');fl('ok','Đã phê duyệt danh sách '.$i.'. Phòng Đào tạo có thể gửi giấy báo.');}
  else{$pdo->rollBack();fl('bad','Danh sách không còn ở trạng thái chờ phê duyệt.');}}
 elseif(trim($_POST['ly_do'])===''){fl('bad','Hãy nhập lý do từ chối.');header('Location: phe-duyet.php?id='.urlencode($i));exit;}
 else{$n=q("UPDATE DANH_SACH_TRUNG_TUYEN SET trang_thai='DU_THAO',ly_do_tu_choi=? WHERE ma_danh_sach=? AND trang_thai='CHO_PHE_DUYET'",[trim($_POST['ly_do']),$i])->rowCount();
  if($n)log_act('DANH_SACH_TRUNG_TUYEN',$i,'TU_CHOI');fl($n?'ok':'bad',$n?'Đã từ chối danh sách '.$i.', trả về Phòng Đào tạo kèm lý do.':'Danh sách không còn chờ phê duyệt.');}
 header('Location: phe-duyet.php');exit;}
$ls=q("SELECT d.ma_danh_sach,t.ten_dot,d.ngay_lap,(SELECT COUNT(*) FROM NGUYEN_VONG n WHERE n.ma_danh_sach=d.ma_danh_sach) so FROM DANH_SACH_TRUNG_TUYEN d JOIN DOT_TUYEN_SINH t ON t.ma_dot=d.ma_dot WHERE d.trang_thai='CHO_PHE_DUYET' ORDER BY d.ngay_lap DESC")->fetchAll();
$sel=isset($_GET['id'])?$_GET['id']:($ls?$ls[0]['ma_danh_sach']:'');$ok=false;foreach($ls as $x)if($x['ma_danh_sach']===$sel)$ok=true;if(!$ok&&$ls)$sel=$ls[0]['ma_danh_sach'];
$title='Phê duyệt danh sách';$sub=count($ls).' danh sách đang chờ phê duyệt';include '../includes/header.php';
if(!empty($_SESSION['f'])){$f=$_SESSION['f'];unset($_SESSION['f']);echo '<div class="note"'.($f[0]==='bad'?' style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"':'').'>'.e($f[1]).'</div>';}
if(!$ls) echo '<div class="card">Hiện không có danh sách nào chờ phê duyệt.</div>'; else { ?>
<div class="card wrap"><h2>Danh sách chờ phê duyệt</h2><table><tr><th>Mã DS</th><th>Đợt</th><th>Số người</th><th>Ngày trình</th><th></th></tr>
<?php foreach($ls as $x): ?><tr<?= $x['ma_danh_sach']===$sel?' style="background:#F3F5F8"':'' ?>><td><b><?= e($x['ma_danh_sach']) ?></b></td><td><?= e($x['ten_dot']) ?></td><td><?= (int)$x['so'] ?></td><td><?= e($x['ngay_lap']) ?></td><td><a href="phe-duyet.php?id=<?= urlencode($x['ma_danh_sach']) ?>"><?= $x['ma_danh_sach']===$sel?'Đang xem':'Xem' ?></a></td></tr><?php endforeach; ?></table></div>
<?php $r=q("SELECT n.diem_xet_tuyen,u.ho_ten,g.ten_nganh FROM NGUYEN_VONG n JOIN HO_SO_TUYEN_SINH h ON h.ma_ho_so=n.ma_ho_so JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh WHERE n.ma_danh_sach=? ORDER BY g.ten_nganh,n.diem_xet_tuyen DESC",[$sel])->fetchAll(); ?>
<div class="card wrap"><h2>Chi tiết <?= e($sel) ?></h2><table><tr><th>Hạng</th><th>Ứng viên</th><th>Ngành</th><th>Điểm xét tuyển</th></tr><?php foreach($r as $i=>$x): ?><tr><td><?= $i+1 ?></td><td><?= e($x['ho_ten']) ?></td><td><?= e($x['ten_nganh']) ?></td><td><?= e($x['diem_xet_tuyen']) ?></td></tr><?php endforeach; ?></table></div>
<form method="post" class="card"><?= hid() ?><input type="hidden" name="id" value="<?= e($sel) ?>"><label>Lý do từ chối (bắt buộc khi từ chối)</label><textarea name="ly_do" rows="2"></textarea>
<p><button name="act" value="ok" class="btn ok">Phê duyệt danh sách</button> <button name="act" value="no" class="btn bad">Từ chối</button></p></form>
<?php } include '../includes/footer.php'; ?>
