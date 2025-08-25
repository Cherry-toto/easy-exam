<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/question_model.php';

// 创建题目模型实例
$questionModel = new QuestionModel();

// 获取分页参数
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize = 10;

// 获取搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 获取题目列表
$questionsResult = $questionModel->getAllQuestions($page, $pageSize, $search);
$questions = $questionsResult['questions'];
$total = $questionsResult['total'];
$pages = $questionsResult['pages'];
$currentPage = $questionsResult['currentPage'];

// 引入头部文件
require_once 'common/header.php';
?>
<!-- 主要内容 -->
<main class="flex-1 md:ml-64 p-6 pt-16 transition-all duration-300">
    <div class="container mx-auto">
        <div class="mt-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">题目管理</h2>
            <p class="text-gray-600 mt-1">管理系统中的所有题目</p>
        </div>

        <!-- 操作按钮区域 -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex space-x-3">
                <a href="question_edit.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i>添加题目
                </a>
                <button id="batchImportBtn" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors duration-300 flex items-center">
                    <i class="fas fa-file-import mr-2"></i>批量导入
                </button>
              
                <button id="batchDeleteBtn" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors duration-300 flex items-center" disabled>
                    <i class="fas fa-trash-alt mr-2"></i>批量删除
                </button>
            </div>
            <form method="get" class="relative w-full sm:w-64">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索题目或试卷..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10">
                <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-search"></i>
                </button>
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- 题目列表 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题目ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题目内容</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">类型</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">科目</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分值</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">难度</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($questions as $question): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-300">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 question-checkbox" value="<?php echo $question['id']; ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $question['id']; ?></td>
                            <td class="px-6 py-4 whitespace-normal max-w-xs text-sm text-gray-500"><?php echo htmlspecialchars($question['title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $question['type'] == 1 ? '单选题' : '多选题'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($question['exam_title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">简单</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $question['create_time']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="question_edit.php?id=<?php echo $question['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">编辑</a>
                                <a href="#" class="text-red-600 hover:text-red-900 delete-question" data-id="<?php echo $question['id']; ?>">删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($questions)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">暂无题目数据</td>
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

    <!-- 批量导入模态框 -->
    <div id="batch-import-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div id="import-modal-content" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
                <h3 class="text-lg font-medium text-gray-900">批量导入题目</h3>
                <button type="button" id="close-import-modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                <form id="batch-import-form" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="import-exam-name">所属试卷</label>
                        <div class="flex items-center space-x-2">
                            <input type="hidden" id="import-exam-id" name="exam_id" value="">
                            <input type="text" id="import-exam-name" name="exam_name" readonly class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 px-3 py-2" placeholder="请选择试卷">
                            <button type="button" id="search-import-exam-btn" class="btn-primary whitespace-nowrap">搜索试卷</button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="import-file">导入文件</label>
                        <input type="file" id="import-file" name="file" accept=".json" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 w-full px-3 py-2">
                        <p class="mt-1 text-xs text-gray-500">仅支持.json格式文件</p>
                    </div>
                </form>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3 sticky bottom-0">
                <button type="button" id="cancel-import-btn" class="btn-outline">取消</button>
                <button type="button" id="confirm-import-btn" class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed" disabled>确认导入</button>
            </div>
        </div>
    </div>

    <!-- 试卷搜索模态框 -->
    <div id="import-exam-search-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div id="import-modal-search-content" class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
                <h3 class="text-lg font-medium text-gray-900">搜索试卷</h3>
                <button type="button" id="close-import-exam-modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4 max-h-[70vh] overflow-y-auto">
                <div class="mb-4">
                    <div class="flex items-center space-x-2">
                        <input type="text" id="import-exam-search-input" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 px-3 py-2" placeholder="输入试卷名称搜索">
                        <button type="button" id="do-import-exam-search" class="btn-primary whitespace-nowrap">搜索</button>
                    </div>
                </div>
                <div id="import-exam-search-results" class="divide-y divide-gray-200">
                    <p class="text-center text-gray-500 py-4">请输入搜索关键词</p>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3 sticky bottom-0">
                <button type="button" id="cancel-import-exam-search" class="btn-outline">取消</button>
                <button type="button" id="confirm-import-exam-selection" class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed" disabled>确认选择</button>
            </div>
        </div>
    </div>
</main>

<?php
// 引入侧边栏文件
require_once 'common/sidebar.php';
?>

<script>
// 批量导入功能
function initBatchImport() {
    const batchImportBtn = document.getElementById('batchImportBtn');
    const importModal = document.getElementById('batch-import-modal');
    const importModalContent = document.getElementById('import-modal-content');
    const closeImportModalBtn = document.getElementById('close-import-modal');
    const cancelImportBtn = document.getElementById('cancel-import-btn');
    const confirmImportBtn = document.getElementById('confirm-import-btn');
    const importExamIdInput = document.getElementById('import-exam-id');
    const importExamNameInput = document.getElementById('import-exam-name');
    const importFileInput = document.getElementById('import-file');
    const searchImportExamBtn = document.getElementById('search-import-exam-btn');
    const importForm = document.getElementById('batch-import-form');

    // 导入试卷搜索模态框
    const importExamSearchModal = document.getElementById('import-exam-search-modal');
    const importExamSearchModalContent = document.getElementById('import-modal-search-content');
    const closeImportExamModalBtn = document.getElementById('close-import-exam-modal');
    const cancelImportExamSearchBtn = document.getElementById('cancel-import-exam-search');
    const confirmImportExamSelectionBtn = document.getElementById('confirm-import-exam-selection');
    let selectedImportExamId = null;
    let selectedImportExamName = null;

    // 打开批量导入模态框
    batchImportBtn.addEventListener('click', function() {
        importModal.classList.remove('hidden');
        setTimeout(() => {
            importModal.classList.add('opacity-100');
            importModalContent.classList.add('scale-100', 'opacity-100');
            importModalContent.classList.remove('scale-95', 'opacity-0');
        }, 10);
    });

    // 关闭批量导入模态框
    function closeImportModal() {
        importModal.classList.remove('opacity-100');
        importModalContent.classList.remove('scale-100', 'opacity-100');
        importModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            importModal.classList.add('hidden');
        }, 300);
    }

    closeImportModalBtn.addEventListener('click', closeImportModal);
    cancelImportBtn.addEventListener('click', closeImportModal);

    // 点击模态框外部关闭
    importModal.addEventListener('click', function(e) {
        if (e.target === importModal) {
            closeImportModal();
        }
    });

    // 打开试卷搜索模态框
    searchImportExamBtn.addEventListener('click', function() {
        importExamSearchModal.classList.remove('hidden');
        setTimeout(() => {
            importExamSearchModal.classList.add('opacity-100');
            importExamSearchModalContent.classList.add('scale-100', 'opacity-100');
            importExamSearchModalContent.classList.remove('scale-95', 'opacity-0');
        }, 10);
        document.getElementById('import-exam-search-input').focus();
    });

    // 关闭试卷搜索模态框
    function closeImportExamModal() {
        importExamSearchModal.classList.remove('opacity-100');
        importExamSearchModalContent.classList.remove('scale-100', 'opacity-100');
        importExamSearchModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            importExamSearchModal.classList.add('hidden');
        }, 300);
    }

    closeImportExamModalBtn.addEventListener('click', closeImportExamModal);
    cancelImportExamSearchBtn.addEventListener('click', closeImportExamModal);

    // 点击模态框外部关闭
    importExamSearchModal.addEventListener('click', function(e) {
        if (e.target === importExamSearchModal) {
            closeImportExamModal();
        }
    });

    // 试卷选择
    document.addEventListener('click', function(e) {
        if (e.target.closest('.select-exam')) {
            const examElement = e.target.closest('.select-exam');
            const examId = examElement.getAttribute('data-id');
            const examName = examElement.getAttribute('data-name');

            // 清除之前的选择
            document.querySelectorAll('.select-exam').forEach(el => {
                el.classList.remove('bg-blue-50', 'border-blue-200');
                el.querySelector('.exam-selected-icon').classList.add('hidden');
            });

            // 选中当前试卷
            examElement.classList.add('bg-blue-50', 'border-blue-200');
            examElement.querySelector('.exam-selected-icon').classList.remove('hidden');

            selectedImportExamId = examId;
            selectedImportExamName = examName;

            // 启用确认按钮
            confirmImportExamSelectionBtn.disabled = false;
        }
    });

    // 确认选择试卷
    confirmImportExamSelectionBtn.addEventListener('click', function() {
        if (selectedImportExamId) {
            importExamIdInput.value = selectedImportExamId;
            importExamNameInput.value = selectedImportExamName;
            closeImportExamModal();
        }
    });

    // 搜索试卷
    document.getElementById('do-import-exam-search').addEventListener('click', function() {
        const searchTerm = document.getElementById('import-exam-search-input').value.trim();
        if (searchTerm) {
            // 显示加载状态
            const resultsContainer = document.getElementById('import-exam-search-results');
            resultsContainer.innerHTML = '<p class="text-center text-gray-500 py-4">搜索中...</p>';

            // 发送AJAX请求获取搜索结果
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'question_edit.php?search_exam_ajax=1&search_term=' + encodeURIComponent(searchTerm), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    resultsContainer.innerHTML = xhr.responseText;
                    // 重新绑定选择事件
                    bindImportExamSelectionEvents();
                } else {
                    resultsContainer.innerHTML = '<p class="text-center text-red-500 py-4">搜索失败，请重试</p>';
                }
            };
            xhr.onerror = function() {
                resultsContainer.innerHTML = '<p class="text-center text-red-500 py-4">网络错误，请重试</p>';
            };
            xhr.send();
        }
    });

    // 绑定试卷选择事件
    function bindImportExamSelectionEvents() {
        document.querySelectorAll('.select-exam').forEach(examElement => {
            examElement.addEventListener('click', function() {
                const examId = this.getAttribute('data-id');
                const examName = this.getAttribute('data-name');

                // 清除之前的选择
                document.querySelectorAll('.select-exam').forEach(el => {
                    el.classList.remove('bg-blue-50', 'border-blue-200');
                    el.querySelector('.exam-selected-icon').classList.add('hidden');
                });

                // 选中当前试卷
                this.classList.add('bg-blue-50', 'border-blue-200');
                this.querySelector('.exam-selected-icon').classList.remove('hidden');

                selectedImportExamId = examId;
                selectedImportExamName = examName;

                // 启用确认按钮
                confirmImportExamSelectionBtn.disabled = false;
            });
        });
    }

    // 按下Enter键搜索
    document.getElementById('import-exam-search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('do-import-exam-search').click();
        }
    });

    // 文件选择监听
    importFileInput.addEventListener('change', function() {
        // 检查试卷ID和文件是否都已选择
        confirmImportBtn.disabled = !importExamIdInput.value || !this.files.length;
    });

    // 试卷ID变化监听
    importExamIdInput.addEventListener('change', function() {
        // 检查试卷ID和文件是否都已选择
        confirmImportBtn.disabled = !this.value || !importFileInput.files.length;
    });

    // 确认导入
    confirmImportBtn.addEventListener('click', function() {
        const examId = importExamIdInput.value;
        const fileInput = importFileInput;

        console.log('确认导入按钮点击');
        console.log('examId:', examId);
        console.log('fileInput.files.length:', fileInput.files.length);

        if (examId && fileInput.files.length) {
            try {
                // 使用FormData构造函数直接添加字段
                const formData = new FormData();
                formData.append('exam_id', examId);
                formData.append('file', fileInput.files[0]);
                
                console.log('FormData创建成功');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'question_import.php', true);
                xhr.onload = function() {
                    console.log('XHR响应状态:', xhr.status);
                    console.log('XHR响应内容:', xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert('导入成功，共导入 ' + response.data.count + ' 条题目');
                                closeImportModal();
                                window.location.reload();
                            } else {
                                alert('导入失败：' + response.message);
                            }
                        } catch (e) {
                            console.error('解析响应失败:', e);
                            alert('导入失败：返回数据格式错误');
                        }
                    } else {
                        alert('网络错误，请重试');
                    }
                };
                xhr.onerror = function() {
                    console.error('XHR请求错误');
                    alert('网络错误，请重试');
                };
                xhr.send(formData);
            } catch (error) {
                console.error('创建FormData时出错:', error);
                alert('创建表单数据时出错: ' + error.message);
            }
        } else {
            alert('请选择试卷和文件');
        }
    });
}

