<?php
/**
 * 관리자 로그인 페이지
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

// 이미 로그인되어 있으면 대시보드로 이동
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// 세션 만료 메시지
if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
    $error_message = '세션이 만료되었습니다. 다시 로그인해주세요.';
}

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // CSRF 토큰 검증
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = '잘못된 요청입니다. 페이지를 새로고침 후 다시 시도해주세요.';
    } else {
        $result = login($username, $password);
        
        if ($result['success']) {
            // 리다이렉트 URL이 있으면 해당 페이지로, 없으면 대시보드로
            $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : 'dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error_message = $result['message'];
        }
    }
}

// CSRF 토큰 생성
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 - IMPEX GLS</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .login-bg {
            background: linear-gradient(135deg, #1B2951 0%, #E31E24 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center login-bg">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <!-- 로고 -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">IMPEX GLS</h1>
            <p class="text-gray-600 mt-2">관리자 시스템</p>
        </div>
        
        <!-- 알림 메시지 -->
        <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo e($error_message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo e($success_message); ?>
        </div>
        <?php endif; ?>
        
        <!-- 로그인 폼 -->
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
            
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">
                    아이디
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required
                       autofocus>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                    비밀번호
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            
            <div class="mb-6">
                <button type="submit" 
                        name="login"
                        class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                    로그인
                </button>
            </div>
        </form>
        
        <!-- 추가 정보 -->
        <div class="text-center text-sm text-gray-600">
            <p>보안을 위해 로그인 후 1시간 동안 활동이 없으면 자동 로그아웃됩니다.</p>
        </div>
    </div>
    
    <!-- 하단 정보 -->
    <div class="absolute bottom-4 text-center text-white text-sm">
        <p>&copy; <?php echo date('Y'); ?> IMPEX GLS. All rights reserved.</p>
    </div>
</body>
</html>