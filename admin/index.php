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
// 1. 总试卷数及环比
    $examCount = 0;
    $examCountLastMonth = 0;
    try {
        global $pdo;
        // 当前总数
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM exam');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $examCount = $result['count'] ?? 0;
        
        // 上月总数
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $thisMonth = date('Y-m-01');
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM exam WHERE create_time < ? AND create_time >= ?');
        $stmt->execute([$thisMonth, $lastMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $examCountLastMonth = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('获取试卷数量失败: ' . $e->getMessage());
    }
    
    // 计算试卷数环比
    $examGrowthRate = 0;
    if ($examCountLastMonth > 0) {
        $examGrowthRate = round(($examCount - $examCountLastMonth) / $examCountLastMonth * 100);
    }

// 2. 总题目数及环比
    $questionCount = 0;
    $questionCountLastMonth = 0;
    try {
        global $pdo;
        // 当前总数
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM question');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $questionCount = $result['count'] ?? 0;
        
        // 上月总数
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $thisMonth = date('Y-m-01');
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM question WHERE create_time < ? AND create_time >= ?');
        $stmt->execute([$thisMonth, $lastMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $questionCountLastMonth = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('获取题目数量失败: ' . $e->getMessage());
    }
    
    // 计算题目数环比
    $questionGrowthRate = 0;
    if ($questionCountLastMonth > 0) {
        $questionGrowthRate = round(($questionCount - $questionCountLastMonth) / $questionCountLastMonth * 100);
    }

// 3. 注册用户数及环比
    $memberCount = 0;
    $memberCountLastMonth = 0;
    try {
        global $pdo;
        // 当前总数
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM member');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $memberCount = $result['count'] ?? 0;
        
        // 上月总数
        $lastMonth = date('Y-m-01', strtotime('-1 month'));
        $thisMonth = date('Y-m-01');
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM member WHERE register_time < ? AND register_time >= ?');
        $stmt->execute([$thisMonth, $lastMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $memberCountLastMonth = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('获取会员数量失败: ' . $e->getMessage());
    }
    
    // 计算用户数环比
    $memberGrowthRate = 0;
    if ($memberCountLastMonth > 0) {
        $memberGrowthRate = round(($memberCount - $memberCountLastMonth) / $memberCountLastMonth * 100);
    }

// 4. 错题本数量及昨日对比
    $mistakeCount = 0;
    $mistakeCountYesterday = 0;
    try {
        global $pdo;
        // 当前总数
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM mistake');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $mistakeCount = $result['count'] ?? 0;
        
        // 昨日总数
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $today = date('Y-m-d');
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM mistake WHERE create_time < ? AND create_time >= ?');
        $stmt->execute([$today, $yesterday]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $mistakeCountYesterday = $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('获取错题数量失败: ' . $e->getMessage());
    }
    
    // 计算错题数日环比
    $mistakeGrowthRate = 0;
    if ($mistakeCountYesterday > 0) {
        $mistakeGrowthRate = round(($mistakeCount - $mistakeCountYesterday) / $mistakeCountYesterday * 100);
    }

// 5. 获取最近考试记录
$recentExams = [];
try {
    global $pdo;
    $sql = "
        SELECT el.*, m.email, e.title 
        FROM exam_log el
        JOIN member m ON el.member_id = m.id
        JOIN exam e ON el.exam_id = e.id
        ORDER BY el.create_time DESC
        LIMIT 10
    ";
    $stmt = $pdo->query($sql);
    $recentExams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('获取最近考试记录失败: ' . $e->getMessage());
}

// 格式化时间函数
function formatTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return $diff . '秒前';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '分钟前';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '小时前';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . '天前';
    } else {
        return date('Y-m-d', $timestamp);
    }
}

// 格式化用时函数
function formatUseTime($seconds) {
    if ($seconds < 60) {
        return $seconds . '秒';
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . '分' . ($seconds % 60) . '秒';
    } else {
        return floor($seconds / 3600) . '小时' . floor(($seconds % 3600) / 60) . '分';
    }
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
                   
                    <?php if ($examGrowthRate > 0): ?>
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-green-500"><?php echo $examGrowthRate; ?>% 较上月</span>
                    <?php elseif ($examGrowthRate < 0): ?>
                    <i class="fas fa-arrow-down mr-1"></i>
                    <span class="text-red-500"><?php echo abs($examGrowthRate); ?>% 较上月</span>
                    <?php else: ?>
                    <i class="fas fa-minus mr-1"></i>
                    <span class="text-gray-500">0% 较上月</span>
                    <?php endif; ?>
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
               
                    <?php if ($questionGrowthRate > 0): ?>
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-green-500"><?php echo $questionGrowthRate; ?>% 较上月</span>
                    <?php elseif ($questionGrowthRate < 0): ?>
                    <i class="fas fa-arrow-down mr-1"></i>
                    <span class="text-red-500"><?php echo abs($questionGrowthRate); ?>% 较上月</span>
                    <?php else: ?>
                    <i class="fas fa-minus mr-1"></i>
                    <span class="text-gray-500">0% 较上月</span>
                    <?php endif; ?>
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
                    
                    <?php if ($memberGrowthRate > 0): ?>
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-green-500"><?php echo $memberGrowthRate; ?>% 较上月</span>
                    <?php elseif ($memberGrowthRate < 0): ?>
                    <i class="fas fa-arrow-down mr-1"></i>
                    <span class="text-red-500"><?php echo abs($memberGrowthRate); ?>% 较上月</span>
                    <?php else: ?>
                    <i class="fas fa-minus mr-1"></i>
                    <span class="text-gray-500">0% 较上月</span>
                    <?php endif; ?>
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
                    
                    <?php if ($mistakeGrowthRate > 0): ?>
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="text-green-500"><?php echo $mistakeGrowthRate; ?>% 较昨日</span>
                    <?php elseif ($mistakeGrowthRate < 0): ?>
                    <i class="fas fa-arrow-down mr-1"></i>
                    <span class="text-red-500"><?php echo abs($mistakeGrowthRate); ?>% 较昨日</span>
                    <?php else: ?>
                    <i class="fas fa-minus mr-1"></i>
                    <span class="text-gray-500">0% 较昨日</span>
                    <?php endif; ?>
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
                        <?php if (!empty($recentExams)): ?>
                            <?php foreach ($recentExams as $exam): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-300">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo explode('@', $exam['email'])[0]; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo $exam['email']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        完成了 <span class="font-medium text-indigo-600"><?php echo $exam['title']; ?></span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo $exam['score']; ?>分
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div><?php echo formatTimeAgo($exam['create_time']); ?></div>
                                        <div class="text-xs text-gray-400">用时: <?php echo formatUseTime($exam['use_time']); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-calendar-alt text-2xl mb-2 block mx-auto"></i>
                                    暂无考试记录
                                </td>
                            </tr>
                        <?php endif; ?>
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