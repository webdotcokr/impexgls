<?php
/**
 * 새 관리자 등록
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '새 관리자 등록';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        
        // 데이터 준비
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($username)) {
            throw new Exception('사용자명을 입력해주세요.');
        }
        if (strlen($username) < 3) {
            throw new Exception('사용자명은 최소 3자 이상이어야 합니다.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new Exception('사용자명은 영문, 숫자, 언더스코어(_)만 사용할 수 있습니다.');
        }
        if (empty($password)) {
            throw new Exception('비밀번호를 입력해주세요.');
        }
        if (strlen($password) < 8) {
            throw new Exception('비밀번호는 최소 8자 이상이어야 합니다.');
        }
        if ($password !== $password_confirm) {
            throw new Exception('비밀번호가 일치하지 않습니다.');
        }
        if (empty($email)) {
            throw new Exception('이메일을 입력해주세요.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('올바른 이메일 형식이 아닙니다.');
        }
        if (empty($full_name)) {
            throw new Exception('전체 이름을 입력해주세요.');
        }
        
        // 중복 검사
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('이미 사용 중인 사용자명입니다.');
        }
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('이미 사용 중인 이메일입니다.');
        }
        
        // 비밀번호 해시
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // 삽입
        $stmt = $pdo->prepare("
            INSERT INTO admins (
                username, password_hash, email, full_name, 
                phone, is_active, created_at
            ) VALUES (
                :username, :password_hash, :email, :full_name,
                :phone, :is_active, NOW()
            )
        ");
        
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => $password_hash,
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone ?: null,
            ':is_active' => $is_active
        ]);
        
        $admin_id = $pdo->lastInsertId();
        logAdminAction('create', 'admins', $admin_id, 'Admin account created: ' . $username);
        
        setAlert('success', '관리자 계정이 생성되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-2xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">새 관리자 등록</h1>
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
                <!-- 사용자명 -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        사용자명 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo e($_POST['username'] ?? ''); ?>"
                           placeholder="admin_user"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500">영문, 숫자, 언더스코어(_)만 사용 가능 (최소 3자)</p>
                </div>
                
                <!-- 비밀번호 -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        비밀번호 <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="••••••••"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500">최소 8자 이상, 영문/숫자/특수문자 조합 권장</p>
                </div>
                
                <!-- 비밀번호 확인 -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        비밀번호 확인 <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirm" 
                           name="password_confirm" 
                           placeholder="••••••••"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 비밀번호 강도 표시 -->
                <div id="password-strength" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">비밀번호 강도</p>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="strength-bar" class="h-2 rounded-full transition-all duration-300"></div>
                    </div>
                    <p id="strength-text" class="mt-1 text-sm"></p>
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
                           value="<?php echo e($_POST['full_name'] ?? ''); ?>"
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
                           value="<?php echo e($_POST['email'] ?? ''); ?>"
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
                           value="<?php echo e($_POST['phone'] ?? ''); ?>"
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
                           <?php echo ($_POST['is_active'] ?? '1') ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        즉시 활성화 (체크하면 바로 로그인 가능)
                    </label>
                </div>
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
// 비밀번호 강도 체크
function checkPasswordStrength(password) {
    let strength = 0;
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    const strengthContainer = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthContainer.classList.add('hidden');
        return;
    }
    
    strengthContainer.classList.remove('hidden');
    
    // 길이 체크
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // 문자 종류 체크
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
    
    // 강도에 따른 표시
    const percentage = (strength / 6) * 100;
    strengthBar.style.width = percentage + '%';
    
    if (strength <= 2) {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-red-500';
        strengthText.textContent = '약함';
        strengthText.className = 'mt-1 text-sm text-red-600';
    } else if (strength <= 4) {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-yellow-500';
        strengthText.textContent = '보통';
        strengthText.className = 'mt-1 text-sm text-yellow-600';
    } else {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-green-500';
        strengthText.textContent = '강함';
        strengthText.className = 'mt-1 text-sm text-green-600';
    }
}

// 비밀번호 입력 이벤트
document.getElementById('password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    
    // 비밀번호 확인 필드와 일치 여부 체크
    const confirmField = document.getElementById('password_confirm');
    if (confirmField.value) {
        checkPasswordMatch();
    }
});

// 비밀번호 확인 일치 체크
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirm').value;
    const confirmField = document.getElementById('password_confirm');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmField.classList.add('border-red-500');
        confirmField.classList.remove('border-gray-300');
    } else {
        confirmField.classList.remove('border-red-500');
        confirmField.classList.add('border-gray-300');
    }
}

document.getElementById('password_confirm').addEventListener('input', checkPasswordMatch);

// 사용자명 검증
document.getElementById('username').addEventListener('input', function() {
    const value = this.value;
    const isValid = /^[a-zA-Z0-9_]*$/.test(value);
    
    if (!isValid) {
        this.classList.add('border-red-500');
        this.classList.remove('border-gray-300');
    } else {
        this.classList.remove('border-red-500');
        this.classList.add('border-gray-300');
    }
});
</script>

<?php include '../includes/footer.php'; ?>