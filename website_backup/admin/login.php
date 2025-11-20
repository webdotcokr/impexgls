<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// 이미 로그인된 경우 대시보드로 리다이렉트
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // 관리자 정보 조회
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username AND is_active = 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // 로그인 성공
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // 마지막 로그인 시간 업데이트
                $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $admin['id']]);
                
                // 로그인 로그 기록
                $logStmt = $pdo->prepare("
                    INSERT INTO admin_logs (admin_id, action, description, ip_address, created_at) 
                    VALUES (:admin_id, 'login', 'Admin login successful', :ip, NOW())
                ");
                $logStmt->execute([
                    ':admin_id' => $admin['id'],
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);
                
                // 세션 정보 저장
                $sessionStmt = $pdo->prepare("
                    INSERT INTO admin_sessions (admin_id, session_id, ip_address, user_agent, created_at, expires_at) 
                    VALUES (:admin_id, :session_id, :ip, :user_agent, NOW(), DATE_ADD(NOW(), INTERVAL 8 HOUR))
                ");
                $sessionStmt->execute([
                    ':admin_id' => $admin['id'],
                    ':session_id' => session_id(),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                    ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
                
                // 실패한 로그인 시도 기록
                if ($admin) {
                    $logStmt = $pdo->prepare("
                        INSERT INTO admin_logs (admin_id, action, description, ip_address, created_at) 
                        VALUES (:admin_id, 'login_failed', 'Failed login attempt', :ip, NOW())
                    ");
                    $logStmt->execute([
                        ':admin_id' => $admin['id'],
                        ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                    ]);
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - IMPEX GLS</title>
    
    <!-- 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --color-primary: <?php echo COLOR_PRIMARY; ?>;
            --color-secondary: <?php echo COLOR_SECONDARY; ?>;
            --color-secondary-dark: <?php echo COLOR_SECONDARY_DARK; ?>;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            width: 150px;
            margin: 0 auto 2rem;
            display: block;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(227, 30, 36, 0.1);
        }
        
        .btn-login {
            width: 100%;
            background: var(--color-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #d11920;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(227, 30, 36, 0.3);
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .form-input.with-icon {
            padding-left: 3rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .remember-me input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- 로고 -->
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="IMPEX GLS" class="logo">
            
            <h1 class="text-2xl font-bold text-center mb-2">Admin Login</h1>
            <p class="text-gray-600 text-center mb-6">Please login to access the admin panel</p>
            
            <!-- 에러 메시지 -->
            <?php if ($error): ?>
            <div class="error-message">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <!-- 로그인 폼 -->
            <form method="POST" action="">
                <div class="input-group">
                    <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <input type="text" name="username" class="form-input with-icon" 
                           placeholder="Username" required autofocus
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="input-group">
                    <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <input type="password" name="password" class="form-input with-icon" 
                           placeholder="Password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember" class="text-gray-600 cursor-pointer">Remember me</label>
                </div>
                
                <button type="submit" class="btn-login">
                    Login to Admin Panel
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>" class="text-gray-600 hover:text-gray-800">
                    ← Back to Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>