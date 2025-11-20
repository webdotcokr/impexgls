<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../config/db-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/about/client.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 데이터베이스에서 클라이언트 정보 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT c.*, cc.category_code, cc.category_name 
    FROM clients c
    JOIN client_categories cc ON c.category_id = cc.id
    WHERE c.is_active = 1 
    ORDER BY cc.sort_order, c.sort_order, c.name
");
$stmt->execute();
$all_clients = $stmt->fetchAll();

// 카테고리별로 그룹화
$clients_by_category = [];
foreach ($all_clients as $client) {
    $clients_by_category[$client['category_code']][] = $client;
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
    
    <style>
        /* CSS variables now defined in global.css */
        
        /* 호버 효과 제거 */
        
        .client-title {
            color: #000;
            font-family: Poppins;
            font-size: 40px;
            font-style: normal;
            font-weight: 700;
            line-height: 56px; /* 140% */
            letter-spacing: -1.2px;
        }
        
        /* h3 PC 스타일 */
        @media (min-width: 769px) {
            .about-page h3 {
                font-size: 16px;
                font-style: normal;
                font-weight: 700;
                line-height: 22px; /* 137.5% */
                letter-spacing: -0.48px;
            }
        }
    </style>
</head>
<body class="about-page">
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'About Us',
        'title' => 'Clients',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Client.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'About us',
        'current_page' => 'Clients',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'About IMPEX GLS', 'url' => BASE_URL . '/pages/about/'],
            ['title' => 'Clients', 'url' => BASE_URL . '/pages/about/clients.php'],
            ['title' => 'Certificates', 'url' => BASE_URL . '/pages/about/certificates.php'],
            ['title' => 'History', 'url' => BASE_URL . '/pages/about/history.php'],
            ['title' => 'ESG', 'url' => BASE_URL . '/pages/about/esg.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section class="pt-20 pb-20 lg:pb-40">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-[25%_75%] gap-12">
                <!-- 좌측: 타이틀 -->
                <div>
                    <h2 class="client-title sticky top-20">Our<br>Customers</h2>
                </div>
                
                <!-- 우측: 카테고리별 로고들 -->
                <div>
                    <?php 
                    // 카테고리 순서 정의
                    $category_order = [
                        'AUDIO EQUIPMENT',
                        'AUTOMOTIVE & PARTS',
                        'AEROSPACE & INDUSTRIAL',
                        'TECHNOLOGY & ELECTRONICS',
                        'MEDICAL & CHEMICAL',
                        'MACHINERY',
                        'CONSUMER GOODS',
                        'FOOD & COSMETICS',
                        'OTHERS'
                    ];
                    
                    foreach ($category_order as $category):
                        if (isset($clients_by_category[$category])):
                    ?>
                    <div class="mb-16">
                        <h3 class="text-sm font-bold mb-6 uppercase"><?php echo $category; ?></h3>
                        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-[14px] md:gap-8">
                            <?php foreach ($clients_by_category[$category] as $client): ?>
                            <div class="client-logo-item text-center">
                                <?php if ($client['logo_path']): ?>
                                    <img src="<?php echo BASE_URL . '/' . $client['logo_path']; ?>" 
                                         alt="<?php echo e($client['name']); ?>" 
                                         class="mx-auto object-contain">
                                <?php else: ?>
                                    <div class="h-12 mx-auto flex items-center justify-center bg-gray-100 rounded px-4">
                                        <span class="text-xs font-medium text-gray-600"><?php echo e($client['name']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    
                    <!-- 하단 안내 문구 -->
                    <div class="mt-32">
                        <p class="text-xs text-gray-500 text-left leading-relaxed" style="font-size: 10px;">
                            * All trademarks, logos, and company names are the property of their respective owners. They are presented solely to illustrate the range of clients Impex GLS has collaborated with. These references do not constitute any endorsement, sponsorship, or affiliation by or with the respective companies. Impex GLS makes no claims of partnership, agency, or representation of the listed entities.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>