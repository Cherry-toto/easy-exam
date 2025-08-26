<?php
require_once 'config.php';
// 检查是否是POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);
    exit;
}

// 获取用户登录信息
$user = checkLoginStatus();
if (!$user) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit();
}
// 接收新密码
$newPassword = trim($_POST['newPassword'] ?? '');

// 验证密码
if (empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => '请输入新密码']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => '密码长度不能少于6个字符']);
    exit;
}


// 更新密码
try {
    $stmt = $pdo->prepare("UPDATE member SET password = :password,salt = :salt WHERE id = :id");
    $memberData['salt'] =  substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
    $hashedPassword = md5($newPassword . $memberData['salt']);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':salt', $memberData['salt']);
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // 密码更新成功，销毁当前会话
        session_destroy();
        echo json_encode(['success' => true, 'message' => '密码修改成功，请重新登录']);
    } else {
        echo json_encode(['success' => false, 'message' => '修改密码失败，请重试']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '服务器错误: ' . $e->getMessage()]);
}

exit;
?>