<?php
/**
 * 会员模型类
 * 处理会员表的CRUD操作
 */
require_once 'logger.php';

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
    // 优化getAllMembers方法，添加错误处理和日志
    public function getAllMembers($page, $pageSize, $search = '') {
        try {
            // 计算偏移量
            $offset = ($page - 1) * $pageSize;

            // 构建搜索条件
            $searchCondition = '';
            $params = [];
            
            if (!empty($search)) {
                $searchCondition = "WHERE email LIKE :search";
                $params[':search'] = '%' . $search . '%';
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

            log_model('MemberModel', 'getAllMembers', [
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search,
                'total' => $total
            ]);

            return [
                'members' => $members,
                'total' => $total,
                'pages' => $pages,
                'currentPage' => $page
            ];
        } catch (PDOException $e) {
            log_model('MemberModel', 'getAllMembers', [
                'error' => $e->getMessage(),
                'page' => $page,
                'pageSize' => $pageSize,
                'search' => $search
            ]);
            return [
                'members' => [],
                'total' => 0,
                'pages' => 0,
                'currentPage' => $page
            ];
        }
    }

    // 优化getMemberById方法，添加错误处理
    public function getMemberById($id) {
        try {
            $sql = "SELECT * FROM member WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            log_model('MemberModel', 'getMemberById', [
                'id' => $id,
                'found' => $result !== false
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('MemberModel', 'getMemberById', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return null;
        }
    }

    // 优化addMember方法，添加错误处理
    public function addMember($data) {
        try {
            $sql = "INSERT INTO member (email, register_time, login_time) VALUES (:email, :register_time, :login_time)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':register_time', $data['register_time'], PDO::PARAM_STR);
            $stmt->bindValue(':login_time', $data['login_time'], PDO::PARAM_STR);
            $result = $stmt->execute();
            
            log_model('MemberModel', 'addMember', [
                'email' => $data['email'],
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('MemberModel', 'addMember', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    // 优化updateMember方法，添加错误处理
    public function updateMember($id, $data) {
        try {
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
            $result = $stmt->execute();
            
            log_model('MemberModel', 'updateMember', [
                'id' => $id,
                'data' => $data,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('MemberModel', 'updateMember', [
                'error' => $e->getMessage(),
                'id' => $id,
                'data' => $data
            ]);
            return false;
        }
    }

    // 优化deleteMember方法，添加错误处理
    public function deleteMember($id) {
        try {
            $sql = "DELETE FROM member WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            log_model('MemberModel', 'deleteMember', [
                'id' => $id,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('MemberModel', 'deleteMember', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            return false;
        }
    }

    // 优化batchDeleteMembers方法，添加错误处理
    public function batchDeleteMembers($ids) {
        if (empty($ids)) {
            return false;
        }

        try {
            // 构建IN子句
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM member WHERE id IN ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);

            // 绑定参数
            foreach ($ids as $index => $id) {
                $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
            }

            $result = $stmt->execute();
            
            log_model('MemberModel', 'batchDeleteMembers', [
                'ids' => $ids,
                'success' => $result
            ]);
            
            return $result;
        } catch (PDOException $e) {
            log_model('MemberModel', 'batchDeleteMembers', [
                'error' => $e->getMessage(),
                'ids' => $ids
            ]);
            return false;
        }
    }
}
?>