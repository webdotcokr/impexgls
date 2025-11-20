<?php
// 인증 확인 파일 - 모든 관리자 페이지에서 include
session_start();

// 로그인 체크
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// 세션 유효성 검증
try {
    $pdo = getDBConnection();
    
    // 세션 정보 확인
    $stmt = $pdo->prepare("
        SELECT * FROM admin_sessions 
        WHERE admin_id = :admin_id 
        AND session_id = :session_id 
        AND expires_at > NOW()
        AND is_active = 1
    ");
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':session_id' => session_id()
    ]);
    
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        // 세션이 만료되었거나 유효하지 않음
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }
    
    // 관리자 정보 갱신
    $adminStmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id AND is_active = 1");
    $adminStmt->execute([':id' => $_SESSION['admin_id']]);
    $currentAdmin = $adminStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentAdmin) {
        // 관리자 계정이 비활성화됨
        session_destroy();
        header('Location: login.php?disabled=1');
        exit;
    }
    
    // 세션 갱신
    $_SESSION['admin_name'] = $currentAdmin['name'];
    $_SESSION['admin_role'] = $currentAdmin['role'];
    
} catch (PDOException $e) {
    // 데이터베이스 오류
    session_destroy();
    header('Location: login.php?error=1');
    exit;
}

// 권한 확인 함수
function checkPermission($requiredRole) {
    $roleHierarchy = [
        'viewer' => 1,
        'editor' => 2,
        'admin' => 3,
        'super_admin' => 4
    ];
    
    $userLevel = $roleHierarchy[$_SESSION['admin_role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
    
    return $userLevel >= $requiredLevel;
}

// 활동 로그 기록 함수
function logAdminAction($action, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, description, ip_address, created_at) 
            VALUES (:admin_id, :action, :description, :ip, NOW())
        ");
        $stmt->execute([
            ':admin_id' => $_SESSION['admin_id'],
            ':action' => $action,
            ':description' => $description,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    } catch (PDOException $e) {
        // 로그 기록 실패는 조용히 처리
    }
}
?>