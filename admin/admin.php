<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/admin_model.php';
// 创建管理员模型实例
$adminModel = new AdminModel();
// 处理分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 10; // 每页显示10条
// 处理搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// 获取管理员数据（带分页和搜索）
$result = $adminModel->getAllAdmins($page, $pageSize, $search);
$admins = $result['admins'];
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
            <h2 class="text-2xl font-bold text-gray-800">后台管理员管理</h2>
            <p class="text-gray-600 mt-1">管理系统中的所有管理员账号</p>
        </div>

        <!-- 操作按钮区域 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex space-x-3">
                <button id="add-admin-btn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i>添加管理员
                </button>
                <button id="batch-delete-btn" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors duration-300 flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>批量删除
                </button>
            </div>
            <div class="relative w-full sm:w-64">
                <input type="text" id="searchInput" placeholder="搜索管理员..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10" value="<?php echo htmlspecialchars($search); ?>">
                <button id="searchBtn" class="absolute right-3 top-3 text-blue-600 hover:text-blue-900"><i class="fas fa-search"></i></button>
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- 管理员列表 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户名</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最后登录时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最后登录IP</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-300">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="ids[]" value="<?php echo $admin['id']; ?>" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $admin['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600">
                                                <i class="fas fa-user-shield"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $admin['login_time'] ? $admin['login_time'] : '未登录'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $admin['login_ip'] ? $admin['login_ip'] : '无'; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $admin['create_time']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="admin_edit.php?id=<?php echo $admin['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">编辑</a>
                                        <a href="#" class="text-yellow-600 hover:text-yellow-900 mr-3 reset-password" data-id="<?php echo $admin['id']; ?>" data-username="<?php echo htmlspecialchars($admin['username']); ?>">重置密码</a>
                                        <a href="#" class="text-red-600 hover:text-red-900 delete-admin" data-id="<?php echo $admin['id']; ?>" data-username="<?php echo htmlspecialchars($admin['username']); ?>">删除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="bg-white border-b">
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    暂无管理员数据
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- 分页 -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between sm:px-6">
                <div class="hidden sm:block">
                    <p class="text-sm text-gray-700">
                                        显示第 <span class="font-medium"><?php echo $total > 0 ? (($currentPage - 1) * $pageSize + 1) : 0; ?></span> 到 <span class="font-medium"><?php echo min($currentPage * $pageSize, $total); ?></span> 条，共 <span class="font-medium"><?php echo $total; ?></span> 条结果
                                    </p>
                </div>
                <div class="flex-1 flex justify-between sm:justify-end">
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>'">
                                        上一页
                                    </button>
                                    <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $currentPage >= $pages ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>'">
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
// 添加管理员按钮点击事件
const addAdminBtn = document.getElementById('add-admin-btn');
if (addAdminBtn) {
    addAdminBtn.addEventListener('click', function() {
        window.location.href = 'admin_edit.php';
    });
}

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

// 声明全局变量
let batchDeleteBtn;
let selectAllCheckbox;

// 更新批量删除按钮状态
function updateBatchDeleteBtn() {
    const checkedBoxes = document.querySelectorAll('input[name="ids[]"]:checked');
    batchDeleteBtn.disabled = checkedBoxes.length === 0;
    if (checkedBoxes.length > 0) {
        batchDeleteBtn.classList.remove('bg-gray-600');
        batchDeleteBtn.classList.add('bg-red-600');
        batchDeleteBtn.classList.add('hover:bg-red-700');
        batchDeleteBtn.classList.remove('hover:bg-gray-700');
    } else {
        batchDeleteBtn.classList.add('bg-gray-600');
        batchDeleteBtn.classList.remove('bg-red-600');
        batchDeleteBtn.classList.remove('hover:bg-red-700');
        batchDeleteBtn.classList.add('hover:bg-gray-700');
    }
}

// 为每个复选框添加事件监听器
document.querySelectorAll('input[name="ids[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateBatchDeleteBtn);
});

// 删除单个管理员
document.addEventListener('DOMContentLoaded', function() {
    // 初始化全选复选框
    selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        // 全选/取消全选
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('input[name="ids[]"]').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBatchDeleteBtn();
        });
    }

    // 初始化批量删除按钮
    batchDeleteBtn = document.getElementById('batch-delete-btn');
    if (batchDeleteBtn) {
        // 批量删除按钮点击事件
        batchDeleteBtn.addEventListener('click', function() {
    const checkedIds = Array.from(document.querySelectorAll('input[name="ids[]"]:checked'))
        .map(checkbox => checkbox.value);

    if (checkedIds.length === 0) {
        alert('请选择要删除的管理员');
        return;
    }

    if (confirm('确定要删除选中的' + checkedIds.length + '个管理员吗？')) {
        fetch('admin_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'ids=' + JSON.stringify(checkedIds)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 删除成功，移除选中的行
                checkedIds.forEach(id => {
                    document.querySelector('input[name="ids[]"][value="' + id + '"]').closest('tr').remove();
                });
                showNotification('success', '批量删除管理员成功');
                // 更新按钮状态
                updateBatchDeleteBtn();
            } else {
                showNotification('error', '批量删除管理员失败：' + data.message);
            }
        })
        .catch(error => {
            console.error('批量删除管理员失败:', error);
            showNotification('error', '批量删除管理员失败，请重试');
        });
    }
        });
    }

    // 删除链接点击事件
    document.querySelectorAll('.delete-admin').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const adminId = this.getAttribute('data-id');
            const adminUsername = this.getAttribute('data-username');

            if (confirm('确定要删除管理员"' + adminUsername + '"吗？')) {
                fetch('admin_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + adminId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 删除成功，移除行
                        this.closest('tr').remove();
                        showNotification('success', '删除管理员成功');
                    } else {
                        showNotification('error', '删除管理员失败：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('删除管理员失败:', error);
                    showNotification('error', '删除管理员失败，请重试');
                });
            }
        });
    });

    // 重置密码链接点击事件
    document.querySelectorAll('.reset-password').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const adminId = this.getAttribute('data-id');
            const adminUsername = this.getAttribute('data-username');

            if (confirm('确定要重置管理员"' + adminUsername + '"的密码吗？\n重置后密码将变为"123456"')) {
                fetch('admin_reset_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + adminId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', '重置管理员密码成功\n新密码：123456');
                    } else {
                        showNotification('error', '重置管理员密码失败：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('重置管理员密码失败:', error);
                    showNotification('error', '重置管理员密码失败，请重试');
                });
            }
        });
    });

    // 通知函数
    function showNotification(type, message) {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 px-4 py-3 rounded-md shadow-lg transition-all duration-300 transform translate-x-full opacity-0 ' + (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        notification.innerHTML = '<i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' + message;

        // 添加到文档
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