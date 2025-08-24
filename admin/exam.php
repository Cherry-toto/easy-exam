<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/exam_model.php';
// 创建试卷模型实例
$examModel = new ExamModel();
// 处理分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 10; // 每页显示10条
// 处理搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 获取试卷数据（带分页和搜索）
$result = $examModel->getAllExams($page, $pageSize, $search);
$exams = $result['exams'];
$pagination = $result['pagination'];
// 引入头部文件
require_once 'common/header.php';
?>
<!-- 主要内容 -->
<main class="flex-1 md:ml-64 p-6 pt-16 transition-all duration-300">
    <div class="container mx-auto">
        <div class="mt-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">试卷管理</h2>
            <p class="text-gray-600 mt-1">管理系统中的所有试卷</p>
        </div>

        <!-- 操作按钮区域 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex space-x-3">
                <a href="exam_edit.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i>添加试卷
                </a>
                <button id="batchDeleteBtn" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors duration-300 flex items-center" disabled>
                    <i class="fas fa-trash-alt mr-2"></i>批量删除
                </button>
            </div>
            <div class="relative w-full sm:w-64">
                <input type="text" id="searchInput" placeholder="搜索试卷..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10" value="<?php echo htmlspecialchars($search); ?>">
                        <button id="searchBtn" class="absolute right-3 top-3 text-blue-600 hover:text-blue-900"><i class="fas fa-search"></i></button>
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- 试卷列表 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">试卷标题</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题目数量</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">总分</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">更新时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($exams as $exam): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="<?php echo $exam['id']; ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($exam['title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $exam['nums']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $exam['score']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $exam['create_time']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $exam['update_time']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="exam_edit.php?id=<?php echo $exam['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">编辑</a>
                                <a href="/exam.html?id=<?php echo $exam['id']; ?>" class="text-green-600 hover:text-green-900 mr-3" target="_blank">预览</a>
                                <a href="#" class="text-red-600 hover:text-red-900 delete-exam" data-id="<?php echo $exam['id']; ?>">删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">暂无试卷数据</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- 分页 -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="hidden sm:block">
                    <p class="text-sm text-gray-700">
                        显示第 <span class="font-medium"><?php echo $pagination['total'] > 0 ? (($pagination['page'] - 1) * $pagination['pageSize'] + 1) : 0; ?></span> 到 <span class="font-medium"><?php echo min($pagination['page'] * $pagination['pageSize'], $pagination['total']); ?></span> 条，共 <span class="font-medium"><?php echo $pagination['total']; ?></span> 条结果
                    </p>
                </div>
            
                <div class="flex-1 flex justify-between sm:justify-end">
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $pagination['page'] - 1; ?>&search=<?php echo urlencode($search); ?>'">
                        上一页
                    </button>
                    <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $pagination['page'] + 1; ?>&search=<?php echo urlencode($search); ?>'">
                        下一页
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
// 搜索功能
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');

// 搜索按钮点击事件
searchBtn.addEventListener('click', function() {
    const searchValue = searchInput.value.trim();
    window.location.href = '?search=' + encodeURIComponent(searchValue);
});

// 搜索输入框回车键事件
searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const searchValue = searchInput.value.trim();
        window.location.href = '?search=' + encodeURIComponent(searchValue);
    }
});

// 删除单个试卷
document.addEventListener('DOMContentLoaded', function() {
    // 删除链接点击事件
    document.querySelectorAll('.delete-exam').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const examId = this.getAttribute('data-id');
            const examName = this.closest('tr').querySelector('td:nth-child(2)').textContent;

            if (confirm('确定要删除试卷"' + examName + '"吗？')) {
                fetch('exam_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + examId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 删除成功，移除行
                        this.closest('tr').remove();
                        // 显示成功消息
                        showNotification('success', data.message);
                    } else {
                        // 显示错误消息
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('删除试卷失败:', error);
                    showNotification('error', '删除试卷失败，请重试');
                });
            }
        });
    });

    // 批量删除按钮
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    const checkboxes = document.querySelectorAll('input[type="checkbox"][value]');

    // 更新批量删除按钮状态
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchDeleteBtn);
    });

    function updateBatchDeleteBtn() {
        const checkedCount = document.querySelectorAll('input[type="checkbox"][value]:checked').length;
        batchDeleteBtn.disabled = checkedCount === 0;
        batchDeleteBtn.classList.toggle('bg-gray-600', checkedCount === 0);
        batchDeleteBtn.classList.toggle('bg-red-600', checkedCount > 0);
        batchDeleteBtn.classList.toggle('hover:bg-gray-700', checkedCount === 0);
        batchDeleteBtn.classList.toggle('hover:bg-red-700', checkedCount > 0);
    }

    // 批量删除点击事件
    batchDeleteBtn.addEventListener('click', function() {
        const checkedIds = Array.from(document.querySelectorAll('input[type="checkbox"][value]:checked'))
            .map(checkbox => checkbox.value);

        if (checkedIds.length === 0) {
            showNotification('error', '请选择要删除的试卷');
            return;
        }

        if (confirm('确定要删除选中的' + checkedIds.length + '个试卷吗？')) {
            // 批量删除实现 - 一次性发送所有ID
            fetch('exam_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'ids=' + encodeURIComponent(JSON.stringify(checkedIds))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 删除成功，移除所有选中的行
                    checkedIds.forEach(id => {
                        const row = document.querySelector('input[type="checkbox"][value="' + id + '"]').closest('tr');
                        if (row) row.remove();
                    });
                    showNotification('success', '成功删除' + checkedIds.length + '个试卷');
                } else {
                    showNotification('error', '删除试卷失败: ' + (data.message || '未知错误'));
                }
                // 更新按钮状态
                updateBatchDeleteBtn();
            })
            .catch(error => {
                console.error('批量删除试卷失败:', error);
                showNotification('error', '删除试卷失败，请重试');
                // 更新按钮状态
                updateBatchDeleteBtn();
            });
        }
    });

    // 通知函数
    function showNotification(type, message) {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 px-4 py-3 rounded-md shadow-lg transition-all duration-300 transform translate-x-full opacity-0 ' + (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(notification);

        // 显示通知
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 10);

        // 隐藏通知
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            // 移除元素
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // 处理URL中的消息
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showNotification('success', message);
        // 移除消息参数
        urlParams.delete('message');
        history.replaceState(null, null, '?' + urlParams.toString() || window.location.pathname);
    }
});
</script>

<?php
// 引入页脚文件
require_once 'common/footer.php';
?>