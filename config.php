<?php
// 数据库配置文件
$db_config = [
    'host' => 'localhost',
    'dbname' => 'exame_system',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'port' => 3306
];

// Redis配置
$redis_config = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => '',
    'timeout' => 2.5,
    'database' => 0
];

// 邮箱配置
$email_config = [
    'host' => 'smtp.163.com',
    'username' => 'ttuuffuu@163.com',
    'password' => 'LAHTNZGVPLMXSHNE',
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

include 'functions.php';

?>