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


// 调用AI接口
$knowledge_points .= '返回字段说明：question为题目名称，options为选项，只能有4个选项，answer为题目答案，multiSelect为是否多选，true为多选，false为单选，explanation为题目答案说明，题目为json格式：[{question: "题目1？",options: ["A.答案1", "B.答案2", "C.答案3", "D.答案4"], answer: "B",multiSelect: false,explanation: "这里是题目1的答案解析" }, {question: "题目2？",options: ["A.答案1", "B.答案2", "C.答案3", "D.答案4"], answer: ["A", "E"],multiSelect: true,explanation: "这里是答案2的解析"}]，不要有换行，不要有空格。';
$aiResponse = baiduAi($knowledge_points);
$content = $aiResponse['choices'][0]['message']['content'];
$content = stripslashes($content);
if(stripos($content,'json')!==false){
    $content = str_replace(["```json\n","\n```"],'',$content);
}
$questions = json_decode($content,true);
if(!$questions){
    log_model('aithink', 'baiduAi', [
                'data' => $content,
                'error' => '解析json失败',
            ],'error');
    echo json_encode(['success' => false, 'message' => 'AI生成题目失败'],JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 开始事务
    $pdo->beginTransaction();
    
    // 创建试卷
    $examData = [
        'title' => $title,
        'nums' => 0, // 暂时设为0，后面会更新
        'score' => 0, // 暂时设为0，后面会更新
        'create_time' => date('Y-m-d H:i:s'),
        'update_time' => date('Y-m-d H:i:s')
    ];
    
    $examId = $examModel->createExam($examData);
    
    if (!$examId) {
        throw new Exception('创建试卷失败');
    }
  
    // 计算总分
    $totalScore = count($questions) * 5;
    
    // 更新试卷的题目数量和总分
    $examModel->updateExamField($examId, [
        'nums' => count($questions),
        'score' => $totalScore,
        'update_time' => date('Y-m-d H:i:s')
    ]);
    
    // 将题目导入到数据库
    foreach ($questions as $questionData) {
        $question = [
            'exam_id' => $examId,
            'title' => $questionData['question'],
            'type' => $questionData['multiSelect'] ? 2 : 1,
            'options' => json_encode($questionData['options'],JSON_UNESCAPED_UNICODE),
            'answer' => $questionData['multiSelect'] ? implode('',$questionData['answer']) : $questionData['answer'],
            'score' => 5,
            'analysis' => $questionData['explanation']
        ];
        
        $questionId = $questionModel->addQuestion($question);
        
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


