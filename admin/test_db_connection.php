<?php
// 测试数据库连接和查询
require_once 'config.php';
require_once 'common/exam_model.php';

// 测试直接查询
echo '<h2>直接查询测试</h2>';
try {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM exam");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '查询结果数量: ' . count($exams) . '<br>';
    if (count($exams) > 0) {
        echo '<pre>';
        print_r($exams[0]);
        echo '</pre>';
    }
} catch (PDOException $e) {
    echo '查询失败: ' . $e->getMessage();
}

// 测试通过模型查询
 echo '<h2>模型查询测试</h2>';
$examModel = new ExamModel();
$exams = $examModel->getAllExams();
 echo '模型查询结果数量: ' . count($exams) . '<br>';
if (count($exams) > 0) {
    echo '<pre>';
    print_r($exams[0]);
    echo '</pre>';
}

// 检查数据库表结构
 echo '<h2>表结构测试</h2>';
 try {
    $stmt = $pdo->query("DESCRIBE exam");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<pre>';
    print_r($columns);
    echo '</pre>';
} catch (PDOException $e) {
    echo '获取表结构失败: ' . $e->getMessage();
}
?>