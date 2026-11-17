<?php // Mở http://localhost/tuyensinh/kiem-tra.php để chẩn đoán đăng nhập. Xóa file này khi nộp bài.
header('Content-Type: text/html; charset=utf-8');
echo '<pre>PHP: '.PHP_VERSION.(function_exists('password_verify')?' (có password_verify)':' (THIẾU password_verify: cần PHP >= 5.5)')."\n";
try{$p=new PDO('mysql:host=localhost;dbname=quan_ly_tuyen_sinh;charset=utf8mb4','root','vertrigo',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);echo "Kết nối MySQL: OK\n";}
catch(Exception $x){exit('Kết nối MySQL LỖI: '.htmlspecialchars($x->getMessage()).' (sai mật khẩu root trong includes/db.php, hoặc chưa import SQL)');}
$n=$p->query("SELECT COUNT(*) FROM TAI_KHOAN")->fetchColumn();echo "Số tài khoản trong DB: $n\n";
if(isset($_GET['reset'])&&function_exists('password_hash')){$h=password_hash('123456',PASSWORD_BCRYPT);$s=$p->prepare("UPDATE TAI_KHOAN SET mat_khau_ma_hoa=?");$s->execute([$h]);echo "ĐÃ ĐẶT LẠI mật khẩu mọi tài khoản = 123456. Hãy thử đăng nhập lại.\n";}
else{$r=$p->query("SELECT mat_khau_ma_hoa FROM TAI_KHOAN WHERE username='pdt_nam'")->fetchColumn();
 echo "pdt_nam tồn tại: ".($r?'có':'KHÔNG')."\n";if($r&&function_exists('password_verify'))echo "Kiểm tra 123456 với pdt_nam: ".(password_verify('123456',$r)?'ĐÚNG':'SAI')."\n";
 echo "\nNếu 'SAI' hoặc lỗi hash: mở kiem-tra.php?reset=1 để đặt lại mật khẩu bằng chính PHP của bạn.\n";}
echo '</pre>';
