<?php
/**
 * 管理员模型类
 * 处理管理员相关的数据操作
 */
class AdminModel {
    private $db;

    /**
     * 构造函数，初始化数据库连接
     */
    public function __construct() {
        // 获取数据库连接
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * 获取所有管理员列表（带分页和搜索）
     * @param int $page 当前页码
     * @param int $pageSize 每页显示数量
     * @param string $search 搜索关键词
     * @return array 包含管理员列表和分页信息的数组
     */
    public function getAllAdmins($page = 1, $pageSize = 10, $search = '') {
        $offset = ($page - 1) * $pageSize;
        $params = [];

        // 构建SQL查询
        $sql = "SELECT * FROM admin";
        if (!empty($search)) {
            $sql .= " WHERE username LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY id DESC LIMIT :offset, :pageSize";
        $params[':offset'] = $offset;
        $params[':pageSize'] = $pageSize;

        // 预处理查询
        $stmt = $this->db->prepare($sql);
        // 绑定参数
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取总数
        $countSql = "SELECT COUNT(*) as total FROM admin";
        if (!empty($search)) {
            $countSql .= " WHERE username LIKE :search";
        }
        $countStmt = $this->db->prepare($countSql);
        if (!empty($search)) {
            $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // 计算总页数
        $pages = ceil($total / $pageSize);

        return [
            'admins' => $admins,
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page
        ];
    }

    /**
     * 根据ID获取管理员信息
     * @param int $id 管理员ID
     * @return array|false 管理员信息数组或false
     */
    public function getAdminById($id) {
        $sql = "SELECT * FROM admin WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 添加新管理员
     * @param array $adminData 管理员数据
     * @return bool 是否添加成功
     */
    public function addAdmin($adminData) {
        // 生成随机盐值
        $salt = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        // 密码加密: md5(md5(password) . salt)
        $password = md5(md5($adminData['password']) . $salt);

        $sql = "INSERT INTO admin (username, password, salt, create_time) VALUES (:username, :password, :salt, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $adminData['username'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->bindValue(':salt', $salt, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * 更新管理员信息
     * @param int $id 管理员ID
     * @param array $adminData 管理员数据
     * @return bool 是否更新成功
     */
    public function updateAdmin($id, $adminData) {
        $sql = "UPDATE admin SET";
        $params = [];
        $updates = [];

        // 构建更新字段
        if (isset($adminData['username'])) {
            $updates[] = " username = :username";
            $params[':username'] = $adminData['username'];
        }

        // 如果提供了密码，则更新密码
        if (isset($adminData['password']) && !empty($adminData['password'])) {
            // 生成新的盐值
            $salt = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
            // 密码加密
            $password = md5(md5($adminData['password']) . $salt);
            $updates[] = " password = :password";
            $updates[] = " salt = :salt";
            $params[':password'] = $password;
            $params[':salt'] = $salt;
        }

        // 如果没有可更新的字段，直接返回true
        if (empty($updates)) {
            return true;
        }

        $sql .= implode(',', $updates) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 删除管理员
     * @param int $id 管理员ID
     * @return bool 是否删除成功
     */
    public function deleteAdmin($id) {
        $sql = "DELETE FROM admin WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * 批量删除管理员
     * @param array $ids 管理员ID数组
     * @return bool 是否删除成功
     */
    public function batchDeleteAdmins($ids) {
        if (empty($ids)) {
            return false;
        }

        // 构建IN子句
        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $paramName = ':id' . $index;
            $placeholders[] = $paramName;
            $params[$paramName] = $id;
        }

        $sql = "DELETE FROM admin WHERE id IN (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 更新管理员密码
     * @param int $id 管理员ID
     * @param string $newPassword 新密码
     * @return bool 是否更新成功
     */
    public function updateAdminPassword($id, $newPassword) {
        // 生成随机盐值
        $salt = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        // 密码加密: md5(md5(password) . salt)
        $password = md5(md5($newPassword) . $salt);

        $sql = "UPDATE admin SET password = :password, salt = :salt WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->bindValue(':salt', $salt, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * 检查用户名是否已存在
     * @param string $username 用户名
     * @param int $excludeId 排除的ID（用于更新操作）
     * @return bool 是否存在
     */
    public function checkUsernameExists($username, $excludeId = 0) {
        $sql = "SELECT COUNT(*) FROM admin WHERE username = :username";
        $params = [':username' => $username];

        if ($excludeId > 0) {
            $sql .= " AND id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
?>