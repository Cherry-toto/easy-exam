<?php
require_once 'config.php';

// 读取json数据
$id = $_GET['id'];
$title = $_GET['title'];
$data = file_get_contents('data/'.$id.'.js');
if($data){
    if(strpos($data,'"question"')===false){
        // 处理不正规的json
        $data = str_replace(['question','options','answer','multiSelect','explanation'],['"question"','"options"','"answer"','"multiSelect"','"explanation"'],$data);
    }

    $dd = str_replace('const shijuan = ','',$data);
    $list = json_decode($dd,true);
    if(!is_array($list)){
        echo '无法处理json';
        echo $data;
        exit;
    }

    // 创建试卷
    $num = count($list);

    $sql = "INSERT INTO exam (title, nums, score) VALUES ('$title', $num, 100)";
    $affectedRows = $pdo->exec($sql);
    if($affectedRows){
        $lastInsertId = $pdo->lastInsertId();
        foreach ($list as $v){
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

            $exam_id = $lastInsertId;
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

    }
    echo '处理成功！';

}