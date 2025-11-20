<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/networks/usa.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// USA 지점 정보 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM network_locations 
    WHERE location_type = 'usa' AND is_active = 1 
    ORDER BY sort_order, id
");
$stmt->execute();
$usa_locations = $stmt->fetchAll();

// Main office와 Satellite office 분리
$main_offices = [];
$satellite_offices = [];

foreach ($usa_locations as $location) {
    if (strpos($location['office_name'], '(Satellite Office)') !== false) {
        $satellite_offices[] = $location;
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
        
        .usa-map-container {
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
        'title' => 'USA',
        'background' => BASE_URL . '/assets/images/subpage-header-image/networks.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Networks',
        'current_page' => 'USA',
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
                <!-- 미국 지도 -->
                <div class="usa-map-container">
                    <picture>
                        <source media="(max-width: 768px)" srcset="<?php echo BASE_URL; ?>/assets/images/networks/usa-mo.webp">
                        <img src="<?php echo BASE_URL; ?>/assets/images/networks/usa.webp" alt="USA Network Map" class="w-full h-auto">
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
                            <a href="tel:<?php echo str_replace([' ', '-', '.'], '', $location['phone']); ?>">
                                <?php echo e($location['phone']); ?>
                            </a>
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
                            <span><?php echo nl2br(e($location['facility_info'])); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($satellite_offices)): ?>
            <!-- Satellite Offices -->
            <div class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-8">
                    <?php foreach ($satellite_offices as $location): ?>
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
                                <a href="tel:<?php echo str_replace([' ', '-', '.'], '', $location['phone']); ?>">
                                    <?php echo e($location['phone']); ?>
                                </a>
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
                                <span><?php echo nl2br(e($location['facility_info'])); ?></span>
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