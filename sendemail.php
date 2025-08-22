<?php
require_once 'config.php';
require_once 'vendor/autoload.php'; // PHPMailer自动加载

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允许POST请求
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

// 验证邮箱格式
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '请输入正确的邮箱地址']);
    exit();
}

$url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
$postdata['secret'] = '6LfO860rAAAAAGQejJZglxduHSr-JUUSyQPfieHl';//v2
//$postdata['secret'] = '6Lfcj4kpAAAAAG-B78mUX5vWbkKgola-oYJjgadQ';//v3
$postdata['response'] = $_REQUEST['recaptcha'];
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

try {
    // 检查发送频率限制（60秒内只能发送一次）
    $lastSentKey = "last_sent_" . md5($email);
    $lastSent = $redis->get($lastSentKey);
    
    if ($lastSent && (time() - intval($lastSent)) < 60) {
        $remaining = 60 - (time() - intval($lastSent));
        echo json_encode(['success' => false, 'message' => "请等待 {$remaining} 秒后再次发送"]);
        exit();
    }

    // 生成6位验证码
    $code = sprintf('%06d', mt_rand(0, 999999));
    
    // 存储验证码到Redis，有效期5分钟
    $redisKey = "verify_code_" . md5($email);
    $redis->setex($redisKey, 300, $code);
    
    // 记录发送时间
    $redis->setex($lastSentKey, 60, time());

    // 发送邮件
    $mail = new PHPMailer();
    
    // 服务器设置
    $mail->isSMTP();
    $mail->Host = $email_config['host'];
    $mail->SMTPAuth = true;
    //$mail->SMTPDebug = true;
    $mail->Username = $email_config['username'];
    $mail->Password = $email_config['password'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $email_config['port'];
    $mail->CharSet = 'UTF-8';
    
    // 发件人设置
    $mail->setFrom($email_config['from'], $email_config['from_name']);
    // 收件人设置
    $mail->addAddress($email,$email_config['from_name']);
    
    // 邮件内容
    $mail->isHTML(true);
    $mail->Subject = '【在线考试系统】登录验证码';
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center;'>
            <h1 style='margin: 0; font-size: 24px;'>在线考试系统</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>安全登录验证</p>
        </div>
        
        <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
            <p style='font-size: 16px; color: #333; margin-bottom: 20px;'>
                您好！感谢您使用在线考试系统。
            </p>
            
            <p style='font-size: 16px; color: #333; margin-bottom: 20px;'>
                您的登录验证码是：
            </p>
            
            <div style='background: white; border: 2px solid #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;'>
                <span style='font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px;'>{$code}</span>
            </div>
            
            <p style='font-size: 14px; color: #666; margin-bottom: 15px;'>
                <strong>重要提示：</strong>
            </p>
            <ul style='font-size: 14px; color: #666; margin-bottom: 20px; padding-left: 20px;'>
                <li>验证码有效期为5分钟</li>
                <li>请勿将此验证码透露给他人</li>
                <li>如非本人操作，请忽略此邮件</li>
            </ul>
            
            <p style='font-size: 12px; color: #999; text-align: center; margin-top: 20px;'>
                此邮件由在线考试系统自动发送，请勿直接回复
            </p>
        </div>
    </div>
    ";
    
    $mail->AltBody = "在线考试系统登录验证码：{$code}\n\n验证码有效期为5分钟，请勿透露给他人。";
    
    $mail->send();
    
    echo json_encode(['success' => true, 'message' => '验证码已发送到您的邮箱']);
    
} catch (Exception $e) {
    // 记录错误日志
    error_log("发送邮件失败: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => '发送失败，请稍后重试']);
} catch (RedisException $e) {
    error_log("Redis错误: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '系统繁忙，请稍后重试']);
}
?>