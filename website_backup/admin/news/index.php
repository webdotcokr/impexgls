<?php
/**
 * 뉴스/공지사항 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '뉴스/공지사항 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// 게시물 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM news_posts WHERE id = ?");
            $stmt->execute([$_POST['post_id']]);
            
            logAdminAction('delete', 'news_posts', $_POST['post_id'], 'News post deleted');
            setAlert('success', '게시물이 삭제되었습니다.');
        } catch (Exception $e) {
            setAlert('error', '삭제에 실패했습니다.');
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// 상태 변경 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE news_posts SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['post_id']]);
            
            logAdminAction('update', 'news_posts', $_POST['post_id'], 'Status changed to ' . $_POST['status']);
            setAlert('success', '상태가 변경되었습니다.');
        } catch (Exception $e) {
            setAlert('error', '상태 변경에 실패했습니다.');
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    
    // 조건 구성
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(title LIKE :search OR title_en LIKE :search OR content LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($status_filter) {
        $where_conditions[] = "status = :status";
        $params[':status'] = $status_filter;
    }
    
    if ($category_filter) {
        $where_conditions[] = "category = :category";
        $params[':category'] = $category_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM news_posts $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM news_posts 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리 목록
    $stmt = $pdo->query("SELECT DISTINCT category FROM news_posts ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("News posts error: " . $e->getMessage());
    $posts = [];
    $total_count = 0;
    $total_pages = 1;
    $categories = [];
}

// 상태 옵션
$status_options = [
    'draft' => ['label' => '초안', 'color' => 'gray'],
    'published' => ['label' => '게시됨', 'color' => 'green'],
    'archived' => ['label' => '보관됨', 'color' => 'yellow']
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 -->
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">뉴스/공지사항 관리</h1>
        <p class="text-gray-600 mt-1">총 <?php echo number_format($total_count); ?>개의 게시물</p>
    </div>
    <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
        <i class="fas fa-plus mr-2"></i>새 게시물
    </a>
</div>

<!-- 검색 및 필터 -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- 검색어 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       name="search" 
                       value="<?php echo e($search); ?>"
                       placeholder="제목, 내용 검색"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 상태 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                <select name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($status_options as $value => $option): ?>
                    <option value="<?php echo $value; ?>" <?php echo $status_filter == $value ? 'selected' : ''; ?>>
                        <?php echo $option['label']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 카테고리 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                <select name="category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                        <?php echo e($cat); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 검색 버튼 -->
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search mr-2"></i>검색
                </button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition duration-200">
                    초기화
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 게시물 목록 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        제목
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        카테고리
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        작성자
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        상태
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        조회수
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        작성일
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        작업
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        게시물이 없습니다.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">
                                    <?php echo e($post['title']); ?>
                                    <?php if ($post['is_featured']): ?>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-star mr-1"></i>Featured
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($post['title_en']): ?>
                                <div class="text-gray-500 text-xs mt-1"><?php echo e($post['title_en']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($post['category']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($post['author']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <select name="status" 
                                        onchange="if(confirm('상태를 변경하시겠습니까?')) this.form.submit();"
                                        class="text-xs rounded-full px-3 py-1 font-medium
                                               bg-<?php echo $status_options[$post['status']]['color']; ?>-100 
                                               text-<?php echo $status_options[$post['status']]['color']; ?>-800">
                                    <?php foreach ($status_options as $value => $option): ?>
                                    <option value="<?php echo $value; ?>" 
                                            <?php echo $post['status'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $option['label']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="hidden"></button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo number_format($post['view_count']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo formatDate($post['created_at'], 'Y-m-d'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-2">
                                <a href="edit.php?id=<?php echo $post['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="" class="inline" 
                                      onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" name="delete_post" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <div class="bg-white px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                총 <span class="font-medium"><?php echo number_format($total_count); ?></span>개
                (<?php echo $page; ?>/<?php echo $total_pages; ?> 페이지)
            </div>
            <div class="flex space-x-1">
                <?php
                $query_params = $_GET;
                unset($query_params['page']);
                $base_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($query_params) . '&page=';
                
                $pagination = generatePagination($page, $total_pages, $base_url . '{page}');
                foreach ($pagination as $item):
                ?>
                    <?php if ($item['label'] === '...'): ?>
                        <span class="px-3 py-1 text-gray-500">...</span>
                    <?php else: ?>
                        <a href="<?php echo $item['url']; ?>" 
                           class="px-3 py-1 rounded <?php echo $item['active'] ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                            <?php echo $item['label']; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>