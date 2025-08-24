<?php
// 检测用户是否登录，未登录则跳转到登录页面
require_once '../config.php';
require_once 'common/auth.php';
require_once 'common/question_model.php';

// 设置响应类型为JSON
header('Content-Type: application/json');

// 检查权限
checkAdminLogin();

// 初始化题目模型
$questionModel = new QuestionModel($pdo);

// 处理单个删除
if (isset($_POST['id'])) {
    $questionId = (int)$_POST['id'];
    $result = $questionModel->deleteQuestion($questionId);
    if ($result) {
        echo json_encode(['success' => true, 'message' => '题目删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '题目删除失败，请重试']);
    }
// 处理批量删除
} elseif (isset($_POST['ids'])) {
    $ids = json_decode($_POST['ids'], true);
    if (!is_array($ids) || empty($ids)) {
        echo json_encode(['success' => false, 'message' => '请选择要删除的题目']);
        exit;
    }
    $result = $questionModel->batchDeleteQuestions($ids);
    if ($result) {
        echo json_encode(['success' => true, 'message' => '成功删除' . count($ids) . '个题目']);
    } else {
        echo json_encode(['success' => false, 'message' => '批量删除失败，请重试']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求参数']);
}

exit;