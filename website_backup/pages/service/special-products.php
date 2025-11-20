<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/service/special-products.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/service.css">
    
    <!-- CSS variables now defined in global.css -->
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Services',
        'title' => 'Special Products',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Special Products.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Service',
        'current_page' => 'Special Products',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'International Transportation', 'url' => BASE_URL . '/pages/service/international-transportation.php'],
            ['title' => 'Contract Logistics', 'url' => BASE_URL . '/pages/service/contract-logistics.php'],
            ['title' => 'Supply Chain Management', 'url' => BASE_URL . '/pages/service/supply-chain-management.php'],
            ['title' => 'Special Products', 'url' => BASE_URL . '/pages/service/special-products.php'],
            ['title' => 'Other Activities & Services', 'url' => BASE_URL . '/pages/service/other-activities.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section class="py-12 lg:py-20">
        <div class="container mx-auto px-4">
            <!-- 헤더 텍스트 -->
            <div class="section-header">
                <h2>Specialized Handling for Your Most Sensitive Cargo</h2>
                <p>We expertly handle unique logistics needs with precision and care.</p>
            </div>
            
            <!-- Air Freight 서비스 박스 -->
            <div class="service-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/service/Exhibitions & Events.webp" 
                     alt="Exhibitions & Events" 
                     class="service-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIHZpZXdCb3g9IjAgMCAxMjAwIDQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiM0QjlDRTMiLz48cGF0aCBkPSJNNjAwIDIwMCBMNjUwIDE4MCBMNDAWIDI0MCBaIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC41Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMzYiIGZpbGw9IndoaXRlIj5BaXIgRnJlaWdodDwvdGV4dD48L3N2Zz4=';">
                
                <div class="service-content mx-auto">
                    <h3 class="service-title">Exhibitions & Events</h3>
                    <p class="service-description">Seamless logistics ensure your events are delivered to perfection.</p>
                </div>
            </div>
            
            <!-- Ocean Freight 서비스 박스 -->
            <div class="service-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/service/Defense Logistics.webp" 
                     alt="Defense Logistics" 
                     class="service-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIHZpZXdCb3g9IjAgMCAxMjAwIDQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiMyOTgwYjkiLz48cGF0aCBkPSJNMjAwIDMwMCBRNDAwIDI4MCA2MDAgMzAwIFQxMDAwIDMwMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSI0IiBvcGFjaXR5PSIwLjUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIzNiIgZmlsbD0id2hpdGUiPk9jZWFuIEZyZWlnaHQ8L3RleHQ+PC9zdmc+';">
                
                <div class="service-content mx-auto">
                    <h3 class="service-title">Defense Logistics</h3>
                    <p class="service-description">Secure, compliant logistics for defense and sensitive goods.</p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>