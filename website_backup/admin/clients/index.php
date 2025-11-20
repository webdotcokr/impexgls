<?php
/**
 * 클라이언트 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '클라이언트 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// 클라이언트 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            
            // 로고 파일 삭제
            $stmt = $pdo->prepare("SELECT logo_path FROM clients WHERE id = ?");
            $stmt->execute([$_POST['client_id']]);
            $logo_path = $stmt->fetchColumn();
            
            if ($logo_path && file_exists(PROJECT_ROOT . $logo_path)) {
                unlink(PROJECT_ROOT . $logo_path);
            }
            
            // DB에서 삭제
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->execute([$_POST['client_id']]);
            
            logAdminAction('delete', 'clients', $_POST['client_id'], 'Client deleted');
            setAlert('success', '클라이언트가 삭제되었습니다.');
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
            $stmt = $pdo->prepare("UPDATE clients SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$_POST['client_id']]);
            
            logAdminAction('update', 'clients', $_POST['client_id'], 'Client active status toggled');
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
                $stmt = $pdo->prepare("UPDATE clients SET sort_order = ? WHERE id = ?");
                $stmt->execute([intval($order), intval($id)]);
            }
            
            $pdo->commit();
            logAdminAction('update', 'clients', null, 'Client sort order updated');
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
        $where_conditions[] = "(name LIKE :search OR name_en LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($category_filter) {
        $where_conditions[] = "category = :category";
        $params[':category'] = $category_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clients $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM clients 
        $where_clause 
        ORDER BY category, sort_order, id DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리 목록 및 통계
    $stmt = $pdo->query("
        SELECT 
            category,
            COUNT(*) as count,
            SUM(is_active = 1) as active_count
        FROM clients 
        GROUP BY category 
        ORDER BY category
    ");
    $category_stats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category_stats[$row['category']] = $row;
    }
    
    // 전체 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive
        FROM clients
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Client error: " . $e->getMessage());
    $clients = [];
    $total_count = 0;
    $total_pages = 1;
    $category_stats = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 및 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">클라이언트 관리</h1>
        </div>
        <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>새 클라이언트
        </a>
    </div>
    
    <!-- 통계 카드 -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">전체</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">활성</p>
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
                    <p class="text-gray-500 text-sm">비활성</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></p>
                </div>
                <div class="bg-gray-100 rounded-full p-2">
                    <i class="fas fa-times-circle text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 카테고리별 통계 -->
    <?php if (!empty($category_stats)): ?>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-3">카테고리별 현황</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php foreach ($category_stats as $cat => $stat): ?>
            <div class="text-center">
                <p class="text-xs text-gray-500"><?php echo e($stat['category_name'] ?? $cat); ?></p>
                <p class="text-lg font-semibold text-gray-800">
                    <?php echo $stat['active_count']; ?>/<?php echo $stat['count']; ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
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
                       placeholder="회사명, 설명 검색"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 카테고리 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                <select name="category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($category_stats as $cat => $stat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                        <?php echo e($stat['category_name'] ?? $cat); ?> (<?php echo $stat['count']; ?>)
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

<!-- 클라이언트 목록 -->
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
                            로고
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            회사 정보
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            카테고리
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            웹사이트
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
                    <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            클라이언트가 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $current_category = '';
                        foreach ($clients as $client): 
                            // 카테고리 구분선
                            if ($current_category !== $client['category']):
                                $current_category = $client['category'];
                        ?>
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-2 text-sm font-semibold text-gray-700">
                                <?php echo e($client['category_name'] ?? $current_category); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" 
                                       name="sort_order[<?php echo $client['id']; ?>]" 
                                       value="<?php echo $client['sort_order']; ?>"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded-md text-sm text-center">
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($client['logo_path'] && file_exists(PROJECT_ROOT . $client['logo_path'])): ?>
                                <img src="<?php echo BASE_URL . $client['logo_path']; ?>" 
                                     alt="<?php echo e($client['name']); ?>"
                                     class="h-12 object-contain">
                                <?php else: ?>
                                <div class="h-12 w-20 bg-gray-100 rounded flex items-center justify-center">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">
                                        <?php echo e($client['name']); ?>
                                    </div>
                                    <?php if ($client['name_en']): ?>
                                    <div class="text-gray-500 text-xs mt-1"><?php echo e($client['name_en']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($client['description']): ?>
                                    <div class="text-gray-600 text-xs mt-1">
                                        <?php echo e(mb_substr($client['description'], 0, 50)); ?>...
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($client['category_name'] ?? $client['category']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($client['website']): ?>
                                <a href="<?php echo e($client['website']); ?>" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <button type="submit" 
                                            name="toggle_active"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   <?php echo $client['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $client['is_active'] ? '활성' : '비활성'; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $client['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline" 
                                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                        <button type="submit" name="delete_client" 
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
        
        <?php if (!empty($clients)): ?>
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