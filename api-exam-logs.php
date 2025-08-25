<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// 引入配置文件
include_once 'config.php';

// 响应数组
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // 使用config.php中的数据库连接
    global $pdo;
    
    // 检查用户登录状态
    if (!isset($_SESSION['user_id'])) {
        // 检查cookie登录
        if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_token'])) {
            $user_email = $_COOKIE['user_email'];
            $user_token = $_COOKIE['user_token'];

            // 验证cookie登录
            $stmt = $pdo->prepare("SELECT * FROM member WHERE email = ?");
            $stmt->execute([$user_email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $response['message'] = '用户未登录';
                echo json_encode($response);
                exit;
            }
            
            // 验证token
            if (hash('sha256', $user_email . $user['id']) !== $user_token) {
                $response['message'] = '登录过期，请重新登录';
                echo json_encode($response);
                exit;
            }
            $user_id = $user['id'];
        } else {
            $response['message'] = '用户未登录';
            echo json_encode($response);
            exit;
        }
    } else {
        $user_id = $_SESSION['user_id'];
    }
    
    // 查询考试记录
    $sql = "
        SELECT 
            el.id,
            el.exam_id,
            el.score,
            el.use_time,
            el.create_time,
            e.title as exam_title
        FROM exam_log el
        JOIN exam e ON el.exam_id = e.id
        WHERE el.member_id = :member_id
        ORDER BY el.create_time DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['member_id' => $user_id]);
    $exam_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($exam_logs) {
        $response['success'] = true;
        $response['data'] = $exam_logs;
        $response['message'] = '获取考试记录成功';
    } else {
        $response['success'] = true;
        $response['data'] = [];
        $response['message'] = '暂无考试记录';
    }
    
} catch (PDOException $e) {
    $response['message'] = '数据库错误：' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = '系统错误：' . $e->getMessage();
}

// 输出JSON响应
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>