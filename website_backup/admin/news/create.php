<?php
/**
 * 뉴스/공지사항 작성
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '새 게시물 작성';

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
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author = trim($_POST['author'] ?? 'IT Management Team');
        $category = $_POST['category'] ?? 'logistics';
        $status = $_POST['status'] ?? 'draft';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        // 발행일 처리
        $published_at = null;
        if ($status === 'published') {
            $published_at = date('Y-m-d H:i:s');
        }
        
        // 유효성 검사
        if (empty($title)) {
            throw new Exception('제목을 입력해주세요.');
        }
        if (empty($content)) {
            throw new Exception('내용을 입력해주세요.');
        }
        
        // 삽입
        $stmt = $pdo->prepare("
            INSERT INTO news_posts (
                title, content, author, category, status, is_featured,
                published_at, created_at
            ) VALUES (
                :title, :content, :author, :category, :status, :is_featured,
                :published_at, NOW()
            )
        ");

        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':author' => $author,
            ':category' => $category,
            ':status' => $status,
            ':is_featured' => $is_featured,
            ':published_at' => $published_at
        ]);
        
        $post_id = $pdo->lastInsertId();
        logAdminAction('create', 'news_posts', $post_id, 'News post created: ' . $title);
        
        setAlert('success', '게시물이 작성되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// URL에서 카테고리 파라미터 읽기
$auto_category = $_GET['category'] ?? '';
$allowed_categories = ['logistics', 'careers', 'announcement', 'maintenance', 'service', 'general'];

// 유효한 카테고리인지 확인
if ($auto_category && !in_array($auto_category, $allowed_categories)) {
    $auto_category = '';
}

// 카테고리 목록
$categories = [
    'logistics' => 'Logistics News (공지사항)',
    'careers' => 'Careers (커리어)',
    'announcement' => 'Announcement',
    'maintenance' => 'Maintenance',
    'service' => 'Service',
    'general' => 'General'
];

// 카테고리별 제목
$category_titles = [
    'logistics' => '공지사항 작성',
    'careers' => '커리어 작성',
    'announcement' => '안내 작성',
    'maintenance' => '유지보수 작성',
    'service' => '서비스 작성',
    'general' => '일반 작성'
];

$page_title = $auto_category && isset($category_titles[$auto_category])
    ? $category_titles[$auto_category]
    : '새 게시물 작성';

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo e($page_title); ?></h1>
        <a href="index.php<?php echo $auto_category ? '?category=' . $auto_category : ''; ?>" class="text-blue-600 hover:text-blue-800">
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
                <!-- 제목 -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        제목 <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="<?php echo e($_POST['title'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 카테고리 (자동 설정) -->
                <?php if ($auto_category): ?>
                    <input type="hidden" name="category" value="<?php echo e($auto_category); ?>">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong><?php echo e($categories[$auto_category]); ?></strong> 게시판에 작성합니다.
                        </p>
                    </div>
                <?php else: ?>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            카테고리
                        </label>
                        <select id="category"
                                name="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($_POST['category'] ?? 'logistics') == $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- 작성자 -->
                <div class="<?php echo $auto_category ? '' : 'grid grid-cols-2 gap-4'; ?>">
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">
                            작성자
                        </label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               value="<?php echo e($_POST['author'] ?? 'IT Management Team'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 내용 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">내용</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 내용 -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        내용 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content"
                              name="content"
                              rows="10"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required><?php echo e($_POST['content'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- 게시 옵션 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">게시 옵션</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        상태
                    </label>
                    <select id="status" 
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" <?php echo ($_POST['status'] ?? 'draft') == 'draft' ? 'selected' : ''; ?>>초안</option>
                        <option value="published" <?php echo ($_POST['status'] ?? '') == 'published' ? 'selected' : ''; ?>>게시</option>
                        <option value="archived" <?php echo ($_POST['status'] ?? '') == 'archived' ? 'selected' : ''; ?>>보관</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_featured" 
                           name="is_featured" 
                           value="1"
                           <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                        주요 게시물로 설정
                    </label>
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

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<!-- Summernote JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function() {
    // Summernote 초기화
    $('#content').summernote({
        height: 400,
        placeholder: 'Enter content...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'Poppins'],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
        callbacks: {
            onImageUpload: function(files) {
                // 이미지 업로드 기능은 향후 구현 가능
                // 현재는 base64 인코딩으로 처리됨
                for(let i = 0; i < files.length; i++) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        let img = $('<img>').attr('src', e.target.result);
                        $('#content').summernote('insertNode', img[0]);
                    };
                    reader.readAsDataURL(files[i]);
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>