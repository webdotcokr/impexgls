<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/networks/headquarters.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 본사 정보 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM network_locations 
    WHERE location_type = 'headquarters' AND is_active = 1 
    ORDER BY sort_order
    LIMIT 1
");
$stmt->execute();
$headquarters = $stmt->fetch();
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/networks.css">
    
    <style>
        /* CSS variables now defined in global.css */
        
        .usa-map {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* 모바일 스타일 */
        @media (max-width: 768px) {
            .headquarters-title {
                font-size: 18px !important;
                font-style: normal;
                font-weight: 700;
                line-height: 32px; /* 177.778% */
                letter-spacing: -0.54px;
            }
            
            .headquarters-description {
                font-size: 12px !important;
                font-style: normal;
                font-weight: 400;
                line-height: 20px; /* 166.667% */
                letter-spacing: -0.36px;
            }
            
            .headquarters-info-box {
                padding: 20px !important;
                margin: 0 -12px;
                border-radius: 0 !important;
                margin-left: 16px;
                margin-right: 16px;
            }
            
            .map-section-container {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
        }
        
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Networks',
        'title' => 'Headquarters',
        'background' => BASE_URL . '/assets/images/subpage-header-image/networks.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Networks',
        'current_page' => 'Headquarters',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Headquarters', 'url' => BASE_URL . '/pages/networks/headquarters.php'],
            ['title' => 'USA', 'url' => BASE_URL . '/pages/networks/usa.php'],
            ['title' => 'Global', 'url' => BASE_URL . '/pages/networks/global.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section>
        <!-- 지도와 정보 섹션 - 전체 너비 회색 배경 -->
        <div class="w-full" style="background-color: #F3F4F8;">
            <div class="container mx-auto px-4 py-20 map-section-container">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- 왼쪽: 지도 -->
                    <div class="usa-map">
                        <picture>
                            <source media="(max-width: 768px)" srcset="<?php echo BASE_URL; ?>/assets/images/networks/Headquarters-mo.webp">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/Headquarters.webp" alt="USA Headquarters Map" class="w-full h-auto">
                        </picture>
                    </div>
                    
                    <!-- 오른쪽: 정보 -->
                    <div class="headquarters-info-box" style="background: #fff; padding: 40px; border-radius: 8px;">
                        <h2 class="headquarters-title text-3xl font-bold mb-6">Headquarters</h2>
                        <p class="headquarters-description text-gray-600 mb-8">
                        IMPEX GLS customers can expect the same high level of quality service across all branches, supported by our headquarters through strategic planning, compliance training, marketing, corporate communication, and the establishment and implementation of company-wide policies. For any issues or concerns, please contact IMPEX GLS Headquarters.
                        </p>
                    
                    <ul class="location-info-list">
                        <?php if ($headquarters): ?>
                            <?php if (!empty($headquarters['address'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/location.png" alt="Location" class="location-info-icon">
                                <span><?php echo e($headquarters['address']); ?></span>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($headquarters['email'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/emial.png" alt="Email" class="location-info-icon">
                                <a href="mailto:<?php echo e($headquarters['email']); ?>"><?php echo e($headquarters['email']); ?></a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($headquarters['phone'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/phone.png" alt="Phone" class="location-info-icon">
                                <a href="tel:<?php echo str_replace([' ', '-', '.'], '', $headquarters['phone']); ?>"><?php echo e($headquarters['phone']); ?></a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($headquarters['fax'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/fax.png" alt="Fax" class="location-info-icon">
                                <span><?php echo e($headquarters['fax']); ?></span>
                            </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Fallback if no data from database -->
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/location.png" alt="Location" class="location-info-icon">
                                <span>2475 Touhy Avenue Suite 100 Elk Grove Village, IL 60007</span>
                            </li>
                            
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/emial.png" alt="Email" class="location-info-icon">
                                <a href="mailto:HQ@IMPEXGLS.COM" class="text-gray-700 hover:text-red-600">HQ@IMPEXGLS.COM</a>
                            </li>
                            
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/phone.png" alt="Phone" class="location-info-icon">
                                <a href="tel:6302279300" class="text-gray-700 hover:text-red-600">630-227-9300</a>
                            </li>
                            
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/fax.png" alt="Fax" class="location-info-icon">
                                <span>630-227-9345</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>