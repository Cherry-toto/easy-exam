<?php
/**
 * 会员模型类
 * 处理会员表的CRUD操作
 */
class MemberModel {
    private $pdo;

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct() {
        // 从配置文件获取数据库连接信息
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * 获取所有会员数据（带分页和搜索）
     * @param int $page 当前页码
     * @param int $pageSize 每页显示数量
     * @param string $search 搜索关键词
     * @return array 包含会员数据、总数、页数等信息的数组
     */
    public function getAllMembers($page, $pageSize, $search = '') {
        // 计算偏移量
        $offset = ($page - 1) * $pageSize;

        // 构建搜索条件
        $searchCondition = '';
        if (!empty($search)) {
            $searchCondition = "WHERE email LIKE :search ";
        }

        // 准备SQL语句
        $sql = "SELECT * FROM member {$searchCondition} ORDER BY id DESC LIMIT :offset, :pageSize";
        $stmt = $this->pdo->prepare($sql);

        // 绑定参数
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);

        // 执行查询
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM member {$searchCondition}";
        $countStmt = $this->pdo->prepare($countSql);
        if (!empty($search)) {
            $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 计算总页数
        $pages = ceil($total / $pageSize);

        return [
            'members' => $members,
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page
        ];
    }

    /**
     * 根据ID获取会员信息
     * @param int $id 会员ID
     * @return array|null 会员信息数组，不存在则返回null
     */
    public function getMemberById($id) {
        $sql = "SELECT * FROM member WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 添加新会员
     * @param array $data 会员数据
     * @return bool 是否添加成功
     */
    public function addMember($data) {
        $sql = "INSERT INTO member (email, register_time, login_time) VALUES (:email, :register_time, :login_time)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':register_time', $data['register_time'], PDO::PARAM_STR);
        $stmt->bindValue(':login_time', $data['login_time'], PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * 更新会员信息
     * @param int $id 会员ID
     * @param array $data 要更新的数据
     * @return bool 是否更新成功
     */
    public function updateMember($id, $data) {
        // 构建更新字段
        $updateFields = [];
        foreach ($data as $key => $value) {
            $updateFields[] = "{$key} = :{$key}";
        }
        $updateFieldsStr = implode(', ', $updateFields);

        $sql = "UPDATE member SET {$updateFieldsStr} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        // 绑定参数
        foreach ($data as $key => $value) {
            $stmt->bindValue(':'.$key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * 删除会员
     * @param int $id 会员ID
     * @return bool 是否删除成功
     */
    public function deleteMember($id) {
        $sql = "DELETE FROM member WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * 批量删除会员
     * @param array $ids 会员ID数组
     * @return bool 是否删除成功
     */
    public function batchDeleteMembers($ids) {
        if (empty($ids)) {
            return false;
        }

        // 构建IN子句
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM member WHERE id IN ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);

        // 绑定参数
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }
}
?>