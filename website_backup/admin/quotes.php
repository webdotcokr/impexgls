<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'auth-check.php';

// 페이지네이션 설정
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// 필터 설정
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// 상태 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (checkPermission('editor')) {
        $quote_id = intval($_POST['quote_id']);
        $new_status = $_POST['status'];
        
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE quote_requests SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $new_status, ':id' => $quote_id]);
            
            logAdminAction('update_quote_status', "Updated quote #$quote_id status to $new_status");
            
            header('Location: quotes.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to update status.';
        }
    } else {
        $error = 'You do not have permission to update quote status.';
    }
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    
    // 조건 구성
    $where = [];
    $params = [];
    
    if ($status) {
        $where[] = "status = :status";
        $params[':status'] = $status;
    }
    
    if ($search) {
        $where[] = "(company_name LIKE :search OR contact_person LIKE :search OR email LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // 전체 개수 조회
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM quote_requests $whereClause");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    $totalPages = ceil($totalCount / $perPage);
    
    // 데이터 조회
    $sql = "SELECT * FROM quote_requests $whereClause ORDER BY created_at DESC LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $quotes = [];
    $totalCount = 0;
    $totalPages = 0;
}

// 페이지 접근 로그
logAdminAction('view_quotes', 'Viewed quote requests list');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Requests - IMPEX GLS Admin</title>
    
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
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-processed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4b5563;
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
            
            <a href="quotes.php" class="nav-item active">
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
            <h1 class="text-3xl font-bold text-gray-800">Quote Requests</h1>
            <p class="text-gray-600">Manage and respond to customer quote requests</p>
        </div>
        
        <!-- 필터 -->
        <div class="data-card p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" placeholder="Search by company, contact, or email..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                </div>
                
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processed" <?php echo $status === 'processed' ? 'selected' : ''; ?>>Processed</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Filter
                </button>
                
                <?php if ($search || $status): ?>
                <a href="quotes.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- 데이터 테이블 -->
        <div class="data-card">
            <?php if (isset($error)): ?>
            <div class="p-4 bg-red-50 text-red-700 border-b border-red-200">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="p-4 bg-green-50 text-green-700 border-b border-green-200">
                Quote status updated successfully.
            </div>
            <?php endif; ?>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($quotes as $quote): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #<?php echo $quote['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($quote['company_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($quote['contact_person']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($quote['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($quote['service_type']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($quote['origin_country']); ?> → <?php echo htmlspecialchars($quote['destination_country']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($quote['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge status-<?php echo $quote['status']; ?>">
                                    <?php echo ucfirst($quote['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewQuote(<?php echo $quote['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                <?php if (checkPermission('editor')): ?>
                                <button onclick="updateStatus(<?php echo $quote['id']; ?>, '<?php echo $quote['status']; ?>')" 
                                        class="text-green-600 hover:text-green-900">Update</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (count($quotes) === 0): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500">No quote requests found.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- 페이지네이션 -->
            <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalCount); ?> of <?php echo $totalCount; ?> results
                    </div>
                    <div class="flex gap-2">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>" 
                                   class="px-3 py-1 rounded <?php echo $i === $page ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <span class="px-3 py-1">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- 상세보기 모달 -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Quote Request Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modalContent"></div>
        </div>
    </div>
    
    <!-- 상태 업데이트 모달 -->
    <div id="statusModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Update Status</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="quote_id" id="statusQuoteId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select New Status</label>
                    <select name="status" id="statusSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Update Status
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // 견적 상세보기
        function viewQuote(id) {
            fetch(`quote-detail.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContent').innerHTML = html;
                    document.getElementById('viewModal').classList.add('active');
                });
        }
        
        // 상태 업데이트
        function updateStatus(id, currentStatus) {
            document.getElementById('statusQuoteId').value = id;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').classList.add('active');
        }
        
        // 모달 닫기
        function closeModal() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('active');
            });
        }
        
        // 모달 외부 클릭시 닫기
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>