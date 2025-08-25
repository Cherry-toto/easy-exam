<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit();
}

// 获取用户登录信息
$user = checkLoginStatus();
if (!$user) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    exit();
}

// 获取提交数据
$input = json_decode(file_get_contents('php://input'), true);
$examId = isset($input['exam_id']) ? intval($input['exam_id']) : 0;
$answers = isset($input['answers']) ? $input['answers'] : [];

if ($examId <= 0 || empty($answers)) {
    echo json_encode(['success' => false, 'message' => '参数不完整']);
    exit();
}

try {
    // 开始事务
    $pdo->beginTransaction();

    // 检查试卷是否存在
    $stmt = $pdo->prepare("SELECT id, title, nums, score FROM exam WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();

    if (!$exam) {
        throw new Exception('试卷不存在');
    }

    // 获取所有题目信息
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            options,
            type,
            answer,
            analysis
        FROM question 
        WHERE exam_id = ? 
        ORDER BY id ASC
    ");
    $stmt->execute([$examId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        throw new Exception('试卷没有题目');
    }

    // 计算得分和记录错题
    $totalScore = 0;
    $correctCount = 0;
    $results = [];

    foreach ($questions as $question) {
        $questionId = $question['id'];
        $userAnswer = isset($answers[$questionId]) ? $answers[$questionId] : '';
        
        // 处理答案格式
        if (is_array($userAnswer)) {
            sort($userAnswer);
            $userAnswer = implode('', $userAnswer);
        }
        
        $correctAnswer = $question['answer'];
        $isCorrect = false;

        // 判断答案是否正确
        if ($question['type'] == 1) {
            // 单选题
            $isCorrect = strtoupper($userAnswer) === strtoupper($correctAnswer);
        } else {
            // 多选题
            $userAnswer = strtoupper($userAnswer);
            $correctAnswer = strtoupper($correctAnswer);
            $isCorrect = $userAnswer === $correctAnswer;
        }

        if ($isCorrect) {
            $correctCount++;
        } else {
            // 记录错题
            $stmt = $pdo->prepare("
                INSERT INTO mistake (
                    title, exam_id, options, type, answer, errors, 
                    member_id, question_id, create_time
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $question['title'],
                $examId,
                $question['options'],
                $question['type'],
                $question['answer'],
                $userAnswer,
                $user['id'],
                $questionId
            ]);
        }

        // 计算单题得分
        $questionScore = $exam['score'] / $exam['nums'];
        if ($isCorrect) {
            $totalScore += $questionScore;
        }

        // 准备返回结果
        $options = json_decode($question['options'], true);
        $results[] = [
            'id' => intval($question['id']),
            'title' => htmlspecialchars($question['title']),
            'options' => $options,
            'type' => intval($question['type']),
            'correct_answer' => $question['answer'],
            'user_answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'analysis' => htmlspecialchars($question['analysis'])
        ];
    }

    // 获取考试用时
    $useTime = isset($input['use_time']) ? intval($input['use_time']) : 0;
    
    // 插入考试记录
    $stmt = $pdo->prepare("INSERT INTO exam_log (exam_id, member_id, score, use_time, create_time) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$examId, $user['id'], round($totalScore, 2), $useTime]);

    // 提交事务
    $pdo->commit();

    // 返回结果
    echo json_encode([
        'success' => true,
        'exam' => [
            'id' => intval($exam['id']),
            'title' => htmlspecialchars($exam['title']),
            'total_score' => intval($exam['score']),
            'user_score' => round($totalScore, 2),
            'correct_count' => $correctCount,
            'total_questions' => count($questions)
        ],
        'results' => $results
    ]);

} catch (Exception $e) {
    // 回滚事务
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("考试提交错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>