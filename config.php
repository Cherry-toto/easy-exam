<?php
// 数据库配置文件
$db_config = [
    'host' => 'localhost',
    'dbname' => 'exame_system',
    'username' => 'exame_system',
    'password' => 'ri5RJX7b3snYW8kF',
    'charset' => 'utf8mb4',
    'port' => 33060
];

// Redis配置
$redis_config = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => 'rmcAbCDas2d28bdb2sd312',
    'timeout' => 2.5,
    'database' => 0
];

// 邮箱配置
$email_config = [
    'host' => 'smtp.163.com',
    'username' => '',
    'password' => '',
    'port' => 465,
    'from' => 'ttuuffuu@163.com',
    'from_name' => '在线考试系统'
];

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}", 
                   $db_config['username'], 
                   $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 创建Redis连接（可选）
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect($redis_config['host'], $redis_config['port'], $redis_config['timeout']);
        if (!empty($redis_config['password'])) {
            $redis->auth($redis_config['password']);
        }
        $redis->select($redis_config['database']);
    } else {
        // Redis扩展不存在，使用空对象模式
        $redis = new class {
            public function __call($name, $arguments) { return false; }
            public function __get($name) { return null; }
        };
    }
} catch(Exception $e) {
    // Redis连接失败，使用空对象模式
    $redis = new class {
        public function __call($name, $arguments) { return false; }
        public function __get($name) { return null; }
    };
}

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 开启session
session_start();

// 检查用户登录状态
function checkLoginStatus() {
    if (isset($_COOKIE['user_email']) && isset($_COOKIE['user_token'])) {
        $email = $_COOKIE['user_email'];
        $token = $_COOKIE['user_token'];
        
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM member WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && hash('sha256', $email . $user['id']) === $token) {
            return $user;
        }
    }
    return false;
}

// 生成登录token
function generateToken($email, $userId) {
    return hash('sha256', $email . $userId);
}

// CURL
 function curl_http($url, $data = null, $method = 'GET')
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($data != null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //结果是否显示出来，1不显示，0显示
        //判断是否https
        if (strpos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
            curl_setopt($ch, CURLOPT_USERAGENT, $UserAgent);
        }


        $data = curl_exec($ch);
        curl_close($ch);
        if ($data === FALSE) {
            $data = "curl Error:" . curl_error($ch);
        }
        return $data;
    }

?>