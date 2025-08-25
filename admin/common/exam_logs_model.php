<?php
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
    public function getExamLogs($page, $pageSize, $search = '') {
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
        
        return [
            'logs' => $logs,
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page,
            'pageSize' => $pageSize
        ];
    }
    
    /**
     * 删除考试记录
     * @param int $id 考试记录ID
     * @return bool 删除是否成功
     */
    public function deleteExamLog($id) {
        $stmt = $this->pdo->prepare("DELETE FROM exam_log WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 根据ID获取单条考试记录
     * @param int $id 考试记录ID
     * @return array|null 考试记录数据或null
     */
    public function getExamLogById($id) {
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取考试记录总数
     * @param string $search 搜索关键词
     * @return int 总数
     */
    public function getTotalExamLogs($search = '') {
        $where = "";
        $params = [];
        
        if (!empty($search)) {
            $where = " WHERE e.title LIKE ? OR m.email LIKE ?";
            $params = ["%$search%", "%$search%"];
        }
        
        $countQuery = "SELECT COUNT(*) as total FROM exam_log el LEFT JOIN exam e ON el.exam_id = e.id LEFT JOIN member m ON el.member_id = m.id" . $where;
        $stmt = $this->pdo->prepare($countQuery);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
}