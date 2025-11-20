<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/about/history.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 히스토리 데이터
$history_items = [
    ['year' => '2024', 'event' => 'Joined JCtrans Network'],
    ['year' => '2023', 'event' => 'Guadalajara (Mexico) Office Established'],
    ['year' => '2019', 'event' => 'Milano (Italy) Office Established'],
    ['year' => '2017', 'event' => 'Mexico City (Mexico) Office Established'],
    ['year' => '2016', 'event' => 'Joined Global Affinity Alliance (WCA Network)'],
    ['year' => '2014', 'event' => 'Frankfurt, Hamburg (Europe) Office Established'],
    ['year' => '2013', 'event' => 'USA West Region Logistic Center Opened (LAX W/H)'],
    ['year' => '2010', 'event' => 'Joined at NCBFAA'],
    ['year' => '2009', 'event' => 'Seoul Liaison Office Established'],
    ['year' => '2008', 'event' => 'C-TPAT Approved (Account # 30184836)'],
    ['year' => '2005', 'event' => [
        'Company Name Changed to IMPEX GLS, INC.',
        '5 US Branches formally established at Atlanta, Chicago, Dallas, <br/>Los Angeles, New York under IMPEX',
        'FMC NVOCC License Obtained',
        'Established affiliated agencies in Canada (YYZ) & Mexico (MEX)'
    ]],
    ['year' => '2003', 'event' => 'Atlanta Office Established'],
    ['year' => '2002', 'event' => 'Headquarters Established'],
    ['year' => '2000', 'event' => 'IMPEX Transport Inc. (Assumed Corporate Name)<br/>Los Angeles Office Established'],
    ['year' => '1998', 'event' => 'IATA License (0118840) obtained'],
    ['year' => '1997', 'event' => 'Founded, World Bright Inc. (850 Dillon Dr. Wood Dale IL 60191 USA)']
];
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
        
        .timeline-item {
            display: flex;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .timeline-item:last-child {
            border-bottom: none;
        }
        
        .timeline-year {
            color: #000;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 700;
            line-height: 27px;
            letter-spacing: -0.48px;
            width: 100px;
            flex-shrink: 0;
        }
        
        .timeline-content {
            color: #000;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 27px;
            letter-spacing: -0.48px;
            flex: 1;
        }
        
        /* History 헤더 스타일 */
        .history-title {
            color: #000;
            font-family: Poppins;
            font-size: 40px;
            font-style: normal;
            font-weight: 700;
            line-height: 56px; /* 140% */
            letter-spacing: -1.2px;
        }
        
        .history-description {
            color: #000;
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 500;
            line-height: 26px; /* 144.444% */
            letter-spacing: -0.54px;
        }
        
        @media (max-width: 768px) {
            .timeline-year {
                width: 80px;
            }
            
            .history-description {
                font-size: 14px;
                font-style: normal;
                font-weight: 400;
                line-height: 159%; /* 22.26px */
                letter-spacing: -0.28px;
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
        'title' => 'History',
        'background' => BASE_URL . '/assets/images/subpage-header-image/History.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'About us',
        'current_page' => 'History',
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
            <!-- 상단: 헤더 섹션 -->
            <div class="mb-16">
                <h2 class="history-title mb-4">Built on Experience,<br>Driven by Innovation</h2>
                <p class="history-description">
                    With a foundation built on resilience and growth, IMPEX GLS continues to <br/>
                    drive innovation and set new standards in the logistics industry.
                </p>
            </div>
            
            <!-- 하단: 이미지와 타임라인 -->
            <div class="grid grid-cols-1 lg:grid-cols-[35%_65%] gap-12 lg:gap-[160px]">
                <!-- 왼쪽: 이미지들 -->
                <div>
                    <div class="space-y-6">
                        <img src="<?php echo BASE_URL; ?>/assets/images/history-1.webp" 
                             alt="Global Network" 
                             class="w-full rounded-lg shadow-lg">
                        
                        <img src="<?php echo BASE_URL; ?>/assets/images/history-2.webp" 
                             alt="Air Freight" 
                             class="w-full rounded-lg shadow-lg">
                    </div>
                </div>
                
                <!-- 오른쪽: 타임라인 -->
                <div>
                    <div class="timeline">
                        <?php foreach ($history_items as $item): ?>
                        <div class="timeline-item">
                            <div class="timeline-year"><?php echo $item['year']; ?></div>
                            <div class="timeline-content">
                                <?php 
                                if (is_array($item['event'])) {
                                    foreach ($item['event'] as $event) {
                                        echo $event . '<br/>';
                                    }
                                } else {
                                    echo $item['event'];
                                }
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>