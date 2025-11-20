<?php
/**
 * 네트워크 위치 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '네트워크 위치 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$country_filter = $_GET['country'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// 위치 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_location'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM network_locations WHERE id = ?");
            $stmt->execute([$_POST['location_id']]);
            
            logAdminAction('delete', 'network_locations', $_POST['location_id'], 'Location deleted');
            setAlert('success', '위치가 삭제되었습니다.');
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
            $stmt = $pdo->prepare("UPDATE network_locations SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$_POST['location_id']]);
            
            logAdminAction('update', 'network_locations', $_POST['location_id'], 'Location active status toggled');
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
                $stmt = $pdo->prepare("UPDATE network_locations SET sort_order = ? WHERE id = ?");
                $stmt->execute([intval($order), intval($id)]);
            }
            
            $pdo->commit();
            logAdminAction('update', 'network_locations', null, 'Location sort order updated');
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
        $where_conditions[] = "(office_name LIKE :search OR city LIKE :search OR address LIKE :search OR phone LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($type_filter) {
        $where_conditions[] = "location_type = :type";
        $params[':type'] = $type_filter;
    }
    
    if ($country_filter) {
        $where_conditions[] = "country_code = :country";
        $params[':country'] = $country_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM network_locations $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM network_locations 
        $where_clause 
        ORDER BY location_type, country_name, sort_order, id DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 국가 목록
    $stmt = $pdo->query("SELECT DISTINCT country_code, country_name FROM network_locations ORDER BY country_name");
    $countries = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive,
            SUM(location_type = 'headquarters') as headquarters,
            SUM(location_type = 'usa') as usa,
            SUM(location_type = 'global') as global
        FROM network_locations
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Location error: " . $e->getMessage());
    $locations = [];
    $total_count = 0;
    $total_pages = 1;
    $countries = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'headquarters' => 0, 'usa' => 0, 'global' => 0];
}

// 위치 타입 정의
$location_types = [
    'headquarters' => '본사',
    'usa' => '미국 지사',
    'global' => '글로벌 네트워크'
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 및 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">네트워크 위치 관리</h1>
        </div>
        <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>새 위치
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
                    <i class="fas fa-globe text-blue-600"></i>
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
                    <p class="text-gray-500 text-sm">본사</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['headquarters']); ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-2">
                    <i class="fas fa-building text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">미국</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['usa']); ?></p>
                </div>
                <div class="bg-red-100 rounded-full p-2">
                    <i class="fas fa-flag-usa text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">글로벌</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['global']); ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-2">
                    <i class="fas fa-globe-asia text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>
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
                       placeholder="사무소명, 도시, 주소, 전화번호"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 타입 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">타입</label>
                <select name="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($location_types as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $type_filter == $value ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- 국가 필터 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">국가</label>
                <select name="country" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <?php foreach ($countries as $code => $name): ?>
                    <option value="<?php echo e($code); ?>" <?php echo $country_filter == $code ? 'selected' : ''; ?>>
                        <?php echo e($name); ?>
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

<!-- 위치 목록 -->
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
                            타입
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            위치 정보
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            연락처
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            좌표
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
                    <?php if (empty($locations)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            위치가 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $current_type = '';
                        foreach ($locations as $location): 
                            // 타입별 구분선
                            if ($current_type !== $location['location_type']):
                                $current_type = $location['location_type'];
                        ?>
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-2 text-sm font-semibold text-gray-700">
                                <?php echo e($location_types[$current_type] ?? $current_type); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" 
                                       name="sort_order[<?php echo $location['id']; ?>]" 
                                       value="<?php echo $location['sort_order']; ?>"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded-md text-sm text-center">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       <?php 
                                       echo $location['location_type'] == 'headquarters' ? 'bg-purple-100 text-purple-800' : 
                                            ($location['location_type'] == 'usa' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                       ?>">
                                    <?php echo e($location_types[$location['location_type']] ?? $location['location_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">
                                        <?php echo e($location['office_name']); ?>
                                    </div>
                                    <div class="text-gray-500">
                                        <?php echo e($location['city']); ?>, <?php echo e($location['country_name']); ?>
                                        <?php if ($location['country_code']): ?>
                                            <span class="text-xs">(<?php echo e($location['country_code']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($location['address']): ?>
                                    <div class="text-gray-600 text-xs mt-1">
                                        <?php echo e(mb_substr($location['address'], 0, 50)); ?>...
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($location['phone']): ?>
                                <div class="text-gray-900">
                                    <i class="fas fa-phone text-gray-400 mr-1"></i>
                                    <?php echo e($location['phone']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($location['fax']): ?>
                                <div class="text-gray-600 text-xs">
                                    <i class="fas fa-fax text-gray-400 mr-1"></i>
                                    <?php echo e($location['fax']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($location['email']): ?>
                                <div class="text-blue-600 text-xs">
                                    <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                    <?php echo e($location['email']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($location['latitude'] && $location['longitude']): ?>
                                <a href="https://maps.google.com/?q=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?php echo number_format($location['latitude'], 4); ?>,
                                    <?php echo number_format($location['longitude'], 4); ?>
                                </a>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                                    <button type="submit" 
                                            name="toggle_active"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                   <?php echo $location['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $location['is_active'] ? '활성' : '비활성'; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?php echo $location['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="" class="inline" 
                                          onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                        <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                                        <button type="submit" name="delete_location" 
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
        
        <?php if (!empty($locations)): ?>
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