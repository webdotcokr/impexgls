<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/notice/logistics-news.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 페이지네이션 설정
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

// 데이터베이스에서 뉴스 가져오기
try {
    $pdo = getDBConnection();
    
    // 총 게시물 수 가져오기
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM news_posts WHERE status = 'published' AND category = 'logistics'");
    $count_stmt->execute();
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    
    // 뉴스 목록 가져오기
    $stmt = $pdo->prepare("
        SELECT id, title, DATE_FORMAT(published_at, '%Y.%m.%d') as date 
        FROM news_posts 
        WHERE status = 'published' AND category = 'logistics'
        ORDER BY published_at DESC, id DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // 에러 시 빈 배열
    $news_items = [];
    $total_items = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo generateMetaTags($page_meta_info); ?>
    
    <!-- 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    
    <style>
        /* Notice Page Specific Styles */
        .news-list-table {
            width: 100%;
            border-top: 2px solid #333;
        }
        
        .news-list-header {
            display: grid;
            grid-template-columns: 100px 1fr 150px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
            font-weight: 600;
            color: #333;
        }
        
        .news-list-item {
            display: grid;
            grid-template-columns: 100px 1fr 150px;
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 20px;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .news-list-item:hover {
            background-color: #f8f9fa;
        }
        
        .news-list-item a {
            text-decoration: none;
            display: block;
        }
        
        .news-list-item a:hover {
            color: var(--primary);
        }
        
        .news-number {
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 600;
            line-height: 21px;
            letter-spacing: -0.36px;
            text-transform: uppercase;
            color: #999;
        }
        
        .news-list-item a {
            color: #000;
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 600;
            line-height: 21px;
            letter-spacing: -0.54px;
        }
        
        .news-date {
            color: #9496A1;
            text-align:right;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 21px;
            letter-spacing: -0.28px;
            text-transform: uppercase;
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 60px;
        }
        
        .pagination-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
            color: #5B5D6B;
            text-decoration: none;
            font-family: Poppins;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover {
            background-color: #f8f9fa;
            color: #B21525;
        }
        
        .pagination-btn.active {
            background: #B21525;
            color: white;
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .pagination-ellipsis {
            color: #5B5D6B;
            font-family: Poppins;
            font-size: 14px;
            padding: 0 4px;
        }
        
        /* Navigation arrow styles */
        .pagination-btn.nav-arrow {
            font-size: 18px;
            font-weight: 300;
        }
        
        @media (max-width: 768px) {
            .news-list-header,
            .news-list-item {
                grid-template-columns: 60px 1fr 100px;
                padding: 12px 16px;
                font-size: 14px;
            }
            
            .news-number {
                font-size: 14px;
                font-style: normal;
                font-weight: 600;
                line-height: 21px;
                letter-spacing: -0.28px;
                text-transform: uppercase;
            }
            
            .news-list-item a {
                overflow: hidden;
                color: #000;
                text-overflow: ellipsis;
                font-family: Poppins;
                font-size: 15px;
                font-style: normal;
                font-weight: 600;
                line-height: 21px;
                letter-spacing: -0.48px;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 1;
                flex: 1 0 0;
            }
            
            .news-date {
                font-size: 11px;
                font-style: normal;
                font-weight: 500;
                line-height: 21px;
                letter-spacing: -0.22px;
                text-transform: uppercase;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'NOTICES',
        'title' => 'Logistics News',
        'background' => BASE_URL . '/assets/images/notice-bg.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Notice',
        'current_page' => 'Logistics News',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Logistics News', 'url' => BASE_URL . '/pages/notice/logistics-news.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <!-- 뉴스 리스트 -->
            <div class="news-list-table">
                <?php if (!empty($news_items)): ?>
                    <?php 
                    $row_number = $total_items - $offset;
                    foreach ($news_items as $item): 
                    ?>
                    <div class="news-list-item">
                        <span class="news-number"><?php echo $row_number--; ?></span>
                        <a href="<?php echo BASE_URL; ?>/pages/notices/logistics-news-detail.php?id=<?php echo $item['id']; ?>">
                            <?php echo e($item['title']); ?>
                        </a>
                        <span class="news-date"><?php echo $item['date']; ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-20 text-gray-500">
                        No news posts available.
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 페이지네이션 -->
            <div class="pagination">
                <a href="?page=1" class="pagination-btn nav-arrow <?php echo $current_page == 1 ? 'disabled' : ''; ?>">«</a>
                <a href="?page=<?php echo max(1, $current_page - 1); ?>" class="pagination-btn nav-arrow <?php echo $current_page == 1 ? 'disabled' : ''; ?>">‹</a>
                
                <?php
                // Always show page 1
                if ($current_page > 3) {
                    echo '<a href="?page=1" class="pagination-btn">1</a>';
                    if ($current_page > 4) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                }
                
                // Show current page and surrounding pages
                for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++):
                    if ($i == $total_pages && $i > 3 && $current_page < $total_pages - 2) {
                        continue; // Skip last page here, we'll show it separately
                    }
                ?>
                    <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php
                // Always show last page
                if ($current_page < $total_pages - 2 && $total_pages > 3) {
                    if ($current_page < $total_pages - 3) {
                        echo '<span class="pagination-ellipsis">...</span>';
                    }
                    echo '<a href="?page=' . $total_pages . '" class="pagination-btn">' . $total_pages . '</a>';
                }
                ?>
                
                <a href="?page=<?php echo min($total_pages, $current_page + 1); ?>" class="pagination-btn nav-arrow <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">›</a>
                <a href="?page=<?php echo $total_pages; ?>" class="pagination-btn nav-arrow <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">»</a>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>