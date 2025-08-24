<?php
// 检查用户是否登录
if(!isset($_COOKIE['admin_logged_in']) || $_COOKIE['admin_logged_in'] !== 'true') {
    header('Location: login.php');
    exit;
}

// 获取登录用户信息
$admin_id = $_COOKIE['admin_id'];
$admin_username = $_COOKIE['admin_username'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在线自学考试系统 - 后台管理</title>
    <!-- Tailwind CSS -->
    <link href="/css/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/fontawesome-free-6.7.2/css/solid.css" rel="stylesheet">
    <link href="/fontawesome-free-6.7.2/css/regular.css" rel="stylesheet">
    <link href="/fontawesome-free-6.7.2/css/brands.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- 顶部导航 -->
    <header class="bg-blue-600 text-white shadow-md fixed w-full top-0 z-30 transition-all duration-300">
        <div class="container mx-auto px-4 py-2 flex justify-between items-center h-16">
            <div class="flex items-center space-x-2">
                <i class="fas fa-graduation-cap text-2xl"></i>
                <h1 class="text-xl font-bold">在线自学考试系统</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span>欢迎, <?php echo $admin_username; ?></span>
                <a href="logout.php" class="hover:text-blue-200 transition-colors duration-300">
                    <i class="fas fa-sign-out-alt mr-1"></i>退出
                </a>
            </div>
        </div>
    </header>