<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允许GET请求
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit();
}

// 获取试卷ID
$examId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($examId <= 0) {
    echo json_encode(['success' => false, 'message' => '试卷ID无效']);
    exit();
}

try {
    // 检查试卷是否存在
    $stmt = $pdo->prepare("SELECT id, title, nums, score FROM exam WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();

    if (!$exam) {
        echo json_encode(['success' => false, 'message' => '试卷不存在']);
        exit();
    }

    // 获取题目列表（不包含答案）
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            options,
            type,
            analysis
        FROM question 
        WHERE exam_id = ? 
        ORDER BY id ASC
    ");
    $stmt->execute([$examId]);
    $questions = $stmt->fetchAll();

    // 格式化题目数据
    $formattedQuestions = array_map(function($question) {
        $options = json_decode($question['options'], true);
        return [
            'id' => intval($question['id']),
            'title' => htmlspecialchars($question['title']),
            'options' => $options,
            'type' => intval($question['type']),
            'analysis' => htmlspecialchars($question['analysis'])
        ];
    }, $questions);

    echo json_encode([
        'success' => true,
        'exam' => [
            'id' => intval($exam['id']),
            'title' => htmlspecialchars($exam['title']),
            'nums' => intval($exam['nums']),
            'score' => intval($exam['score'])
        ],
        'questions' => $formattedQuestions
    ]);

} catch (PDOException $e) {
    error_log("数据库错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系统繁忙，请稍后重试']);
}
?>