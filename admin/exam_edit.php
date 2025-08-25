<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/exam_model.php';

// 获取试卷ID，如果存在则为编辑，否则为添加
$examId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 创建试卷模型实例
$examModel = new ExamModel();

// 初始化试卷数据
$examData = [
    'title' => '',
    'nums' => 0,
    'score' => 0
];

// 如果是编辑，获取试卷数据
if ($examId > 0) {
    $exam = $examModel->getExamById($examId);
    if ($exam) {
        $examData = $exam;
    } else {
        // 试卷不存在，重定向到列表页
        header('Location: exam.php');
        exit;
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 检查是否为AJAX请求
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    
    // 获取表单数据
    $title = trim($_POST['title'] ?? '');
    $nums = intval($_POST['nums'] ?? 0);
    $score = intval($_POST['score'] ?? 0);

    // 验证数据
    $errors = [];
    if (empty($title)) {
        $errors[] = '试卷标题不能为空';
    }
    if ($nums < 1) {
        $errors[] = '题目数量必须大于0';
    }
    if ($score < 1) {
        $errors[] = '总分必须大于0';
    }

    // 如果没有错误，保存数据
    if (empty($errors)) {
        $data = [
            'title' => $title,
            'nums' => $nums,
            'score' => $score,
            'status' => 1
        ];

        try {
            if ($examId > 0) {
                // 更新试卷
                $result = $examModel->updateExam($examId, $data);
                $message = $result ? '试卷更新成功' : '试卷更新失败';
                $redirect = 'exam.php';
            } else {
                // 添加试卷
                $result = $examModel->addExam($data);
                $message = $result ? '试卷添加成功' : '试卷添加失败';
                $redirect = 'exam.php';
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result,
                    'message' => $message,
                    'redirect' => $redirect
                ]);
                exit;
            } else {
                // 传统提交重定向到列表页
                header('Location: ' . $redirect . '?message=' . urlencode($message));
                exit;
            }
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => ['系统错误：' . $e->getMessage()]
                ]);
                exit;
            } else {
                $errors[] = '系统错误：' . $e->getMessage();
            }
        }
    } else {
        // 验证失败
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit;
        }
    }
}

// 引入头部文件
require_once 'common/header.php';
?>
<!-- 主要内容 -->
<main class="flex-1 md:ml-64 p-6 pt-16 transition-all duration-300">
    <div class="container mx-auto">
        <div class="mt-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $examId > 0 ? '编辑试卷' : '添加试卷'; ?></h2>
            <p class="text-gray-600 mt-1"><?php echo $examId > 0 ? '修改试卷信息' : '创建新试卷'; ?></p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">提交失败</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 表单 -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="post" action="<?php echo $examId > 0 ? 'exam_edit.php?id=' . $examId : 'exam_edit.php'; ?>" id="examForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">试卷标题 <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($examData['title']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>

                    <div class="col-span-1">
                        <label for="nums" class="block text-sm font-medium text-gray-700 mb-1">题目数量 <span class="text-red-500">*</span></label>
                        <input type="number" id="nums" name="nums" value="<?php echo $examData['nums']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" required>
                    </div>

                    <div class="col-span-1">
                        <label for="score" class="block text-sm font-medium text-gray-700 mb-1">总分 <span class="text-red-500">*</span></label>
                        <input type="number" id="score" name="score" value="<?php echo $examData['score']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" required>
                    </div>


                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="exam.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">取消</a>
                    <button type="submit" id="submitBtn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">保存</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
// 引入侧边栏和页脚文件
require_once 'common/sidebar.php';
require_once 'common/footer.php';
?>

<script src="../static/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // AJAX表单提交
        $('#examForm').on('submit', function(e) {
            e.preventDefault();
            
            const $submitBtn = $('#submitBtn');
            const originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i>提交中...');
            
            $.ajax({
                url: '',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .done(function(data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.href = data.redirect, 1500);
                } else {
                    showErrors(data.errors);
                }
            })
            .fail(function() {
                showErrors(['网络错误，请稍后重试']);
            })
            .always(function() {
                $submitBtn.prop('disabled', false).html(originalText);
            });
        });
        
        // 显示错误信息
        function showErrors(errors) {
            const $errorModal = $('#error-modal');
            const $errorMessages = $('#error-messages');
            
            $errorMessages.empty();
            
            if (Array.isArray(errors)) {
                const $ul = $('<ul class="list-disc pl-5 space-y-1"></ul>');
                errors.forEach(error => $ul.append(`<li>${error}</li>`));
                $errorMessages.append($ul);
            } else {
                $errorMessages.text(errors);
            }
            
            $errorModal.removeClass('hidden').addClass('opacity-100')
                .find('#error-modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }
        
        // 关闭错误弹窗
        $('#close-error-modal').on('click', function() {
            $('#error-modal').removeClass('opacity-100').addClass('hidden')
                .find('#error-modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        });
        
        // 显示成功提示
        function showToast(message, type = 'success') {
            const $toast = $(`
                <div class="fixed top-4 right-4 p-4 rounded-md shadow-lg text-white z-50 transition-all duration-300 transform translate-x-full ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}">
                    <div class="flex items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `);
            
            $('body').append($toast);
            
            setTimeout(() => $toast.removeClass('translate-x-full').addClass('translate-x-0'), 100);
            setTimeout(() => {
                $toast.addClass('translate-x-full').removeClass('translate-x-0');
                setTimeout(() => $toast.remove(), 300);
            }, 2000);
        }
    });
</script>

<!-- 错误提示弹窗 -->
<div id="error-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="error-modal-content">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">提交失败</h3>
                </div>
            </div>
            <div id="error-messages" class="text-sm text-gray-600 mb-4">
                <!-- 错误信息将在这里显示 -->
            </div>
            <div class="flex justify-end">
                <button type="button" id="close-error-modal" class="btn-primary">
                    确定
                </button>
            </div>
        </div>
    </div>
</div>