<?php
require_once 'logger.php';

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
        try {
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

            log_model('QuestionModel', 'getAllQuestions', [
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search,
                'total' => $total,
                'result' => count($questions)
            ]);

            return [
                'questions' => $questions,
                'total' => $total,
                'pages' => ceil($total / $pageSize),
                'currentPage' => $page
            ];
        } catch (PDOException $e) {
            log_model('QuestionModel', 'getAllQuestions', [
                'error' => $e->getMessage(),
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search
            ], 'error');
            return [
                'questions' => [],
                'total' => 0,
                'pages' => 0,
                'currentPage' => $page
            ];
        }
    }

    // 根据ID获取题目
    public function getQuestionById($id) {
        try {
            $query = 'SELECT * FROM question WHERE id = :id';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            log_model('QuestionModel', 'getQuestionById', [
                'id' => $id,
                'found' => $result !== false
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'getQuestionById', [
                'error' => $e->getMessage(),
                'id' => $id
            ], 'error');
            return false;
        }
    }

    // 更新试卷题目数量和选项数量
    public function updateExamQuestionCount($exam_id) {
        try {
            $query = 'UPDATE exam SET question_count = (SELECT COUNT(*) FROM question WHERE exam_id = :exam_id),
                      option_count = (SELECT COUNT(DISTINCT option) FROM question WHERE exam_id = :exam_id)
                      WHERE id = :exam_id';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            log_model('QuestionModel', 'updateExamQuestionCount', [
                'exam_id' => $exam_id,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'updateExamQuestionCount', [
                'error' => $e->getMessage(),
                'exam_id' => $exam_id
            ], 'error');
            return false;
        }
    }

    // 添加题目
    public function addQuestion($data) {
        try {
            $query = 'INSERT INTO question (exam_id, title, options, type, answer, analysis) 
                      VALUES (:exam_id, :title, :options, :type, :answer, :analysis)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':exam_id', $data['exam_id'], PDO::PARAM_INT);
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':options', $data['options'], PDO::PARAM_STR);
            $stmt->bindParam(':type', $data['type'], PDO::PARAM_INT);
            $stmt->bindParam(':answer', $data['answer'], PDO::PARAM_STR);
            $stmt->bindParam(':analysis', $data['analysis'], PDO::PARAM_STR);
            $result = $stmt->execute();
            
            $questionId = $this->pdo->lastInsertId();
            
            log_model('QuestionModel', 'addQuestion', [
                'exam_id' => $data['exam_id'],
                'title' => $data['title'],
                'type' => $data['type'],
                'success' => $result,
                'question_id' => $questionId
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'addQuestion', [
                'error' => $e->getMessage(),
                'data' => $data
            ], 'error');
            return false;
        }
    }

    // 更新题目
    public function updateQuestion($id, $data) {
        try {
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
            $result = $stmt->execute();
            
            log_model('QuestionModel', 'updateQuestion', [
                'id' => $id,
                'data' => $data,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'updateQuestion', [
                'error' => $e->getMessage(),
                'id' => $id,
                'data' => $data
            ], 'error');
            return false;
        }
    }

    // 删除题目
    public function deleteQuestion($id) {
        try {
            $query = 'DELETE FROM question WHERE id = :id';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            log_model('QuestionModel', 'deleteQuestion', [
                'id' => $id,
                'success' => $result,
                'affected_rows' => $stmt->rowCount()
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'deleteQuestion', [
                'error' => $e->getMessage(),
                'id' => $id
            ], 'error');
            return false;
        }
    }

    // 批量删除题目
    public function batchDeleteQuestions($ids) {
        try {
            if (empty($ids)) {
                return false;
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $query = 'DELETE FROM question WHERE id IN (' . $placeholders . ')';
            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute($ids);
            
            log_model('QuestionModel', 'batchDeleteQuestions', [
                'ids' => $ids,
                'success' => $result,
                'count' => count($ids),
                'affected_rows' => $stmt->rowCount()
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'batchDeleteQuestions', [
                'error' => $e->getMessage(),
                'ids' => $ids,
                'count' => count($ids)
            ], 'error');
            return false;
        }
    }

    // 批量插入题目
    public function batchInsertQuestions($questions) {
        try {
            if (empty($questions)) {
                return false;
            }

            // 开始事务
            $this->pdo->beginTransaction();

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
            
            log_model('QuestionModel', 'batchInsertQuestions', [
                'count' => count($questions),
                'success' => true,
                'exam_id' => $questions[0]['exam_id'] ?? null
            ]);
            
            return true;
        } catch (Exception $e) {
            // 回滚事务
            $this->pdo->rollBack();
            // 记录错误日志
            log_model('QuestionModel', 'batchInsertQuestions', [
                'error' => $e->getMessage(),
                'count' => count($questions),
                'exam_id' => $questions[0]['exam_id'] ?? null
            ], 'error');
            return false;
        }
    }

    // 搜索试卷
    public function searchExams($keyword) {
        try {
            $query = 'SELECT id, title FROM exam WHERE title LIKE :keyword LIMIT 10';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            log_model('QuestionModel', 'searchExams', [
                'keyword' => $keyword,
                'results' => count($results)
            ]);
            
            return $results;
        } catch (PDOException $e) {
            log_model('QuestionModel', 'searchExams', [
                'error' => $e->getMessage(),
                'keyword' => $keyword
            ], 'error');
            return [];
        }
    }
}
?>