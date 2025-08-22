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

try {
    // 构建查询
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "title LIKE ?";
        $params[] = "%{$search}%";
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // 查询试卷列表
    $sql = "
        SELECT 
            id, 
            title, 
            nums, 
            score, 
            create_time, 
            update_time
        FROM exam 
        {$whereClause}
        ORDER BY create_time DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $exams = $stmt->fetchAll();
    
    // 获取总数
    $countSql = "SELECT COUNT(*) as total FROM exam {$whereClause}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    // 格式化数据
    $formattedExams = array_map(function($exam) {
        return [
            'id' => intval($exam['id']),
            'title' => htmlspecialchars($exam['title']),
            'nums' => intval($exam['nums']),
            'score' => intval($exam['score']),
            'create_time' => $exam['create_time'],
            'update_time' => $exam['update_time']
        ];
    }, $exams);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedExams,
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