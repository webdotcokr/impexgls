<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/about/esg.php';
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
    
    <style>
        /* CSS variables now defined in global.css */
        
        /* ESG 섹션 타이틀 스타일 */
        .esg-title {
            color: #000;
            font-family: Poppins;
            font-size: 36px;
            font-style: normal;
            font-weight: 700;
            line-height: 56px; /* 155.556% */
            letter-spacing: -1.08px;
        }
        
        .esg-description {
            color: #000;
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 400;
            line-height: 26px; /* 144.444% */
            letter-spacing: -0.54px;
        }
        
        .esg-image {
            border-radius: 0;
        }
        
        .esg-card {
            transition: all 0.3s ease;
        }
        
        .esg-card:hover {
            transform: translateY(-5px);
        }
        
        .esg-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
        }
        
        .esg-card h3 {
            color: var(--primary, #9E0C1B);
            text-align: center;
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 26px; /* 144.444% */
            letter-spacing: -0.54px;
            margin-bottom: 0.75rem;
        }
        
        .esg-card p {
            color: var(--zinc-600, #52525B);
            text-align: center;
            font-family: Poppins;
            font-size: 13px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px; /* 153.846% */
            letter-spacing: -0.5px;
        }
        
        .esg-grid {
            display: grid;
            column-gap: 66px;
            row-gap: 110px;
        }
        
        .esg-bottom-section {
            margin-top: 110px;
        }
        
        /* ESG 페이지 section-header 중앙 정렬 */
        .section-header {
            text-align: center;
        }
        
        @media (min-width: 1024px) {
            .esg-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 1023px) {
            .esg-grid {
                grid-template-columns: repeat(2, 1fr);
                column-gap: 30px;
                row-gap: 60px;
            }
        }
        
        @media (max-width: 768px) {
            .esg-description {
                font-size: 14px;
                font-style: normal;
                font-weight: 400;
                line-height: 159%; /* 22.26px */
                letter-spacing: -0.28px;
            }
            
            .esg-card h3 {
                font-size: 16px;
                font-style: normal;
                font-weight: 700;
                line-height: 26px; /* 162.5% */
                letter-spacing: -0.48px;
            }
            
            .esg-card p {
                font-size: 13px;
                font-style: normal;
                font-weight: 400;
                line-height: 20px;
                letter-spacing: -0.5px;
            }
            
            /* 이미지 텍스트 간격 */
            .grid.gap-12 {
                gap: 30px;
            }
            
            /* ESG 카드 1열 배치 */
            .esg-grid {
                grid-template-columns: 1fr !important;
                row-gap: 30px;
            }
            
            /* ESG 카드 최대 너비 */
            .esg-card {
                max-width: 80%;
                margin: 0 auto;
            }
            
            /* ESG 아이콘 모바일 스타일 */
            .esg-icon {
                width: 120px;
                height: 120px;
                padding-bottom: 8px;
            }
            
            /* ESG 하단 섹션 모바일 */
            .esg-bottom-section {
                margin-top: 32px;
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
        'title' => 'ESG',
        'background' => BASE_URL . '/assets/images/subpage-header-image/ESG.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'About us',
        'current_page' => 'ESG',
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
            <!-- 첫 번째 줄: ESG 이미지와 Our commitment -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20 lg:mb-[120px]">
                <!-- 왼쪽: ESG 이미지 (모바일에서는 두 번째로) -->
                <div class="order-2 lg:order-1">
                    <img src="<?php echo BASE_URL; ?>/assets/images/esg/esg-1.webp" 
                         alt="ESG" 
                         class="w-full esg-image">
                </div>
                
                <!-- 오른쪽: Our commitment (모바일에서는 첫 번째로) -->
                <div class="order-1 lg:order-2">
                    <h2 class="esg-title mb-4">Our Commitment to<br>Ethics and Compliance</h2>
                    <p class="esg-description">
                        At IMPEX GLS, we operate with unwavering ethical standards and full respect for all laws and international regulations. Our team ensures ongoing compliance through continuous monitoring of the regulatory landscape.
                    </p>
                </div>
            </div>
            
            <!-- 두 번째 줄: 이미지와 Strong corporate -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20 lg:mb-[120px]">
                            

                
                <!-- 오른쪽: Strong corporate -->
                <div>
                    <h3 class="esg-title mb-4">A Strong Corporate<br>Ethics and Compliance Program</h3>
                    <p class="esg-description mb-6">
                        IMPEX GLS maintains a comprehensive approach to governance, integrating business operations, corporate security, operational controls, compliance, and human resources.
                    </p>
                    
                    <p class="esg-description">We adhere strictly to international regulations, with a special focus on competition, data protection, export controls, anti-money laundering, anti-corruption, and antitrust requirements.</p>
                </div>
                                <!-- 왼쪽: 이미지 -->
                <div>
                    <img src="<?php echo BASE_URL; ?>/assets/images/esg/esg-2.webp" 
                         alt="ESG Commitment" 
                         class="w-full esg-image">
                </div>
            </div>
            
            <!-- 하단: ESG 컴포넌트 -->
            <div class="section-header">
                <h2>The Seven Components of Our Ethics<br>and Compliance Approach</h2>
                <p>We are committed to ethics and compliance across seven key areas.</p>
            </div>
            
            <!-- 상단 4개 그리드 -->
            <div class="esg-grid mb-0">
                <?php
                $esg_components_top = [
                    [
                        'title' => 'Management Commitment',
                        'description' => 'Senior Management Commitment to Promote and Support an Effective Ethics and Compliance Program and Foster a Culture of Integrity',
                        'svg_file' => 'Management Commitment.svg'
                    ],
                    [
                        'title' => 'Code of Ethics',
                        'description' => 'Standards, Rules, and Internal Policies to Guide Employees in Daily Ethics and Compliance Matters',
                        'svg_file' => 'Code of Ethics.svg'
                    ],
                    [
                        'title' => 'Dedicated Resources',
                        'description' => 'Qualified Ethics and Compliance Specialists with Direct Access to Executive Management or the Board of Directors',
                        'svg_file' => 'Dedicated Resources.svg'
                    ],
                    [
                        'title' => 'Risk Assessment',
                        'description' => 'Risk Assessment Programs to Identify and Address Compliance Weaknesses, Including Internal Audits',
                        'svg_file' => 'Risk Assessment.svg'
                    ]
                ];
                
                foreach ($esg_components_top as $component):
                ?>
                <div class="esg-card bg-white rounded-lg text-center">
                    <div class="esg-icon">
                        <img src="<?php echo BASE_URL; ?>/assets/images/esg/<?php echo $component['svg_file']; ?>" 
                             alt="<?php echo $component['title']; ?>" 
                             class="w-full h-full object-contain">
                    </div>
                    <h3><?php echo $component['title']; ?></h3>
                    <p><?php echo $component['description']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 하단 3개 그리드 (중앙 정렬) -->
            <div class="flex justify-center esg-bottom-section">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-[66px] gap-y-[30px] lg:gap-y-0 max-w-full lg:max-w-[75%]">
                    <?php
                    $esg_components_bottom = [
                        [
                            'title' => 'Educational Program',
                            'description' => 'Educational Programs to Ensure Employees Are Informed and Trained for Legal Compliance',
                            'svg_file' => 'Educational Program.svg'
                        ],
                        [
                            'title' => 'Disciplinary Procedures',
                            'description' => 'Enforcement of Disciplinary Actions and Sanctions for Compliance Violations',
                            'svg_file' => 'Disciplinary Procedures.svg'
                        ],
                        [
                            'title' => 'Suppliers and Partners',
                            'description' => 'Requirements for Contractor and Supplier Programs with Ethics Components Aligned to Company Standards',
                            'svg_file' => 'Suppliers and Partners.svg'
                        ]
                    ];
                    
                    foreach ($esg_components_bottom as $component):
                    ?>
                    <div class="esg-card bg-white rounded-lg text-center">
                        <div class="esg-icon">
                            <img src="<?php echo BASE_URL; ?>/assets/images/esg/<?php echo $component['svg_file']; ?>" 
                                 alt="<?php echo $component['title']; ?>" 
                                 class="w-full h-full object-contain">
                        </div>
                        <h3 class="font-bold text-lg mb-3 text-red-600"><?php echo $component['title']; ?></h3>
                        <p class="text-sm text-gray-600 leading-relaxed"><?php echo $component['description']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>