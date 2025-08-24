<?php
/**
 * 试卷数据模型
 * 封装与试卷相关的数据库操作
 */

class ExamModel {
    private $pdo;

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * 获取所有试卷列表
     * @return array 试卷列表
     */
    /**
     * 获取试卷列表（带分页）
     * @param int $page 页码（从1开始）
     * @param int $pageSize 每页数量
     * @param string $search 搜索关键词（可选）
     * @return array 包含试卷列表和分页信息的数组
     */
    public function getAllExams($page = 1, $pageSize = 10, $search = '') {
        try {
            // 计算偏移量
            $offset = ($page - 1) * $pageSize;

            // 构建查询条件
            $conditions = [];
            $params = [];

            if (!empty($search)) {
                $conditions[] = "title LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
            // 构建SQL查询 - 添加索引提示
            $sql = "SELECT id, title, nums, score, create_time, update_time FROM exam ";
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            $sql .= " ORDER BY create_time DESC LIMIT :limit OFFSET :offset";

            // 准备并执行查询
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 获取总记录数 - 使用更高效的COUNT(1)而非COUNT(*)
            $countSql = "SELECT COUNT(1) as total FROM exam";
            if (!empty($conditions)) {
                $countSql .= " WHERE " . implode(' AND ', $conditions);
            }
            $countStmt = $this->pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();
            // 计算总页数
            $totalPages = ceil($total / $pageSize);

            return [
                'exams' => $exams,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'totalPages' => $totalPages
                ]
            ];
        } catch (PDOException $e) {
            error_log('获取试卷列表失败: ' . $e->getMessage());
            return [
                'exams' => [],
                'pagination' => [
                    'total' => 0,
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'totalPages' => 0
                ]
            ];
        }
    }

    /**
     * 根据ID获取试卷信息
     * @param int $examId 试卷ID
     * @return array|null 试卷信息或null
     */
    /**
     * 根据ID获取试卷信息
     * @param int $examId 试卷ID
     * @return array|null 试卷信息或null
     */
    public function getExamById($examId) {
        try {
            // 明确指定需要的字段，而不是使用*
            $stmt = $this->pdo->prepare("SELECT id, title, nums, score, create_time, update_time FROM exam WHERE id = :id");
            $stmt->bindParam(':id', $examId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('获取试卷信息失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 添加新试卷
     * @param array $examData 试卷数据
     * @return int|bool 新增试卷的ID或false
     */
    public function addExam($examData) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO exam (title, nums, score, status, create_time, update_time) VALUES (:title, :nums, :score, :status, NOW(), NOW())");
            $stmt->bindParam(':title', $examData['title'], PDO::PARAM_STR);
            $stmt->bindParam(':nums', $examData['nums'], PDO::PARAM_INT);
            $stmt->bindParam(':score', $examData['score'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $examData['status'], PDO::PARAM_INT);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('添加试卷失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 更新试卷信息
     * @param int $examId 试卷ID
     * @param array $examData 试卷数据
     * @return bool 是否更新成功
     */
    public function updateExam($examId, $examData) {
        try {
            $stmt = $this->pdo->prepare("UPDATE exam SET title = :title, nums = :nums, score = :score, status = :status, update_time = NOW() WHERE id = :id");
            $stmt->bindParam(':title', $examData['title'], PDO::PARAM_STR);
            $stmt->bindParam(':nums', $examData['nums'], PDO::PARAM_INT);
            $stmt->bindParam(':score', $examData['score'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $examData['status'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $examId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('更新试卷失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除试卷
     * @param int $examId 试卷ID
     * @return bool 是否删除成功
     */
    public function deleteExam($examId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM exam WHERE id = :id");
            $stmt->bindParam(':id', $examId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('删除试卷失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 批量删除试卷
     * @param array $examIds 试卷ID数组
     * @return bool 是否删除成功
     */
    public function batchDeleteExams($examIds) {
        if (empty($examIds)) {
            return false;
        }

        try {
            // 使用IN子句批量删除
            $placeholders = implode(',', array_fill(0, count($examIds), '?'));
            $sql = "DELETE FROM exam WHERE id IN ($placeholders)";
            $stmt = $this->pdo->prepare($sql);

            // 绑定参数
            foreach ($examIds as $index => $id) {
                $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('批量删除试卷失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 根据试卷ID获取试卷名称
     * @param int $examId 试卷ID
     * @return string|null 试卷名称，如果不存在则返回null
     */
    public function getExamNameById($examId) {
        try {
            $stmt = $this->pdo->prepare("SELECT title FROM exam WHERE id = :id");
            $stmt->bindParam(':id', $examId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return $result ? $result : null;
        } catch (PDOException $e) {
            error_log('获取试卷名称失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 搜索试卷
     * @param string $search 搜索关键词
     * @return array 符合条件的试卷列表
     */
    public function searchExams($search) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, title, create_time FROM exam WHERE title LIKE :search ORDER BY create_time DESC");
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('搜索试卷失败: ' . $e->getMessage());
            return [];
        }
    }
}
?>