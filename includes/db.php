<?php
// Đổi mật khẩu MySQL ở dòng dưới nếu Vertrigo của bạn dùng mật khẩu khác ('vertrigo' là mặc định)
session_start();
try{$pdo=new PDO('mysql:host=localhost;dbname=quan_ly_tuyen_sinh;charset=utf8mb4','root','vertrigo',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}
catch(Exception $x){die('Không kết nối được MySQL. Kiểm tra includes/db.php và đã import file SQL chưa.');}
$ROLE=['UNG_VIEN'=>'uv','HOC_VIEN'=>'hv','NHAN_VIEN_PDT'=>'pdt','NGUOI_CO_THAM_QUYEN'=>'ct'];
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function q($sql,$p=[]){global $pdo;$s=$pdo->prepare($sql);$s->execute($p);return $s;}
function guard($role){global $ROLE;if(empty($_SESSION['u'])||$ROLE[$_SESSION['u']['vai_tro']]!==$role){header('Location: ../dang-nhap.php');exit;}}
function csrf(){if(empty($_SESSION['t']))$_SESSION['t']=md5(uniqid(mt_rand(),true));return $_SESSION['t'];}
function chk(){if(!isset($_POST['t'])||$_POST['t']!==$_SESSION['t']){http_response_code(403);exit('Phiên không hợp lệ');}}
function hid(){return '<input type="hidden" name="t" value="'.csrf().'">';}
function log_act($bang,$id,$moi){q("INSERT INTO NHAT_KY_HE_THONG(ma_tai_khoan,hanh_dong,bang_tac_dong,ma_ban_ghi,du_lieu_moi,dia_chi_ip,thoi_gian) VALUES(?,?,?,?,?,?,NOW())",[$_SESSION['u']['ma_tai_khoan'],'CAP_NHAT',$bang,$id,$moi,$_SERVER['REMOTE_ADDR']]);}
function badge($k){$m=['NHAP'=>['Nháp',''],'CHO_DUYET'=>['Chờ duyệt','b-warn'],'CAN_BO_SUNG'=>['Cần bổ sung','b-info'],'DA_DUYET'=>['Đã duyệt','b-ok'],'TRUNG_TUYEN'=>['Trúng tuyển','b-ok'],'KHONG_TRUNG_TUYEN'=>['Không trúng tuyển','b-bad'],'DA_XAC_NHAN_NHAP_HOC'=>['Đã nhập học','b-ok'],'DA_XAC_THUC_THU_CONG'=>['Hợp lệ','b-ok'],'KHONG_HOP_LE'=>['Không hợp lệ','b-bad'],'CAN_KIEM_TRA_THU_CONG'=>['Chờ kiểm tra','b-warn'],'CHUA_XAC_THUC'=>['Chưa xác thực',''],'DU_THAO'=>['Dự thảo',''],'CHO_PHE_DUYET'=>['Chờ phê duyệt','b-warn'],'DA_PHE_DUYET'=>['Đã phê duyệt','b-ok'],'DANG_MO'=>['Đang mở','b-ok'],'CHUA_MO'=>['Chưa mở',''],'DA_DONG'=>['Đã đóng','b-bad']];$v=isset($m[$k])?$m[$k]:[$k,''];return '<span class="badge '.$v[1].'">'.e($v[0]).'</span>';}
