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
            $title_ko = trim($_POST['title_ko'] ?? '');
            $title_en = trim($_POST['title_en'] ?? '');
            $description_ko = trim($_POST['description_ko'] ?? '');
            $description_en = trim($_POST['description_en'] ?? '');
            $issuer_ko = trim($_POST['issuer_ko'] ?? '');
            $issuer_en = trim($_POST['issuer_en'] ?? '');
            $issue_date = $_POST['issue_date'] ?? null;
            $expiry_date = $_POST['expiry_date'] ?? null;
            $certificate_number = trim($_POST['certificate_number'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $display_order = intval($_POST['display_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            try {
                $pdo = getDBConnection();
                
                if ($id > 0) {
                    // 수정
                    $stmt = $pdo->prepare("
                        UPDATE certificates SET
                            title_ko = :title_ko,
                            title_en = :title_en,
                            description_ko = :description_ko,
                            description_en = :description_en,
                            issuer_ko = :issuer_ko,
                            issuer_en = :issuer_en,
                            issue_date = :issue_date,
                            expiry_date = :expiry_date,
                            certificate_number = :certificate_number,
                            image_url = :image_url,
                            display_order = :display_order,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':id' => $id,
                        ':title_ko' => $title_ko,
                        ':title_en' => $title_en,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':issuer_ko' => $issuer_ko,
                        ':issuer_en' => $issuer_en,
                        ':issue_date' => $issue_date,
                        ':expiry_date' => $expiry_date,
                        ':certificate_number' => $certificate_number,
                        ':image_url' => $image_url,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('update_certificate', "Updated certificate: $title_en");
                    $success = 'Certificate updated successfully.';
                } else {
                    // 추가
                    $stmt = $pdo->prepare("
                        INSERT INTO certificates (
                            title_ko, title_en, description_ko, description_en,
                            issuer_ko, issuer_en, issue_date, expiry_date,
                            certificate_number, image_url, display_order, 
                            is_active, created_at
                        ) VALUES (
                            :title_ko, :title_en, :description_ko, :description_en,
                            :issuer_ko, :issuer_en, :issue_date, :expiry_date,
                            :certificate_number, :image_url, :display_order,
                            :is_active, NOW()
                        )
                    ");
                    $stmt->execute([
                        ':title_ko' => $title_ko,
                        ':title_en' => $title_en,
                        ':description_ko' => $description_ko,
                        ':description_en' => $description_en,
                        ':issuer_ko' => $issuer_ko,
                        ':issuer_en' => $issuer_en,
                        ':issue_date' => $issue_date,
                        ':expiry_date' => $expiry_date,
                        ':certificate_number' => $certificate_number,
                        ':image_url' => $image_url,
                        ':display_order' => $display_order,
                        ':is_active' => $is_active
                    ]);
                    
                    logAdminAction('add_certificate', "Added new certificate: $title_en");
                    $success = 'Certificate added successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to save certificate.';
            }
        } elseif ($action === 'delete' && checkPermission('admin')) {
            $id = intval($_POST['id']);
            
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                logAdminAction('delete_certificate', "Deleted certificate ID: $id");
                $success = 'Certificate deleted successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to delete certificate.';
            }
        }
    } else {
        $error = 'You do not have permission to perform this action.';
    }
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM certificates ORDER BY display_order, title_en");
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $certificates = [];
}

// 편집할 인증서 정보 가져오기
$editCert = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $editCert = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 오류 무시
    }
}

