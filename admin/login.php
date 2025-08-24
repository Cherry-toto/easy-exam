<?php
// 检查是否已登录
if(isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === 'true') {
    header('Location: index.php');
    exit;
}

// 引入数据库配置
require_once 'config.php';

$error = '';

// 处理登录表单提交
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 验证输入
    if(empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        try {
            // 查询管理员信息
            $stmt = $pdo->prepare("SELECT id, username, password, salt FROM admin WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if($admin) {
                // 验证密码
                $hashed_password = md5(md5($password) . $admin['salt']);
                if($hashed_password === $admin['password']) {
                    // 登录成功，设置cookie
                    $expiry = time() + (7 * 24 * 60 * 60); // 7天
                    setcookie('admin_logged_in', 'true', $expiry, '/');
                    setcookie('admin_id', $admin['id'], $expiry, '/');
                    setcookie('admin_username', $admin['username'], $expiry, '/');

                    // 记录登录时间和IP
                    $login_time = date('Y-m-d H:i:s');
                    $login_ip = $_SERVER['REMOTE_ADDR'];
                    $update_stmt = $pdo->prepare("UPDATE admin SET login_time = :login_time, login_ip = :login_ip WHERE id = :id");
                    $update_stmt->bindParam(':login_time', $login_time);
                    $update_stmt->bindParam(':login_ip', $login_ip);
                    $update_stmt->bindParam(':id', $admin['id']);
                    $update_stmt->execute();

                    header('Location: index.php');
                    exit;
                } else {
                    $error = '密码错误';
                }
            } else {
                $error = '用户名不存在';
            }
        } catch(PDOException $e) {
            $error = '数据库错误: ' . $e->getMessage();
        }
    }
}

// 关闭数据库连接
closeConnection($pdo);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>在线自学考试系统 - 管理员登录</title>
    <!-- Tailwind CSS -->
    <link href="../css/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../fontawesome-free-6.7.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 py-6 px-8 text-center">
            <i class="fas fa-user-shield text-4xl text-white mb-3"></i>
            <h1 class="text-2xl font-bold text-white">管理员登录</h1>
            <p class="text-blue-100 mt-1">请输入您的用户名和密码</p>
        </div>
        <div class="p-8">
            <?php if(!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-6">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">用户名</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="username" name="username" class="pl-10 w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" placeholder="请输入用户名">
                    </div>
                </div>
                <div class="mb-8">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">密码</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="pl-10 w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" placeholder="请输入密码">
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">记住我</label>
                    </div>
                    <a href="#" class="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">忘记密码?</a>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition-colors duration-300 flex items-center justify-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>登录
                </button>
            </form>
        </div>
    </div>
</body>
</html>