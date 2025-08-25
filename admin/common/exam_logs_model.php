<?php
require_once 'logger.php';

class ExamLogsModel {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 获取考试记录列表（带分页和搜索）
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @param string $search 搜索关键词
     * @return array 包含考试记录和分页信息的数组
     */
    // 修改getExamLogs方法，添加错误处理
    public function getExamLogs($page, $pageSize, $search = '') {
        try {
            $offset = ($page - 1) * $pageSize;
            
            // 基础查询
            $baseQuery = "
                SELECT 
                    el.id,
                    el.exam_id,
                    el.member_id,
                    el.score,
                    el.use_time,
                    el.create_time,
                    e.title as exam_title,
                    m.email as member_email
                FROM exam_log el
                LEFT JOIN exam e ON el.exam_id = e.id
                LEFT JOIN member m ON el.member_id = m.id
            ";
            
            // 搜索条件
            $where = "";
            $params = [];
            
            if (!empty($search)) {
                $where = " WHERE e.title LIKE ? OR m.email LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
            
            // 获取总数
            $countQuery = "SELECT COUNT(*) as total FROM exam_log el LEFT JOIN exam e ON el.exam_id = e.id LEFT JOIN member m ON el.member_id = m.id" . $where;
            $stmt = $this->pdo->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            // 获取数据
            $dataQuery = $baseQuery . $where . " ORDER BY el.create_time DESC LIMIT " . intval($pageSize) . " OFFSET " . intval($offset);
            $stmt = $this->pdo->prepare($dataQuery);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 计算分页信息
            $pages = ceil($total / $pageSize);
            
            log_model('ExamLogsModel', 'getExamLogs', [
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search,
                'total' => $total
            ]);
            
            return [
                'logs' => $logs,
                'total' => $total,
                'pages' => $pages,
                'currentPage' => $page,
                'pageSize' => $pageSize
            ];
        } catch (PDOException $e) {
            log_model('ExamLogsModel', 'getExamLogs', [
                'error' => $e->getMessage(),
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search
            ]);
            return [
                'logs' => [],
                'total' => 0,
                'pages' => 0,
                'currentPage' => $page,
                'pageSize' => $pageSize
            ];
        }
    }

    // 修改deleteExamLog方法，添加错误处理
    public function deleteExamLog($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM exam_log WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            log_model('ExamLogsModel', 'deleteExamLog', [
                'id' => $id,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('ExamLogsModel', 'deleteExamLog', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return false;
        }
    }

    // 修改getExamLogById方法，添加错误处理
    public function getExamLogById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    el.id,
                    el.exam_id,
                    el.member_id,
                    el.score,
                    el.use_time,
                    el.create_time,
                    e.title as exam_title,
                    m.email as member_email
                FROM exam_log el
                LEFT JOIN exam e ON el.exam_id = e.id
                LEFT JOIN member m ON el.member_id = m.id
                WHERE el.id = ?
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            log_model('ExamLogsModel', 'getExamLogById', [
                'id' => $id,
                'found' => $result !== false
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('ExamLogsModel', 'getExamLogById', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return null;
        }
    }

    // 修改getTotalExamLogs方法，添加错误处理
    public function getTotalExamLogs($search = '') {
        try {
            $where = "";
            $params = [];
            
            if (!empty($search)) {
                $where = " WHERE e.title LIKE ? OR m.email LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
            
            $countQuery = "SELECT COUNT(*) as total FROM exam_log el LEFT JOIN exam e ON el.exam_id = e.id LEFT JOIN member m ON el.member_id = m.id" . $where;
            $stmt = $this->pdo->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];
            
            log_model('ExamLogsModel', 'getTotalExamLogs', [
                'search' => $search,
                'total' => $total
            ]);
            
            return $total;
        } catch (PDOException $e) {
            log_model('ExamLogsModel', 'getTotalExamLogs', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);
            return 0;
        }
    }
}