<?php
// 题目模型类
class QuestionModel {
    private $pdo;

    // 构造函数，初始化数据库连接
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // 获取所有题目
    public function getAllQuestions($page = 1, $pageSize = 10, $search = '') {
        $offset = ($page - 1) * $pageSize;
        $params = [];
        $query = 'SELECT q.*, e.title as exam_title FROM question q LEFT JOIN exam e ON q.exam_id = e.id WHERE 1=1';

        if (!empty($search)) {
            $query .= ' AND (q.title LIKE :search OR e.title LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $query .= ' ORDER BY q.create_time DESC LIMIT :offset, :pageSize';
        $params[':offset'] = $offset;
        $params[':pageSize'] = $pageSize;

        $stmt = $this->pdo->prepare($query);
        // 明确指定参数类型为整数
        $stmt->bindParam(':offset', $params[':offset'], PDO::PARAM_INT);
        $stmt->bindParam(':pageSize', $params[':pageSize'], PDO::PARAM_INT);
        if (!empty($search)) {
            $stmt->bindParam(':search', $params[':search'], PDO::PARAM_STR);
        }
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取总数
        $countQuery = 'SELECT COUNT(*) as total FROM question q LEFT JOIN exam e ON q.exam_id = e.id WHERE 1=1';
        if (!empty($search)) {
            $countQuery .= ' AND (q.title LIKE :search OR e.title LIKE :search)';
        }
        $countStmt = $this->pdo->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'questions' => $questions,
            'total' => $total,
            'pages' => ceil($total / $pageSize),
            'currentPage' => $page
        ];
    }

    // 根据ID获取题目
    public function getQuestionById($id) {
        $query = 'SELECT * FROM question WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 添加题目
    public function addQuestion($data) {
        $query = 'INSERT INTO question (exam_id, title, options, type, answer, analysis) 
                  VALUES (:exam_id, :title, :options, :type, :answer, :analysis)';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':exam_id', $data['exam_id'], PDO::PARAM_INT);
        $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':options', $data['options'], PDO::PARAM_STR);
        $stmt->bindParam(':type', $data['type'], PDO::PARAM_INT);
        $stmt->bindParam(':answer', $data['answer'], PDO::PARAM_STR);
        $stmt->bindParam(':analysis', $data['analysis'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    // 更新题目
    public function updateQuestion($id, $data) {
        $query = 'UPDATE question SET exam_id = :exam_id, title = :title, options = :options, 
                  type = :type, answer = :answer, analysis = :analysis WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':exam_id', $data['exam_id'], PDO::PARAM_INT);
        $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':options', $data['options'], PDO::PARAM_STR);
        $stmt->bindParam(':type', $data['type'], PDO::PARAM_INT);
        $stmt->bindParam(':answer', $data['answer'], PDO::PARAM_STR);
        $stmt->bindParam(':analysis', $data['analysis'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 删除题目
    public function deleteQuestion($id) {
        $query = 'DELETE FROM question WHERE id = :id';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 批量删除题目
    public function batchDeleteQuestions($ids) {
        if (empty($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = 'DELETE FROM question WHERE id IN (' . $placeholders . ')';
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($ids);
    }

    // 批量插入题目
    public function batchInsertQuestions($questions) {
        if (empty($questions)) {
            return false;
        }

        // 开始事务
        $this->pdo->beginTransaction();

        try {
            $query = 'INSERT INTO question (exam_id, title, options, type, answer, analysis, score, difficulty, create_time) 
                      VALUES (:exam_id, :title, :options, :type, :answer, :analysis, :score, :difficulty, :create_time)';
            $stmt = $this->pdo->prepare($query);

            foreach ($questions as $question) {
                $stmt->bindParam(':exam_id', $question['exam_id'], PDO::PARAM_INT);
                $stmt->bindParam(':title', $question['title'], PDO::PARAM_STR);
                $stmt->bindParam(':options', $question['options'], PDO::PARAM_STR);
                $stmt->bindParam(':type', $question['type'], PDO::PARAM_INT);
                $stmt->bindParam(':answer', $question['answer'], PDO::PARAM_STR);
                $stmt->bindParam(':analysis', $question['analysis'], PDO::PARAM_STR);
                $stmt->bindParam(':score', $question['score'], PDO::PARAM_INT);
                $stmt->bindParam(':difficulty', $question['difficulty'], PDO::PARAM_INT);
                $stmt->bindParam(':create_time', $question['create_time'], PDO::PARAM_STR);
                $stmt->execute();
            }

            // 提交事务
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // 回滚事务
            $this->pdo->rollBack();
            // 记录错误日志
            error_log('批量插入题目失败: ' . $e->getMessage());
            return false;
        }
    }

    // 搜索试卷
    public function searchExams($keyword) {
        $query = 'SELECT id, title FROM exam WHERE title LIKE :keyword LIMIT 10';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>