<?php
// 引入头部文件
require_once 'common/header.php';
// 引入数据库配置
require_once '../config.php';
// 引入模型文件
require_once 'common/exam_model.php';
require_once 'common/question_model.php';
require_once 'common/member_model.php';

// 创建模型实例
$examModel = new ExamModel();
$questionModel = new QuestionModel();
$memberModel = new MemberModel();

// 获取统计数据
// 1. 总试卷数
$examCount = 0;
try {
    global $pdo;
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM exam');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $examCount = $result['count'] ?? 0;
} catch (PDOException $e) {
    error_log('获取试卷数量失败: ' . $e->getMessage());
}

// 2. 总题目数
$questionCount = 0;
try {
    global $pdo;
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM question');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $questionCount = $result['count'] ?? 0;
} catch (PDOException $e) {
    error_log('获取题目数量失败: ' . $e->getMessage());
}

// 3. 注册用户数
$memberCount = 0;
try {
    global $pdo;
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM member');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $memberCount = $result['count'] ?? 0;
} catch (PDOException $e) {
    error_log('获取会员数量失败: ' . $e->getMessage());
}

// 4. 错题本数量
$mistakeCount = 0;
try {
    global $pdo;
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM mistake');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $mistakeCount = $result['count'] ?? 0;
} catch (PDOException $e) {
    error_log('获取错题数量失败: ' . $e->getMessage());
}
?>
<!-- 主要内容 -->
<main class="flex-1 md:ml-64 p-6 pt-16 transition-all duration-300">
    <div class="container mx-auto">
        <div class="mt-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">后台首页</h2>
            <p class="text-gray-600 mt-1">欢迎使用在线自学考试系统后台管理</p>
        </div>

        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">总试卷数</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $examCount; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-alt text-blue-500 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-green-500">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>12% 较上月</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">总题目数</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $questionCount; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-question-circle text-green-500 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-green-500">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>8% 较上月</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">注册用户</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $memberCount; ?></h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-users text-purple-500 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-green-500">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>15% 较上月</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">错题本</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $mistakeCount; ?></h3>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-file-signature text-red-500 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-red-500">
                    <i class="fas fa-arrow-down mr-1"></i>
                    <span>5% 较昨日</span>
                </div>
            </div>
        </div>

        <!-- 最近活动 -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">最近活动</h3>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-300">查看全部</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">活动</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">时间</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">张三</div>
                                        <div class="text-sm text-gray-500">student@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">完成了 PHP基础知识测试</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10分钟前</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">李四</div>
                                        <div class="text-sm text-gray-500">student2@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">完成了 JavaScript基础测试</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">25分钟前</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">admin</div>
                                        <div class="text-sm text-gray-500">admin@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">添加了新试卷 MySQL数据库测试</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1小时前</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
// 引入侧边栏和页脚文件
require_once 'common/sidebar.php';
require_once 'common/footer.php';
?>