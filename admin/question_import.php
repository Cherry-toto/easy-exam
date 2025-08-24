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

// 统一JSON响应函数
function jsonResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    return json_encode($response, JSON_UNESCAPED_UNICODE);
}

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
$allowed_extensions = ['xls', 'xlsx', 'csv'];
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
if (!in_array(strtolower($file_extension), $allowed_extensions)) {
    echo jsonResponse(false, '只支持Excel或CSV格式文件', [
        'allowed_extensions' => $allowed_extensions,
        'provided_extension' => $file_extension
    ]);
    exit;
}

// 检查文件大小
$max_size = 5 * 1024 * 1024; // 5MB
if ($file_size > $max_size) {
    echo jsonResponse(false, '文件大小不能超过5MB', [
        'max_size' => $max_size,
        'provided_size' => $file_size
    ]);
    exit;
}

// 创建题目模型实例
$questionModel = new QuestionModel();

// 处理上传文件
$questions = [];

if (in_array(strtolower($file_extension), ['xls', 'xlsx'])) {
    // 检查是否安装了PHPExcel
if (!class_exists('PHPExcel')) {
    // 尝试自动加载
    if (file_exists('common/PHPExcel.php')) {
        require_once 'common/PHPExcel.php';
    } else {
        echo jsonResponse(false, '未找到PHPExcel库，请先安装');
        exit;
    }
}

    try {
        // 读取Excel文件
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        if ($file_extension === 'xls') {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
        }
        $objPHPExcel = $objReader->load($file_tmp);

        // 获取第一个工作表
        $sheet = $objPHPExcel->getSheet(0);

        // 获取最大行号
        $highestRow = $sheet->getHighestRow();

        // 从第二行开始读取数据（第一行是表头）
        for ($row = 2; $row <= $highestRow; $row++) {
            $title = $sheet->getCell('A' . $row)->getValue();
            $type = $sheet->getCell('B' . $row)->getValue();
            $option_a = $sheet->getCell('C' . $row)->getValue();
            $option_b = $sheet->getCell('D' . $row)->getValue();
            $option_c = $sheet->getCell('E' . $row)->getValue();
            $option_d = $sheet->getCell('F' . $row)->getValue();
            $correct_answer = $sheet->getCell('G' . $row)->getValue();
            $analysis = $sheet->getCell('H' . $row)->getValue();

            // 跳过空行
            if (empty($title)) {
                continue;
            }

            // 处理题目类型
            $type_id = strtolower($type) === '单选题' || $type === 1 ? 1 : 2;

            // 构造选项数组
            $options = [
                'A' => $option_a,
                'B' => $option_b,
                'C' => $option_c,
                'D' => $option_d
            ];

            // 构造题目数据
            $question = [
                'exam_id' => $exam_id,
                'title' => $title,
                'options' => json_encode($options),
                'type' => $type_id,
                'answer' => $correct_answer,
                'analysis' => $analysis,
                'score' => 5, // 默认分值
                'difficulty' => 1, // 默认难度
                'create_time' => date('Y-m-d H:i:s')
            ];

            $questions[] = $question;
        }
    } catch (Exception $e) {
            echo jsonResponse(false, 'Excel文件解析失败: ' . $e->getMessage());
            exit;
        }
} else if (strtolower($file_extension) === 'csv') {
    try {
        // 打开CSV文件
        $handle = fopen($file_tmp, 'r');

        // 跳过表头
        fgetcsv($handle);

        // 读取数据
        while (($data = fgetcsv($handle)) !== false) {
            // 确保有足够的列
            if (count($data) < 7) {
                continue;
            }

            $title = $data[0];
            $type = $data[1];
            $option_a = $data[2];
            $option_b = $data[3];
            $option_c = $data[4];
            $option_d = $data[5];
            $correct_answer = $data[6];
            $analysis = isset($data[7]) ? $data[7] : '';

            // 跳过空行
            if (empty($title)) {
                continue;
            }

            // 处理题目类型
            $type_id = strtolower($type) === '单选题' || $type === 1 ? 1 : 2;

            // 构造选项数组
            $options = [
                'A' => $option_a,
                'B' => $option_b,
                'C' => $option_c,
                'D' => $option_d
            ];

            // 构造题目数据
            $question = [
                'exam_id' => $exam_id,
                'title' => $title,
                'options' => json_encode($options),
                'type' => $type_id,
                'answer' => $correct_answer,
                'analysis' => $analysis,
                'score' => 5, // 默认分值
                'difficulty' => 1, // 默认难度
                'create_time' => date('Y-m-d H:i:s')
            ];

            $questions[] = $question;
        }

        fclose($handle);
    } catch (Exception $e) {
        echo jsonResponse(false, 'CSV文件解析失败: ' . $e->getMessage());
        exit;
    }
}

// 批量插入题目
if (!empty($questions)) {
    $totalCount = count($questions);
    try {
        $success = $questionModel->batchInsertQuestions($questions);
        if ($success) {
            echo jsonResponse(true, '导入成功', [
                'count' => $totalCount,
                'exam_id' => $exam_id
            ]);
        } else {
            echo jsonResponse(false, '导入失败: 数据库操作未成功');
        }
    } catch (Exception $e) {
        echo jsonResponse(false, '导入失败: ' . $e->getMessage());
    }
} else {
    echo jsonResponse(false, '未找到有效题目数据');
}

// 清理临时文件
unlink($file_tmp);
?>