<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../config/db-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/about/certificate.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 데이터베이스에서 인증서 정보 가져오기
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM certificates 
    WHERE is_active = 1 
    ORDER BY sort_order
");
$stmt->execute();
$certificates = $stmt->fetchAll();
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
        
        .certificate-card {
            transition: all 0.3s ease;
        }
        
        .certificate-card:hover {
            transform: translateY(-10px);
        }
        
        .certificate-info h3 {
            color: #000;
            font-family: Poppins;
            font-size: 20px;
            font-style: normal;
            font-weight: 700;
            line-height: 28px; /* 140% */
            letter-spacing: -0.6px;
            margin-bottom: 0.5rem;
        }
        
        .certificate-info p {
            color: #777986;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 22px; /* 137.5% */
            letter-spacing: -0.48px;
        }
        
        /* 모바일 스타일 */
        @media (max-width: 768px) {
            /* 헤더 설명 텍스트 */
            .text-lg.text-gray-600 {
                font-size: 14px !important;
                font-style: normal;
                font-weight: 400;
                line-height: 159%; /* 22.26px */
                letter-spacing: -0.28px;
            }
            
            /* 인증서 정보 제목 */
            .certificate-info h3 {
                font-size: 16px;
                font-style: normal;
                font-weight: 700;
                line-height: 24px; /* 150% */
                letter-spacing: -0.48px;
            }
            
            /* 인증서 정보 설명 */
            .certificate-info p {
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: 18px; /* 150% */
                letter-spacing: -0.36px;
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
        'title' => 'Certificates',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Certificate.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'About us',
        'current_page' => 'Certificate',
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
            <!-- 헤더 텍스트 -->
            <div class="mb-16 text-left">
                <h2 class="text-4xl font-bold mb-4">Global Standards, Certified Excellence</h2>
                <p class="text-lg text-gray-600">
                    IMPEX GLS adheres to the highest international logistics standards,<br>
                    ensuring compliance, security, and operational excellence.
                </p>
            </div>
            
            <!-- 인증서 그리드 -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-[30px] md:gap-x-[30px] md:gap-y-[80px]">
                <?php foreach ($certificates as $cert): ?>
                <div class="certificate-card bg-white rounded-lg">
                    <?php if ($cert['image_path']): ?>
                    <div class="cert-logo mb-6 flex items-start">
                        <img src="<?php echo BASE_URL . '/' . $cert['image_path']; ?>" 
                             alt="<?php echo e($cert['title']); ?>" 
                             class="max-h-full object-contain">
                    </div>
                    <?php endif; ?>
                    <div class="certificate-info">
                        <h3><?php echo e($cert['title']); ?></h3>
                        <?php if ($cert['description']): ?>
                        <p><?php echo e_nl2br($cert['description']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>