// 删除单个题目
document.addEventListener('DOMContentLoaded', function() {
    // 删除链接点击事件
    document.querySelectorAll('.delete-question').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const questionId = this.getAttribute('data-id');
            const questionTitle = this.closest('tr').querySelector('td:nth-child(3)').textContent;

            if (confirm('确定要删除题目"' + questionTitle + '"吗？')) {
                fetch('question_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + questionId
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
                    console.error('删除题目失败:', error);
                    showNotification('error', '删除题目失败，请重试');
                });
            }
        });
    });

    // 批量删除按钮
    const batchDeleteBtn = document.querySelector('#batchDeleteBtn');
    const checkboxes = document.querySelectorAll('.question-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');

    // 全选功能
    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBatchDeleteBtn();
    });

    // 更新批量删除按钮状态
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchDeleteBtn);
    });

    function updateBatchDeleteBtn() {
        const checkedCount = document.querySelectorAll('.question-checkbox:checked').length;
        batchDeleteBtn.disabled = checkedCount === 0;
        batchDeleteBtn.classList.toggle('bg-gray-600', checkedCount === 0);
        batchDeleteBtn.classList.toggle('bg-red-600', checkedCount > 0);
        batchDeleteBtn.classList.toggle('hover:bg-gray-700', checkedCount === 0);
        batchDeleteBtn.classList.toggle('hover:bg-red-700', checkedCount > 0);
        // 更新全选框状态
        selectAllCheckbox.checked = checkboxes.length > 0 && checkboxes.length === checkedCount;
    }

    // 批量删除点击事件
    batchDeleteBtn.addEventListener('click', function() {
        const checkedIds = Array.from(document.querySelectorAll('.question-checkbox:checked'))
            .map(checkbox => checkbox.value);

        if (checkedIds.length === 0) {
            showNotification('error', '请选择要删除的题目');
            return;
        }

        if (confirm('确定要删除选中的' + checkedIds.length + '个题目吗？')) {
            // 批量删除实现
            fetch('question_delete.php', {
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
                        document.querySelector('.question-checkbox[value="' + id + '"]').closest('tr').remove();
                    });
                    // 显示成功消息
                    showNotification('success', data.message);
                } else {
                    // 显示错误消息
                    showNotification('error', data.message);
                }
                // 更新按钮状态
                updateBatchDeleteBtn();
            })
            .catch(error => {
                console.error('批量删除题目失败:', error);
                showNotification('error', '批量删除题目失败，请重试');
            });
        }
    });

    // 通知函数
    function showNotification(type, message) {
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 px-4 py-3 rounded-md shadow-lg transition-all duration-300 transform translate-x-full opacity-0 ' + (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        notification.innerHTML = '<div><i class="fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + ' mr-2"></i>' + message + '</div>';
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

    // 初始化批量导入功能
    initBatchImport();

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