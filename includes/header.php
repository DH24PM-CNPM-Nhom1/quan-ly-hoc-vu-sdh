<?php
require_once __DIR__.'/db.php'; guard($role);
// Đặt trước khi include: $base, $role (uv|pdt|ct|hv), $active, $title, $sub (tùy chọn)
$menus = [
 'uv'=>[['ho-so.php','Hồ sơ của tôi'],['nguyen-vong.php','Nguyện vọng'],['giay-bao.php','Giấy báo trúng tuyển']],
 'pdt'=>[['dashboard.php','Tổng quan'],['ho-so.php','Hồ sơ tuyển sinh'],['dot-tuyen-sinh.php','Đợt tuyển sinh'],['danh-sach.php','Danh sách trúng tuyển'],['phan-lop.php','Phân lớp']],
 'ct'=>[['phe-duyet.php','Phê duyệt danh sách']],
 'hv'=>[['ho-so.php','Hồ sơ học viên'],['chuong-trinh.php','Chương trình đào tạo'],['tien-do.php','Tiến độ học tập'],['thong-bao.php','Thông báo']],
];
$names = ['uv'=>['Nguyễn Văn An','Ứng viên'],'pdt'=>['Trần Văn Nam','Nhân viên Phòng Đào tạo'],'ct'=>['Lê Thị Hương','Người có thẩm quyền'],'hv'=>['Nguyễn Văn An','Học viên']];
?><!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $title ?> - Tuyển sinh Sau đại học</title>
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head><body>
<div class="layout">
<nav class="side">
  <div class="brand">Tuyển sinh Sau đại học<small>Trường Đại học An Giang</small></div>
  <?php foreach($menus[$role] as $m): ?>
    <a href="<?= $base.$role.'/'.$m[0] ?>" class="<?= $active===$m[0]?'on':'' ?>"><?= $m[1] ?></a>
  <?php endforeach; ?>
  <a class="out" href="<?= $base ?>dang-xuat.php">Đăng xuất</a>
</nav>
<main class="main">
<div class="top">
  <div><h1><?= $title ?></h1><?php if(!empty($sub)): ?><p class="sub"><?= $sub ?></p><?php endif; ?></div>
  <div class="user"><b><?= e($_SESSION['u']['ho_ten']) ?></b><br><?= $names[$role][1] ?></div>
</div>
