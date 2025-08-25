<?php
// 侧边栏菜单文件
// 获取当前页面名称
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Font Awesome -->
<link rel="stylesheet" href="/static/fontawesome-free-6.7.2/css/all.min.css">
<!-- 侧边栏 -->
<aside class="w-64 bg-gray-800 text-white fixed left-0 top-0 bottom-0 shadow-lg hidden md:block transition-all duration-300 z-30 overflow-y-auto pt-4 mt-16">
    <nav class="p-4 space-y-1 h-full flex flex-col">
        <a href="index.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'index.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>后台首页</span>
        </a>
        <a href="exam.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'exam.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-file-alt mr-3"></i>
            <span>试卷管理</span>
        </a>
        <a href="questions.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'questions.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-question-circle mr-3"></i>
            <span>题目管理</span>
        </a>
        <a href="member.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'member.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-users mr-3"></i>
            <span>用户管理</span>
        </a>
        <a href="admin.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'admin.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-user-shield mr-3"></i>
            <span>后台管理员</span>
        </a>
        <a href="exam_logs.php" class="flex items-center px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300 <?php echo $current_page == 'exam_logs.php' ? 'bg-gray-700 font-bold' : ''; ?>">
            <i class="fas fa-history mr-3"></i>
            <span>考试记录</span>
        </a>
        
        <!-- 退出登录 -->
        <div class="mt-auto pt-4">
            <a href="#" onclick="confirmLogout()" class="flex items-center px-4 py-3 rounded-md hover:bg-red-600 bg-gray-700 text-red-300 hover:text-white transition-colors duration-300">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>退出登录</span>
            </a>
        </div>
    </nav>
</aside>

<!-- 移动端菜单按钮 -->
<div class="md:hidden fixed bottom-6 right-6 z-50">
    <button id="mobile-menu-button" class="bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-colors duration-300">
        <i class="fas fa-bars text-xl"></i>
    </button>
</div>

<!-- 移动端侧边栏 -->
<div id="mobile-sidebar" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300">
    <div id="mobile-sidebar-content" class="bg-gray-800 text-white h-full w-64 p-4 transform transition-transform duration-300 -translate-x-full overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">菜单</h2>
            <button id="close-mobile-menu" class="text-white hover:text-gray-300 transition-colors duration-300">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="space-y-1">
            <a href="index.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>后台首页</span>
            </a>
            <a href="exam.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-file-alt mr-3"></i>
                <span>试卷管理</span>
            </a>
            <a href="questions.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-question-circle mr-3"></i>
                <span>题目管理</span>
            </a>
            <a href="member.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-users mr-3"></i>
                <span>用户管理</span>
            </a>
            <a href="admin.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-user-shield mr-3"></i>
                <span>后台管理员</span>
            </a>
            <a href="exam_logs.php" class="block px-4 py-3 rounded-md hover:bg-gray-700 transition-colors duration-300">
                <i class="fas fa-history mr-3"></i>
                <span>考试记录</span>
            </a>
            <a href="#" onclick="confirmLogout()" class="block px-4 py-3 rounded-md hover:bg-red-600 bg-gray-700 text-red-300 hover:text-white transition-colors duration-300">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <span>退出登录</span>
            </a>
        </nav>
    </div>
</div>

<script>
// 退出登录确认
function confirmLogout() {
    if (confirm('确定要退出登录吗？')) {
        window.location.href = 'loginout.php';
    }
}

// 移动端菜单交互
const mobileMenuButton = document.getElementById('mobile-menu-button');
const closeMobileMenuButton = document.getElementById('close-mobile-menu');
const mobileSidebar = document.getElementById('mobile-sidebar');
const mobileSidebarContent = document.getElementById('mobile-sidebar-content');

mobileMenuButton.addEventListener('click', () => {
    mobileSidebar.classList.remove('hidden');
    setTimeout(() => {
        mobileSidebarContent.classList.remove('-translate-x-full');
    }, 10);
});

function closeMobileMenu() {
    mobileSidebarContent.classList.add('-translate-x-full');
    setTimeout(() => {
        mobileSidebar.classList.add('hidden');
    }, 300);
}

closeMobileMenuButton.addEventListener('click', closeMobileMenu);
mobileSidebar.addEventListener('click', (e) => {
    if (e.target === mobileSidebar) {
        closeMobileMenu();
    }
});
</script>