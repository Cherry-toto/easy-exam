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

// 处理AJAX表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    
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
        $analysis = trim($_POST['analysis'] ?? '') ?: '';
    
    $errors = [];
    
    // 验证必填字段
    if (empty($title)) {
        $errors[] = '题目内容不能为空';
    }
    if (empty($type)) {
        $errors[] = '请选择题目类型';
    }
    if (empty($correctAnswer)) {
        $errors[] = '请选择正确答案';
    }
    if ($score <= 0) {
        $errors[] = '分值必须大于0';
    }
    if ($examId <= 0) {
        $errors[] = '请选择所属试卷';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // 转换题目类型为数字
    $typeNum = ($type === 'single') ? 1 : 2;
    
    // 处理多选题答案
    if ($type === 'multiple' && is_array($correctAnswer)) {
        $correctAnswer = implode(',', $correctAnswer);
    }
    
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
            'answer' => $correctAnswer,
            'score' => $score,
            'exam_id' => $examId,
            'analysis' => $analysis
        ];

    try {
        // 更新或添加题目
        if ($questionId > 0) {
            $result = $questionModel->updateQuestion($questionId, $questionData);
            $message = '题目更新成功';
        } else {
            $result = $questionModel->addQuestion($questionData);
            $message = '题目添加成功';
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => $message, 'redirect' => 'questions.php']);
        } else {
            echo json_encode(['success' => false, 'errors' => ['操作失败，请重试']]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
    }
    exit;
}

// 兼容传统表单提交
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
        
        // 处理多选题答案
        if ($type === 'multiple' && is_array($correctAnswer)) {
            $correctAnswer = implode(',', $correctAnswer);
        }
        
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
            'exam_id' => $examId,
            'analysis' => $analysis
        ];

        // 更新或添加题目
        if ($questionId > 0) {
            $result = $questionModel->updateQuestion($questionId, $questionData);
        } else {
            $result = $questionModel->addQuestion($questionData);
        }

        if ($result) {
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
            <form method="post" class="space-y-6" id="questionForm">
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

                    <!-- 题目解析 -->
                    <div>
                        <label for="analysis" class="block text-sm font-medium text-gray-700 mb-1">题目解析</label>
                        <textarea id="analysis" name="analysis" rows="3" class="w-full rounded-md border-gray-300 shadow-sm input-focus transition-custom"><?php echo $question ? htmlspecialchars($question['analysis']) : ''; ?></textarea>
                    </div>

                    <!-- 操作按钮 -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" class="btn-outline flex items-center" onclick="window.history.back();">
                            <i class="fas fa-times mr-2"></i>取消
                        </button>
                        <button type="submit" class="btn-primary flex items-center" id="submitBtn">
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

<script src="../static/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // 题目类型切换
        $('input[name="type"]').on('change', function() {
            if ($(this).val() === 'single') {
                $('#single-answer').show();
                $('#multiple-answer').hide();
            } else {
                $('#single-answer').hide();
                $('#multiple-answer').show();
            }
        });

        // 试卷搜索模态框
        const $modal = $('#exam-search-modal');
        const $modalContent = $('#modal-content');
        let selectedExamId = null;
        let selectedExamName = null;

        function openModal() {
            $modal.removeClass('hidden').addClass('opacity-100');
            $modalContent.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            $('#exam-search-input').focus();
        }

        function closeModal() {
            $modal.removeClass('opacity-100').addClass('hidden');
            $modalContent.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        }

        $('#search-exam-btn').on('click', openModal);
        $('#close-modal, #cancel-search').on('click', closeModal);

        // 点击模态框外部关闭
        $modal.on('click', function(e) {
            if (e.target === this) closeModal();
        });

        // 搜索试卷
        function searchExams() {
            const searchTerm = $('#exam-search-input').val().trim();
            if (!searchTerm) {
                $('#exam-search-results').html('<p class="text-center text-gray-500 py-4">请输入搜索关键词</p>');
                return;
            }

            $('#exam-search-results').html('<p class="text-center text-gray-500 py-4">搜索中...</p>');

            $.get('question_edit.php', {
                search_exam_ajax: 1,
                search_term: searchTerm,
                <?php echo $questionId ? "id: $questionId" : ''; ?>
            })
            .done(function(data) {
                $('#exam-search-results').html(data);
                bindExamSelectionEvents();
            })
            .fail(function() {
                $('#exam-search-results').html('<p class="text-center text-red-500 py-4">搜索失败，请重试</p>');
            });
        }

        $('#do-search-exam').on('click', searchExams);
        $('#exam-search-input').on('keypress', function(e) {
            if (e.which === 13) searchExams();
        });

        // 试卷选择事件
        function bindExamSelectionEvents() {
            $('.select-exam').on('click', function() {
                $('.select-exam').removeClass('bg-blue-50 border-blue-200')
                    .find('.exam-selected-icon').addClass('hidden');

                $(this).addClass('bg-blue-50 border-blue-200')
                    .find('.exam-selected-icon').removeClass('hidden');

                selectedExamId = $(this).data('id');
                selectedExamName = $(this).data('name');
                $('#confirm-exam-selection').prop('disabled', false);
            });
        }

        // 确认选择试卷
        $('#confirm-exam-selection').on('click', function() {
            if (selectedExamId) {
                $('#exam_id').val(selectedExamId);
                $('#exam_name').val(selectedExamName);
                closeModal();
            }
        });

        // 初始化多选答案字段
        if ($('input[name="type"][value="multiple"]').is(':checked')) {
            const $hiddenInput = $('<input type="hidden" name="correct_answer">').appendTo('form');
            
            function updateMultipleAnswers() {
                const values = $('input[name="correct_answer[]"]:checked').map(function() {
                    return $(this).val();
                }).get().join(',');
                $hiddenInput.val(values);
            }

            $('input[name="correct_answer[]"]').on('change', updateMultipleAnswers);
            updateMultipleAnswers();
        }

        // AJAX表单提交
        $('#questionForm').on('submit', function(e) {
            e.preventDefault();
            
            const $submitBtn = $('#submitBtn');
            const originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin mr-2"></i>提交中...');

            const formData = new FormData(this);
            
            // 处理多选题答案
            if ($('input[name="type"]:checked').val() === 'multiple') {
                const values = $('input[name="correct_answer[]"]:checked').map(function() {
                    return $(this).val();
                }).get().join(',');
                formData.set('correct_answer', values);
            }

            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
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
</body>
</html>