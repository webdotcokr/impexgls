<?php
/**
 * 문의하기 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '문의하기 관리';

// 검색 및 필터링
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;

// 상태 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE quote_requests SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['quote_id']]);
            
            logAdminAction('update', 'quote_requests', $_POST['quote_id'], 'Quote status updated to ' . $_POST['status']);
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
        $where_conditions[] = "(company_name LIKE :search OR contact_name LIKE :search OR email LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($status_filter) {
        $where_conditions[] = "status = :status";
        $params[':status'] = $status_filter;
    }
    
    if ($date_from) {
        $where_conditions[] = "created_at >= :date_from";
        $params[':date_from'] = $date_from . ' 00:00:00';
    }
    
    if ($date_to) {
        $where_conditions[] = "created_at <= :date_to";
        $params[':date_to'] = $date_to . ' 23:59:59';
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // 전체 개수
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quote_requests $where_clause");
    $stmt->execute($params);
    $total_count = $stmt->fetchColumn();
    
    // 페이지 계산
    $total_pages = ceil($total_count / $per_page);
    $offset = ($page - 1) * $per_page;
    
    // 데이터 조회
    $stmt = $pdo->prepare("
        SELECT * FROM quote_requests 
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
    
    $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 상태별 통계
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM quote_requests 
        GROUP BY status
    ");
    $status_stats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['status']] = $row['count'];
    }
    
} catch (Exception $e) {
    error_log("Quote requests error: " . $e->getMessage());
    $quotes = [];
    $total_count = 0;
    $total_pages = 1;
    $status_stats = [];
}

// 상태 옵션
$status_options = [
    'pending' => ['label' => '대기', 'color' => 'yellow'],
    'processing' => ['label' => '처리중', 'color' => 'blue'],
    'quoted' => ['label' => '견적완료', 'color' => 'green'],
    'closed' => ['label' => '종료', 'color' => 'gray']
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 통계 카드 -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php foreach ($status_options as $status => $option): ?>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm"><?php echo $option['label']; ?></p>
                <p class="text-2xl font-bold text-gray-800">
                    <?php echo number_format($status_stats[$status] ?? 0); ?>
                </p>
            </div>
            <div class="bg-<?php echo $option['color']; ?>-100 rounded-full p-2">
                <i class="fas fa-<?php echo $status == 'pending' ? 'clock' : ($status == 'processing' ? 'spinner' : ($status == 'quoted' ? 'check' : 'times')); ?> 
                   text-<?php echo $option['color']; ?>-600"></i>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
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
                       placeholder="회사명, 담당자, 이메일"
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
            <div class="md:col-span-4 flex justify-end space-x-2">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition duration-200">
                    초기화
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-search mr-2"></i>검색
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 문의 목록 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        접수일시
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        회사/담당자
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        문의 유형
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        경로
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
                <?php if (empty($quotes)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        문의 내역이 없습니다.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($quotes as $quote): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo formatDate($quote['created_at'], 'Y-m-d'); ?><br>
                            <span class="text-gray-500"><?php echo formatDate($quote['created_at'], 'H:i'); ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-900">
                                <?php echo e($quote['company_name'] ?: '-'); ?>
                            </div>
                            <div class="text-gray-500">
                                <?php echo e($quote['contact_name'] ?? $quote['full_name'] ?? '-'); ?>
                            </div>
                            <div class="text-gray-500 text-xs">
                                <?php echo e($quote['email'] ?? $quote['email_address'] ?? '-'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo e($quote['request_type'] ?: '일반문의'); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php if ($quote['departure_city'] && $quote['destination_city']): ?>
                                <div class="text-xs">
                                    <span class="text-gray-500">출발:</span> <?php echo e($quote['departure_city']); ?><br>
                                    <span class="text-gray-500">도착:</span> <?php echo e($quote['destination_city']); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                <select name="status" 
                                        onchange="if(confirm('상태를 변경하시겠습니까?')) this.form.submit();"
                                        class="text-xs rounded-full px-3 py-1 font-medium
                                               bg-<?php echo $status_options[$quote['status']]['color']; ?>-100 
                                               text-<?php echo $status_options[$quote['status']]['color']; ?>-800">
                                    <?php foreach ($status_options as $value => $option): ?>
                                    <option value="<?php echo $value; ?>" 
                                            <?php echo $quote['status'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $option['label']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="hidden"></button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="view.php?id=<?php echo $quote['id']; ?>" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye mr-1"></i>상세
                            </a>
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
                총 <span class="font-medium"><?php echo number_format($total_count); ?></span>건
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