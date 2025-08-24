<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'common/config.php';
require_once 'common/exam_model.php';

// 验证请求方式
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// 创建试卷模型实例
$examModel = new ExamModel();

// 处理批量删除
if (isset($_POST['ids'])) {
    try {
        $ids = json_decode($_POST['ids'], true);
        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['success' => false, 'message' => '无效的试卷ID列表']);
            exit;
        }
        // 过滤非数字ID
        $ids = array_filter($ids, 'is_numeric');
        $ids = array_map('intval', $ids);

        $result = $examModel->batchDeleteExams($ids);
        if ($result) {
            echo json_encode(['success' => true, 'message' => '成功删除' . count($ids) . '个试卷']);
        } else {
            echo json_encode(['success' => false, 'message' => '批量删除试卷失败']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '处理批量删除时出错: ' . $e->getMessage()]);
    }
} else {
    // 处理单个删除
    $examId = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($examId <= 0) {
        echo json_encode(['success' => false, 'message' => '无效的试卷ID']);
        exit;
    }

    $result = $examModel->deleteExam($examId);

    if ($result) {
        echo json_encode(['success' => true, 'message' => '试卷删除成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '试卷删除失败']);
    }
}
?>