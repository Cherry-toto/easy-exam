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
    
    // 检查是否提供了exam_log_id参数
    if (!isset($_GET['exam_log_id']) || empty($_GET['exam_log_id'])) {
        $response['message'] = '缺少必要的参数';
        echo json_encode($response);
        exit;
    }
    
    $exam_log_id = intval($_GET['exam_log_id']);
    
    // 查询考试记录信息
    $sql = "
        SELECT 
            el.id, 
            el.exam_id, 
            el.score, 
            el.use_time, 
            el.create_time, 
            e.title as exam_title,
            e.nums as question_count,
            e.score as total_score
        FROM exam_log el
        JOIN exam e ON el.exam_id = e.id
        WHERE el.id = :exam_log_id AND el.member_id = :member_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['exam_log_id' => $exam_log_id, 'member_id' => $user_id]);
    $exam_log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$exam_log) {
        $response['message'] = '考试记录不存在或无权查看';
        echo json_encode($response);
        exit;
    }
    
    // 查询该试卷的所有题目
    $questions_sql = "
        SELECT 
            q.id, 
            q.title, 
            q.options, 
            q.type, 
            q.answer, 
            q.analysis,
            q.score
        FROM question q
        WHERE q.exam_id = :exam_id
        ORDER BY q.id ASC
    ";
    $questions_stmt = $pdo->prepare($questions_sql);
    $questions_stmt->execute(['exam_id' => $exam_log['exam_id']]);
    $questions = $questions_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 查询用户在该次考试中的错题
    $mistakes_sql = "
        SELECT 
            m.question_id, 
            m.errors
        FROM mistake m
        WHERE m.exam_id = :exam_id AND m.member_id = :member_id
    ";
    $mistakes_stmt = $pdo->prepare($mistakes_sql);
    $mistakes_stmt->execute(['exam_id' => $exam_log['exam_id'], 'member_id' => $user_id]);
    $mistakes = $mistakes_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 将错题信息转换为关联数组，方便查找
    $mistakes_map = [];
    foreach ($mistakes as $mistake) {
        $mistakes_map[$mistake['question_id']] = $mistake['errors'];
    }
    
    // 处理题目信息，添加用户答案状态
    $processed_questions = [];
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $is_mistake = isset($mistakes_map[$question_id]);
        
        // 格式化选项
        $options = json_decode($question['options'], true);
        if (!is_array($options)) {
            $options = [];
        }
        
        $processed_questions[] = [
            'id' => $question_id,
            'title' => htmlspecialchars($question['title']),
            'options' => $options,
            'type' => $question['type'], // 1单选，2多选
            'answer' => $question['answer'],
            'analysis' => htmlspecialchars($question['analysis']),
            'score' => $question['score'],
            'is_mistake' => $is_mistake,
            'user_answer' => $is_mistake ? $mistakes_map[$question_id] : ''
        ];
    }
    
    // 组装返回数据
    $response['data'] = [
        'exam_log_id' => $exam_log['id'],
        'exam_id' => $exam_log['exam_id'],
        'exam_title' => $exam_log['exam_title'],
        'score' => $exam_log['score'],
        'use_time' => $exam_log['use_time'],
        'create_time' => $exam_log['create_time'],
        'question_count' => $exam_log['question_count'],
        'total_score' => $exam_log['total_score'],
        'mistake_count' => count($mistakes),
        'questions' => $processed_questions
    ];
    
    $response['success'] = true;
    $response['message'] = '获取考试详情成功';
    
} catch (PDOException $e) {
    $response['message'] = '数据库错误：' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = '系统错误：' . $e->getMessage();
}

// 输出JSON响应
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>