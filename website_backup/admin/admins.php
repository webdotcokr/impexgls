<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'auth-check.php';

// 권한 확인
if (!checkPermission('admin')) {
    header('Location: dashboard.php');
    exit;
}

// 추가/수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        $errors = [];
        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        
        try {
            $pdo = getDBConnection();
            
            // 중복 확인
            $checkStmt = $pdo->prepare("SELECT id FROM admins WHERE username = :username AND id != :id");
            $checkStmt->execute([':username' => $username, ':id' => $id]);
            if ($checkStmt->fetch()) {
                $errors[] = 'Username already exists.';
            }
            
            if ($id == 0 && empty($password)) {
                $errors[] = 'Password is required for new admin.';
            }
            
            if (!empty($password) && $password !== $confirm_password) {
                $errors[] = 'Passwords do not match.';
            }
            
            if (!empty($password) && strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
            
            if (empty($errors)) {
                if ($id > 0) {
                    // 수정
                    if (!empty($password)) {
                        // 비밀번호 변경
                        $stmt = $pdo->prepare("
                            UPDATE admins SET
                                username = :username,
                                name = :name,
                                email = :email,
                                role = :role,
                                password = :password,
                                is_active = :is_active,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            ':id' => $id,
                            ':username' => $username,
                            ':name' => $name,
                            ':email' => $email,
                            ':role' => $role,
                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                            ':is_active' => $is_active
                        ]);
                    } else {
                        // 비밀번호 변경 없음
                        $stmt = $pdo->prepare("
                            UPDATE admins SET
                                username = :username,
                                name = :name,
                                email = :email,
                                role = :role,
                                is_active = :is_active,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            ':id' => $id,
                            ':username' => $username,
                            ':name' => $name,
                            ':email' => $email,
                            ':role' => $role,
                            ':is_active' => $is_active
                        ]);
                    }
                    
                    logAdminAction('update_admin', "Updated admin: $username");
                    $success = 'Admin updated successfully.';
                } else {
                    // 추가
                    $stmt = $pdo->prepare("
                        INSERT INTO admins (
                            username, name, email, role, password, 
                            is_active, created_at
                        ) VALUES (
                            :username, :name, :email, :role, :password,
                            :is_active, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':username' => $username,
                        ':name' => $name,
                        ':email' => $email,
                        ':role' => $role,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('add_admin', "Added new admin: $username");
                    $success = 'Admin added successfully.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        } catch (PDOException $e) {
            $error = 'Failed to save admin.';
        }
    } elseif ($action === 'delete' && checkPermission('super_admin')) {
        $id = intval($_POST['id']);
        
        // 자기 자신은 삭제 불가
        if ($id == $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM admins WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                logAdminAction('delete_admin', "Deleted admin ID: $id");
                $success = 'Admin deleted successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to delete admin.';
            }
        }
    }
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admins = [];
}

// 편집할 관리자 정보 가져오기
$editAdmin = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $editAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 오류 무시
    }
}

// 페이지 접근 로그
logAdminAction('view_admins', 'Viewed admins list');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admins - IMPEX GLS Admin</title>
    
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
            background: #f3f4f6;
        }
        
        .sidebar {
            background: var(--color-secondary);
            width: 250px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .data-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .role-super_admin {
            background: #dc2626;
            color: white;
        }
        
        .role-admin {
            background: #f59e0b;
            color: white;
        }
        
        .role-editor {
            background: #3b82f6;
            color: white;
        }
        
        .role-viewer {
            background: #6b7280;
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- 사이드바 -->
    <aside class="sidebar">
        <div class="p-6">
            <h2 class="text-white text-xl font-bold">IMPEX GLS Admin</h2>
            <p class="text-white/70 text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
        </div>
        
        <nav class="mt-6">
            <a href="dashboard.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
            
            <a href="quotes.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Quote Requests
            </a>
            
            <a href="clients.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Clients
            </a>
            
            <a href="faqs.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                FAQs
            </a>
            
            <a href="certificates.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                Certificates
            </a>
            
            <a href="links.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                Quick Links
            </a>
            
            <?php if (checkPermission('admin')): ?>
            <a href="admins.php" class="nav-item active">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Admins
            </a>
            <?php endif; ?>
            
            <div class="border-t border-white/20 my-4"></div>
            
            <a href="logout.php" class="nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
        </nav>
    </aside>
    
    <!-- 메인 콘텐츠 -->
    <main class="main-content">
        <!-- 헤더 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Admin Management</h1>
            <p class="text-gray-600">Manage admin accounts and permissions</p>
        </div>
        
        <!-- 메시지 표시 -->
        <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg">
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <!-- 추가/수정 폼 -->
        <div class="data-card p-6 mb-6">
            <h2 class="text-xl font-bold mb-4"><?php echo $editAdmin ? 'Edit Admin' : 'Add New Admin'; ?></h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save">
                <?php if ($editAdmin): ?>
                <input type="hidden" name="id" value="<?php echo $editAdmin['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                        <input type="text" name="username" required
                               value="<?php echo htmlspecialchars($editAdmin['username'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($editAdmin['name'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($editAdmin['email'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                        <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                            <option value="viewer" <?php echo ($editAdmin['role'] ?? '') === 'viewer' ? 'selected' : ''; ?>>Viewer (Read only)</option>
                            <option value="editor" <?php echo ($editAdmin['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor (Can edit content)</option>
                            <option value="admin" <?php echo ($editAdmin['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Full access)</option>
                            <?php if (checkPermission('super_admin')): ?>
                            <option value="super_admin" <?php echo ($editAdmin['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password <?php echo $editAdmin ? '(Leave blank to keep current)' : '*'; ?>
                        </label>
                        <input type="password" name="password" 
                               <?php echo $editAdmin ? '' : 'required'; ?>
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo ($editAdmin['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <?php echo $editAdmin ? 'Update Admin' : 'Add Admin'; ?>
                    </button>
                    <?php if ($editAdmin): ?>
                    <a href="admins.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- 관리자 목록 -->
        <div class="data-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($admins as $admin): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($admin['name']); ?>
                                <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                <span class="text-xs text-gray-500 ml-2">(You)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($admin['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="role-badge role-<?php echo $admin['role']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $admin['role'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($admin['is_active']): ?>
                                <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>
                                <?php else: ?>
                                <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="?edit=<?php echo $admin['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <?php if (checkPermission('super_admin') && $admin['id'] != $_SESSION['admin_id']): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this admin?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (count($admins) === 0): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500">No admins found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>