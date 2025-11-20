<?php
/**
 * FAQ 작성
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '새 FAQ 작성';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        
        // 데이터 준비
        $category = $_POST['category'] ?? '일반';
        $question = trim($_POST['question'] ?? '');
        $question_en = trim($_POST['question_en'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $answer_en = trim($_POST['answer_en'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($question)) {
            throw new Exception('질문을 입력해주세요.');
        }
        if (empty($answer)) {
            throw new Exception('답변을 입력해주세요.');
        }
        
        // 삽입
        $stmt = $pdo->prepare("
            INSERT INTO faqs (
                category, question, question_en, answer, answer_en,
                sort_order, is_active, created_at
            ) VALUES (
                :category, :question, :question_en, :answer, :answer_en,
                :sort_order, :is_active, NOW()
            )
        ");
        
        $stmt->execute([
            ':category' => $category,
            ':question' => $question,
            ':question_en' => $question_en,
            ':answer' => $answer,
            ':answer_en' => $answer_en,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active
        ]);
        
        $faq_id = $pdo->lastInsertId();
        logAdminAction('create', 'faqs', $faq_id, 'FAQ created: ' . $question);
        
        setAlert('success', 'FAQ가 작성되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 카테고리 목록
$categories = [
    '일반' => '일반 문의',
    '운송' => '운송 서비스',
    '통관' => '통관 업무',
    '창고' => '창고 서비스',
    '기타' => '기타'
];

// 다음 순서 번호 가져오기
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT MAX(sort_order) FROM faqs");
    $next_order = $stmt->fetchColumn() + 10;
} catch (Exception $e) {
    $next_order = 10;
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">새 FAQ 작성</h1>
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>목록으로
        </a>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
        
        <!-- 기본 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">기본 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 카테고리 및 순서 -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            카테고리 <span class="text-red-500">*</span>
                        </label>
                        <select id="category" 
                                name="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($_POST['category'] ?? '일반') == $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            정렬 순서
                        </label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="<?php echo e($_POST['sort_order'] ?? $next_order); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">숫자가 작을수록 먼저 표시됩니다</p>
                    </div>
                </div>
                
                <!-- 활성화 상태 -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo ($_POST['is_active'] ?? '1') ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        활성화 (체크하면 프론트엔드에 표시됩니다)
                    </label>
                </div>
            </div>
        </div>
        
        <!-- 질문 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">질문</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 질문 (한국어) -->
                <div>
                    <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                        질문 (한국어) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="question" 
                           name="question" 
                           value="<?php echo e($_POST['question'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 질문 (영어) -->
                <div>
                    <label for="question_en" class="block text-sm font-medium text-gray-700 mb-2">
                        질문 (영어)
                    </label>
                    <input type="text" 
                           id="question_en" 
                           name="question_en" 
                           value="<?php echo e($_POST['question_en'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- 답변 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">답변</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 답변 (한국어) -->
                <div>
                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                        답변 (한국어) <span class="text-red-500">*</span>
                    </label>
                    <textarea id="answer" 
                              name="answer" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required><?php echo e($_POST['answer'] ?? ''); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                        기본적인 HTML 태그를 사용할 수 있습니다: &lt;p&gt;, &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;
                    </p>
                </div>
                
                <!-- 답변 (영어) -->
                <div>
                    <label for="answer_en" class="block text-sm font-medium text-gray-700 mb-2">
                        답변 (영어)
                    </label>
                    <textarea id="answer_en" 
                              name="answer_en" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($_POST['answer_en'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- 미리보기 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">미리보기</h3>
            </div>
            <div class="p-6" id="preview">
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-900">Q. <span id="preview-question">질문을 입력하세요</span></h4>
                        <div class="mt-2 text-gray-700" id="preview-answer">답변을 입력하세요</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 저장 버튼 -->
        <div class="flex justify-end space-x-3">
            <a href="index.php" 
               class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition duration-200">
                취소
            </a>
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                <i class="fas fa-save mr-2"></i>저장
            </button>
        </div>
    </form>
</div>

<script>
// 실시간 미리보기
document.getElementById('question').addEventListener('input', function() {
    document.getElementById('preview-question').textContent = this.value || '질문을 입력하세요';
});

document.getElementById('answer').addEventListener('input', function() {
    document.getElementById('preview-answer').innerHTML = this.value || '답변을 입력하세요';
});

// 페이지 로드 시 미리보기 업데이트
document.addEventListener('DOMContentLoaded', function() {
    const questionInput = document.getElementById('question');
    const answerInput = document.getElementById('answer');
    
    if (questionInput.value) {
        document.getElementById('preview-question').textContent = questionInput.value;
    }
    if (answerInput.value) {
        document.getElementById('preview-answer').innerHTML = answerInput.value;
    }
});
</script>

<?php include '../includes/footer.php'; ?>