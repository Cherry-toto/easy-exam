<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();

// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/exam_logs_model.php';

// 创建考试记录模型实例
$examLogsModel = new ExamLogsModel($pdo);

// 获取分页参数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize = 10;

// 获取搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 处理删除操作
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($examLogsModel->deleteExamLog($id)) {
        header('Location: exam_logs.php?message=删除成功');
        exit;
    } else {
        header('Location: exam_logs.php?error=删除失败');
        exit;
    }
}

// 获取考试记录
$result = $examLogsModel->getExamLogs($page, $pageSize, $search);
$logs = $result['logs'];
$total = $result['total'];
$pages = $result['pages'];
$currentPage = $result['currentPage'];

// 引入头部文件
require_once 'common/header.php';
?>

<!-- 主要内容 -->
<main class="flex-1 md:ml-64 p-6 pt-16 transition-all duration-300">
    <div class="container mx-auto">
        <div class="mt-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">考试记录</h2>
            <p class="text-gray-600 mt-1">查看学生考试记录</p>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- 搜索区域 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0">
            <form method="get" class="relative w-full sm:w-64">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索试卷或用户邮箱..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10">
                <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-search"></i>
                </button>
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </form>
        </div>

        <!-- 考试记录列表 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">试卷名称</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户邮箱</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分数</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用时</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">考试时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $log['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($log['exam_title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($log['member_email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $log['score'] >= 60 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $log['score']; ?>分
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php 
                                $hours = floor($log['use_time'] / 3600);
                                $minutes = floor(($log['use_time'] % 3600) / 60);
                                $seconds = $log['use_time'] % 60;
                                echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $log['create_time']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-red-600 hover:text-red-900 delete-log" data-id="<?php echo $log['id']; ?>">删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">暂无考试记录</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 分页 -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="hidden sm:block">
                    <p class="text-sm text-gray-700">
                        显示第 <span class="font-medium"><?php echo ($currentPage - 1) * $pageSize + 1; ?></span> 到 <span class="font-medium"><?php echo min($currentPage * $pageSize, $total); ?></span> 条，共 <span class="font-medium"><?php echo $total; ?></span> 条结果
                    </p>
                </div>
                <div class="flex-1 flex justify-between sm:justify-end">
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="block w-full h-full">上一页</a>
                    </button>
                    <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $currentPage >= $pages ? 'disabled' : ''; ?>>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="block w-full h-full">下一页</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// 引入侧边栏文件
require_once 'common/sidebar.php';
?>

<script>
// 删除确认
document.querySelectorAll('.delete-log').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        
        if (confirm('确定要删除这条考试记录吗？')) {
            window.location.href = 'exam_logs.php?action=delete&id=' + id;
        }
    });
});
</script>