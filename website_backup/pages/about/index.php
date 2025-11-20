<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/about/index.php';
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
    
    <!-- CSS variables now defined in global.css -->
    <style>
        /* About Page Specific Styles */
        .about-h2 {
            color: #000;
            text-align: center;
            font-family: Poppins;
            font-size: 40px;
            font-style: normal;
            font-weight: 700;
            line-height: 56px;
            letter-spacing: -1.2px;
            margin-bottom: 48px;
        }
        
        .about-h2.gradient-text {
            text-align: center;
            font-family: Poppins;
            font-size: 50px;
            font-style: normal;
            font-weight: 700;
            letter-spacing: -1.5px;
            background: linear-gradient(90deg, #121E3D 28.58%, #B21525 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .about-h2.mb-20px {
            margin-bottom: 20px;
        }
        
        .about-h2.text-left {
            text-align: left;
        }
        
        @media (max-width: 768px) {
            .about-h2 {
                font-size: 28px;
                line-height: 40px;
                letter-spacing: -0.84px;
                margin-bottom: 32px;
            }
            
            .about-h2.gradient-text {
                font-size: 32px;
                letter-spacing: -1px;
            }
        }
        
        .company-info-grid {
            max-width: 900px;
            margin-left: auto;
            margin-right: 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .company-info-item {
            display: grid;
            grid-template-columns: 200px 1fr;
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 0;
        }
        
        .company-info-item:last-child {
            border-bottom: none;
        }
        
        .company-info-item h3 {
            color: #000;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 700;
            line-height: 27px;
            letter-spacing: -0.48px;
            text-transform: uppercase;
        }
        
        .company-info-item p {
            color: #000;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 36px;
            letter-spacing: -0.48px;
        }
        
        .service-card {
            position: relative;
            overflow: hidden;
            aspect-ratio: 200/280;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        @media (max-width: 768px) {
            .service-card {
                aspect-ratio: 335/255;
            }
        }
        
        .service-card-1 {
            background: linear-gradient(180deg, rgba(2, 64, 80, 0.00) 45.87%, #024050 69.1%), 
                        url('<?php echo BASE_URL; ?>/assets/images/about/offer-1.webp');
            background-position: center, center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        
        .service-card-2 {
            background: linear-gradient(180deg, rgba(200, 121, 91, 0.00) 47.79%, #C8795B 69.17%), 
                        url('<?php echo BASE_URL; ?>/assets/images/about/offer-2.webp');
            background-position: center, center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        
        .service-card-3 {
            background: linear-gradient(180deg, rgba(31, 32, 35, 0.00) 37.91%, #1F2023 69.17%), 
                        url('<?php echo BASE_URL; ?>/assets/images/about/offer-3.webp');
            background-position: center, center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        
        .service-card-4 {
            background: linear-gradient(180deg, rgba(121, 107, 103, 0.00) 43.81%, #796B67 67.99%), 
                        url('<?php echo BASE_URL; ?>/assets/images/about/offer-4.webp');
            background-position: center, center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        
        .service-card-5 {
            background: linear-gradient(180deg, rgba(46, 66, 82, 0.00) 47.79%, #2E4252 67.7%), 
                        url('<?php echo BASE_URL; ?>/assets/images/about/offer-5.webp');
            background-position: center, center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        
        .service-card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            color: white;
        }
        
        .service-card-title {
            color: #FFF;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 600;
            line-height: 22px;
            letter-spacing: -0.48px;
            margin-bottom: 0.5rem;
        }
        
        .service-card-description {
            color: #FFF;
            font-family: Poppins;
            font-size: 12px;
            font-style: normal;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: -0.36px;
            opacity: 0.7;
        }
        
        .mission-circle {
            width: 290px;
            height: 290px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
        }
        
        .mission-number {
            color: #FFF;
            text-align: center;
            font-family: Poppins;
            font-size: 40px;
            font-style: italic;
            font-weight: 400;
            line-height: 26px;
            letter-spacing: -1.2px;
            margin-bottom: -12px;
            opacity: 0.3;
        }
        
        .mission-icon {
            width: 48px;
            height: 48px;
            margin: 1rem 0;
        }
        
        .mission-icon img {
            width: 100%;
            height: 100%;
            filter: brightness(0) invert(1);
        }
        
        .about-page .mission-title {
            color: #FFF;
            text-align: center;
            font-family: Poppins;
            font-size: 20px;
            font-style: normal;
            font-weight: 500;
            line-height: 26px;
            letter-spacing: -0.6px;
            text-transform: uppercase;
        }
        
        @media (max-width: 768px) {
            .company-info-grid {
                max-width: 100%;
            }
            
            .company-info-item {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .company-info-item h3 {
                font-size: 14px;
                font-style: normal;
                font-weight: 700;
                line-height: 27px; /* 192.857% */
                letter-spacing: -0.42px;
            }
            
            .company-info-item p {
                font-size: 15px;
                font-style: normal;
                font-weight: 400;
                line-height: 36px; /* 240% */
                letter-spacing: -0.45px;
            }
            
            /* body-lg 클래스 모바일 스타일 */
            .body-lg {
                font-size: 14px !important;
                font-style: normal;
                font-weight: 400;
                line-height: 159%; /* 22.26px */
                letter-spacing: -0.28px;
            }
            
            .service-card-content {
                padding: 1.5rem;
            }
            
            .mission-circle {
                width: 165px;
                height: 165px;
                padding: 1.5rem;
            }
            
            .mission-number {
                font-size: 32px;
            }
            
            .mission-icon {
                width: 20px;
                height: 20px;
            }
            
            .about-page .mission-title {
                font-size: 16px;
                line-height: 22px;
            }
            
            /* 세계 지도 모바일 margin */
            .world-map-container {
                margin-bottom: 0 !important;
            }
            
            /* Our Promise 섹션 모바일 패딩 */
            .promise-section {
                padding-top: 60px !important;
                padding-bottom: 80px !important;
            }
            
            /* 서비스 카드 모바일 배경 이미지 */
            .service-card-1 {
                background: linear-gradient(180deg, rgba(2, 64, 80, 0.00) 45.87%, #024050 69.1%), 
                            url('<?php echo BASE_URL; ?>/assets/images/about/offer-1-mo.webp');
            }
            
            .service-card-2 {
                background: linear-gradient(180deg, rgba(200, 121, 91, 0.00) 47.79%, #C8795B 69.17%), 
                            url('<?php echo BASE_URL; ?>/assets/images/about/offer-2-mo.webp');
            }
            
            .service-card-3 {
                background: linear-gradient(180deg, rgba(31, 32, 35, 0.00) 37.91%, #1F2023 69.17%), 
                            url('<?php echo BASE_URL; ?>/assets/images/about/offer-3-mo.webp');
            }
            
            .service-card-4 {
                background: linear-gradient(180deg, rgba(121, 107, 103, 0.00) 43.81%, #796B67 67.99%), 
                            url('<?php echo BASE_URL; ?>/assets/images/about/offer-4-mo.webp');
            }
            
            .service-card-5 {
                background: linear-gradient(180deg, rgba(46, 66, 82, 0.00) 47.79%, #2E4252 67.7%), 
                            url('<?php echo BASE_URL; ?>/assets/images/about/offer-5-mo.webp');
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
        'title' => 'About IMPEX GLS',
        'background' => BASE_URL . '/assets/images/subpage-header-image/about.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'About us',
        'current_page' => 'About IMPEX GLS',
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
            <!-- Global Logistics Excellence -->
            <div class="mb-20">
                <h2 class="about-h2 mb-20px text-left">Global Logistics<br>Excellence Since 1997</h2>
                <p class="body-lg mb-12" style="color: #000;">
                    Delivering outstanding supply chain solutions <br/>
                    through innovation, expertise, and distinction.
                </p>
                
                <!-- 세계 지도 -->
                <div class="relative mb-16 world-map-container">
                    <picture>
                        <source media="(max-width: 768px)" srcset="<?php echo BASE_URL; ?>/assets/images/about/map-mo.webp">
                        <img src="<?php echo BASE_URL; ?>/assets/images/about/map.webp" alt="Global Network" class="w-full">
                    </picture>
                </div>
                
                <!-- 회사 정보 그리드 -->
                <div class="company-info-grid">
                    <div class="company-info-item">
                        <h3>COMPANY NAME</h3>
                        <p>IMPEX GLS INC.</p>
                    </div>
                    <div class="company-info-item">
                        <h3>HQ ADDRESS</h3>
                        <p>2475 Touhy Avenue Suite 100, Elk Grove Village, IL 60007</p>
                    </div>
                    <div class="company-info-item">
                        <h3>ESTABLISHED</h3>
                        <p>5/27/1997</p>
                    </div>
                    <div class="company-info-item">
                        <h3>LICENSE</h3>
                        <p>
                            NVOCC (US FMC / Federal Maritime Committee)<br>
                            Air Cargo Transportation Agency (IATA / Int'l Air Transport Association)<br>
                            C-TPAT (US Customs and Border Protection)<br>
                            Indirect Air Carrier (US TSA / Transportation Security Administration)<br>
                            D-U-N-S (D&B / Dun & Bradstreet)<br>
                            Dangerous Goods Handling Certified (IATA / Global Transportation Training Services)<br>
                            NCBFAA (National Customs Brokers and Forwarders Association of America)<br>
                            GAA (Global Affinity Alliance)<br>
                        </p>
                    </div>
                    <div class="company-info-item">
                        <h3>SERVICES</h3>
                        <p>International Freight Forwarding<br>
                        Contract Logistics<br>
                        Supply Chain Management<br>
                        Special Products<br>
                        Other Value-Added Services<br>
                        Customs Brokerage</p>
                    </div>
                    <div class="company-info-item">
                        <h3>ORGANIZATION</h3>
                        <p>US Branch : Atlanta, Chicago, Dallas, Los Angeles, Miami, New York, Seattle<br>
                        Global : Frankfurt, Hamburg, Toronto, Mexico City, Guadalajara, Seoul<br>
                    </p>
                    </div>
                    <div class="company-info-item">
                        <h3>MAIN CONTACT</h3>
                        <p>hq@impexgls.com<br>
                        630-227-9300</p>
                    </div>
                    <div class="company-info-item">
                        <h3>HOMEPAGE</h3>
                        <p>www.impexgls.com</p>
                    </div>
                </div>
            </div>
            
            <!-- What We Offer -->
            <div class="mb-20">
                <h2 class="about-h2" style="margin-bottom: 30px;">What We Offer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-0">
                    <?php
                    $services = [
                        [
                            'title' => 'International<br>Freight Forwarding',
                            'description' => 'Air & ocean freight tailored to <br class="md:hidden">global trade demands.'
                        ],
                        [
                            'title' => 'Contract Logistics',
                            'description' => 'Warehousing & distribution<br class="md:hidden">solutions optimizing<br class="md:hidden">your supply chain.'
                        ],
                        [
                            'title' => 'Supply Chain<br>Management',
                            'description' => 'Industry-specific, customized<br class="md:hidden">logistics strategies.'
                        ],
                        [
                            'title' => 'Specialized Cargo<br>Handling',
                            'description' => 'Expert solutions for exhibitions, <br class="md:hidden">defense logistics, and more.'
                        ],
                        [
                            'title' => 'Value-Added Services',
                            'description' => 'Insurance, packaging, and <br class="md:hidden">financial services enhancing your <br class="md:hidden">logistics efficiency.'
                        ]
                    ];
                    
                    foreach($services as $index => $service):
                    ?>
                    <div class="service-card service-card-<?php echo $index + 1; ?>">
                        <div class="service-card-content">
                            <div class="service-card-title"><?php echo $service['title']; ?></div>
                            <div class="service-card-description"><?php echo $service['description']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Our Promise Section -->
    <section style="background-color: #F3F4F8; padding-top: 120px; padding-bottom: 170px;" class="promise-section">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20">
                <p class="body-lg text-gray-500 mb-4">Our Promise</p>
                <h2 class="about-h2" style="margin-bottom: 20px;">Trusted Logistics Partner,<br>Always Striving for More</h2>
                <p class="body-lg max-w-3xl mx-auto" style="color: #000; text-align: center;">
                    With a proven track record, IMPEX GLS is dedicated to innovation, reliability, <br/>
                    and consistently exceeding client expectations with every shipment.
                </p>
            </div>
            
            <!-- Our Mission -->
            <div>
                <h2 class="about-h2 gradient-text">OUR MISSION</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php
                    $missions = [
                        [
                            'number' => '01',
                            'icon' => 'mission-1.svg',
                            'title' => 'MAINTAIN<br>COMPETITIVE RATES',
                            'bgColor' => '#212328'
                        ],
                        [
                            'number' => '02',
                            'icon' => 'mission-2.svg',
                            'title' => 'EFFECTIVELY<br>TRAIN EMPLOYEES',
                            'bgColor' => '#071537'
                        ],
                        [
                            'number' => '03',
                            'icon' => 'mission-3.svg',
                            'title' => 'UPHOLD<br>POSITIVE ATTITUDE',
                            'bgColor' => '#9E0C1B'
                        ],
                        [
                            'number' => '04',
                            'icon' => 'mission-4.svg',
                            'title' => 'PROVIDE<br>CUSTOMIZED LOGISTICS<br>SOLUTIONS',
                            'bgColor' => '#22304A'
                        ]
                    ];
                    
                    foreach($missions as $mission):
                    ?>
                    <div class="text-center">
                        <div class="mission-circle" style="background-color: <?php echo $mission['bgColor']; ?>;">
                            <div class="mission-number"><?php echo $mission['number']; ?></div>
                            <div class="mission-icon">
                                <img src="<?php echo BASE_URL; ?>/assets/images/about/<?php echo $mission['icon']; ?>" alt="Mission icon">
                            </div>
                            <h3 class="mission-title"><?php echo $mission['title']; ?></h3>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>