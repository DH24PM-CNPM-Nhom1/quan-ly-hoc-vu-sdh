<?php $base='../';$role='pdt';$active='danh-sach.php';require '../includes/db.php';guard('pdt');
function fl($t,$m){$_SESSION['f']=[$t,$m];}
if($_SERVER['REQUEST_METHOD']==='POST'){chk();$a=$_POST['act'];$nv=$_SESSION['u']['ma_nv'];
 if($a==='luu'){$n=0;foreach($_POST['diem'] as $id=>$d){$d=trim($d);if($d===''||(is_numeric($d)&&$d>=0&&$d<=100)){$n+=q("UPDATE NGUYEN_VONG SET diem_xet_tuyen=? WHERE ma_nguyen_vong=? AND ma_danh_sach IS NULL",[$d===''?null:$d,$id])->rowCount();}}fl('ok','Đã lưu điểm ('.$n.' nguyện vọng thay đổi).');}
 elseif($a==='lap'){$d=$_POST['ma_dot'];
  $rows=q("SELECT n.ma_nguyen_vong,n.ma_ho_so,n.ma_nganh,g.chi_tieu_tuyen_sinh-(SELECT COUNT(*) FROM NGUYEN_VONG t WHERE t.ma_nganh=n.ma_nganh AND t.trang_thai='TRUNG_TUYEN') con FROM NGUYEN_VONG n JOIN HO_SO_TUYEN_SINH h ON h.ma_ho_so=n.ma_ho_so JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh WHERE h.ma_dot=? AND h.trang_thai='DA_DUYET' AND n.ma_danh_sach IS NULL AND n.trang_thai='CHO_XET' AND n.diem_xet_tuyen IS NOT NULL ORDER BY n.thu_tu,n.diem_xet_tuyen DESC",[$d])->fetchAll();
  $con=[];$hs=[];$pick=[];foreach($rows as $r){$k=$r['ma_nganh'];if(!isset($con[$k]))$con[$k]=$r['con'];if($con[$k]>0&&!isset($hs[$r['ma_ho_so']])){$con[$k]--;$hs[$r['ma_ho_so']]=1;$pick[]=$r['ma_nguyen_vong'];}}
  if(!$pick)fl('bad','Không có nguyện vọng nào đủ điều kiện (hồ sơ đã duyệt, đã nhập điểm, còn chỉ tiêu).');
  else{$pdo->beginTransaction();$ma='DS'.sprintf('%02d',q("SELECT COUNT(*)+1 FROM DANH_SACH_TRUNG_TUYEN")->fetchColumn());
   q("INSERT INTO DANH_SACH_TRUNG_TUYEN(ma_danh_sach,ma_dot,ma_nhan_vien_lap,trang_thai,ngay_lap) VALUES(?,?,?,'DU_THAO',NOW())",[$ma,$d,$nv]);
   q("UPDATE NGUYEN_VONG SET ma_danh_sach=? WHERE ma_nguyen_vong IN (".implode(',',array_fill(0,count($pick),'?')).")",array_merge([$ma],$pick));$pdo->commit();
   log_act('DANH_SACH_TRUNG_TUYEN',$ma,'DU_THAO');fl('ok','Đã lập danh sách '.$ma.' gồm '.count($pick).' nguyện vọng (xếp theo thứ tự nguyện vọng, điểm cao trước, trong giới hạn chỉ tiêu còn lại).');}}
 elseif($a==='trinh'){$n=q("UPDATE DANH_SACH_TRUNG_TUYEN SET trang_thai='CHO_PHE_DUYET',ma_nhan_vien_lap=?,ngay_lap=NOW() WHERE ma_danh_sach=? AND trang_thai='DU_THAO' AND EXISTS(SELECT 1 FROM NGUYEN_VONG WHERE ma_danh_sach=?)",[$nv,$_POST['id'],$_POST['id']])->rowCount();
  if($n)log_act('DANH_SACH_TRUNG_TUYEN',$_POST['id'],'CHO_PHE_DUYET');fl($n?'ok':'bad',$n?'Đã trình '.$_POST['id'].' lên người có thẩm quyền.':'Không trình được danh sách này.');}
 elseif($a==='huy'){$pdo->beginTransaction();q("UPDATE NGUYEN_VONG SET ma_danh_sach=NULL WHERE ma_danh_sach=?",[$_POST['id']]);$n=q("DELETE FROM DANH_SACH_TRUNG_TUYEN WHERE ma_danh_sach=? AND trang_thai='DU_THAO'",[$_POST['id']])->rowCount();
  if($n){$pdo->commit();fl('ok','Đã hủy danh sách nháp '.$_POST['id'].'.');}else{$pdo->rollBack();fl('bad','Chỉ hủy được danh sách ở trạng thái dự thảo.');}}
 elseif($a==='gui'){$i=$_POST['id'];$han=$_POST['han'];
  if($han<date('Y-m-d'))fl('bad','Hạn xác nhận phải từ hôm nay trở đi.');
  else{$ds=q("SELECT n.ma_nguyen_vong,u.ma_tai_khoan,g.ten_nganh FROM NGUYEN_VONG n JOIN HO_SO_TUYEN_SINH h ON h.ma_ho_so=n.ma_ho_so JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh JOIN DANH_SACH_TRUNG_TUYEN d ON d.ma_danh_sach=n.ma_danh_sach WHERE d.ma_danh_sach=? AND d.trang_thai='DA_PHE_DUYET' AND n.trang_thai='TRUNG_TUYEN' AND NOT EXISTS(SELECT 1 FROM GIAY_BAO_TRUNG_TUYEN b WHERE b.ma_nguyen_vong=n.ma_nguyen_vong)",[$i])->fetchAll();
   if(!$ds)fl('bad','Không có ứng viên nào cần gửi giấy báo (đã gửi hết hoặc danh sách chưa được phê duyệt).');
   else{$pdo->beginTransaction();$k=q("SELECT COALESCE(MAX(CAST(SUBSTRING(ma_giay_bao,3) AS UNSIGNED)),0) FROM GIAY_BAO_TRUNG_TUYEN")->fetchColumn();
    foreach($ds as $x){$k++;q("INSERT INTO GIAY_BAO_TRUNG_TUYEN(ma_giay_bao,ma_nguyen_vong,ngay_gui,han_xac_nhan) VALUES(?,?,NOW(),?)",['GB'.sprintf('%02d',$k),$x['ma_nguyen_vong'],$han]);
     q("INSERT INTO THONG_BAO(ma_tai_khoan,tieu_de,noi_dung,ngay_gui) VALUES(?,?,?,NOW())",[$x['ma_tai_khoan'],'Giấy báo trúng tuyển','Bạn đã trúng tuyển ngành '.$x['ten_nganh'].'. Vui lòng xác nhận nhập học trước '.date('d/m/Y',strtotime($han)).'.']);}
    $pdo->commit();log_act('GIAY_BAO_TRUNG_TUYEN',$i,'GUI '.count($ds));fl('ok','Đã gửi giấy báo cho '.count($ds).' ứng viên.');}}}
 header('Location: danh-sach.php');exit;}
