<?php
/**
 * FAQ 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'FAQ 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// FAQ 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faq'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$_POST['faq_id']]);
            
            logAdminAction('delete', 'faqs', $_POST['faq_id'], 'FAQ deleted');
            setAlert('success', 'FAQ가 삭제되었습니다.');
        } catch (Exception $e) {
            setAlert('error', '삭제에 실패했습니다.');
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// 활성화 상태 변경
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE faqs SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$_POST['faq_id']]);
            
            logAdminAction('update', 'faqs', $_POST['faq_id'], 'FAQ active status toggled');
            setAlert('success', '상태가 변경되었습니다.');
        } catch (Exception $e) {
            setAlert('error', '상태 변경에 실패했습니다.');
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit;
}

// 순서 변경 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            foreach ($_POST['sort_order'] as $id => $order) {
                $stmt = $pdo->prepare("UPDATE faqs SET sort_order = ? WHERE id = ?");
                $stmt->execute([intval($order), intval($id)]);
            }
            
            $pdo->commit();
            logAdminAction('update', 'faqs', null, 'FAQ sort order updated');
            setAlert('success', '순서가 변경되었습니다.');
        } catch (Exception $e) {
            $pdo->rollBack();
            setAlert('error', '순서 변경에 실패했습니다.');
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
        $where_conditions[] = "(question LIKE :search OR question_en LIKE :search OR answer LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($category_filter) {
        $where_conditions[] = "category = :category";
        $params[':category'] = $category_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM faqs $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM faqs 
        $where_clause 
        ORDER BY category, sort_order, id 
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리 목록
    $stmt = $pdo->query("SELECT DISTINCT category FROM faqs ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive
        FROM faqs
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("FAQ error: " . $e->getMessage());
    $faqs = [];
    $total_count = 0;
    $total_pages = 1;
    $categories = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
}

// 카테고리 한글명
$category_names = [
    '일반' => '일반 문의',
    '운송' => '운송 서비스',
    '통관' => '통관 업무',
    '창고' => '창고 서비스',
    '기타' => '기타'
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 및 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">FAQ 관리</h1>
        </div>
        <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>새 FAQ
        </a>
    </div>
    
    <!-- 통계 카드 -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">전체 FAQ</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-question-circle text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">활성 FAQ</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active']); ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">비활성 FAQ</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></p>
                </div>
                <div class="bg-gray-100 rounded-full p-2">
                    <i class="fas fa-times-circle text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 검색 및 필터 -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 검색어 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                <input type="text" 
                       name="search" 
                       value="<?php echo e($search); ?>"
                       placeholder="질문, 답변 검색"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 카테고리 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                <select name="category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                        <?php echo e($category_names[$cat] ?? $cat); ?>
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

<!-- FAQ 목록 -->
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            순서
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            카테고리
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            질문
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            조회수
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            상태
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            작업
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            FAQ가 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $current_category = '';
                        foreach ($faqs as $faq): 
                            // 카테고리 구분선
                            if ($current_category !== $faq['category']):
                                $current_category = $faq['category'];
                        ?>
                        <tr class="bg-gray-50">
                            <td colspan="6" class="px-6 py-2 text-sm font-semibold text-gray-700">
                                <?php echo e($category_names[$current_category] ?? $current_category); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" 
                                       name="sort_order[<?php echo $faq['id']; ?>]" 
                                       value="<?php echo $faq['sort_order']; ?>"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded-md text-sm text-center">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($category_names[$faq['category']] ?? $faq['category']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">
                                        <?php echo e($faq['question']); ?>
                                    </div>
                                    <?php if ($faq['question_en']): ?>
                                    <div class="text-gray-500 text-xs mt-1"><?php echo e($faq['question_en']); ?></div>
                                    <?php endif; ?>
                                    <div class="text-gray-600 text-xs mt-2">
                                        <?php echo e(mb_substr(strip_tags($faq['answer']), 0, 100)); ?>...
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format($faq['view_count']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                    <button type="submit" 
                                            name="toggle_active"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   <?php echo $faq['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $faq['is_active'] ? '활성' : '비활성'; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $faq['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline" 
                                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                        <button type="submit" name="delete_faq" 
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
        
        <?php if (!empty($faqs)): ?>
        <!-- 순서 저장 버튼 -->
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <button type="submit" 
                    name="update_order"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-200">
                <i class="fas fa-sort mr-2"></i>순서 저장
            </button>
        </div>
        <?php endif; ?>
    </div>
</form>

<!-- 페이지네이션 -->
<?php if ($total_pages > 1): ?>
<div class="mt-4 flex items-center justify-between">
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
<?php endif; ?>

<?php include '../includes/footer.php'; ?>