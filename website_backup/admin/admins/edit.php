<?php
/**
 * 관리자 계정 수정
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '관리자 계정 수정';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 관리자 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        setAlert('error', '관리자를 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Admin error: " . $e->getMessage());
    setAlert('error', '데이터 조회에 실패했습니다.');
    header('Location: index.php');
    exit;
}

// 현재 로그인한 관리자 여부
$is_current_admin = ($id == $_SESSION['admin_id']);

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
        exit;
    }
    
    try {
        // 데이터 준비
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 비밀번호 변경 처리
        $change_password = !empty($_POST['new_password']);
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        
        // 유효성 검사
        if (empty($email)) {
            throw new Exception('이메일을 입력해주세요.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('올바른 이메일 형식이 아닙니다.');
        }
        if (empty($full_name)) {
            throw new Exception('전체 이름을 입력해주세요.');
        }
        
        // 이메일 중복 검사 (자신 제외)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('이미 사용 중인 이메일입니다.');
        }
        
        // 비밀번호 변경 시 검증
        if ($change_password) {
            // 현재 로그인한 관리자가 자신의 비밀번호를 변경하는 경우 현재 비밀번호 확인
            if ($is_current_admin) {
                if (empty($current_password)) {
                    throw new Exception('현재 비밀번호를 입력해주세요.');
                }
                if (!password_verify($current_password, $admin['password_hash'])) {
                    throw new Exception('현재 비밀번호가 올바르지 않습니다.');
                }
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception('새 비밀번호는 최소 8자 이상이어야 합니다.');
            }
            if ($new_password !== $new_password_confirm) {
                throw new Exception('새 비밀번호가 일치하지 않습니다.');
            }
        }
        
        // 활성화 상태 변경 시 검증
        if (!$is_active && $is_current_admin) {
            throw new Exception('현재 로그인한 계정은 비활성화할 수 없습니다.');
        }
        
        // 최소 1명의 활성 관리자 확인
        if (!$is_active && $admin['is_active']) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1");
            $active_count = $stmt->fetchColumn();
            if ($active_count <= 1) {
                throw new Exception('최소 1명의 활성 관리자가 필요합니다.');
            }
        }
        
        // 업데이트 쿼리 구성
        $update_fields = [
            'email = :email',
            'full_name = :full_name',
            'phone = :phone',
            'is_active = :is_active'
        ];
        $params = [
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone ?: null,
            ':is_active' => $is_active,
            ':id' => $id
        ];
        
        // 비밀번호 변경 시 추가
        if ($change_password) {
            $update_fields[] = 'password_hash = :password_hash';
            $params[':password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $sql = "UPDATE admins SET " . implode(', ', $update_fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        logAdminAction('update', 'admins', $id, 'Admin account updated: ' . $admin['username']);
        
        setAlert('success', '관리자 계정이 수정되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 최근 활동 통계
try {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_actions,
            COUNT(DISTINCT DATE(created_at)) as active_days,
            MAX(created_at) as last_activity
        FROM admin_logs 
        WHERE admin_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$id]);
    $activity_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $activity_stats = ['total_actions' => 0, 'active_days' => 0, 'last_activity' => null];
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-2xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">관리자 계정 수정</h1>
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>목록으로
        </a>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
        
        <!-- 계정 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">계정 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 사용자명 (읽기 전용) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        사용자명
                    </label>
                    <input type="text" 
                           value="<?php echo e($admin['username']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                           readonly disabled>
                    <p class="mt-1 text-sm text-gray-500">사용자명은 변경할 수 없습니다</p>
                </div>
                
                <!-- 계정 상태 및 활동 정보 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">등록일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($admin['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">현재 상태</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php echo $admin['is_active'] ? '<span class="text-green-600">활성</span>' : '<span class="text-gray-600">비활성</span>'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">최근 30일 활동</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php echo number_format($activity_stats['total_actions']); ?>회 
                                (<?php echo $activity_stats['active_days']; ?>일)
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">마지막 활동</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php echo $activity_stats['last_activity'] ? formatDate($activity_stats['last_activity']) : '활동 없음'; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- 비밀번호 변경 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">비밀번호 변경</h3>
            </div>
            <div class="p-6 space-y-6">
                <?php if ($is_current_admin): ?>
                <!-- 현재 비밀번호 (본인만) -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        현재 비밀번호
                    </label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           placeholder="비밀번호 변경 시 입력"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">비밀번호를 변경하려면 현재 비밀번호를 입력하세요</p>
                </div>
                <?php endif; ?>
                
                <!-- 새 비밀번호 -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        새 비밀번호
                    </label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           placeholder="변경하지 않으려면 비워두세요"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">최소 8자 이상, 영문/숫자/특수문자 조합 권장</p>
                </div>
                
                <!-- 새 비밀번호 확인 -->
                <div>
                    <label for="new_password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        새 비밀번호 확인
                    </label>
                    <input type="password" 
                           id="new_password_confirm" 
                           name="new_password_confirm" 
                           placeholder="새 비밀번호를 다시 입력"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- 개인 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">개인 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 전체 이름 -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                        전체 이름 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           value="<?php echo e($admin['full_name']); ?>"
                           placeholder="홍길동"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 이메일 -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        이메일 <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo e($admin['email']); ?>"
                           placeholder="admin@example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500">비밀번호 재설정 등에 사용됩니다</p>
                </div>
                
                <!-- 전화번호 -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        전화번호
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo e($admin['phone']); ?>"
                           placeholder="010-1234-5678"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- 계정 설정 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">계정 설정</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo $admin['is_active'] ? 'checked' : ''; ?>
                           <?php echo $is_current_admin ? 'disabled' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        계정 활성화
                        <?php if ($is_current_admin): ?>
                            <span class="text-gray-500">(현재 로그인한 계정은 비활성화할 수 없습니다)</span>
                        <?php endif; ?>
                    </label>
                </div>
                <?php if ($is_current_admin): ?>
                    <input type="hidden" name="is_active" value="1">
                <?php endif; ?>
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

<script>
// 비밀번호 확인 일치 체크
function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirm').value;
    const confirmField = document.getElementById('new_password_confirm');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmField.classList.add('border-red-500');
        confirmField.classList.remove('border-gray-300');
    } else {
        confirmField.classList.remove('border-red-500');
        confirmField.classList.add('border-gray-300');
    }
}

document.getElementById('new_password').addEventListener('input', function() {
    const confirmField = document.getElementById('new_password_confirm');
    if (confirmField.value) {
        checkPasswordMatch();
    }
});

document.getElementById('new_password_confirm').addEventListener('input', checkPasswordMatch);
</script>

<?php include '../includes/footer.php'; ?>