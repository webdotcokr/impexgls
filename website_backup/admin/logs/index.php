<?php
/**
 * 관리자 활동 로그
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '관리자 활동 로그';

// 필터링 파라미터
$admin_filter = $_GET['admin'] ?? '';
$action_filter = $_GET['action'] ?? '';
$table_filter = $_GET['table'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 50;

// 데이터 조회
try {
    $pdo = getDBConnection();
    
    // 조건 구성
    $where_conditions = [];
    $params = [];
    
    if ($admin_filter) {
        $where_conditions[] = "l.admin_id = :admin_id";
        $params[':admin_id'] = $admin_filter;
    }
    
    if ($action_filter) {
        $where_conditions[] = "l.action = :action";
        $params[':action'] = $action_filter;
    }
    
    if ($table_filter) {
        $where_conditions[] = "l.table_name = :table_name";
        $params[':table_name'] = $table_filter;
    }
    
    if ($date_from) {
        $where_conditions[] = "DATE(l.created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if ($date_to) {
        $where_conditions[] = "DATE(l.created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    if ($search) {
        $where_conditions[] = "(l.details LIKE :search OR l.ip_address LIKE :search OR l.user_agent LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM admin_logs l 
        LEFT JOIN admins a ON l.admin_id = a.id 
        $where_clause
    ");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT l.*, a.username as admin_username 
        FROM admin_logs l 
        LEFT JOIN admins a ON l.admin_id = a.id 
        $where_clause 
        ORDER BY l.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 관리자 목록 (필터용)
    $stmt = $pdo->query("SELECT id, username FROM admins ORDER BY username");
    $admins = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // 액션 목록 (필터용)
    $stmt = $pdo->query("SELECT DISTINCT action FROM admin_logs ORDER BY action");
    $actions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 테이블 목록 (필터용)
    $stmt = $pdo->query("SELECT DISTINCT table_name FROM admin_logs WHERE table_name IS NOT NULL ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(DISTINCT admin_id) as admins,
            COUNT(DISTINCT DATE(created_at)) as days,
            SUM(action = 'login') as logins,
            SUM(action = 'create') as creates,
            SUM(action = 'update') as updates,
            SUM(action = 'delete') as deletes
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Logs error: " . $e->getMessage());
    $logs = [];
    $total_count = 0;
    $total_pages = 1;
    $admins = [];
    $actions = [];
    $tables = [];
    $stats = ['total' => 0, 'admins' => 0, 'days' => 0, 'logins' => 0, 'creates' => 0, 'updates' => 0, 'deletes' => 0];
}

// 액션 라벨
$action_labels = [
    'login' => '로그인',
    'logout' => '로그아웃',
    'create' => '생성',
    'update' => '수정',
    'delete' => '삭제',
    'view' => '조회'
];

// 테이블 라벨
$table_labels = [
    'quote_requests' => '문의하기',
    'news_posts' => '뉴스',
    'faqs' => 'FAQ',
    'certificates' => '인증서',
    'clients' => '클라이언트',
    'useful_links' => '유용한 링크',
    'network_locations' => '네트워크 위치',
    'container_types' => '컨테이너 타입',
    'site_settings' => '사이트 설정',
    'admins' => '관리자'
];

// 액션 아이콘
$action_icons = [
    'login' => 'fas fa-sign-in-alt text-green-600',
    'logout' => 'fas fa-sign-out-alt text-gray-600',
    'create' => 'fas fa-plus-circle text-blue-600',
    'update' => 'fas fa-edit text-yellow-600',
    'delete' => 'fas fa-trash text-red-600',
    'view' => 'fas fa-eye text-gray-600'
];

include '../includes/header.php';
?>

<!-- 상단 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">관리자 활동 로그</h1>
    </div>
    
    <!-- 통계 카드 (최근 30일) -->
    <div class="grid grid-cols-2 md:grid-cols-7 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">전체 활동</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-history text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">활동 관리자</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['admins']); ?></p>
                </div>
                <div class="bg-purple-100 rounded-full p-2">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">활동 일수</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['days']); ?></p>
                </div>
                <div class="bg-gray-100 rounded-full p-2">
                    <i class="fas fa-calendar text-gray-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">로그인</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['logins'] ?? 0); ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-2">
                    <i class="fas fa-sign-in-alt text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">생성</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['creates'] ?? 0); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-plus-circle text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">수정</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['updates'] ?? 0); ?></p>
                </div>
                <div class="bg-yellow-100 rounded-full p-2">
                    <i class="fas fa-edit text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">삭제</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['deletes'] ?? 0); ?></p>
                </div>
                <div class="bg-red-100 rounded-full p-2">
                    <i class="fas fa-trash text-red-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 검색 및 필터 -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4">
        <form method="GET" action="" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- 관리자 필터 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">관리자</label>
                    <select name="admin" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <?php foreach ($admins as $id => $username): ?>
                        <option value="<?php echo $id; ?>" <?php echo $admin_filter == $id ? 'selected' : ''; ?>>
                            <?php echo e($username); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 액션 필터 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">액션</label>
                    <select name="action" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <?php foreach ($actions as $action): ?>
                        <option value="<?php echo e($action); ?>" <?php echo $action_filter == $action ? 'selected' : ''; ?>>
                            <?php echo e($action_labels[$action] ?? $action); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 테이블 필터 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">대상</label>
                    <select name="table" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">전체</option>
                        <?php foreach ($tables as $table): ?>
                        <option value="<?php echo e($table); ?>" <?php echo $table_filter == $table ? 'selected' : ''; ?>>
                            <?php echo e($table_labels[$table] ?? $table); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 검색어 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                    <input type="text" 
                           name="search" 
                           value="<?php echo e($search); ?>"
                           placeholder="상세 내용, IP, User Agent"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- 날짜 범위 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">시작일</label>
                    <input type="date" 
                           name="date_from" 
                           value="<?php echo e($date_from); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                    <input type="date" 
                           name="date_to" 
                           value="<?php echo e($date_to); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            </div>
        </form>
    </div>
</div>

<!-- 로그 목록 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        일시
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        관리자
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        액션
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        대상
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        상세 내용
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        IP 주소
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        활동 로그가 없습니다.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <div class="font-medium"><?php echo date('Y-m-d', strtotime($log['created_at'])); ?></div>
                                <div class="text-gray-500"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="font-medium text-gray-900">
                                <?php echo e($log['admin_username'] ?? 'Unknown'); ?>
                            </div>
                            <div class="text-gray-500 text-xs">
                                ID: <?php echo $log['admin_id']; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center">
                                <i class="<?php echo $action_icons[$log['action']] ?? 'fas fa-circle text-gray-400'; ?> mr-2"></i>
                                <?php echo e($action_labels[$log['action']] ?? $log['action']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($log['table_name']): ?>
                                <div>
                                    <div class="font-medium">
                                        <?php echo e($table_labels[$log['table_name']] ?? $log['table_name']); ?>
                                    </div>
                                    <?php if ($log['record_id']): ?>
                                    <div class="text-gray-500 text-xs">
                                        ID: <?php echo $log['record_id']; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="<?php echo e($log['details']); ?>">
                                <?php echo e($log['details']); ?>
                            </div>
                            <?php if ($log['user_agent']): ?>
                            <div class="text-gray-500 text-xs mt-1 truncate" title="<?php echo e($log['user_agent']); ?>">
                                <?php 
                                // 간단한 User Agent 파싱
                                $ua = $log['user_agent'];
                                if (strpos($ua, 'Chrome') !== false) echo 'Chrome';
                                elseif (strpos($ua, 'Safari') !== false) echo 'Safari';
                                elseif (strpos($ua, 'Firefox') !== false) echo 'Firefox';
                                elseif (strpos($ua, 'Edge') !== false) echo 'Edge';
                                else echo 'Other';
                                
                                if (strpos($ua, 'Windows') !== false) echo ' / Windows';
                                elseif (strpos($ua, 'Mac OS') !== false) echo ' / macOS';
                                elseif (strpos($ua, 'Linux') !== false) echo ' / Linux';
                                elseif (strpos($ua, 'iPhone') !== false) echo ' / iPhone';
                                elseif (strpos($ua, 'Android') !== false) echo ' / Android';
                                ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($log['ip_address']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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