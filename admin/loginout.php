<?php
/**
 * 后台管理退出登录
 * 清除所有登录状态并重定向到登录页面
 */

// 引入权限检测文件
require_once 'common/auth.php';

// 执行退出操作
adminLogout();
?>