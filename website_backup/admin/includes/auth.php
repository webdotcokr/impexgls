<?php
/**
 * 관리자 인증 관련 함수들
 * AWS Lightsail Bitnami LAMP 환경 호환
 */

// 세션 설정 (Bitnami 환경 고려)
if (session_status() == PHP_SESSION_NONE) {
    // 세션 저장 경로 설정 (Bitnami에서 쓰기 권한 있는 경로)
    $session_path = sys_get_temp_dir() . '/impex_admin_sessions';
    if (!file_exists($session_path)) {
        mkdir($session_path, 0777, true);
    }
    session_save_path($session_path);
    
    // 세션 보안 설정
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 3600); // 1시간
    
    session_start();
}

// 프로젝트 루트 경로 설정 (상대 경로 사용으로 배포 환경 대응)
define('ADMIN_ROOT', dirname(dirname(__FILE__)));
define('PROJECT_ROOT', dirname(ADMIN_ROOT));

// 설정 파일 포함
require_once PROJECT_ROOT . '/config/config.php';
require_once PROJECT_ROOT . '/config/db-config.php';

/**
 * 로그인 상태 확인
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_username']) && 
           isset($_SESSION['admin_login_time']) &&
           isset($_SESSION['admin_ip']) &&
           $_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR'];
}

/**
 * 로그인 필수 페이지 체크
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $current_url = urlencode($_SERVER['REQUEST_URI']);
        header('Location: ' . getAdminUrl('/index.php?redirect=' . $current_url));
        exit;
    }
    
    // 세션 타임아웃 체크 (1시간)
    if (time() - $_SESSION['admin_login_time'] > 3600) {
        logout();
        header('Location: ' . getAdminUrl('/index.php?error=session_expired'));
        exit;
    }
    
    // 세션 시간 갱신
    $_SESSION['admin_login_time'] = time();
}

/**
 * 로그인 처리
 */
function login($username, $password) {
    try {
        $pdo = getDBConnection();
        
        // 로그인 시도 체크
        if (!checkLoginAttempts($_SERVER['REMOTE_ADDR'])) {
            return ['success' => false, 'message' => '너무 많은 로그인 시도입니다. 30분 후에 다시 시도해주세요.'];
        }
        
        // 관리자 정보 조회
        $stmt = $pdo->prepare("SELECT id, username, password, name, email, is_active FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            recordLoginAttempt($_SERVER['REMOTE_ADDR'], false);
            return ['success' => false, 'message' => '아이디 또는 비밀번호가 일치하지 않습니다.'];
        }
        
        // 계정 활성화 상태 확인
        if (!$admin['is_active']) {
            return ['success' => false, 'message' => '비활성화된 계정입니다.'];
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $admin['password'])) {
            recordLoginAttempt($_SERVER['REMOTE_ADDR'], false);
            return ['success' => false, 'message' => '아이디 또는 비밀번호가 일치하지 않습니다.'];
        }
        
        // 로그인 성공
        recordLoginAttempt($_SERVER['REMOTE_ADDR'], true);
        
        // 세션 재생성 (세션 고정 공격 방지)
        session_regenerate_id(true);
        
        // 세션 설정
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        
        // 마지막 로그인 시간 업데이트
        $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$admin['id']]);
        
        // 관리자 로그 기록
        logAdminAction('login', 'admins', $admin['id'], 'Admin login successful');
        
        return ['success' => true, 'message' => '로그인되었습니다.'];
        
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        return ['success' => false, 'message' => '로그인 처리 중 오류가 발생했습니다.'];
    }
}

/**
 * 로그아웃 처리
 */
function logout() {
    if (isset($_SESSION['admin_id'])) {
        // 로그아웃 로그 기록
        logAdminAction('logout', 'admins', $_SESSION['admin_id'], 'Admin logout');
    }
    
    // 세션 파괴
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
}

/**
 * 로그인 시도 제한 체크
 */
function checkLoginAttempts($ip) {
    $max_attempts = 5;
    $lockout_time = 1800; // 30분
    
    $file = sys_get_temp_dir() . '/login_attempts_' . md5($ip) . '.json';
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        
        // 잠금 시간이 지났으면 초기화
        if (time() - $data['last_attempt'] > $lockout_time) {
            unlink($file);
            return true;
        }
        
        // 최대 시도 횟수 초과
        if ($data['attempts'] >= $max_attempts) {
            return false;
        }
    }
    
    return true;
}

/**
 * 로그인 시도 기록
 */
function recordLoginAttempt($ip, $success) {
    $file = sys_get_temp_dir() . '/login_attempts_' . md5($ip) . '.json';
    
    if ($success) {
        // 성공 시 기록 삭제
        if (file_exists($file)) {
            unlink($file);
        }
        return;
    }
    
    // 실패 시 기록
    $data = ['attempts' => 1, 'last_attempt' => time()];
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $data['attempts']++;
        $data['last_attempt'] = time();
    }
    
    file_put_contents($file, json_encode($data));
}

/**
 * 관리자 활동 로그 기록
 */
function logAdminAction($action, $table_name = null, $record_id = null, $description = null) {
    try {
        $pdo = getDBConnection();
        
        $admin_id = $_SESSION['admin_id'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $admin_id,
            $action,
            $table_name,
            $record_id,
            $description,
            $ip_address,
            $user_agent
        ]);
        
    } catch (Exception $e) {
        error_log("Admin log error: " . $e->getMessage());
    }
}

/**
 * CSRF 토큰 생성
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF 토큰 검증
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 관리자 URL 생성 (배포 환경 고려)
 */
function getAdminUrl($path = '') {
    // 프로젝트 루트 경로 찾기
    $script_path = $_SERVER['SCRIPT_NAME'];
    $admin_pos = strpos($script_path, '/admin/');
    
    if ($admin_pos !== false) {
        // /admin/ 까지의 경로 추출
        $base = substr($script_path, 0, $admin_pos + 6); // '/admin' 포함
    } else {
        // 기본값
        $base = '/admin';
    }
    
    return $base . $path;
}

/**
 * 비밀번호 강도 체크
 */
function validatePassword($password) {
    $min_length = 8;
    
    if (strlen($password) < $min_length) {
        return ['valid' => false, 'message' => "비밀번호는 최소 {$min_length}자 이상이어야 합니다."];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => '비밀번호에 대문자가 포함되어야 합니다.'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => '비밀번호에 소문자가 포함되어야 합니다.'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => '비밀번호에 숫자가 포함되어야 합니다.'];
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        return ['valid' => false, 'message' => '비밀번호에 특수문자가 포함되어야 합니다.'];
    }
    
    return ['valid' => true];
}

/**
 * 관리자 권한 체크 (추후 확장 가능)
 */
function hasPermission($permission) {
    // 현재는 모든 관리자가 모든 권한을 가짐
    // 추후 권한 시스템 확장 시 수정
    return isLoggedIn();
}

/**
 * IP 주소 가져오기 (프록시 고려)
 */
function getClientIP() {
    $headers = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_CLIENT_IP',             // Proxy
        'HTTP_X_FORWARDED_FOR',       // Load balancer
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($headers as $header) {
        if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>