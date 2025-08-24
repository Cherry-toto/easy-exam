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

    if (empty($errors)) {
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
        } else {
            // 更新现有会员
            $result = $memberModel->updateMember($id, $memberData);
        }

        if ($result) {
            // 操作成功，重定向到会员列表页
            header('Location: member.php?message=' . urlencode(empty($id) ? '添加会员成功' : '更新会员成功'));
            exit;
        } else {
            $errors[] = empty($id) ? '添加会员失败' : '更新会员失败';
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
                <form method="POST" action="member_edit.php">
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
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300">
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

<script>
// 表单验证
(document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];

        // 验证邮箱
        if (!emailInput.value.trim()) {
            errors.push('邮箱不能为空');
            isValid = false;
        } else if (!isValidEmail(emailInput.value.trim())) {
            errors.push('请输入有效的邮箱地址');
            isValid = false;
        }

        // 验证密码
        <?php if (empty($id)): ?>
        if (!passwordInput.value.trim()) {
            errors.push('密码不能为空');
            isValid = false;
        } else if (passwordInput.value.length < 6) {
            errors.push('密码长度不能少于6位');
            isValid = false;
        }
        <?php endif; ?>

        // 如果有错误，阻止表单提交并显示错误
        if (!isValid) {
            e.preventDefault();
            // 移除旧的错误提示
            const oldErrorDiv = document.querySelector('.bg-red-50');
            if (oldErrorDiv) {
                oldErrorDiv.remove();
            }
            // 创建新的错误提示
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded';
            let errorHtml = '<ul class="list-disc pl-5 space-y-1">';
            errors.forEach(error => {
                errorHtml += `<li>${error}</li>`;
            });
            errorHtml += '</ul>';
            errorDiv.innerHTML = errorHtml;
            // 插入到表单前面
            form.parentNode.insertBefore(errorDiv, form);
        }
    });

    // 邮箱验证函数
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}));
</script>

<?php
// 引入页脚文件
require_once 'common/footer.php';
?>