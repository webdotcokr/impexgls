<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';
require_once '../../config/db-config.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/resources/useful-links.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 현재 선택된 카테고리
$current_category = $_GET['category'] ?? 'trade';

// 카테고리 정의
$categories = [
    'trade' => 'Trade Information',
    'airlines' => 'Airlines',
    'Steamship Lines' => 'Steamship Lines',
    'Logistics Information' => 'Logistics Information',
    'Government Agencies' => 'Government Agencies'
];

// DB에서 현재 카테고리의 링크 가져오기
$category_name = $categories[$current_category] ?? 'Trade Information';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM useful_links WHERE category = ? AND is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute([$category_name]);
    
    $links = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $links[] = [
            'name' => $row['title'],
            'logo' => $row['icon_path'],
            'url' => $row['url'],
            'description' => $row['description']
        ];
    }
} catch (PDOException $e) {
    $links = [];
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
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
    <!-- 반응형 스타일 -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <!-- Styles moved to global.css -->
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Resources',
        'title' => 'Quick Links',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Useful Link.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Resources',
        'current_page' => 'Quick Links',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Quick Links', 'url' => BASE_URL . '/pages/resources/quick-links.php'],
            ['title' => 'Knowledge Base', 'url' => BASE_URL . '/pages/resources/knowledge-base.php'],
            ['title' => 'Terms & Conditions of Service', 'url' => BASE_URL . '/pages/resources/terms.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 탭 네비게이션 -->
    <section class="border-b z-40 lg:mt-[61px]" style="background-color: #F3F4F8;">
        <div class="container mx-auto px-4">
            <div class="flex gap-2 overflow-x-auto py-4 -mx-4 px-4 scrollbar-hide">
                <?php foreach ($categories as $key => $name): ?>
                <a href="<?php echo BASE_URL; ?>/pages/resources/quick-links.php?category=<?php echo $key; ?>" 
                   class="tab-button <?php echo $current_category === $key ? 'active' : ''; ?>">
                    <?php echo $name; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- 링크 그리드 -->
    <section class="py-12 lg:py-20">
        <div class="container mx-auto px-4">
            <div class="link-grid grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-x-3 gap-y-10 md:gap-x-5 md:gap-y-20">
                <?php foreach ($links as $link): ?>
                <a href="<?php echo $link['url']; ?>" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="block group">
                    <div class="link-card">
                        <!-- 로고 이미지 -->
                        <div class="link-logo-wrapper">
                            <img src="<?php echo BASE_URL . $link['logo']; ?>" 
                                 alt="<?php echo $link['name']; ?>" 
                                 class="link-logo"
                                 onerror="this.style.display='none'">
                        </div>
                        
                        <div class="link-content">
                            <!-- 타이틀 -->
                            <h3 class="link-title">
                                <?php echo $link['name']; ?>
                            </h3>
                            
                            <!-- 화살표 아이콘 -->
                            <svg class="external-link-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-bottom: 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M7 17L17 7M17 7H7M17 7V17"></path>
                            </svg>
                            
                            <!-- 설명 -->
                            <!-- <p class="link-description">
                                <?php echo $link['description']; ?>
                            </p> -->
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($links)): ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No links available in this category.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    // 탭 스크롤 시 그림자 효과
    const tabContainer = document.querySelector('.scrollbar-hide');
    if (tabContainer) {
        tabContainer.addEventListener('scroll', function() {
            if (this.scrollLeft > 0) {
                this.classList.add('shadow-inner');
            } else {
                this.classList.remove('shadow-inner');
            }
        });
    }
    </script>
</body>
</html>