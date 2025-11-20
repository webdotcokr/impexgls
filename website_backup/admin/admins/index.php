<?php
/**
 * 관리자 계정 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '관리자 계정 관리';

// 관리자 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        // 현재 로그인한 관리자는 삭제할 수 없음
        if ($_POST['admin_id'] == $_SESSION['admin_id']) {
            setAlert('error', '현재 로그인한 계정은 삭제할 수 없습니다.');
        } else {
            try {
                $pdo = getDBConnection();
                
                // 최소 1명의 활성 관리자는 있어야 함
                $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1");
                $active_count = $stmt->fetchColumn();
                
                if ($active_count <= 1) {
                    throw new Exception('최소 1명의 활성 관리자가 필요합니다.');
                }
                
                // 삭제 대신 비활성화
                $stmt = $pdo->prepare("UPDATE admins SET is_active = 0 WHERE id = ?");
                $stmt->execute([$_POST['admin_id']]);
                
                logAdminAction('delete', 'admins', $_POST['admin_id'], 'Admin account deactivated');
                setAlert('success', '관리자 계정이 비활성화되었습니다.');
            } catch (Exception $e) {
                setAlert('error', $e->getMessage());
            }
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 활성화 상태 변경
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            
            // 현재 상태 확인
            $stmt = $pdo->prepare("SELECT is_active FROM admins WHERE id = ?");
            $stmt->execute([$_POST['admin_id']]);
            $current_status = $stmt->fetchColumn();
            
            // 비활성화하려는 경우, 최소 1명의 활성 관리자 확인
            if ($current_status == 1) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1");
                $active_count = $stmt->fetchColumn();
                
                if ($active_count <= 1) {
                    throw new Exception('최소 1명의 활성 관리자가 필요합니다.');
                }
            }
            
            $stmt = $pdo->prepare("UPDATE admins SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$_POST['admin_id']]);
            
            logAdminAction('update', 'admins', $_POST['admin_id'], 'Admin active status toggled');
            setAlert('success', '상태가 변경되었습니다.');
        } catch (Exception $e) {
            setAlert('error', $e->getMessage());
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    
    // 관리자 목록
    $stmt = $pdo->query("
        SELECT a.*, 
               (SELECT COUNT(*) FROM admin_logs WHERE admin_id = a.id) as log_count,
               (SELECT MAX(created_at) FROM admin_logs WHERE admin_id = a.id AND action = 'login') as last_login
        FROM admins a 
        ORDER BY a.created_at DESC
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 통계
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive
        FROM admins
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 최근 활동 통계 (30일)
    $stmt = $pdo->query("
        SELECT 
            admin_id,
            COUNT(*) as activity_count,
            COUNT(DISTINCT DATE(created_at)) as active_days
        FROM admin_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY admin_id
    ");
    $recent_activities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_activities[$row['admin_id']] = $row;
    }
    
} catch (Exception $e) {
    error_log("Admin management error: " . $e->getMessage());
    $admins = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
    $recent_activities = [];
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<!-- 상단 버튼 및 통계 -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">관리자 계정 관리</h1>
        <a href="create.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-2"></i>새 관리자
        </a>
    </div>
    
    <!-- 통계 카드 -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">전체 관리자</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="bg-blue-100 rounded-full p-2">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">활성 관리자</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active']); ?></p>
                </div>
                <div class="bg-green-100 rounded-full p-2">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">비활성 관리자</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['inactive']); ?></p>
                </div>
                <div class="bg-gray-100 rounded-full p-2">
                    <i class="fas fa-user-times text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 관리자 목록 -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        관리자
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        연락처
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        마지막 로그인
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        최근 활동
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
                <?php if (empty($admins)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        관리자가 없습니다.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($admins as $admin): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo e($admin['full_name']); ?>
                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                현재 사용자
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @<?php echo e($admin['username']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-gray-900"><?php echo e($admin['email']); ?></div>
                            <?php if ($admin['phone']): ?>
                            <div class="text-gray-500"><?php echo e($admin['phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php if ($admin['last_login']): ?>
                                <div class="text-gray-900">
                                    <?php echo formatDate($admin['last_login']); ?>
                                </div>
                                <div class="text-gray-500 text-xs">
                                    <?php 
                                    $last_login_time = strtotime($admin['last_login']);
                                    $diff = time() - $last_login_time;
                                    
                                    if ($diff < 3600) {
                                        echo floor($diff / 60) . '분 전';
                                    } elseif ($diff < 86400) {
                                        echo floor($diff / 3600) . '시간 전';
                                    } elseif ($diff < 604800) {
                                        echo floor($diff / 86400) . '일 전';
                                    } else {
                                        echo floor($diff / 604800) . '주 전';
                                    }
                                    ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">로그인 기록 없음</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php 
                            $activity = $recent_activities[$admin['id']] ?? null;
                            if ($activity): 
                            ?>
                                <div class="text-gray-900">
                                    <?php echo number_format($activity['activity_count']); ?>회 활동
                                </div>
                                <div class="text-gray-500 text-xs">
                                    최근 30일간 <?php echo $activity['active_days']; ?>일 활동
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">최근 활동 없음</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                <button type="submit" 
                                        name="toggle_active"
                                        <?php echo ($admin['id'] == $_SESSION['admin_id']) ? 'disabled' : ''; ?>
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                               <?php echo $admin['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>
                                               <?php echo ($admin['id'] == $_SESSION['admin_id']) ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                    <?php echo $admin['is_active'] ? '활성' : '비활성'; ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-2">
                                <a href="edit.php?id=<?php echo $admin['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/admin/logs/?admin=<?php echo $admin['id']; ?>" 
                                   class="text-gray-600 hover:text-gray-900"
                                   title="활동 로그 보기">
                                    <i class="fas fa-history"></i>
                                </a>
                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                <form method="POST" action="" class="inline" 
                                      onsubmit="return confirm('정말 이 관리자 계정을 비활성화하시겠습니까?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" name="delete_admin" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 보안 안내 -->
<div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">보안 안내</h3>
            <div class="mt-2 text-sm text-yellow-700">
                <ul class="list-disc list-inside space-y-1">
                    <li>관리자 계정은 최소한으로 유지하세요</li>
                    <li>강력한 비밀번호를 사용하고 정기적으로 변경하세요</li>
                    <li>사용하지 않는 계정은 비활성화하세요</li>
                    <li>모든 관리자 활동은 로그에 기록됩니다</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>