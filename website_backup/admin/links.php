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
            $category = $_POST['category'] ?? 'other';
            $title_ko = trim($_POST['title_ko'] ?? '');
            $title_en = trim($_POST['title_en'] ?? '');
            $description_ko = trim($_POST['description_ko'] ?? '');
            $description_en = trim($_POST['description_en'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $icon_class = trim($_POST['icon_class'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            try {
                $pdo = getDBConnection();
                
                if ($id > 0) {
                    // 수정
                    $stmt = $pdo->prepare("
                        UPDATE useful_links SET
                            category = :category,
                            title_ko = :title_ko,
                            title_en = :title_en,
                            description_ko = :description_ko,
                            description_en = :description_en,
                            url = :url,
                            icon_class = :icon_class,
                            display_order = :display_order,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':id' => $id,
                        ':category' => $category,
                        ':title_ko' => $title_ko,
                        ':title_en' => $title_en,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':url' => $url,
                        ':icon_class' => $icon_class,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('update_link', "Updated link: $title_en");
                    $success = 'Link updated successfully.';
                } else {
                    // 추가
                    $stmt = $pdo->prepare("
                        INSERT INTO useful_links (
                            category, title_ko, title_en, 
                            description_ko, description_en, url,
                            icon_class, display_order, is_active, created_at
                        ) VALUES (
                            :category, :title_ko, :title_en,
                            :description_ko, :description_en, :url,
                            :icon_class, :display_order, :is_active, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':category' => $category,
                        ':title_ko' => $title_ko,
                        ':title_en' => $title_en,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':url' => $url,
                        ':icon_class' => $icon_class,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('add_link', "Added new link: $title_en");
                    $success = 'Link added successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to save link.';
            }
        } elseif ($action === 'delete' && checkPermission('admin')) {
            $id = intval($_POST['id']);
            
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM useful_links WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                logAdminAction('delete_link', "Deleted link ID: $id");
                $success = 'Link deleted successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to delete link.';
            }
        }
    } else {
        $error = 'You do not have permission to perform this action.';
    }
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM useful_links ORDER BY category, display_order, title_en");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 카테고리별로 그룹화
    $grouped_links = [];
    foreach ($links as $link) {
        $grouped_links[$link['category']][] = $link;
    }
} catch (PDOException $e) {
    $links = [];
    $grouped_links = [];
}

// 편집할 링크 정보 가져오기
$editLink = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM useful_links WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $editLink = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 오류 무시
    }
}

// 페이지 접근 로그
logAdminAction('view_links', 'Viewed Quick Links list');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Links - IMPEX GLS Admin</title>
    
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
        
        .link-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .link-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            
            <a href="links.php" class="nav-item active">
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
            <h1 class="text-3xl font-bold text-gray-800">Quick Links</h1>
            <p class="text-gray-600">Manage external links and resources</p>
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
            <h2 class="text-xl font-bold mb-4"><?php echo $editLink ? 'Edit Link' : 'Add New Link'; ?></h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save">
                <?php if ($editLink): ?>
                <input type="hidden" name="id" value="<?php echo $editLink['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                            <option value="government" <?php echo ($editLink['category'] ?? '') === 'government' ? 'selected' : ''; ?>>Government Agencies</option>
                            <option value="shipping" <?php echo ($editLink['category'] ?? '') === 'shipping' ? 'selected' : ''; ?>>Shipping Lines</option>
                            <option value="airlines" <?php echo ($editLink['category'] ?? '') === 'airlines' ? 'selected' : ''; ?>>Airlines</option>
                            <option value="ports" <?php echo ($editLink['category'] ?? '') === 'ports' ? 'selected' : ''; ?>>Ports & Terminals</option>
                            <option value="tools" <?php echo ($editLink['category'] ?? '') === 'tools' ? 'selected' : ''; ?>>Useful Tools</option>
                            <option value="associations" <?php echo ($editLink['category'] ?? '') === 'associations' ? 'selected' : ''; ?>>Trade Associations</option>
                            <option value="other" <?php echo ($editLink['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                        <input type="number" name="display_order" 
                               value="<?php echo htmlspecialchars($editLink['display_order'] ?? '0'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title (Korean)</label>
                        <input type="text" name="title_ko" 
                               value="<?php echo htmlspecialchars($editLink['title_ko'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title (English) *</label>
                        <input type="text" name="title_en" required
                               value="<?php echo htmlspecialchars($editLink['title_en'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL *</label>
                        <input type="url" name="url" required
                               value="<?php echo htmlspecialchars($editLink['url'] ?? ''); ?>"
                               placeholder="https://example.com"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                        <input type="text" name="icon_class" 
                               value="<?php echo htmlspecialchars($editLink['icon_class'] ?? ''); ?>"
                               placeholder="e.g., fas fa-link"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                    <textarea name="description_ko" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editLink['description_ko'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea name="description_en" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editLink['description_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo ($editLink['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <?php echo $editLink ? 'Update Link' : 'Add Link'; ?>
                    </button>
                    <?php if ($editLink): ?>
                    <a href="links.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- 링크 목록 -->
        <?php
        $categories = [
            'government' => 'Government Agencies',
            'shipping' => 'Shipping Lines',
            'airlines' => 'Airlines',
            'ports' => 'Ports & Terminals',
            'tools' => 'Useful Tools',
            'associations' => 'Trade Associations',
            'other' => 'Other'
        ];
        
        foreach ($categories as $cat_key => $cat_name):
            if (isset($grouped_links[$cat_key]) && count($grouped_links[$cat_key]) > 0):
        ?>
        <div class="mb-8">
            <h3 class="text-xl font-bold mb-4"><?php echo $cat_name; ?></h3>
            <?php foreach ($grouped_links[$cat_key] as $link): ?>
            <div class="link-item">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-medium mb-1">
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <?php echo htmlspecialchars($link['title_en']); ?>
                                <?php if ($link['title_ko']): ?>
                                <span class="text-gray-500 text-sm ml-2">(<?php echo htmlspecialchars($link['title_ko']); ?>)</span>
                                <?php endif; ?>
                                <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </h4>
                        <?php if ($link['description_en'] || $link['description_ko']): ?>
                        <p class="text-sm text-gray-600">
                            <?php echo htmlspecialchars($link['description_en']); ?>
                            <?php if ($link['description_ko']): ?>
                            <br><span class="text-gray-500"><?php echo htmlspecialchars($link['description_ko']); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($link['url']); ?></p>
                    </div>
                    
                    <div class="flex items-center gap-2 ml-4">
                        <?php if ($link['is_active']): ?>
                        <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>
                        <?php else: ?>
                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Inactive</span>
                        <?php endif; ?>
                        
                        <?php if (checkPermission('editor')): ?>
                        <a href="?edit=<?php echo $link['id']; ?>" 
                           class="text-sm text-blue-600 hover:text-blue-800">Edit</a>
                        <?php endif; ?>
                        
                        <?php if (checkPermission('admin')): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this link?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php 
            endif;
        endforeach; 
        ?>
        
        <?php if (count($links) === 0): ?>
        <div class="data-card p-12 text-center">
            <p class="text-gray-500">No links found. Add your first link above.</p>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>