$title='Danh sách trúng tuyển';$sub='PĐT nhập điểm, lập danh sách trong chỉ tiêu, trình phê duyệt rồi gửi giấy báo';include '../includes/header.php';
if(!empty($_SESSION['f'])){$f=$_SESSION['f'];unset($_SESSION['f']);echo '<div class="note"'.($f[0]==='bad'?' style="border-color:var(--bad);background:#FDE7E5;color:var(--bad)"':'').'>'.e($f[1]).'</div>';}
$nv=q("SELECT n.ma_nguyen_vong,n.ma_ho_so,n.thu_tu,n.diem_xet_tuyen,u.ho_ten,g.ten_nganh FROM NGUYEN_VONG n JOIN HO_SO_TUYEN_SINH h ON h.ma_ho_so=n.ma_ho_so JOIN UNG_VIEN u ON u.ma_ung_vien=h.ma_ung_vien JOIN NGANH_DAO_TAO g ON g.ma_nganh=n.ma_nganh WHERE h.trang_thai='DA_DUYET' AND n.ma_danh_sach IS NULL AND n.trang_thai='CHO_XET' ORDER BY g.ten_nganh,n.diem_xet_tuyen DESC")->fetchAll();
$dots=q("SELECT ma_dot,ten_dot FROM DOT_TUYEN_SINH ORDER BY nam_tuyen DESC,ngay_bat_dau DESC")->fetchAll();
$dl=q("SELECT d.*,t.ten_dot,(SELECT COUNT(*) FROM NGUYEN_VONG n WHERE n.ma_danh_sach=d.ma_danh_sach) so,(SELECT COUNT(*) FROM NGUYEN_VONG n JOIN GIAY_BAO_TRUNG_TUYEN b ON b.ma_nguyen_vong=n.ma_nguyen_vong WHERE n.ma_danh_sach=d.ma_danh_sach) gb FROM DANH_SACH_TRUNG_TUYEN d JOIN DOT_TUYEN_SINH t ON t.ma_dot=d.ma_dot ORDER BY d.ngay_lap DESC")->fetchAll(); ?>
<form method="post" class="card wrap"><?= hid() ?><h2>1. Nhập điểm xét tuyển</h2><p class="sub">Chỉ hiện nguyện vọng của hồ sơ đã duyệt và chưa nằm trong danh sách nào.</p>
<table><tr><th>Hồ sơ</th><th>Ứng viên</th><th>Ngành</th><th>NV</th><th>Điểm</th></tr>
<?php foreach($nv as $r): ?><tr><td><?= e($r['ma_ho_so']) ?></td><td><?= e($r['ho_ten']) ?></td><td><?= e($r['ten_nganh']) ?></td><td><?= (int)$r['thu_tu'] ?></td><td><input type="number" step="0.01" min="0" max="100" name="diem[<?= e($r['ma_nguyen_vong']) ?>]" value="<?= e($r['diem_xet_tuyen']) ?>" style="width:100px"></td></tr><?php endforeach; if(!$nv): ?><tr><td colspan="5">Chưa có hồ sơ đã duyệt nào cần chấm điểm. Hãy duyệt hồ sơ ở mục Hồ sơ tuyển sinh trước.</td></tr><?php endif; ?></table>
<?php if($nv): ?><p><button name="act" value="luu" class="btn">Lưu điểm</button></p><?php endif; ?></form>
<form method="post" class="card"><?= hid() ?><h2>2. Lập danh sách dự kiến</h2><p class="sub">Lấy các nguyện vọng đã có điểm, ưu tiên nguyện vọng thứ tự nhỏ rồi điểm cao, không vượt chỉ tiêu còn lại của ngành.</p>
<div class="filter"><select name="ma_dot"><?php foreach($dots as $d): ?><option value="<?= e($d['ma_dot']) ?>"><?= e($d['ten_dot']) ?></option><?php endforeach; ?></select><button name="act" value="lap" class="btn">Lập danh sách dự kiến</button></div></form>
<div class="card wrap"><h2>3. Các danh sách</h2><table><tr><th>Mã DS</th><th>Đợt</th><th>Số người</th><th>Trạng thái</th><th>Thao tác</th></tr>
<?php foreach($dl as $d): ?><tr><td><?= e($d['ma_danh_sach']) ?></td><td><?= e($d['ten_dot']) ?></td><td><?= (int)$d['so'] ?></td><td><?= badge($d['trang_thai']) ?><?= ($d['trang_thai']==='DU_THAO'&&$d['ly_do_tu_choi'])?'<br><small class="sub">Bị trả lại: '.e($d['ly_do_tu_choi']).'</small>':'' ?></td>
<td><form method="post" style="display:flex;gap:6px;flex-wrap:wrap"><?= hid() ?><input type="hidden" name="id" value="<?= e($d['ma_danh_sach']) ?>">
<?php if($d['trang_thai']==='DU_THAO'): ?><button name="act" value="trinh" class="btn sm">Trình phê duyệt</button><button name="act" value="huy" class="btn bad sm">Hủy nháp</button>
<?php elseif($d['trang_thai']==='DA_PHE_DUYET'): ?><?php if($d['gb']<$d['so']): ?><input type="date" name="han" value="<?= date('Y-m-d',strtotime('+14 days')) ?>" style="width:auto"><button name="act" value="gui" class="btn ok sm">Gửi giấy báo</button><?php else: ?><span class="sub">Đã gửi giấy báo (<?= (int)$d['gb'] ?>)</span><?php endif; endif; ?></form></td></tr><?php endforeach; if(!$dl): ?><tr><td colspan="5">Chưa có danh sách nào.</td></tr><?php endif; ?></table></div>
<?php include '../includes/footer.php'; ?>