// 페이지 접근 로그
logAdminAction('view_certificates', 'Viewed certificates list');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - IMPEX GLS Admin</title>
    
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
        
        .cert-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .cert-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .cert-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: #f3f4f6;
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
            
            <a href="certificates.php" class="nav-item active">
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
            <h1 class="text-3xl font-bold text-gray-800">Certificates</h1>
            <p class="text-gray-600">Manage company certificates and accreditations</p>
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
            <h2 class="text-xl font-bold mb-4"><?php echo $editCert ? 'Edit Certificate' : 'Add New Certificate'; ?></h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save">
                <?php if ($editCert): ?>
                <input type="hidden" name="id" value="<?php echo $editCert['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title (Korean)</label>
                        <input type="text" name="title_ko" 
                               value="<?php echo htmlspecialchars($editCert['title_ko'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title (English) *</label>
                        <input type="text" name="title_en" required
                               value="<?php echo htmlspecialchars($editCert['title_en'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issuer (Korean)</label>
                        <input type="text" name="issuer_ko" 
                               value="<?php echo htmlspecialchars($editCert['issuer_ko'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issuer (English) *</label>
                        <input type="text" name="issuer_en" required
                               value="<?php echo htmlspecialchars($editCert['issuer_en'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Number</label>
                        <input type="text" name="certificate_number" 
                               value="<?php echo htmlspecialchars($editCert['certificate_number'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="text" name="image_url" 
                               value="<?php echo htmlspecialchars($editCert['image_url'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
                        <input type="date" name="issue_date" 
                               value="<?php echo htmlspecialchars($editCert['issue_date'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="date" name="expiry_date" 
                               value="<?php echo htmlspecialchars($editCert['expiry_date'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                        <input type="number" name="display_order" 
                               value="<?php echo htmlspecialchars($editCert['display_order'] ?? '0'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                    <textarea name="description_ko" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editCert['description_ko'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea name="description_en" rows="2" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"><?php echo htmlspecialchars($editCert['description_en'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo ($editCert['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <?php echo $editCert ? 'Update Certificate' : 'Add Certificate'; ?>
                    </button>
                    <?php if ($editCert): ?>
                    <a href="certificates.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- 인증서 목록 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($certificates as $cert): ?>
            <div class="cert-card">
                <?php if ($cert['image_url']): ?>
                <img src="<?php echo htmlspecialchars($cert['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($cert['title_en']); ?>"
                     class="cert-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDQwMCAyMDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmM2Y0ZjYiLz48Y2lyY2xlIGN4PSIyMDAiIGN5PSIxMDAiIHI9IjQwIiBmaWxsPSJub25lIiBzdHJva2U9IiNkMWQ1ZGIiIHN0cm9rZS13aWR0aD0iMyIvPjxsaW5lIHgxPSIxNzAiIHkxPSIxMDAiIHgyPSIxODUiIHkyPSIxMDAiIHN0cm9rZT0iI2QxZDVkYiIgc3Ryb2tlLXdpZHRoPSIzIi8+PGxpbmUgeDE9IjIxNSIgeTE9IjEwMCIgeDI9IjIzMCIgeTI9IjEwMCIgc3Ryb2tlPSIjZDFkNWRiIiBzdHJva2Utd2lkdGg9IjMiLz48cGF0aCBkPSJNMTkwIDg1IEwyMDAgOTUgMjIwIDc1IiBmaWxsPSJub25lIiBzdHJva2U9IiNkMWQ1ZGIiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PHRleHQgeD0iNTAlIiB5PSI3NSUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiNhMGEwYTAiPkNlcnRpZmljYXRlPC90ZXh0Pjwvc3ZnPg==';">
                <?php else: ?>
                <div class="cert-image flex items-center justify-center bg-gray-100">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <?php endif; ?>
                
                <h3 class="font-bold text-lg mb-1">
                    <?php echo htmlspecialchars($cert['title_en']); ?>
                    <?php if ($cert['title_ko']): ?>
                    <span class="text-gray-500 text-sm block"><?php echo htmlspecialchars($cert['title_ko']); ?></span>
                    <?php endif; ?>
                </h3>
                
                <p class="text-sm text-gray-600 mb-2">
                    Issued by: <?php echo htmlspecialchars($cert['issuer_en']); ?>
                    <?php if ($cert['issuer_ko']): ?>
                    <span class="text-gray-500 block"><?php echo htmlspecialchars($cert['issuer_ko']); ?></span>
                    <?php endif; ?>
                </p>
                
                <?php if ($cert['certificate_number']): ?>
                <p class="text-xs text-gray-500 mb-2">No: <?php echo htmlspecialchars($cert['certificate_number']); ?></p>
                <?php endif; ?>
                
                <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                    <?php if ($cert['issue_date']): ?>
                    <span>Issued: <?php echo date('M Y', strtotime($cert['issue_date'])); ?></span>
                    <?php endif; ?>
                    <?php if ($cert['expiry_date']): ?>
                    <span class="<?php echo strtotime($cert['expiry_date']) < time() ? 'text-red-600' : ''; ?>">
                        Expires: <?php echo date('M Y', strtotime($cert['expiry_date'])); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-between items-center">
                    <div>
                        <?php if ($cert['is_active']): ?>
                        <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Active</span>
                        <?php else: ?>
                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (checkPermission('editor')): ?>
                    <div class="flex gap-2">
                        <a href="?edit=<?php echo $cert['id']; ?>" 
                           class="text-sm text-blue-600 hover:text-blue-800">Edit</a>
                        <?php if (checkPermission('admin')): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Delete this certificate?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $cert['id']; ?>">
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($certificates) === 0): ?>
        <div class="data-card p-12 text-center">
            <p class="text-gray-500">No certificates found. Add your first certificate above.</p>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>