<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/question_model.php';

// 设置JSON响应头
header('Content-Type: application/json; charset=utf-8');


// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo jsonResponse(false, '只允许POST请求');
    exit;
}

// 检查是否选择了试卷
if (!isset($_POST['exam_id']) || empty($_POST['exam_id'])) {
    echo jsonResponse(false, '请选择试卷');
    exit;
}

$exam_id = intval($_POST['exam_id']);

// 检查是否上传了文件
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = '文件上传失败';
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errorMsg .= '：文件大小超过限制';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMsg .= '：文件仅部分上传';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMsg .= '：未选择文件';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMsg .= '：缺少临时文件夹';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMsg .= '：写入文件失败';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMsg .= '：文件上传被扩展程序阻止';
            break;
    }
    echo jsonResponse(false, $errorMsg);
    exit;
}

// 获取上传文件信息
$file = $_FILES['file'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];

// 检查文件类型
$allowed_extensions = ['json'];
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
if (!in_array(strtolower($file_extension), $allowed_extensions)) {
    echo jsonResponse(false, '只支持JSON格式文件', [
        'allowed_extensions' => $allowed_extensions,
        'provided_extension' => $file_extension
    ]);
    exit;
}

// 检查文件大小
$max_size = 100 * 1024 * 1024; // 100MB
if ($file_size > $max_size) {
    echo jsonResponse(false, '文件大小不能超过100MB', [
        'max_size' => $max_size,
        'provided_size' => $file_size
    ]);
    exit;
}

// 创建题目模型实例
$questionModel = new QuestionModel();

// 处理上传文件
$questions = [];

if (strtolower($file_extension) === 'json') {
    // 读取JSON文件内容
    
    $jsonContent = file_get_contents($file_tmp);
    // 处理json格式
    if(strpos($jsonContent,'"question"')===false){
        // 处理不正规的json
        $jsonContent = str_replace(['question','options','answer','multiSelect','explanation'],['"question"','"options"','"answer"','"multiSelect"','"explanation"'],$jsonContent);
    }
    $questions = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo jsonResponse(false, 'JSON文件格式错误');
        exit;
    }


}else{
    echo jsonResponse(false, '文件格式错误');
    exit;
}


 foreach ($questions as $v){
    $sql = "INSERT INTO question (exam_id, title, options, type,answer, analysis) 
    VALUES (:exam_id, :title, :options, :type,:answer, :analysis)";

    // 准备查询
    $stmt = $pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':exam_id', $exam_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':options', $options);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':answer', $answer);
    $stmt->bindParam(':analysis', $analysis);

    $title = $v['question'];
    if(count($v['options'])!=4){
        continue;
    }
    $options = array_map(function($q){
        return str_replace(['A. ','B. ','C. ','D. '],'',$q);
    },$v['options']);
    $options = array_combine(['A','B','C','D'],$options);
    $options = json_encode($options,JSON_UNESCAPED_UNICODE);
    $type = $v['multiSelect'] ? 2 : 1;
    $answer = $v['multiSelect'] ? implode('',$v['answer']) : $v['answer'];
    $analysis = $v['explanation'];
    // 执行插入
    $stmt->execute();
}
// 更新试卷题目数量
$questionModel->updateExamQuestionCount($exam_id);

// 清理临时文件
unlink($file_tmp);

// 返回成功响应
echo jsonResponse(true, '导入成功', ['count' => count($questions)]);
?>