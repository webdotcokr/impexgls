<?php
/**
 * 컨테이너 타입 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '컨테이너 타입 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// 컨테이너 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_container'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            
            // 이미지 파일 삭제
            $stmt = $pdo->prepare("SELECT image_path FROM container_types WHERE id = ?");
            $stmt->execute([$_POST['container_id']]);
            $image_path = $stmt->fetchColumn();
            
            if ($image_path && file_exists(PROJECT_ROOT . $image_path)) {
                unlink(PROJECT_ROOT . $image_path);
            }
            
            // DB에서 삭제
            $stmt = $pdo->prepare("DELETE FROM container_types WHERE id = ?");
            $stmt->execute([$_POST['container_id']]);
            
            logAdminAction('delete', 'container_types', $_POST['container_id'], 'Container type deleted');
            setAlert('success', '컨테이너 타입이 삭제되었습니다.');
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
            $stmt = $pdo->prepare("UPDATE container_types SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$_POST['container_id']]);
            
            logAdminAction('update', 'container_types', $_POST['container_id'], 'Container type active status toggled');
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
                $stmt = $pdo->prepare("UPDATE container_types SET sort_order = ? WHERE id = ?");
                $stmt->execute([intval($order), intval($id)]);
            }
            
            $pdo->commit();
            logAdminAction('update', 'container_types', null, 'Container types sort order updated');
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
        $where_conditions[] = "(name LIKE :search OR name_ko LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($category_filter) {
        $where_conditions[] = "category = :category";
        $params[':category'] = $category_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM container_types $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM container_types 
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
    
    $containers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리 목록
    $stmt = $pdo->query("SELECT DISTINCT category FROM container_types ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive,
            SUM(category = 'Dry') as dry,
            SUM(category = 'Reefer') as reefer,
            SUM(category = 'Special') as special
        FROM container_types
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Container error: " . $e->getMessage());
    $containers = [];
    $total_count = 0;
    $total_pages = 1;
    $categories = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'dry' => 0, 'reefer' => 0, 'special' => 0];
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 및 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">컨테이너 타입 관리</h1>
        </div>
        <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>새 컨테이너 타입
        </a>
    </div>
    
    <!-- 통계 카드 -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">전체</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-box text-blue-600"></i>
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
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Dry</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['dry']); ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-2">
                    <i class="fas fa-cube text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Reefer</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['reefer']); ?></p>
                </div>
                <div class="bg-cyan-100 rounded-full p-2">
                    <i class="fas fa-snowflake text-cyan-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Special</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['special']); ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-2">
                    <i class="fas fa-star text-purple-600"></i>
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
                       placeholder="컨테이너 타입명, 설명"
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

<!-- 컨테이너 목록 -->
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
                            이미지
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            컨테이너 정보
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            카테고리
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            사양
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
                    <?php if (empty($containers)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            컨테이너 타입이 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $current_category = '';
                        foreach ($containers as $container): 
                            // 카테고리별 구분선
                            if ($current_category !== $container['category']):
                                $current_category = $container['category'];
                        ?>
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-2 text-sm font-semibold text-gray-700">
                                <?php echo e($current_category); ?> Containers
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" 
                                       name="sort_order[<?php echo $container['id']; ?>]" 
                                       value="<?php echo $container['sort_order']; ?>"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded-md text-sm text-center">
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($container['image_path'] && file_exists(PROJECT_ROOT . $container['image_path'])): ?>
                                <img src="<?php echo BASE_URL . $container['image_path']; ?>" 
                                     alt="<?php echo e($container['name']); ?>"
                                     class="h-16 w-auto object-contain">
                                <?php else: ?>
                                <div class="h-16 w-20 bg-gray-100 rounded flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400 text-xl"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">
                                        <?php echo e($container['name']); ?>
                                        <?php if ($container['name_ko']): ?>
                                            <span class="text-gray-500">(<?php echo e($container['name_ko']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($container['description']): ?>
                                    <div class="text-gray-600 text-xs mt-1">
                                        <?php echo e(mb_substr($container['description'], 0, 50)); ?>...
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       <?php 
                                       echo $container['category'] == 'Dry' ? 'bg-yellow-100 text-yellow-800' : 
                                            ($container['category'] == 'Reefer' ? 'bg-cyan-100 text-cyan-800' : 'bg-purple-100 text-purple-800'); 
                                       ?>">
                                    <?php echo e($container['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="space-y-1">
                                    <?php if ($container['size_feet']): ?>
                                    <div class="text-gray-600">
                                        <i class="fas fa-ruler-horizontal text-gray-400 mr-1 text-xs"></i>
                                        <?php echo e($container['size_feet']); ?> feet
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($container['max_weight_kg']): ?>
                                    <div class="text-gray-600">
                                        <i class="fas fa-weight text-gray-400 mr-1 text-xs"></i>
                                        <?php echo number_format($container['max_weight_kg']); ?> kg
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($container['volume_cbm']): ?>
                                    <div class="text-gray-600">
                                        <i class="fas fa-cube text-gray-400 mr-1 text-xs"></i>
                                        <?php echo number_format($container['volume_cbm'], 1); ?> CBM
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                    <button type="submit" 
                                            name="toggle_active"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   <?php echo $container['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $container['is_active'] ? '활성' : '비활성'; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $container['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline" 
                                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <input type="hidden" name="container_id" value="<?php echo $container['id']; ?>">
                                        <button type="submit" name="delete_container" 
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
        
        <?php if (!empty($containers)): ?>
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