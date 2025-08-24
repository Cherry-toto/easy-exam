<?php
// 检测用户是否登录，未登录则跳转到登录页面
require_once '../config.php';
require_once 'common/auth.php';
require_once 'common/question_model.php';
require_once 'common/exam_model.php';

// 检查权限
checkAdminLogin();

// 初始化题目模型
$questionModel = new QuestionModel($pdo);
$examModel = new ExamModel($pdo);

// 获取题目ID，如果存在则为编辑模式
$questionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$question = null;
$examId = 0;
$options = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];

// 如果是编辑模式，获取题目信息
if ($questionId > 0) {
    $question = $questionModel->getQuestionById($questionId);
    if (!$question) {
        die('题目不存在');
    }
    $examId = $question['exam_id'];
    // 解析选项JSON
    if (!empty($question['options'])) {
        $options = json_decode($question['options'], true) ?: $options;
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证表单数据
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $optionA = trim($_POST['option_a'] ?? '');
    $optionB = trim($_POST['option_b'] ?? '');
    $optionC = trim($_POST['option_c'] ?? '');
    $optionD = trim($_POST['option_d'] ?? '');
    $correctAnswer = $_POST['correct_answer'] ?? '';
    $score = (float)($_POST['score'] ?? 0);
    $examId = (int)($_POST['exam_id'] ?? 0);
    
    // 验证必填字段
    if (empty($title) || empty($type) || empty($correctAnswer) || $score <= 0 || $examId <= 0) {
        $error = '请填写所有必填字段';
    } else {
        // 转换题目类型为数字
        $typeNum = ($type === 'single') ? 1 : 2;
        
        // 准备选项JSON
        $optionsJson = json_encode([
            'A' => $optionA,
            'B' => $optionB,
            'C' => $optionC,
            'D' => $optionD
        ]);

        // 准备题目数据
        $questionData = [
            'title' => $title,
            'type' => $typeNum,
            'options' => $optionsJson,
            'correct_answer' => $correctAnswer,
            'score' => $score,
            'exam_id' => $examId
        ];

        // 更新或添加题目
        if ($questionId > 0) {
            // 更新题目
            $result = $questionModel->updateQuestion($questionId, $questionData);
        } else {
            // 添加题目
            $result = $questionModel->addQuestion($questionData);
        }

        if ($result) {
            // 重定向到题目列表页，并显示成功消息
            header('Location: questions.php?message=' . urlencode($questionId > 0 ? '题目更新成功' : '题目添加成功'));
            exit;
        } else {
            $error = '操作失败，请重试';
        }
    }
}

// 处理AJAX试卷搜索请求
if (isset($_GET['search_exam_ajax']) && $_GET['search_exam_ajax'] == 1) {
    $searchTerm = trim($_GET['search_term'] ?? '');
    $exams = [];
    if (!empty($searchTerm)) {
        $exams = $examModel->searchExams($searchTerm);
    }

    // 输出搜索结果HTML
    if (!empty($exams)) {
        foreach ($exams as $exam) {
            echo '<div class="p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer transition-custom flex justify-between items-center select-exam" data-id="' . $exam['id'] . '" data-name="' . htmlspecialchars($exam['title']) . '">
';
            echo '    <div>
';
            echo '        <h4 class="font-medium text-gray-900">' . htmlspecialchars($exam['title']) . '</h4>
';
         
            echo '    </div>';
            echo '    <i class="fas fa-check-circle text-green-500 hidden exam-selected-icon"></i>
';
            echo '</div>
';
        }
    } else {
        echo '<p class="text-center text-gray-500 py-4">暂无搜索结果</p>';
    }
    exit;
}

// 常规页面加载时的试卷搜索
$searchExam = trim($_GET['search_exam'] ?? '');
$exams = [];
if (!empty($searchExam)) {
    $exams = $examModel->searchExams($searchExam);
}
?>

<!-- 顶部导航栏 -->
<?php require_once 'common/header.php'; ?>


<!-- 主内容区 -->
<main class="flex-1 overflow-y-auto p-6 bg-gray-50 transition-all duration-300">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-question-circle mr-2 text-primary"></i>
                <?php echo $questionId > 0 ? '编辑题目' : '添加题目'; ?>
            </h1>
            <a href="questions.php" class="btn-outline flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>返回列表
            </a>
        </div>

        <!-- 错误提示 -->
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center animate-fade-in-down">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- 表单 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg">
            <div class="p-6">
                <form method="post" class="space-y-6">
                    <!-- 题目内容 -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">题目内容 <span class="text-danger">*</span></label>
                        <textarea id="title" name="title" rows="3" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" required><?php echo $question ? htmlspecialchars($question['title']) : ''; ?></textarea>
                    </div>

                    <!-- 题目类型 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">题目类型 <span class="text-danger">*</span></label>
                        <div class="flex space-x-4 mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="type" value="single" class="text-primary focus:ring-primary h-4 w-4" <?php echo (!$question || $question['type'] == 1) ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">单选题</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="type" value="multiple" class="text-primary focus:ring-primary h-4 w-4" <?php echo $question && $question['type'] == 2 ? 'checked' : ''; ?>>
                                <span class="ml-2 text-sm text-gray-700">多选题</span>
                            </label>
                        </div>
                    </div>

                    <!-- 所属试卷 -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">所属试卷 <span class="text-danger">*</span></label>
                        <div class="flex space-x-2 mt-2">
                            <input type="hidden" id="exam_id" name="exam_id" value="<?php echo $examId; ?>">
                            <input type="text" id="exam_name" class="flex-1 rounded-md border-gray-300 shadow-sm input-focus transition-custom" placeholder="搜索试卷名称" readonly value="<?php echo $examId ? $examModel->getExamNameById($examId) : ''; ?>">
                            <button type="button" id="search-exam-btn" class="btn-primary whitespace-nowrap">
                                <i class="fas fa-search mr-1"></i>搜索
                            </button>
                        </div>
                    </div>

                    <!-- 选项 -->
                    <div id="options-container">
                        <div class="mb-4">
                            <label for="option_a" class="block text-sm font-medium text-gray-700 mb-1">选项A</label>
                            <input type="text" id="option_a" name="option_a" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" value="<?php echo htmlspecialchars($options['A']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="option_b" class="block text-sm font-medium text-gray-700 mb-1">选项B</label>
                            <input type="text" id="option_b" name="option_b" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" value="<?php echo htmlspecialchars($options['B']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="option_c" class="block text-sm font-medium text-gray-700 mb-1">选项C</label>
                            <input type="text" id="option_c" name="option_c" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" value="<?php echo htmlspecialchars($options['C']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="option_d" class="block text-sm font-medium text-gray-700 mb-1">选项D</label>
                            <input type="text" id="option_d" name="option_d" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" value="<?php echo htmlspecialchars($options['D']); ?>">
                        </div>
                    </div>

                    <!-- 正确答案 -->
                    <div id="answer-container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">正确答案 <span class="text-danger">*</span></label>
                        <div id="single-answer" class="mt-2" style="<?php echo $question && $question['type'] == 2 ? 'display: none;' : ''; ?>">
                            <select name="correct_answer" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" required>
                                <option value="">请选择正确答案</option>
                                <option value="A" <?php echo $question && $question['answer'] === 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo $question && $question['answer'] === 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo $question && $question['answer'] === 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo $question && $question['answer'] === 'D' ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>
                        <div id="multiple-answer" class="mt-2" style="<?php echo $question && $question['type'] == 1 ? 'display: none;' : ''; ?>">
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="correct_answer[]" value="A" class="text-primary focus:ring-primary h-4 w-4" <?php echo $question && strpos($question['answer'], 'A') !== false ? 'checked' : ''; ?>>
                                    <span class="ml-2 text-sm text-gray-700">A</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="correct_answer[]" value="B" class="text-primary focus:ring-primary h-4 w-4" <?php echo $question && strpos($question['answer'], 'B') !== false ? 'checked' : ''; ?>>
                                    <span class="ml-2 text-sm text-gray-700">B</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="correct_answer[]" value="C" class="text-primary focus:ring-primary h-4 w-4" <?php echo $question && strpos($question['answer'], 'C') !== false ? 'checked' : ''; ?>>
                                    <span class="ml-2 text-sm text-gray-700">C</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="correct_answer[]" value="D" class="text-primary focus:ring-primary h-4 w-4" <?php echo $question && strpos($question['answer'], 'D') !== false ? 'checked' : ''; ?>>
                                    <span class="ml-2 text-sm text-gray-700">D</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">注：可选择多个选项</p>
                        </div>
                    </div>

                    <!-- 分值 -->
                    <div>
                        <label for="score" class="block text-sm font-medium text-gray-700 mb-1">分值 <span class="text-danger">*</span></label>
                        <input type="number" id="score" name="score" min="0.1" step="0.1" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom" required value="<?php echo $question ? $question['score'] : '1'; ?>">
                    </div>

                    <!-- 操作按钮 -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" class="btn-outline flex items-center" onclick="window.history.back();">
                            <i class="fas fa-times mr-2"></i>取消
                        </button>
                        <button type="submit" class="btn-primary flex items-center">
                            <i class="fas fa-save mr-2"></i><?php echo $questionId > 0 ? '更新题目' : '添加题目'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- 侧边栏 -->
<?php require_once 'common/sidebar.php'; ?>

<!-- 试卷搜索模态框 -->
<div id="exam-search-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
<div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
        <h3 class="text-lg font-medium text-gray-900">搜索试卷</h3>
        <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="p-6">
        <div class="flex mb-4">
            <input type="text" id="exam-search-input" class="flex-1 rounded-l-md border-gray-300 shadow-sm input-focus transition-custom" placeholder="输入试卷名称搜索">
            <button type="button" id="do-search-exam" class="btn-primary rounded-l-none">
                <i class="fas fa-search mr-1"></i>搜索
            </button>
        </div>
        <div id="exam-search-results" class="space-y-2 max-h-[40vh] overflow-y-auto">
            <?php if (!empty($exams)): ?>
                <?php foreach ($exams as $exam): ?>
                    <div class="p-3 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer transition-custom flex justify-between items-center select-exam" data-id="<?php echo $exam['id']; ?>" data-name="<?php echo htmlspecialchars($exam['name']); ?>>
                        <div>
                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($exam['name']); ?></h4>
                            <p class="text-sm text-gray-500">创建时间: <?php echo date('Y-m-d H:i', strtotime($exam['created_at'])); ?></p>
                        </div>
                        <i class="fas fa-check-circle text-green-500 hidden exam-selected-icon"></i>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500 py-4">暂无搜索结果</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3 sticky bottom-0">
        <button type="button" id="cancel-search" class="btn-outline">取消</button>
        <button type="button" id="confirm-exam-selection" class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed" disabled>确认选择</button>
    </div>
</div>
</div>

<script>
    // 题目类型切换
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'single') {
                document.getElementById('single-answer').style.display = 'block';
                document.getElementById('multiple-answer').style.display = 'none';
            } else {
                document.getElementById('single-answer').style.display = 'none';
                document.getElementById('multiple-answer').style.display = 'block';
            }
        });
    });

    // 试卷搜索模态框
    const modal = document.getElementById('exam-search-modal');
    const modalContent = document.getElementById('modal-content');
    const searchExamBtn = document.getElementById('search-exam-btn');
    const closeModalBtn = document.getElementById('close-modal');
    const cancelSearchBtn = document.getElementById('cancel-search');
    const confirmExamSelectionBtn = document.getElementById('confirm-exam-selection');
    let selectedExamId = null;
    let selectedExamName = null;

    // 打开模态框
    searchExamBtn.addEventListener('click', function() {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalContent.classList.add('scale-100', 'opacity-100');
            modalContent.classList.remove('scale-95', 'opacity-0');
        }, 10);
        document.getElementById('exam-search-input').focus();
    });

    // 关闭模态框
    function closeModal() {
        modal.classList.remove('opacity-100');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    closeModalBtn.addEventListener('click', closeModal);
    cancelSearchBtn.addEventListener('click', closeModal);

    // 点击模态框外部关闭
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
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

            selectedExamId = examId;
            selectedExamName = examName;

            // 启用确认按钮
            confirmExamSelectionBtn.disabled = false;
        }
    });

    // 确认选择试卷
    confirmExamSelectionBtn.addEventListener('click', function() {
        if (selectedExamId) {
            document.getElementById('exam_id').value = selectedExamId;
            document.getElementById('exam_name').value = selectedExamName;
            closeModal();
        }
    });

    // 搜索试卷
    document.getElementById('do-search-exam').addEventListener('click', function() {
        const searchTerm = document.getElementById('exam-search-input').value.trim();
        if (searchTerm) {
            // 显示加载状态
            const resultsContainer = document.getElementById('exam-search-results');
            resultsContainer.innerHTML = '<p class="text-center text-gray-500 py-4">搜索中...</p>';

            // 发送AJAX请求获取搜索结果
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'question_edit.php?search_exam_ajax=1&search_term=' + encodeURIComponent(searchTerm) + '<?php echo $questionId ? "&id=$questionId" : ''; ?>', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    resultsContainer.innerHTML = xhr.responseText;
                    // 重新绑定选择事件
                    bindExamSelectionEvents();
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
    function bindExamSelectionEvents() {
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

                selectedExamId = examId;
                selectedExamName = examName;

                // 启用确认按钮
                confirmExamSelectionBtn.disabled = false;
            });
        });
    }

    // 按下Enter键搜索
    document.getElementById('exam-search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('do-search-exam').click();
        }
    });

    // 初始化多选答案字段
    if (document.querySelector('input[name="type"][value="multiple"]').checked) {
        // 为多选答案创建隐藏字段
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'correct_answer';
        document.querySelector('form').appendChild(hiddenInput);

        // 监听复选框变化
        document.querySelectorAll('input[name="correct_answer[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateMultipleAnswers);
        });

        // 初始化
        updateMultipleAnswers();

        function updateMultipleAnswers() {
            const selectedValues = Array.from(document.querySelectorAll('input[name="correct_answer[]"]:checked'))
                .map(checkbox => checkbox.value);
            hiddenInput.value = selectedValues.join('');
        }
    }
</script>
<?php
// 引入页脚文件
require_once 'common/footer.php';
?>
</body>
</html>