<?php
// 管理员密码重置处理文件
// 引入数据库配置
require_once 'common/db_config.php';
// 引入管理员模型
require_once 'common/admin_model.php';
// 引入权限检测
require_once 'common/auth.php';
checkAdminLogin();

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只支持POST请求']);
    exit;
}

// 获取管理员ID
$adminId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($adminId <= 0) {
    echo json_encode(['success' => false, 'message' => '无效的管理员ID']);
    exit;
}

// 初始化管理员模型
$adminModel = new AdminModel($pdo);

// 重置密码为123456
$newPassword = password_hash('123456', PASSWORD_DEFAULT);

// 更新密码
$result = $adminModel->updateAdminPassword($adminId, $newPassword);

if ($result) {
    echo json_encode(['success' => true, 'message' => '管理员密码重置成功']);
} else {
    echo json_encode(['success' => false, 'message' => '管理员密码重置失败，请重试']);
}