<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'auth-check.php';

// 추가/수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (checkPermission('editor')) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $name_ko = trim($_POST['name_ko'] ?? '');
            $name_en = trim($_POST['name_en'] ?? '');
            $logo_url = trim($_POST['logo_url'] ?? '');
            $website_url = trim($_POST['website_url'] ?? '');
            $description_ko = trim($_POST['description_ko'] ?? '');
            $description_en = trim($_POST['description_en'] ?? '');
            $category = $_POST['category'] ?? 'technology';
            $display_order = intval($_POST['display_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            try {
                $pdo = getDBConnection();
                
                if ($id > 0) {
                    // 수정
                    $stmt = $pdo->prepare("
                        UPDATE clients SET
                            name_ko = :name_ko,
                            name_en = :name_en,
                            logo_url = :logo_url,
                            website_url = :website_url,
                            description_ko = :description_ko,
                            description_en = :description_en,
                            category = :category,
                            display_order = :display_order,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':id' => $id,
                        ':name_ko' => $name_ko,
                        ':name_en' => $name_en,
                        ':logo_url' => $logo_url,
                        ':website_url' => $website_url,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':category' => $category,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('update_client', "Updated client: $name_en");
                    $success = 'Client updated successfully.';
                } else {
                    // 추가
                    $stmt = $pdo->prepare("
                        INSERT INTO clients (
                            name_ko, name_en, logo_url, website_url, 
                            description_ko, description_en, category, 
                            display_order, is_active, created_at
                        ) VALUES (
                            :name_ko, :name_en, :logo_url, :website_url,
                            :description_ko, :description_en, :category,
                            :display_order, :is_active, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':name_ko' => $name_ko,
                        ':name_en' => $name_en,
                        ':logo_url' => $logo_url,
                        ':website_url' => $website_url,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':category' => $category,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('add_client', "Added new client: $name_en");
                    $success = 'Client added successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to save client.';
            }
        } elseif ($action === 'delete' && checkPermission('admin')) {
            $id = intval($_POST['id']);
            
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                logAdminAction('delete_client', "Deleted client ID: $id");
                $success = 'Client deleted successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to delete client.';
            }
        }
    } else {
        $error = 'You do not have permission to perform this action.';
    }
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY category, display_order, name_en");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리별로 그룹화
    $grouped_clients = [];
    foreach ($clients as $client) {
        $grouped_clients[$client['category']][] = $client;
    }
} catch (PDOException $e) {
    $clients = [];
    $grouped_clients = [];
}

// 편집할 클라이언트 정보 가져오기
$editClient = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $editClient = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 오류 무시
    }
}

// 페이지 접근 로그
logAdminAction('view_clients', 'Viewed clients list');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - IMPEX GLS Admin</title>
    
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
        
        .client-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .client-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .client-logo {
            width: 100%;
            height: 80px;
            object-fit: contain;
            margin-bottom: 0.5rem;
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
            
            <a href="clients.php" class="nav-item active">
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
            <a href="admins.php" class="nav-item">
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
            <h1 class="text-3xl font-bold text-gray-800">Clients</h1>
            <p class="text-gray-600">Manage client information and logos</p>
        </div>
        
        <!-- 메시지 표시 -->
        <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg">
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <?php if (checkPermission('editor')): ?>
        <!-- 추가/수정 폼 -->
        <div class="data-card p-6 mb-6">
            <h2 class="text-xl font-bold mb-4"><?php echo $editClient ? 'Edit Client' : 'Add New Client'; ?></h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save">
                <?php if ($editClient): ?>
                <input type="hidden" name="id" value="<?php echo $editClient['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name (Korean)</label>
                        <input type="text" name="name_ko" 
                               value="<?php echo htmlspecialchars($editClient['name_ko'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                        <input type="text" name="name_en" required
                               value="<?php echo htmlspecialchars($editClient['name_en'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                        <input type="text" name="logo_url" 
                               value="<?php echo htmlspecialchars($editClient['logo_url'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                        <input type="text" name="website_url" 
                               value="<?php echo htmlspecialchars($editClient['website_url'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                            <option value="technology" <?php echo ($editClient['category'] ?? '') === 'technology' ? 'selected' : ''; ?>>Technology</option>
                            <option value="automotive" <?php echo ($editClient['category'] ?? '') === 'automotive' ? 'selected' : ''; ?>>Automotive</option>
                            <option value="retail" <?php echo ($editClient['category'] ?? '') === 'retail' ? 'selected' : ''; ?>>Retail</option>
                            <option value="manufacturing" <?php echo ($editClient['category'] ?? '') === 'manufacturing' ? 'selected' : ''; ?>>Manufacturing</option>
                            <option value="healthcare" <?php echo ($editClient['category'] ?? '') === 'healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                            <option value="other" <?php echo ($editClient['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                        <input type="number" name="display_order" 
                               value="<?php echo htmlspecialchars($editClient['display_order'] ?? '0'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                    <textarea name="description_ko" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editClient['description_ko'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea name="description_en" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editClient['description_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo ($editClient['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <?php echo $editClient ? 'Update Client' : 'Add Client'; ?>
                    </button>
                    <?php if ($editClient): ?>
                    <a href="clients.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- 클라이언트 목록 -->
        <?php
        $categories = [
            'technology' => 'Technology',
            'automotive' => 'Automotive',
            'retail' => 'Retail',
            'manufacturing' => 'Manufacturing',
            'healthcare' => 'Healthcare',
            'other' => 'Other'
        ];
        
        foreach ($categories as $cat_key => $cat_name):
            if (isset($grouped_clients[$cat_key]) && count($grouped_clients[$cat_key]) > 0):
        ?>
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4"><?php echo $cat_name; ?></h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($grouped_clients[$cat_key] as $client): ?>
                <div class="client-card">
                    <?php if ($client['logo_url']): ?>
                    <img src="<?php echo htmlspecialchars($client['logo_url']); ?>" 
                         alt="<?php echo htmlspecialchars($client['name_en']); ?>"
                         class="client-logo"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMjAwIDgwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iODAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iI2EwYTBhMCI+Tm8gTG9nbzwvdGV4dD48L3N2Zz4=';">
                    <?php else: ?>
                    <div class="client-logo bg-gray-100 flex items-center justify-center">
                        <span class="text-gray-400">No Logo</span>
                    </div>
                    <?php endif; ?>
                    
                    <h4 class="font-medium text-sm mb-1"><?php echo htmlspecialchars($client['name_en']); ?></h4>
                    
                    <?php if ($client['is_active']): ?>
                    <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>
                    <?php else: ?>
                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Inactive</span>
                    <?php endif; ?>
                    
                    <?php if (checkPermission('editor')): ?>
                    <div class="mt-2 flex gap-2">
                        <a href="?edit=<?php echo $client['id']; ?>" 
                           class="text-sm text-blue-600 hover:text-blue-800">Edit</a>
                        <?php if (checkPermission('admin')): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this client?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php 
            endif;
        endforeach; 
        ?>
        
        <?php if (count($clients) === 0): ?>
        <div class="data-card p-12 text-center">
            <p class="text-gray-500">No clients found. Add your first client above.</p>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>