<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/networks/global.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// Global 지점 정보 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM network_locations 
    WHERE location_type = 'global' AND is_active = 1 
    ORDER BY sort_order, id
");
$stmt->execute();
$global_locations = $stmt->fetchAll();

// Main office와 Affiliated Agency 분리
$main_offices = [];
$affiliated_agencies = [];

foreach ($global_locations as $location) {
    if (strpos($location['office_name'], '(Affiliated Agency)') !== false) {
        $affiliated_agencies[] = $location;
    } else {
        $main_offices[] = $location;
    }
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/networks.css">
    
    <style>
        /* CSS variables now defined in global.css */
        
        .world-map-container {
            position: relative;
            margin: 0 auto;
        }
        
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Networks',
        'title' => 'Global',
        'background' => BASE_URL . '/assets/images/subpage-header-image/networks.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Networks',
        'current_page' => 'Global',
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
        <!-- 지도 섹션 - 전체 너비 회색 배경 -->
        <div class="w-full" style="background-color: #F3F4F8;">
            <div class="container mx-auto px-4 py-20 map-section-container">
                <!-- 세계 지도 -->
                <div class="world-map-container">
                    <picture>
                        <source media="(max-width: 768px)" srcset="<?php echo BASE_URL; ?>/assets/images/networks/global-mo.webp">
                        <img src="<?php echo BASE_URL; ?>/assets/images/networks/global.webp" alt="Global Network Map" class="w-full h-auto">
                    </picture>
                </div>
            </div>
        </div>
        
        <!-- 지점 정보 섹션 -->
        <div class="container mx-auto px-4 py-20 location-info-section">
            <?php if (!empty($main_offices)): ?>
            <!-- Main Offices -->
            <div class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-8">
                    <?php foreach ($main_offices as $location): ?>
                    <div class="location-card">
                        <h3><?php echo formatOfficeName($location['office_name']); ?></h3>
                    
                    <ul class="location-info-list">
                        <?php if (!empty($location['address'])): ?>
                        <li class="location-info-item">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/location.png" alt="Location" class="location-info-icon">
                            <span><?php echo e_nl2br($location['address']); ?></span>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($location['email'])): ?>
                        <li class="location-info-item">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/emial.png" alt="Email" class="location-info-icon">
                            <a href="mailto:<?php echo e($location['email']); ?>">
                                <?php echo e($location['email']); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($location['phone'])): ?>
                        <li class="location-info-item">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/phone.png" alt="Phone" class="location-info-icon">
                            <?php if ($location['phone'] === 'TBA'): ?>
                                <span><?php echo e($location['phone']); ?></span>
                            <?php else: ?>
                                <a href="tel:<?php echo str_replace([' ', '-', '.'], '', $location['phone']); ?>">
                                    <?php echo e($location['phone']); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($location['fax'])): ?>
                        <li class="location-info-item">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/fax.png" alt="Fax" class="location-info-icon">
                            <span><?php echo e($location['fax']); ?></span>
                        </li>
                        <?php endif; ?>
                        
                        <!-- 시설 정보 -->
                        <?php if (!empty($location['facility_info'])): ?>
                        <li class="location-info-item">
                            <img src="<?php echo BASE_URL; ?>/assets/images/networks/facilty.png" alt="Facility" class="location-info-icon">
                            <span><?php echo e_nl2br($location['facility_info']); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($affiliated_agencies)): ?>
            <!-- Affiliated Agencies -->
            <div class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-8">
                    <?php foreach ($affiliated_agencies as $location): ?>
                    <div class="location-card">
                        <h3><?php echo formatOfficeName($location['office_name']); ?></h3>
                        
                        <ul class="location-info-list">
                            <?php if (!empty($location['address'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/location.png" alt="Location" class="location-info-icon">
                                <span><?php echo e_nl2br($location['address']); ?></span>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($location['email'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/emial.png" alt="Email" class="location-info-icon">
                                <a href="mailto:<?php echo e($location['email']); ?>">
                                    <?php echo e($location['email']); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($location['phone'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/phone.png" alt="Phone" class="location-info-icon">
                                <?php if ($location['phone'] === 'TBA'): ?>
                                    <span><?php echo e($location['phone']); ?></span>
                                <?php else: ?>
                                    <a href="tel:<?php echo str_replace([' ', '-', '.'], '', $location['phone']); ?>">
                                        <?php echo e($location['phone']); ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (!empty($location['fax'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/fax.png" alt="Fax" class="location-info-icon">
                                <span><?php echo e($location['fax']); ?></span>
                            </li>
                            <?php endif; ?>
                            
                            <!-- 시설 정보 -->
                            <?php if (!empty($location['facility_info'])): ?>
                            <li class="location-info-item">
                                <img src="<?php echo BASE_URL; ?>/assets/images/networks/facilty.png" alt="Facility" class="location-info-icon">
                                <span><?php echo e_nl2br($location['facility_info']); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>