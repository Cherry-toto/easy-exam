<?php
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

// 统一JSON响应函数
function jsonResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    return json_encode($response, JSON_UNESCAPED_UNICODE);
}

function getRandChar($length = 8){
  $str = null;
  $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
  $max = strlen($strPol)-1;
  
  for($i=0;$i<$length;$i++){
    $str.=$strPol[rand(0,$max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
  }
  
  return $str;
}