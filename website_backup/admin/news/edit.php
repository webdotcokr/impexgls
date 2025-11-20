<?php
/**
 * 뉴스/공지사항 수정
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '게시물 수정';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 게시물 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        setAlert('error', '게시물을 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("News post error: " . $e->getMessage());
    setAlert('error', '데이터 조회에 실패했습니다.');
    header('Location: index.php');
    exit;
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
        exit;
    }
    
    try {
        // 데이터 준비
        $title = trim($_POST['title'] ?? '');
        $title_en = trim($_POST['title_en'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $content_en = trim($_POST['content_en'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $author = trim($_POST['author'] ?? 'IT Management Team');
        $category = $_POST['category'] ?? 'logistics';
        $status = $_POST['status'] ?? 'draft';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        
        // 발행일 처리
        $published_at = $post['published_at'];
        if ($status === 'published' && !$published_at) {
            $published_at = date('Y-m-d H:i:s');
        }
        
        // 유효성 검사
        if (empty($title)) {
            throw new Exception('제목을 입력해주세요.');
        }
        if (empty($content)) {
            throw new Exception('내용을 입력해주세요.');
        }
        
        // 업데이트
        $stmt = $pdo->prepare("
            UPDATE news_posts SET
                title = :title,
                title_en = :title_en,
                content = :content,
                content_en = :content_en,
                excerpt = :excerpt,
                author = :author,
                category = :category,
                status = :status,
                is_featured = :is_featured,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                published_at = :published_at,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':title_en' => $title_en,
            ':content' => $content,
            ':content_en' => $content_en,
            ':excerpt' => $excerpt,
            ':author' => $author,
            ':category' => $category,
            ':status' => $status,
            ':is_featured' => $is_featured,
            ':meta_title' => $meta_title,
            ':meta_description' => $meta_description,
            ':meta_keywords' => $meta_keywords,
            ':published_at' => $published_at,
            ':id' => $id
        ]);
        
        logAdminAction('update', 'news_posts', $id, 'News post updated: ' . $title);
        
        setAlert('success', '게시물이 수정되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 카테고리 목록
$categories = ['logistics', 'announcement', 'maintenance', 'service', 'general'];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">게시물 수정</h1>
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
                <!-- 제목 (한국어) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        제목 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo e($post['title']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 제목 (영어) -->
                <div>
                    <label for="title_en" class="block text-sm font-medium text-gray-700 mb-2">
                        제목 (영어)
                    </label>
                    <input type="text" 
                           id="title_en" 
                           name="title_en" 
                           value="<?php echo e($post['title_en']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- 요약 -->
                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                        요약
                    </label>
                    <textarea id="excerpt" 
                              name="excerpt" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($post['excerpt']); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">목록에 표시될 짧은 요약문</p>
                </div>
                
                <!-- 카테고리/작성자 -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            카테고리
                        </label>
                        <select id="category" 
                                name="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $post['category'] == $cat ? 'selected' : ''; ?>>
                                <?php echo ucfirst($cat); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-2">
                            작성자
                        </label>
                        <input type="text" 
                               id="author" 
                               name="author" 
                               value="<?php echo e($post['author']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- 게시물 정보 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">작성일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($post['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">수정일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($post['updated_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">발행일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo $post['published_at'] ? formatDate($post['published_at']) : '-'; ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">조회수</dt>
                            <dd class="mt-1 text-gray-900"><?php echo number_format($post['view_count']); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- 내용 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">내용</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 내용 (한국어) -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        내용 <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content" 
                              name="content" 
                              rows="10"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required><?php echo e($post['content']); ?></textarea>
                </div>
                
                <!-- 내용 (영어) -->
                <div>
                    <label for="content_en" class="block text-sm font-medium text-gray-700 mb-2">
                        내용 (영어)
                    </label>
                    <textarea id="content_en" 
                              name="content_en" 
                              rows="10"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($post['content_en']); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- SEO 설정 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">SEO 설정</h3>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                        메타 제목
                    </label>
                    <input type="text" 
                           id="meta_title" 
                           name="meta_title" 
                           value="<?php echo e($post['meta_title']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">비워두면 게시물 제목이 사용됩니다</p>
                </div>
                
                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                        메타 설명
                    </label>
                    <textarea id="meta_description" 
                              name="meta_description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($post['meta_description']); ?></textarea>
                </div>
                
                <div>
                    <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                        메타 키워드
                    </label>
                    <input type="text" 
                           id="meta_keywords" 
                           name="meta_keywords" 
                           value="<?php echo e($post['meta_keywords']); ?>"
                           placeholder="키워드1, 키워드2, 키워드3"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>초안</option>
                        <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>게시</option>
                        <option value="archived" <?php echo $post['status'] == 'archived' ? 'selected' : ''; ?>>보관</option>
                    </select>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_featured" 
                           name="is_featured" 
                           value="1"
                           <?php echo $post['is_featured'] ? 'checked' : ''; ?>
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

<?php include '../includes/footer.php'; ?>