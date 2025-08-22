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

// 获取查询参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 检查用户登录状态
$user = checkLoginStatus();
if (!$user) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit();
}

try {
    // 构建查询
    $where = ["m.member_id = ?"];
    $params = [$user['id']];
    
    if (!empty($search)) {
        $where[] = "m.title LIKE ?";
        $params[] = "%{$search}%";
    }
    
    $whereClause = "WHERE " . implode(" AND ", $where);
    
    // 查询错题列表
    $sql = "
        SELECT 
            m.id,
            m.title,
            m.exam_id,
            m.options,
            m.type,
            m.answer,
            m.errors,
            m.create_time,
            e.title as exam_title,
            q.analysis
        FROM mistake m
        LEFT JOIN exam e ON m.exam_id = e.id
        LEFT JOIN question q ON m.question_id = q.id
        {$whereClause}
        ORDER BY m.create_time DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $mistakes = $stmt->fetchAll();
    
    // 获取总数
    $countSql = "SELECT COUNT(*) as total FROM mistake m {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    // 格式化数据
    $formattedMistakes = array_map(function($mistake) {
        // 解析JSON选项
        $options = json_decode($mistake['options'], true);
        if (!$options) {
            $options = [];
        }
        
        return [
            'id' => intval($mistake['id']),
            'title' => htmlspecialchars($mistake['title']),
            'exam_id' => intval($mistake['exam_id']),
            'exam_title' => htmlspecialchars($mistake['exam_title']),
            'options' => $options,
            'type' => intval($mistake['type']),
            'answer' => htmlspecialchars($mistake['answer']),
            'errors' => htmlspecialchars($mistake['errors']),
            'analysis' => htmlspecialchars($mistake['analysis'] ?? ''),
            'create_time' => $mistake['create_time']
        ];
    }, $mistakes);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedMistakes,
        'pagination' => [
            'total' => intval($total),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("数据库错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系统繁忙，请稍后重试']);
}
?>