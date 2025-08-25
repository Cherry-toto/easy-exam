<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 处理GET请求 - 检查登录状态
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = checkLoginStatus();
    echo json_encode(['logged_in' => $user !== false, 'user' => $user]);
    exit();
}

// 处理POST请求 - 验证登录
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit();
}

// 获取输入数据
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = isset($input['email']) ? trim($input['email']) : '';
$code = isset($input['code']) ? trim($input['code']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

// 验证输入
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '请输入正确的邮箱地址']);
    exit();
}

$recaptcha = isset($input['recaptcha']) ? trim($input['recaptcha']) : '';

if ($code && !preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => '验证码格式不正确']);
    exit();
}

if($password){
    $url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
    $postdata['secret'] = '6LdW6LErAAAAABbszS5hXE7O0FxaOIKMDzpVDrur';//v2
    //$postdata['secret'] = '6LfO860rAAAAAGQejJZglxduHSr-JUUSyQPfieHl';//v2
    //$postdata['secret'] = '6Lfcj4kpAAAAAG-B78mUX5vWbkKgola-oYJjgadQ';//v3
    $postdata['response'] = $recaptcha;
    $json = curl_http($url,$postdata,'POST');

    //var_dump($res);
    $res = json_decode($json,true);

    if($res['success']){
        //验证成功
    }else{
        //验证失败！
        echo json_encode(['success' => false, 'message' => '人机验证失败，请重试！']);
        exit();
    }
}

try {
    // 验证验证码
    $redisKey = "verify_code_" . md5($email);
    if($code){
        $storedCode = $redis->get($redisKey);
            
        if (!$storedCode) {
            echo json_encode(['success' => false, 'message' => '验证码已过期，请重新获取']);
            exit();
        }
        
        if ($storedCode !== $code) {
            echo json_encode(['success' => false, 'message' => '验证码错误']);
            exit();
        }
    }
    
    
    // 开始事务
    $pdo->beginTransaction();
    
    try {

        // 检查用户是否已存在
        $stmt = $pdo->prepare("SELECT id, email, password, salt, register_time, login_time FROM member WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $current_time = date('Y-m-d H:i:s');
        
        if ($user) {
            if($password && md5($password.$user['salt']) != $user['password']){
                echo json_encode(['success' => false, 'message' => '密码错误']);
                exit();
            }
            // 更新最后登录时间
            $stmt = $pdo->prepare("UPDATE member SET login_time = ? WHERE id = ?");
            $stmt->execute([$current_time, $user['id']]);
            $user_id = $user['id'];
        } else {
            // 创建新用户
            $stmt = $pdo->prepare("INSERT INTO member (email, register_time, login_time) VALUES (?, ?, ?)");
            $stmt->execute([$email, $current_time, $current_time]);
            $user_id = $pdo->lastInsertId();
            
            $user = [
                'id' => $user_id,
                'email' => $email,
                'register_time' => $current_time,
                'login_time' => $current_time
            ];
        }
        
        // 删除已使用的验证码
        $redis->del($redisKey);
        
        // 生成登录token
        $token = generateToken($email, $user_id);
        
        // 设置cookie（有效期1个月）
        setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        setcookie('user_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        
        // 存储session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['login_time'] = $current_time;
        
        // 提交事务
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => '登录成功',
            'user' => [
                'id' => $user_id,
                'email' => $email,
                'register_time' => $user['register_time'],
                'login_time' => $current_time
            ]
        ]);
        
    } catch (Exception $e) {
        // 回滚事务
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("数据库错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系统繁忙，请稍后重试'.$e->getMessage()]);
} catch (RedisException $e) {
    error_log("Redis错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系统繁忙，请稍后重试']);
}
?>