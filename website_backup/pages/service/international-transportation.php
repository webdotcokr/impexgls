<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/service/international-transportation.php';
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
    
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Services',
        'title' => 'International Transportation',
        'background' => BASE_URL . '/assets/images/subpage-header-image/International Transportation.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Service',
        'current_page' => 'International Transportation',
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
                <h2>Connecting Your Business Seamlessly to the World</h2>
                <p>We provide secure, timely, and cost-effective freight solutions that connect your products to global markets.</p>
            </div>
            
            <!-- Air Freight 서비스 박스 -->
            <div class="service-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/service/Air Freight.webp" 
                     alt="Air Freight" 
                     class="service-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIHZpZXdCb3g9IjAgMCAxMjAwIDQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiM0QjlDRTMiLz48cGF0aCBkPSJNNjAwIDIwMCBMNjUwIDE4MCBMNDAWIDI0MCBaIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC41Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMzYiIGZpbGw9IndoaXRlIj5BaXIgRnJlaWdodDwvdGV4dD48L3N2Zz4=';">
                
                <div class="service-content mx-auto">
                    <h3 class="service-title">Air Freight</h3>
                    <p class="service-description">Rapid, reliable air transport for your urgent cargo.</p>
                    
                    <div class="feature-tags">
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Air Freight/1.svg" alt="" class="w-5 h-5">
                            Import and Export Air Transportation Worldwide
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Air Freight/2.svg" alt="" class="w-5 h-5">
                            Import Consolidations
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Air Freight/3.svg" alt="" class="w-5 h-5">
                            Express Services
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Air Freight/4.svg" alt="" class="w-5 h-5">
                            Certified to Handle Hazardous and Perishable Material
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Air Freight/5.svg" alt="" class="w-5 h-5">
                            Weekly Cargo Reporting - Export and Import
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Ocean Freight 서비스 박스 -->
            <div class="service-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/service/Ocean Freight.webp" 
                     alt="Ocean Freight" 
                     class="service-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIHZpZXdCb3g9IjAgMCAxMjAwIDQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiMyOTgwYjkiLz48cGF0aCBkPSJNMjAwIDMwMCBRNDAwIDI4MCA2MDAgMzAwIFQxMDAwIDMwMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSI0IiBvcGFjaXR5PSIwLjUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIzNiIgZmlsbD0id2hpdGUiPk9jZWFuIEZyZWlnaHQ8L3RleHQ+PC9zdmc+';">
                
                <div class="service-content mx-auto">
                    <h3 class="service-title">Ocean Freight</h3>
                    <p class="service-description">Efficient, cost-effective ocean shipping solutions for large-scale logistics needs.</p>
                    
                    <div class="feature-tags">
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/1.svg" alt="" class="w-5 h-5">
                            Import and Export Ocean Transportation Services Available Including FCL, LCL, and Breakbulk
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/2.svg" alt="" class="w-5 h-5">
                            Regular FAK Consolidations
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/3.svg" alt="" class="w-5 h-5">
                            Customs Clearance
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/4.svg" alt="" class="w-5 h-5">
                            Specializing in Project & Oversized Freight
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/5.svg" alt="" class="w-5 h-5">
                            Flexible Transit Times and Options
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/6.svg" alt="" class="w-5 h-5">
                            Multimodal Transportation Including Storage
                        </span>
                        <span class="feature-tag">
                            <img src="<?php echo BASE_URL; ?>/assets/images/service/icon/Ocean Freight/7.svg" alt="" class="w-5 h-5">
                            Special Containers: Reefer, Open Top, Flat Rack
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Customs Brokerage 서비스 박스 -->
            <div class="service-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/service/Customs Brokerage.webp" 
                     alt="Customs Brokerage" 
                     class="service-image"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIHZpZXdCb3g9IjAgMCAxMjAwIDQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTIwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNFMzFFMjQiLz48cmVjdCB4PSI0MDAiIHk9IjEwMCIgd2lkdGg9IjQwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iNCIgb3BhY2l0eT0iMC41Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMzYiIGZpbGw9IndoaXRlIj5DdXN0b21zIEJyb2tlcmFnZTwvdGV4dD48L3N2Zz4=';">
                
                <div class="service-content mx-auto">
                    <h3 class="service-title">Customs Brokerage</h3>
                    <p class="service-description">Streamlined customs clearance for seamless international trade.</p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>