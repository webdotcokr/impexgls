<?php
/**
 * 관리자 대시보드
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = '대시보드';

// 통계 데이터 가져오기
try {
    $pdo = getDBConnection();
    
    // 문의 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as quoted,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
        FROM quote_requests
    ");
    $quote_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 뉴스 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
        FROM news_posts
    ");
    $news_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 인증서 통계
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates");
    $certificates_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 클라이언트 통계
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
    $clients_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 유용한 링크 통계 (퀵링크)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM useful_links");
    $quicklinks_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 네트워크 위치 통계
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM network_locations");
    $network_locations_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 최근 문의 목록
    $stmt = $pdo->query("
        SELECT id, company_name, contact_name, email, status, created_at
        FROM quote_requests
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recent_quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 최근 활동 로그
    $stmt = $pdo->query("
        SELECT al.*, a.name as admin_name
        FROM admin_logs al
        LEFT JOIN admins a ON al.admin_id = a.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 오늘 방문자 수 (간단한 통계 - 실제로는 별도의 방문자 추적 시스템 필요)
    $today_visitors = rand(100, 500); // 임시 데이터
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $quote_stats = ['total' => 0, 'pending' => 0, 'processing' => 0, 'quoted' => 0, 'closed' => 0];
    $news_stats = ['total' => 0, 'published' => 0, 'draft' => 0];
    $recent_quotes = [];
    $recent_logs = [];
    $today_visitors = 0;
}

include 'includes/header.php';
?>

<!-- 관리 메뉴 카드 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- 뉴스 관리 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">뉴스 관리</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($news_stats['published'] ?? 0); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-newspaper text-green-600 text-2xl"></i>
            </div>
        </div>
        <a href="<?php echo getAdminUrl('/content/news.php'); ?>" class="text-blue-600 text-sm hover:underline mt-2 inline-block">
            관리하기 →
        </a>
    </div>
    
    <!-- 인증서 관리 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">인증서 관리</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($certificates_stats['total'] ?? 0); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-certificate text-blue-600 text-2xl"></i>
            </div>
        </div>
        <a href="<?php echo getAdminUrl('/certificates/'); ?>" class="text-blue-600 text-sm hover:underline mt-2 inline-block">
            관리하기 →
        </a>
    </div>
    
    <!-- 클라이언트 관리 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">클라이언트 관리</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($clients_stats['total'] ?? 0); ?></p>
            </div>
            <div class="bg-orange-100 rounded-full p-3">
                <i class="fas fa-users text-orange-600 text-2xl"></i>
            </div>
        </div>
        <a href="<?php echo getAdminUrl('/clients/'); ?>" class="text-blue-600 text-sm hover:underline mt-2 inline-block">
            관리하기 →
        </a>
    </div>
    
    <!-- 퀵링크 관리 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">퀵링크 관리</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($quicklinks_stats['total'] ?? 0); ?></p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <i class="fas fa-link text-purple-600 text-2xl"></i>
            </div>
        </div>
        <a href="<?php echo getAdminUrl('/links/'); ?>" class="text-blue-600 text-sm hover:underline mt-2 inline-block">
            관리하기 →
        </a>
    </div>
    
    <!-- 네트워크 위치 관리 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">네트워크 위치 관리</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($network_locations_stats['total'] ?? 0); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-map-marker-alt text-red-600 text-2xl"></i>
            </div>
        </div>
        <a href="<?php echo getAdminUrl('/locations/'); ?>" class="text-blue-600 text-sm hover:underline mt-2 inline-block">
            관리하기 →
        </a>
    </div>
</div>


    
    <!-- 최근 활동 -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">최근 활동</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recent_logs)): ?>
                <p class="text-gray-500 text-center py-4">최근 활동이 없습니다.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_logs as $log): ?>
                    <div class="flex items-start space-x-3 text-sm">
                        <div class="flex-shrink-0 mt-1">
                            <?php
                            $action_icons = [
                                'login' => 'fa-sign-in-alt text-green-500',
                                'logout' => 'fa-sign-out-alt text-gray-500',
                                'create' => 'fa-plus text-blue-500',
                                'update' => 'fa-edit text-yellow-500',
                                'delete' => 'fa-trash text-red-500'
                            ];
                            $icon_class = $action_icons[$log['action']] ?? 'fa-circle text-gray-400';
                            ?>
                            <i class="fas <?php echo $icon_class; ?>"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-800">
                                <span class="font-medium"><?php echo e($log['admin_name'] ?? '시스템'); ?></span>
                                <?php echo e($log['description'] ?? $log['action']); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo formatDate($log['created_at'], 'Y-m-d H:i'); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>