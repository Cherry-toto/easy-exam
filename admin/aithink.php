<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();

// 确保请求方法是POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 获取表单数据
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$knowledge_points = isset($_POST['knowledge_points']) ? trim($_POST['knowledge_points']) : '';

// 验证数据
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => '试卷标题不能为空']);
    exit;
}

if (empty($knowledge_points)) {
    echo json_encode(['success' => false, 'message' => '知识点不能为空']);
    exit;
}

// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/exam_model.php';
require_once 'common/question_model.php';

// 创建模型实例
$examModel = new ExamModel();
$questionModel = new QuestionModel();

// 模拟AI请求获取题目数据
function simulateAIRequest($knowledge_points) {
    // 模拟题目数据，实际应用中这里会调用真实的AI API
    $mockQuestions = [
        [
            'type' => 1, // 选择题
            'content' => '以下哪种不是PHP的数据类型？',
            'options' => [
                'A' => 'integer',
                'B' => 'float',
                'C' => 'string',
                'D' => 'arraylist'
            ],
            'answer' => 'D',
            'score' => 5,
            'analysis' => 'PHP中的数据类型包括integer、float、string、boolean、array、object、NULL、resource等，没有arraylist类型。'
        ],
        [
            'type' => 1, // 选择题
            'content' => '在PHP中，连接字符串的运算符是什么？',
            'options' => [
                'A' => '+',
                'B' => '.',
                'C' => '&',
                'D' => '&&'
            ],
            'answer' => 'B',
            'score' => 5,
            'analysis' => 'PHP中使用点号(.)作为字符串连接运算符。'
        ],
        [
            'type' => 2, // 判断题
            'content' => 'PHP是一种编译型语言。',
            'answer' => '错误',
            'score' => 3,
            'analysis' => 'PHP是一种解释型语言，不需要编译成机器码即可执行。'
        ],
        [
            'type' => 3, // 填空题
            'content' => 'PHP的全称是__________________。',
            'answer' => 'PHP: Hypertext Preprocessor',
            'score' => 4,
            'analysis' => 'PHP最初代表Personal Home Page，现在代表PHP: Hypertext Preprocessor。'
        ],
        [
            'type' => 4, // 简答题
            'content' => '简述PHP的主要特点。',
            'answer' => '1. 开源免费；2. 易于学习；3. 跨平台；4. 面向对象；5. 支持多种数据库；6. 与HTML无缝集成；7. 广泛的社区支持。',
            'score' => 10,
            'analysis' => 'PHP作为一种流行的服务器端脚本语言，具有以上主要特点，使其成为Web开发的重要工具。'
        ]
    ];
    
    return $mockQuestions;
}



try {
    // 开始事务
    $pdo->beginTransaction();
    
    // 创建试卷
    $examData = [
        'title' => $title,
        'nums' => 0, // 暂时设为0，后面会更新
        'score' => 0, // 暂时设为0，后面会更新
        'description' => '由AI自动生成的试卷，知识点：' . $knowledge_points,
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $examId = $examModel->createExam($examData);
    
    if (!$examId) {
        throw new Exception('创建试卷失败');
    }
    
    // 获取模拟的题目数据
    $questions = simulateAIRequest($knowledge_points);
    
    // 计算总分
    $totalScore = 0;
    foreach ($questions as $question) {
        $totalScore += $question['score'];
    }
    
    // 更新试卷的题目数量和总分
    $examModel->updateExam($examId, [
        'nums' => count($questions),
        'score' => $totalScore,
        'update_time' => date('Y-m-d H:i:s')
    ]);
    
    // 将题目导入到数据库
    foreach ($questions as $questionData) {
        $question = [
            'exam_id' => $examId,
            'type' => $questionData['type'],
            'content' => $questionData['content'],
            'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
            'answer' => $questionData['answer'],
            'score' => $questionData['score'],
            'analysis' => $questionData['analysis'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ];
        
        $questionId = $questionModel->createQuestion($question);
        
        if (!$questionId) {
            throw new Exception('导入题目失败');
        }
    }
    
    // 提交事务
    $pdo->commit();
    
    // 返回成功响应
    echo json_encode([
        'success' => true,
        'message' => 'AI组卷成功，已创建试卷：' . $title,
        'exam_id' => $examId
    ]);
    
} catch (Exception $e) {
    // 回滚事务
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // 记录错误
    error_log('AI组卷失败：' . $e->getMessage());
    
    // 返回错误响应
    echo json_encode([
        'success' => false,
        'message' => 'AI组卷失败：' . $e->getMessage()
    ]);
}

// 如果ExamModel类中没有createExam方法，这里提供一个简单的实现示例
if (!method_exists('ExamModel', 'createExam')) {
    class ExamModel {
        public function createExam($data) {
            global $pdo;
            $sql = "INSERT INTO exam (title, nums, score, description, create_time, update_time) VALUES (:title, :nums, :score, :description, :create_time, :update_time)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            return $pdo->lastInsertId();
        }
        
        public function updateExam($id, $data) {
            global $pdo;
            $sql = "UPDATE exam SET ";
            $params = [];
            foreach ($data as $key => $value) {
                $sql .= "$key = :$key, ";
                $params[":$key"] = $value;
            }
            $sql = rtrim($sql, ', ') . " WHERE id = :id";
            $params[':id'] = $id;
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        }
    }
}

// 如果QuestionModel类中没有createQuestion方法，这里提供一个简单的实现示例
if (!method_exists('QuestionModel', 'createQuestion')) {
    class QuestionModel {
        public function createQuestion($data) {
            global $pdo;
            $sql = "INSERT INTO question (exam_id, type, content, options, answer, score, analysis, create_time, update_time) VALUES (:exam_id, :type, :content, :options, :answer, :score, :analysis, :create_time, :update_time)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            return $pdo->lastInsertId();
        }
    }
}