<?php
/**
 * 公共权限检测方法
 * 用于验证用户是否已登录
 */
function checkAdminLogin() {
    // 检查是否存在登录Cookie
    if (!isset($_COOKIE['admin_logged_in']) || $_COOKIE['admin_logged_in'] !== 'true') {
        // 未登录，重定向到登录页面
        header('Location: login.php');
        exit;
    }

    // 验证登录信息（可选，可根据实际需求添加数据库验证）
    $adminId = isset($_COOKIE['admin_id']) ? $_COOKIE['admin_id'] : '';
    $adminUsername = isset($_COOKIE['admin_username']) ? $_COOKIE['admin_username'] : '';

    if (empty($adminId) || empty($adminUsername)) {
        // 登录信息不完整，清除Cookie并重定向
        setcookie('admin_logged_in', '', time() - 3600, '/');
        setcookie('admin_id', '', time() - 3600, '/');
        setcookie('admin_username', '', time() - 3600, '/');
        header('Location: login.php');
        exit;
    }

    // 登录验证通过，返回用户信息
    return [
        'id' => $adminId,
        'username' => $adminUsername
    ];
}

/**
 * 登出函数
 */
function adminLogout() {
    // 清除登录Cookie
    setcookie('admin_logged_in', '', time() - 3600, '/');
    setcookie('admin_id', '', time() - 3600, '/');
    setcookie('admin_username', '', time() - 3600, '/');
    // 重定向到登录页面
    header('Location: login.php');
    exit;
}
?>