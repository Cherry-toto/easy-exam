<?php
// 引入权限检测文件
require_once 'common/auth.php';
// 检查登录状态
checkAdminLogin();
// 引入数据库配置和模型
require_once 'config.php';
require_once 'common/member_model.php';
// 创建会员模型实例
$memberModel = new MemberModel();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 检查是否为AJAX请求
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 验证表单数据
    $errors = [];
    if (empty($email)) {
        $errors[] = '邮箱不能为空';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '请输入有效的邮箱地址';
    }

    // 如果是添加新会员，密码不能为空
    if (empty($id) && empty($password)) {
        $errors[] = '密码不能为空';
    }
    
    // 验证密码长度
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = '密码长度不能少于6位';
    }

    if (empty($errors)) {
        try {
            // 准备会员数据
            $memberData = [
                'email' => $email
            ];

            // 如果提供了密码，则更新密码
            if (!empty($password)) {
                $memberData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            // 添加或更新会员
            if (empty($id)) {
                // 添加新会员
                $memberData['register_time'] = date('Y-m-d H:i:s');
                $memberData['login_time'] = null;
                $result = $memberModel->addMember($memberData);
                $message = '添加会员成功';
            } else {
                // 更新现有会员
                $result = $memberModel->updateMember($id, $memberData);
                $message = '更新会员成功';
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result,
                    'message' => $message,
                    'redirect' => 'member.php'
                ]);
                exit;
            } else {
                if ($result) {
                    // 操作成功，重定向到会员列表页
                    header('Location: member.php?message=' . urlencode($message));
                    exit;
                } else {
                    $errors[] = empty($id) ? '添加会员失败' : '更新会员失败';
                }
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
} else {
    // GET请求，显示表单
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $member = null;
    if (!empty($id)) {
        // 获取会员信息
        $member = $memberModel->getMemberById($id);
        if (!$member) {
            header('Location: member.php?error=' . urlencode('会员不存在'));
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
            <h2 class="text-2xl font-bold text-gray-800"><?php echo empty($id) ? '添加会员' : '编辑会员'; ?></h2>
            <p class="text-gray-600 mt-1"><?php echo empty($id) ? '创建新会员账户' : '修改会员信息'; ?></p>
        </div>

        <!-- 错误提示 -->
        <?php if (!empty($errors)): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- 表单 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <form method="POST" action="member_edit.php" id="memberForm">
                    <?php if (!empty($id)): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-1">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">邮箱地址 <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value="<?php echo !empty($member) ? htmlspecialchars($member['email']) : ''; ?>">
                        </div>

                        <div class="col-span-1">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密码 <?php echo empty($id) ? '<span class="text-red-500">*</span>' : '<span class="text-gray-500">(留空表示不修改)</span>'; ?></label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="<?php echo empty($id) ? '输入密码' : '输入新密码或留空'; ?>">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="member.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-300">
                            取消
                        </a>
                        <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300">
                            <?php echo empty($id) ? '添加会员' : '更新会员'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
// 引入侧边栏文件
require_once 'common/sidebar.php';
?>

<script src="../static/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // AJAX表单提交
        $('#memberForm').on('submit', function(e) {
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

<?php
// 引入页脚文件
require_once 'common/footer.php';
?>