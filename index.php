<?php
require_once 'config.php';

// 检查用户是否已登录
$user = checkLoginStatus();
if (!$user) {
    //header('Location: login.html');
    //exit();
    $userEmail = '请先登录系统~';
}else{
    // 设置用户信息到JS变量
    $userEmail = htmlspecialchars($user['email']);
    $userId = intval($user['id']);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页 - 在线考试系统</title>
       <link href="/css/tailwind.min.css" rel="stylesheet">
    <link href="/fontawesome-free-6.7.2/css/all.min.css" rel="stylesheet">
    <script src="/js/jquery.min.js"></script>
    <script>
        // 开始考试函数 - 放在这里确保在全局作用域中定义
        function startExam(examId) {
            window.location.href = `/exam.html?id=${examId}`;
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding-bottom: 80px;
            padding-top: 16px;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .search-input {
            transition: all 0.3s ease;
        }
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .loading-spinner {
            display: none;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nav-active {
            color: #667eea !important;
            border-bottom: 2px solid #667eea;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .tag {
            display: inline-block;
            padding: 4px 8px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 12px;
            font-size: 12px;
            margin-right: 4px;
        }
        .bottom-nav-link {
            flex: 1;
            text-align: center;
            padding: 12px 0;
        }
        .bottom-nav-link i {
            font-size: 24px;
            margin-bottom: 4px;
        }
        .bottom-nav-link span {
            font-size: 12px;
            display: block;
        }
        @media (max-width: 768px) {
            .bottom-nav-link span {
                display: none;
            }
            .bottom-nav-link i {
                font-size: 28px;
                margin-bottom: 0;
            }
            .bottom-nav-link {
                padding: 16px 0;
            }
        }
        @media (min-width: 769px) {
            .bottom-nav-link span {
                display: block;
            }
        }
        .nav-link-mobile {
            color: #374151;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .nav-link-mobile:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>
    <!-- 顶部导航 -->
    <nav class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-2xl text-purple-600 mr-2"></i>
                    <span class="font-bold text-lg">在线考试</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#" class="nav-link nav-active text-gray-700 font-medium" data-tab="home">
                        <i class="fas fa-home mr-1"></i>首页
                    </a>
                    <a href="/exam-list.html" class="nav-link text-gray-700 font-medium">
                <i class="fas fa-list-alt mr-1"></i>试卷列表
            </a>
                    <a href="/user.html" class="nav-link text-gray-700 font-medium">
                <i class="fas fa-user mr-1"></i>个人中心
            </a>
                </div>
                <div class="md:hidden">
                    <button id="mobileMenuBtn" class="text-gray-700 p-2">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- 移动端菜单 -->
    <div id="mobileMenu" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <div class="absolute top-0 right-0 bottom-0 w-64 bg-white shadow-lg">
            <div class="flex justify-between items-center p-4 border-b">
                <span class="font-semibold text-gray-800">菜单</span>
                <button id="closeMobileMenu" class="text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="py-4">
                <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 nav-link-mobile" data-tab="home">
                    <i class="fas fa-home mr-3 w-5"></i>首页
                </a>
                <a href="/exam-list.html" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 nav-link-mobile">
                    <i class="fas fa-list-alt mr-3 w-5"></i>试卷列表
                </a>
                <a href="/user.html" class="block px-4 py-3 text-gray-700 hover:bg-gray-100 nav-link-mobile">
                    <i class="fas fa-user mr-3 w-5"></i>个人中心
                </a>
            </div>
        </div>
    </div>

    <!-- 主要内容区域 -->
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- 欢迎区域 -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 fade-in">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">欢迎回来，<?php echo $userEmail; ?>！</h1>
            <p class="text-gray-600">开始您的学习之旅，选择试卷或查看错题</p>
        </div>

        <!-- 搜索区域 -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex space-x-4">
                <div class="flex-1">
                    <input type="text" 
                           id="examSearch" 
                           placeholder="搜索试卷..." 
                           class="search-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
                <div class="flex-1">
                    <input type="text" 
                           id="mistakeSearch" 
                           placeholder="搜索错题..." 
                           class="search-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                </div>
            </div>
        </div>

        <!-- 试卷列表 -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-file-alt mr-2 text-purple-600"></i>试卷列表
                </h2>
                <button id="refreshExams" class="text-purple-600 hover:text-purple-800">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div id="examList" class="space-y-4">
                <div class="loading-spinner text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i>
                    <p class="mt-2 text-gray-600">加载中...</p>
                </div>
            </div>
        </div>

        <!-- 错题列表 -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>错题列表
            </h2>
            <button id="refreshMistakes" class="text-red-600 hover:text-red-800">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div id="mistakeList" class="space-y-4">
            <div class="loading-spinner text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-red-600"></i>
                <p class="mt-2 text-gray-600">加载中...</p>
            </div>
        </div>
    </div>

    <!-- APP下载板块 -->
    <div id="appdownload" class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-sm p-8 text-white text-center mb-6">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-center mb-4">
                <i class="fas fa-mobile-alt text-5xl"></i>
            </div>
            <h2 class="text-2xl font-bold mb-2">下载APP，随时随地学习</h2>
            <p class="text-purple-100 mb-6">扫码下载官方APP，享受更便捷的学习体验</p>
            
            <div class="flex flex-col md:flex-row justify-center items-center space-y-4 md:space-y-0 md:space-x-8">
                <!-- 二维码 -->
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <div class="w-32 h-32 bg-gray-100 rounded flex items-center justify-center mb-2">
                        <img src="/app/qrcode.png" alt="二维码" class="w-30 h-30">
                    </div>
                    <p class="text-sm text-gray-800 font-medium">扫码下载APP</p>
                </div>
                
                <!-- 下载按钮 -->
                <div class="space-y-4">
                    <a href="/app/1.0.0_release.apk" class="block bg-white text-purple-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                        <i class="fab fa-android mr-2"></i>
                        Android版下载
                    </a>
                    <a href="javascript:;" class="block bg-white text-purple-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                        <i class="fab fa-apple mr-2"></i>
                        暂不支持iOS版
                    </a>
                </div>
            </div>
            
            <div class="mt-6 text-sm text-purple-100">
                <p>支持安卓 | 最新版本：v1.0.0</p>
            </div>
        </div>
    </div>

    <!-- 联系方式板块 -->
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 text-white">
        <div class="max-w-4xl mx-auto px-4 py-12">
            <div class="text-center mb-8">
                <h3 class="text-3xl font-bold mb-2">联系我们</h3>
                <p class="text-blue-100 opacity-90">随时为您提供专业支持</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 微信联系 -->
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fab fa-weixin text-2xl text-white"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">微信客服</h4>
                    <p class="text-blue-100 text-sm mb-3">扫码添加客服微信</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="font-mono text-sm">TF-2581047041</p>
                    </div>
                    <button class="copy-wechat mt-3 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                         <i class="fas fa-copy mr-1"></i>复制微信号
                     </button>
                </div>
                
                <!-- 邮箱联系 -->
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-envelope text-2xl text-white"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">邮箱支持</h4>
                    <p class="text-blue-100 text-sm mb-3">发送邮件获取帮助</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="font-mono text-sm">2581047041@qq.com</p>
                    </div>
                    <button class="copy-email mt-3 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                        <i class="fas fa-envelope mr-1"></i>复制邮箱
                    </button>
                </div>
                
                <!-- 服务时间 -->
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 text-center transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-clock text-2xl text-white"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">服务时间</h4>
                    <p class="text-blue-100 text-sm mb-3">专业客服在线</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="font-bold">每日 9:00-21:00</p>
                        <p class="text-xs text-blue-100 mt-1">节假日无休</p>
                    </div>
                    <div class="mt-3 text-xs text-blue-100">
                        <i class="fas fa-shield-alt mr-1"></i>7×12小时在线支持
                    </div>
                </div>
            </div>
            
            <!-- 快速联系按钮 -->
            <div class="mt-8 text-center">
                <p class="text-blue-100 mb-4">遇到问题？立即联系我们获取帮助</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <button class="copy-wechat-btn bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center cursor-pointer">
                        <i class="fab fa-weixin mr-2"></i>
                        复制微信号
                    </button>
                    <button class="copy-email-btn bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center justify-center cursor-pointer">
                        <i class="fas fa-envelope mr-2"></i>
                        复制邮箱
                    </button>
                </div>
            </div>
            
            <!-- 版权信息 -->
            <div class="mt-8 pt-6 border-t border-white border-opacity-20 text-center">
                <p class="text-blue-100 text-sm">
                    <i class="fas fa-heart text-red-400 mr-1"></i>
                    用心服务每一位用户 | 
                    <span class="font-medium">在线考试系统</span>
                </p>
                <p class="text-blue-200 text-xs mt-1">
                    © 2025 在线考试系统. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- 底部导航 -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden">
        <div class="flex justify-around py-3">
            <a href="#" class="bottom-nav-link text-center py-2 px-4 nav-active" data-tab="home">
                <i class="fas fa-home text-2xl"></i>
                <span class="hidden">首页</span>
            </a>
            <a href="/exam-list.html" class="bottom-nav-link text-center py-2 px-4">
                <i class="fas fa-list-alt text-2xl"></i>
                <span class="hidden">试卷</span>
            </a>
            <a href="/user.html" class="bottom-nav-link text-center py-2 px-4">
                <i class="fas fa-user text-2xl"></i>
                <span class="hidden">个人中心</span>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let examData = [];
            let mistakeData = [];

            // 通用复制函数
            function copyToClipboard(text, element, successText, originalText) {
                // 优先使用现代的 clipboard API
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        showCopySuccess(element, successText);
                    }).catch(() => {
                        // 如果 clipboard API 失败，使用备用方案
                        fallbackCopy(text, element, successText);
                    });
                } else {
                    // 使用备用方案
                    fallbackCopy(text, element, successText);
                }
            }

            // 备用复制方案（适用于所有浏览器）
            function fallbackCopy(text, element, successText) {
                try {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '-9999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    
                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    
                    if (successful) {
                        showCopySuccess(element, successText);
                    } else {
                        showCopyError(element);
                    }
                } catch (err) {
                    console.error('复制失败:', err);
                    showCopyError(element);
                }
            }

            // 显示复制成功
            function showCopySuccess(element, successText) {
                const originalHTML = $(element).html();
                $(element).html(successText);
                setTimeout(() => {
                    $(element).html(originalHTML);
                }, 2000);
            }

            // 显示复制失败
            function showCopyError(element) {
                const originalHTML = $(element).html();
                $(element).html('<i class="fas fa-exclamation-triangle mr-1"></i>复制失败');
                setTimeout(() => {
                    $(element).html(originalHTML);
                }, 2000);
            }

            // 复制微信号功能
            $('.copy-wechat').click(function() {
                copyToClipboard('TF-2581047041', this, '<i class="fas fa-check mr-1"></i>已复制', '<i class="fas fa-copy mr-1"></i>复制微信号');
            });

            // 复制邮箱功能
            $('.copy-email').click(function() {
                copyToClipboard('2581047041@qq.com', this, '<i class="fas fa-check mr-1"></i>已复制', '<i class="fas fa-copy mr-1"></i>复制邮箱');
            });

            // 底部快速联系按钮 - 复制微信号
            $('.copy-wechat-btn').click(function() {
                copyToClipboard('TF-2581047041', this, '<i class="fas fa-check mr-2"></i>已复制', '<i class="fab fa-weixin mr-2"></i>复制微信号');
            });

            // 底部快速联系按钮 - 复制邮箱
            $('.copy-email-btn').click(function() {
                copyToClipboard('2581047041@qq.com', this, '<i class="fas fa-check mr-2"></i>已复制', '<i class="fas fa-envelope mr-2"></i>复制邮箱');
            });

            // 初始化加载数据
            loadExams();
            loadMistakes();

            // 导航切换
            $('.nav-link, .bottom-nav-link').click(function(e) {
                // 只阻止首页标签的默认行为，其他链接正常跳转
                if ($(this).data('tab') === 'home') {
                    e.preventDefault();
                    switchTab('home');
                }
                // 其他链接允许正常跳转
            });

            // 搜索功能
            $('#examSearch').on('input', function() {
                const query = $(this).val().toLowerCase();
                filterExams(query);
            });

            $('#mistakeSearch').on('input', function() {
                const query = $(this).val().toLowerCase();
                filterMistakes(query);
            });

            // 刷新按钮
            $('#refreshExams').click(function() {
                loadExams();
            });

            $('#refreshMistakes').click(function() {
                loadMistakes();
            });

            // 移动端菜单
            $('#mobileMenuBtn').click(function() {
                $('#mobileMenu').removeClass('hidden');
            });

            $('#closeMobileMenu').click(function() {
                $('#mobileMenu').addClass('hidden');
            });

            $('#mobileMenu').click(function(e) {
                if (e.target === this) {
                    $(this).addClass('hidden');
                }
            });

            $('.nav-link-mobile').click(function() {
                $('#mobileMenu').addClass('hidden');
            });

            // 加载试卷列表
            function loadExams() {
                $('#examList .loading-spinner').show();
                $('#examList .exam-item').remove();

                $.ajax({
                    url: '/api-exam.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            examData = response.data;
                            renderExams(examData);
                        } else {
                            showError('examList', response.message || '加载失败');
                        }
                    },
                    error: function() {
                        showError('examList', '网络错误，请重试');
                    },
                    complete: function() {
                        $('#examList .loading-spinner').hide();
                    }
                });
            }

            // 加载错题列表
            function loadMistakes() {
                $('#mistakeList .loading-spinner').show();
                $('#mistakeList .mistake-item').remove();

                $.ajax({
                    url: '/api-mistake.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mistakeData = response.data;
                            renderMistakes(mistakeData);
                        } else {
                            showError('mistakeList', response.message || '加载失败');
                        }
                    },
                    error: function() {
                        showError('mistakeList', '网络错误，请重试');
                    },
                    complete: function() {
                        $('#mistakeList .loading-spinner').hide();
                    }
                });
            }

            // 渲染试卷列表
            function renderExams(exams) {
                const container = $('#examList');
                container.empty();

                if (exams.length === 0) {
                    container.append(`
                        <div class="empty-state">
                            <i class="fas fa-file-alt text-4xl mb-4"></i>
                            <p>暂无试卷</p>
                        </div>
                    `);
                    return;
                }

                exams.forEach(exam => {
                    const examHtml = `
                        <div class="exam-item card-hover bg-gray-50 rounded-lg p-4 cursor-pointer fade-in">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-800 text-lg">${exam.title}</h3>
                                <span class="tag">${exam.nums}题</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-star text-yellow-500 mr-1"></i>
                                <span>${exam.score}分</span>
                                <span class="mx-2">|</span>
                                <i class="fas fa-clock mr-1"></i>
                                <span>${formatDate(exam.create_time)}</span>
                            </div>
                            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 text-sm" onclick="startExam(${exam.id})">
                            <i class="fas fa-play mr-1"></i>
                            开始考试
                        </button>
                        </div>
                    `;
                    container.append(examHtml);
                });
            }

            // 渲染错题列表
            function renderMistakes(mistakes) {
                const container = $('#mistakeList');
                container.empty();

                if (mistakes.length === 0) {
                    container.append(`
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                            <p>暂无错题</p>
                        </div>
                    `);
                    return;
                }

                mistakes.forEach(mistake => {
                    const typeText = mistake.type == 1 ? '单选' : '多选';
                    const typeColor = mistake.type == 1 ? 'blue' : 'green';
                    
                    const mistakeHtml = `
                        <div class="mistake-item card-hover bg-red-50 rounded-lg p-4 fade-in" data-mistake-id="${mistake.id}">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-800 flex-1">${mistake.title}</h3>
                                <span class="tag bg-${typeColor}-100 text-${typeColor}-800">${typeText}</span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <span>正确答案: <strong class="text-green-600">${mistake.answer}</strong></span>
                                <span class="mx-2">|</span>
                                <span>您的答案: <strong class="text-red-600">${mistake.errors}</strong></span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500 mb-3">
                                <i class="fas fa-calendar mr-1"></i>
                                <span>${formatDate(mistake.create_time)}</span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="practice-btn bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition duration-300" data-action="practice" data-id="${mistake.id}">
                                    <i class="fas fa-redo mr-1"></i>重新练习
                                </button>
                                <button class="analysis-btn bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition duration-300" data-action="analysis" data-id="${mistake.id}">
                                    <i class="fas fa-eye mr-1"></i>查看解析
                                </button>
                            </div>
                        </div>
                    `;
                    container.append(mistakeHtml);
                });
            }

            // 筛选试卷
            function filterExams(query) {
                const filtered = examData.filter(exam => 
                    exam.title.toLowerCase().includes(query)
                );
                renderExams(filtered);
            }

            // 筛选错题
            function filterMistakes(query) {
                const filtered = mistakeData.filter(mistake => 
                    mistake.title.toLowerCase().includes(query)
                );
                renderMistakes(filtered);
            }

            // 格式化日期
            function formatDate(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diff = now - date;
                
                if (diff < 60000) {
                    return '刚刚';
                } else if (diff < 3600000) {
                    return Math.floor(diff / 60000) + '分钟前';
                } else if (diff < 86400000) {
                    return Math.floor(diff / 3600000) + '小时前';
                } else {
                    return date.toLocaleDateString('zh-CN');
                }
            }





            // 显示错误信息
            function showError(containerId, message) {
                const container = $(`#${containerId}`);
                container.empty();
                container.append(`
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle text-4xl mb-4 text-red-500"></i>
                        <p>${message}</p>
                        <button onclick="location.reload()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
                            重新加载
                        </button>
                    </div>
                `);
            }

            // 切换标签页（预留功能）
            function switchTab(tab) {
                $('.nav-link, .bottom-nav-link').removeClass('nav-active');
                $(`[data-tab="${tab}"]`).addClass('nav-active');
                
                // 这里可以添加更多标签页切换逻辑
            }

            // 页面滚动加载更多（预留）
            let isLoading = false;
            $(window).scroll(function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                    // 预留加载更多功能
                }
            });

            // 使用事件委托处理动态生成的按钮点击事件
            $(document).on('click', '.practice-btn', function() {
                const mistakeId = $(this).data('id');
                const mistake = mistakeData.find(m => m.id == mistakeId);
                if (mistake) {
                    // 处理选项数据 - 支持对象格式和数组格式
                    let options = {};
                    try {
                        if (typeof mistake.options === 'string') {
                            const parsed = JSON.parse(mistake.options || '{}');
                            if (Array.isArray(parsed)) {
                                // 数组格式转换为对象格式
                                options = {};
                                const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                parsed.forEach((item, index) => {
                                    if (index < letters.length) {
                                        options[letters[index]] = item;
                                    }
                                });
                            } else if (typeof parsed === 'object' && parsed !== null) {
                                // 对象格式直接使用
                                options = parsed;
                            } else {
                                options = {};
                            }
                        } else if (Array.isArray(mistake.options)) {
                            // 数组格式转换为对象格式
                            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            mistake.options.forEach((item, index) => {
                                if (index < letters.length) {
                                    options[letters[index]] = item;
                                }
                            });
                        } else if (typeof mistake.options === 'object' && mistake.options !== null) {
                            // 对象格式直接使用
                            options = mistake.options;
                        } else {
                            options = {};
                        }
                    } catch (e) {
                        console.error('解析选项失败:', e);
                        options = {};
                    }
                    
                    // 构建练习参数
                    const practiceData = {
                        title: mistake.title,
                        type: mistake.type,
                        options: options,
                        answer: mistake.answer,
                        analysis: mistake.analysis || ''
                    };
                    
                    // 将数据存储到sessionStorage
                    sessionStorage.setItem('practiceMistake', JSON.stringify(practiceData));
                    
                    // 跳转到练习页面
                    window.location.href = '/practice.html?mistakeId=' + mistakeId;
                } else {
                    alert('未找到对应的错题信息');
                }
            });

            $(document).on('click', '.analysis-btn', function() {
                const mistakeId = $(this).data('id');
                const mistake = mistakeData.find(m => m.id == mistakeId);
                if (mistake) {
                    // 跳转到错题详情页面
                    window.location.href = '/mistake.html?id=' + mistakeId;
                } else {
                    alert('未找到对应的错题信息');
                }
            });
        });
    </script>
</body>
</html>