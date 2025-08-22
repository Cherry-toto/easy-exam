<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

setcookie('user_email', '', time() - 3600, '/', '', false, true);

setcookie('user_token', '', time() - 3600, '/', '', false, true);

echo json_encode([
    'success' => true, 
    'message' => '退出成功'
]);
exit;