<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/member_model.php';
// 创建会员模型实例
$memberModel = new MemberModel();

// 确保是POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit;
}

// 处理单个删除
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => '无效的会员ID']);
        exit;
    }

    $result = $memberModel->deleteMember($id);
    if ($result) {
        echo json_encode(['success' => true, 'message' => '删除会员成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '删除会员失败']);
    }
// 处理批量删除
} elseif (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
    $ids = array_filter($ids, function($id) { return $id > 0; });

    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => '没有选择有效的会员ID']);
        exit;
    }

    $result = $memberModel->batchDeleteMembers($ids);
    if ($result) {
        echo json_encode(['success' => true, 'message' => '批量删除会员成功', 'count' => count($ids)]);
    } else {
        echo json_encode(['success' => false, 'message' => '批量删除会员失败']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '缺少必要的参数']);
}
?>