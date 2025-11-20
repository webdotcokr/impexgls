<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/config.php';
require_once 'config/meta-config.php';
require_once 'includes/functions.php';
require_once 'config/db-config.php';

// 현재 페이지의 메타 정보 가져오기
$current_page = basename($_SERVER['PHP_SELF']);
$page_meta_info = isset($page_meta[$current_page]) ? array_merge($meta_defaults, $page_meta[$current_page]) : $meta_defaults;
?>
<!DOCTYPE html>
<html lang="ko">
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
    
    <!-- 반응형 스타일 -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <style>
        /* CSS variables now defined in global.css */
        :root {
            --font-en: 'Poppins', sans-serif;
            --font-kr: 'Pretendard', sans-serif;
        }
        
        body {
            font-family: var(--font-kr);
        }
        
        .font-en {
            font-family: var(--font-en);
        }
        
        /* 메인 페이지 전용 스타일 */
        body.is-home .pc-header:not(.scrolled) {
            background-color: transparent;
        }
        
        body.is-home .pc-header:not(.scrolled) .pc-nav-link {
            color: white;
        }
        
        body.is-home .pc-header:not(.scrolled) .logo-pc svg text {
            fill: white;
        }
        
        body.is-home .pc-header:not(.scrolled) .logo-pc svg g {
            fill: white;
        }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/hero-video.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/hero-mobile.css">
</head>
<body class="is-home">
    <?php include 'includes/header.php'; ?>
    
    <!-- 메인 비주얼 (히어로 섹션) -->
    <section class="hero-section relative h-screen min-h-[600px] overflow-hidden -mt-16 lg:-mt-[125px]">
        <!-- 비디오 배경 -->
        <div class="hero-video-wrapper absolute inset-0 z-0">
            <!-- 비디오 컨테이너 (4개 영상) -->
            <div class="hero-video-container absolute inset-0" id="heroVideoContainer">
                <!-- 영상들은 JavaScript로 동적 생성 -->
            </div>
            <div class="hero-overlay absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-black/40"></div>
        </div>
        
        <!-- 히어로 콘텐츠 -->
        <div class="hero-content relative z-10 h-full flex items-end">
            <div class="container mx-auto px-4">
                <div class="hero-text">
                    <!-- <span class="hero-label text-white mb-2 md:mb-4 block">FAST</span> -->
                    <h1 class="hero-title text-white mb-4 md:mb-6 font-en">
                        New Standard in Global Logistics <br/>
                        IMPEX GLS<br/>
                    </h1>
                    <p class="hero-subtitle text-white/90 mb-8">
                         Global Excellence in Supply Chain Management
                    </p>
                    
                    <!-- 비디오 컨트롤 -->
                    <div class="video-controls-wrapper inline-flex items-center gap-4">
                        <button class="video-play-pause w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center hover:bg-white/30 transition-all duration-300" id="playPauseBtn">
                            <svg class="w-5 h-5 text-white play-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                            </svg>
                            <svg class="w-5 h-5 text-white pause-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6 4v12h4V4H6zM14 4v12h4V4h-4z"/>
                            </svg>
                        </button>
                        
                        <!-- 프로그레스 바 (4개) -->
                        <div class="video-progress-bars flex items-center gap-2">
                            <div class="video-progress-item" data-index="0">
                                <div class="video-progress-bar w-16 bg-white/20 rounded-full overflow-hidden" style="height: 0.15rem;">
                                    <div class="video-progress-fill h-full bg-white transition-transform duration-100 origin-left" style="transform: scaleX(0)"></div>
                                </div>
                            </div>
                            <div class="video-progress-item" data-index="1">
                                <div class="video-progress-bar w-16 bg-white/20 rounded-full overflow-hidden" style="height: 0.15rem;">
                                    <div class="video-progress-fill h-full bg-white transition-transform duration-100 origin-left" style="transform: scaleX(0)"></div>
                                </div>
                            </div>
                            <div class="video-progress-item" data-index="2">
                                <div class="video-progress-bar w-16 bg-white/20 rounded-full overflow-hidden" style="height: 0.15rem;">
                                    <div class="video-progress-fill h-full bg-white transition-transform duration-100 origin-left" style="transform: scaleX(0)"></div>
                                </div>
                            </div>
                            <div class="video-progress-item" data-index="3">
                                <div class="video-progress-bar w-16 bg-white/20 rounded-full overflow-hidden" style="height: 0.15rem;">
                                    <div class="video-progress-fill h-full bg-white transition-transform duration-100 origin-left" style="transform: scaleX(0)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 회사 소개 섹션 -->
    <section class="intro-section relative overflow-hidden">
        <style>
            .intro-title {
                color: #000;
                font-family: Poppins;
                font-size: 45px;
                font-style: normal;
                font-weight: 700;
                line-height: 54px;
                letter-spacing: -1.35px;
            }
            
            .intro-desc {
                color: #000;
                font-family: Poppins;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: 159%;
                letter-spacing: -0.4px;
            }
            
            .intro-disclaimer {
                color: #ccc;
                font-family: Poppins;
                font-size: 10px;
                font-style: normal;
                font-weight: 400;
                line-height: 159%;
                letter-spacing: -0.24px;
            }
            
            /* CLIENTS 지도 타이틀 스타일 */
            .clients-map-title {
                font-size: 20px;
                font-style: normal;
                font-weight: 700;
                line-height: 54px; /* 270% */
                letter-spacing: -0.6px;
            }
            
            /* 클라이언트 로고 컨테이너 - 반응형 높이 */
            .client-logo-container {
                height: clamp(60px, 8vw, 120px); /* 모바일 60px ~ 대형 화면 120px */
            }

            /* 클라이언트 로고 - 반응형 크기 */
            .client-logo {
                height: clamp(50px, 7vw, 110px); /* 컨테이너보다 약간 작게 */
                margin-left: clamp(1.5rem, 3vw, 4rem); /* 로고 간격도 반응형 */
                margin-right: clamp(1.5rem, 3vw, 4rem);
            }

            .logo-slider {
                animation: slide-logos 60s linear infinite;
                display: flex;
                white-space: nowrap;
            }

            .logo-slider-reverse {
                animation: slide-logos-reverse 60s linear infinite;
                display: flex;
                white-space: nowrap;
            }

            @keyframes slide-logos {
                0% { transform: translateX(0); }
                100% { transform: translateX(-25%); }
            }

            @keyframes slide-logos-reverse {
                0% { transform: translateX(-25%); }
                100% { transform: translateX(0); }
            }

            /* 태블릿 크기에서 로고 크기 조정 */
            @media (min-width: 768px) and (max-width: 1023px) {
                .client-logo-container {
                    height: clamp(70px, 9vw, 100px);
                }
                .client-logo {
                    height: clamp(60px, 8vw, 90px);
                }
            }

            /* 대형 화면에서 로고 크기 최적화 */
            @media (min-width: 1440px) {
                .client-logo-container {
                    height: 140px;
                }
                .client-logo {
                    height: 130px;
                    margin-left: 5rem;
                    margin-right: 5rem;
                }
            }

            /* 초대형 화면 (1920px 이상) */
            @media (min-width: 1920px) {
                .client-logo-container {
                    height: 160px;
                }
                .client-logo {
                    height: 150px;
                    margin-left: 6rem;
                    margin-right: 6rem;
                }
            }
            
            
            @media (max-width: 768px) {
                .intro-title {
                    font-size: 30px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: 37px;
                    letter-spacing: -0.9px;
                }
                
                .intro-desc {
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: 159%;
                    letter-spacing: -0.28px;
                }
            }
        </style>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 max-h-[900px] min-h-[600px]">
            <!-- 왼쪽: 지도 이미지 (33% 너비) -->
            <div class="relative bg-secondary overflow-hidden lg:col-span-1 hidden lg:block">
                <img src="<?php echo BASE_URL; ?>/assets/images/index/left.webp" 
                     alt="Global Network Map" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center">
                    <h3 class="clients-map-title text-white">CLIENTS</h3>
                </div>
            </div>
            
            <!-- 오른쪽: 콘텐츠 (67% 너비) -->
            <div class="bg-white py-16 lg:py-20 px-8 lg:px-16 lg:col-span-2">
                <div>
                    <h2 class="intro-title mb-6 max-w-3xl">
                        World-Class Logistics<br>
                        Proven Solutions
                    </h2>
                    <p class="intro-desc mb-12 max-w-3xl">
                        IMPEX GLS supports our customers’ global growth through sustainable logistics innovation, prioritizing trust and efficiency in everything we do.
                    </p>
                    
                    <!-- 클라이언트 로고 슬라이더 (전체 너비) -->
                    <div class="mb-8 space-y-4 overflow-hidden -mx-8 lg:-mx-16" style="pointer-events: none;">
                        <?php
                        // DB 연결
                        try {
                            $pdo = getDBConnection();

                            // 활성화된 클라이언트 로고 가져오기
                            $stmt = $pdo->prepare("SELECT * FROM clients WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
                            $stmt->execute();
                            $all_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (!empty($all_clients)) {
                                // 두 줄로 나누기
                                $half = ceil(count($all_clients) / 2);
                                $client_logos = [
                                    array_slice($all_clients, 0, $half),
                                    array_slice($all_clients, $half)
                                ];
                            } else {
                                // 데이터가 없는 경우 빈 배열
                                $client_logos = [[], []];
                            }

                        } catch (PDOException $e) {
                            // DB 연결 실패 시 빈 배열
                            error_log("DB Error: " . $e->getMessage());
                            $client_logos = [[], []];
                        }
                        ?>

                        <?php if(!empty($client_logos[0])): ?>
                        <!-- 첫 번째 줄 -->
                        <div class="overflow-hidden relative client-logo-container px-8 lg:px-16">
                            <div class="flex absolute logo-slider">
                                <?php for($i = 0; $i < 4; $i++): // 더 부드러운 무한 루프를 위해 4번 반복 ?>
                                    <?php foreach($client_logos[0] as $client): ?>
                                    <img src="<?php echo BASE_URL . '/' . $client['logo_path']; ?>"
                                         alt="<?php echo htmlspecialchars($client['name_en'] ?? $client['name'] ?? ''); ?>"
                                         class="client-logo w-auto object-contain flex-shrink-0">
                                    <?php endforeach; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 두 번째 줄 -->
                        <?php if(!empty($client_logos[1])): ?>
                        <div class="overflow-hidden relative client-logo-container px-8 lg:px-16">
                            <div class="flex absolute logo-slider-reverse">
                                <?php for($i = 0; $i < 4; $i++): // 더 부드러운 무한 루프를 위해 4번 반복 ?>
                                    <?php foreach($client_logos[1] as $client): ?>
                                    <img src="<?php echo BASE_URL . '/' . $client['logo_path']; ?>"
                                         alt="<?php echo htmlspecialchars($client['name_en'] ?? $client['name'] ?? ''); ?>"
                                         class="client-logo w-auto object-contain flex-shrink-0">
                                    <?php endforeach; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(empty($client_logos[0]) && empty($client_logos[1])): ?>
                        <p class="text-gray-400 text-center py-8">Client logos will be displayed here once added to the database.</p>
                        <?php endif; ?>
                    </div>
                    
                    <p class="intro-disclaimer max-w-2xl">
                        * All trademarks, logos, and company names are the property of their respective owners. They are presented solely to illustrate the range of clients Impex GLS has collaborated with. These references do not constitute any endorsement, sponsorship, or affiliation by or with the respective companies. Impex GLS makes no claims of partnership, agency, or representation of the listed entities.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 서비스 그리드 섹션 -->
    <section class="services-grid-section py-20" style="background: var(--cg100, #F3F4F8);">
        <div class="container mx-auto px-4">
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/services-grid-mobile.css">
            <style>
                .service-title {
                    color: #FFF;
                    font-family: Poppins;
                    font-size: 24px;
                    font-style: normal;
                    font-weight: 600;
                    line-height: normal;
                    letter-spacing: -0.72px;
                }
                
                .service-desc {
                    color: var(--cg600, #5B5D6B);
                    font-family: Poppins;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: normal;
                    letter-spacing: -0.28px;
                }
                
                /* 모바일 서비스 설명 텍스트 */
                @media (max-width: 768px) {
                    .service-desc {
                        font-size: 11px;
                        font-style: normal;
                        font-weight: 400;
                        line-height: normal;
                        letter-spacing: -0.22px;
                    }
                }
                
                .service-title-dark {
                    color: #131313;
                }
                
                /* PC 스타일 */
                .service-card-content {
                    padding: 0 36px 40px 36px;
                }
                
                .our-business {
                    color: #000;
                    font-family: Poppins;
                    font-size: 100px;
                    font-style: italic;
                    font-weight: 800;
                    line-height: normal;
                    letter-spacing: -3px;
                    opacity: 0.05;
                }
                
                /* 모바일 스타일 */
                @media (max-width: 768px) {
                    .our-business {
                        font-size: 32px;
                        margin-bottom: 1rem;
                    }
                    
                    .service-title {
                        font-size: 18px;
                        font-style: normal;
                        font-weight: 600;
                        line-height: normal;
                        letter-spacing: -0.54px;
                    }
                    
                    .service-card-content {
                        padding: 0 26px 20px 26px;
                    }
                    
                    /* 모바일에서 서비스 그리드 간격 조정 */
                    .services-grid-section .grid > div:not(:last-child) {
                        margin-bottom: 0;
                    }
                }
            </style>
            
            <!-- OUR BUSINESS 타이틀 (별도 위치) -->
            <div class="relative" style="margin-bottom: -1.5rem; text-align: left;">
                <h2 class="our-business">Our Business</h2>
            </div>
            
            <!-- 모바일과 PC 레이아웃 -->
            <div class="services-grid">
                <!-- 모바일 레이아웃 -->
                <div class="grid lg:hidden gap-0">
                    <!-- 첫 번째 줄: 3PL & OUR BUSINESS 설명 -->
                    <div class="grid grid-cols-2 gap-0">
                        <!-- 3PL & Warehouse -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/contract-logistics.php" class="service-item group relative overflow-hidden aspect-[335/255]">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/3PL & Warehouse.webp" 
                                 alt="3PL & Warehouse" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">3PL &<br>Warehouse</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                        
                        <!-- OUR BUSINESS 설명 텍스트 -->
                        <div class="bg-white flex items-end aspect-[335/255]" style="padding: 0 26px 20px 26px;">
                            <p class="service-desc" style="text-align: left;">
                                A trusted leader in freight forwarding and logistics solutions.
                            </p>
                        </div>
                    </div>
                    
                    <!-- 두 번째 줄: International Freight Forwarding (full width) -->
                    <a href="<?php echo BASE_URL; ?>/pages/service/international-transportation.php" class="service-item group relative overflow-hidden aspect-[335/255]">
                        <img src="<?php echo BASE_URL; ?>/assets/images/index/International Freight Forwarding.webp" 
                             alt="International Freight Forwarding" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                        <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                            <h3 class="service-title">International Freight Forwarding</h3>
                            <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </a>
                    
                    <!-- 세 번째 줄: Customs & Trucking -->
                    <div class="grid grid-cols-2 gap-0">
                        <!-- Customs Clearance -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/other-activities.php" class="service-item group relative overflow-hidden aspect-[335/255]">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/Customs Clearance.webp" 
                                 alt="Customs Clearance" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">Customs<br>Clearance</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                        
                        <!-- Trucking -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/special-products.php" class="service-item group relative overflow-hidden aspect-[335/255]">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/Trucking.webp" 
                                 alt="Trucking" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">Trucking</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                    
                    <!-- 네 번째 줄: Logistics Consulting (full width) -->
                    <a href="<?php echo BASE_URL; ?>/pages/service/supply-chain-management.php" class="service-item group relative overflow-hidden aspect-[335/255] bg-white">
                        <img src="<?php echo BASE_URL; ?>/assets/images/index/Logistics Consulting.webp" 
                             alt="Logistics Consulting" 
                             class="w-full h-full object-cover">
                        <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                            <h3 class="service-title service-title-dark">Logistics Consulting</h3>
                            <svg class="flex-shrink-0 ml-2 text-gray-800" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                    </a>
                </div>
                
                <!-- PC 레이아웃 (기존 코드) -->
                <div class="hidden lg:grid grid-cols-1 gap-0">
                    <!-- 첫 번째 줄: 1fr 1fr 2fr -->
                    <div class="grid grid-cols-4 gap-0">
                        <!-- 3PL & Warehouse -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/contract-logistics.php" class="service-item group relative overflow-hidden aspect-square">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/3PL & Warehouse.webp" 
                                 alt="3PL & Warehouse" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">3PL & Warehouse</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                        
                        <!-- OUR BUSINESS 설명 텍스트 -->
                        <div class="flex items-end aspect-square bg-transparent" style="padding: 0 36px 40px 36px;">
                            <p class="service-desc" style="text-align: left;">
                                A trusted leader in freight forwarding and logistics solutions.
                            </p>
                        </div>
                        
                        <!-- International Freight Forwarding -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/international-transportation.php" class="service-item group relative overflow-hidden aspect-[2/1] col-span-2">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/International Freight Forwarding.webp" 
                                 alt="International Freight Forwarding" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">International Freight Forwarding</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                    
                    <!-- 두 번째 줄: 1fr 2fr 1fr -->
                    <div class="grid grid-cols-4 gap-0">
                        <!-- Customs Clearance -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/other-activities.php" class="service-item group relative overflow-hidden aspect-square">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/Customs Clearance.webp" 
                                 alt="Customs Clearance" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">Customs Clearance</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                        
                        <!-- Logistics Consulting -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/supply-chain-management.php" class="service-item group relative overflow-hidden aspect-[2/1] col-span-2 bg-white">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/Logistics Consulting.webp" 
                                 alt="Logistics Consulting" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title service-title-dark">Logistics Consulting</h3>
                                <svg class="flex-shrink-0 ml-2 text-gray-800" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                        
                        <!-- Trucking -->
                        <a href="<?php echo BASE_URL; ?>/pages/service/special-products.php" class="service-item group relative overflow-hidden aspect-square">
                            <img src="<?php echo BASE_URL; ?>/assets/images/index/Trucking.webp" 
                                 alt="Trucking" 
                                 class="w-full h-full object-cover">
                            <div class="service-card-content absolute bottom-0 left-0 right-0 flex items-end justify-between">
                                <h3 class="service-title">Trucking</h3>
                                <svg class="flex-shrink-0 ml-2 text-white" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 뉴스 섹션 -->
    <section class="news-section bg-white" style="padding: 120px 0;">
        <div class="container mx-auto px-4">
            <style>
                .news-title {
                    color: #000;
                    font-family: Poppins;
                    font-size: 45px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: 54px;
                    letter-spacing: -1.35px;
                }
                
                .news-date-day {
                    color: #000;
                    text-align: center;
                    font-family: Poppins;
                    font-size: 50px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: 50px;
                    letter-spacing: -1.5px;
                }
                
                .news-date-month {
                    color: var(--cg400, #9496A1);
                    text-align: center;
                    font-family: Poppins;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 600;
                    line-height: normal;
                    letter-spacing: -0.42px;
                    text-transform: uppercase;
                }
                
                .news-item-title {
                    color: #000;
                    font-family: Poppins;
                    font-size: 20px;
                    font-style: normal;
                    font-weight: 600;
                    line-height: normal;
                    letter-spacing: -0.6px;
                }
                
                .news-item-desc {
                    color: var(--cg600, #5B5D6B);
                    font-family: Poppins;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: normal;
                    letter-spacing: -0.42px;
                }
                
                .more-view {
                    color: #B21525;
                    font-family: Poppins;
                    font-size: 14px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: normal;
                    letter-spacing: -0.42px;
                    text-transform: uppercase;
                }
                
                @media (max-width: 768px) {
                    .news-title {
                        font-size: 30px;
                        font-style: normal;
                        font-weight: 700;
                        line-height: 37px;
                        letter-spacing: -0.9px;
                    }
                    
                    .news-date-day {
                        font-size: 30px;
                        font-style: normal;
                        font-weight: 700;
                        line-height: normal;
                        letter-spacing: -0.9px;
                    }
                    
                    .news-date-month {
                        font-size: 11px;
                        font-style: normal;
                        font-weight: 600;
                        line-height: normal;
                        letter-spacing: -0.33px;
                    }
                    
                    .news-item-title {
                        font-size: 16px;
                        font-style: normal;
                        font-weight: 600;
                        line-height: normal;
                        letter-spacing: -0.48px;
                    }
                    
                    .news-item-desc {
                        font-size: 12px;
                        font-style: normal;
                        font-weight: 400;
                        line-height: normal;
                        letter-spacing: -0.36px;
                    }
                }
            </style>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <!-- 왼쪽: 타이틀 -->
                <div class="lg:col-span-1">
                    <h2 class="news-title">
                        Latest News<br>
                        from IMPEX GLS
                    </h2>
                </div>
                
                <!-- 오른쪽: 뉴스 리스트 -->
                <div class="lg:col-span-2">
                    <?php
                    // 데이터베이스에서 최신 뉴스 가져오기
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->prepare("
                            SELECT id, title, content, excerpt, DATE_FORMAT(published_at, '%Y-%m-%d') as date
                            FROM news_posts 
                            WHERE status = 'published' AND category = 'logistics'
                            ORDER BY published_at DESC
                            LIMIT 3
                        ");
                        $stmt->execute();
                        $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // excerpt가 없으면 content에서 생성
                        foreach ($news_items as &$news) {
                            if (empty($news['excerpt'])) {
                                $news['description'] = mb_substr(strip_tags($news['content']), 0, 150) . '...';
                            } else {
                                $news['description'] = $news['excerpt'];
                            }
                        }
                    } catch (Exception $e) {
                        // 에러 시 빈 배열
                        $news_items = [];
                    }
                    ?>
                    
                    <div class="space-y-8">
                        <?php if (!empty($news_items)): ?>
                        <?php foreach($news_items as $news): 
                            $date = DateTime::createFromFormat('Y-m-d', $news['date']);
                            $day = $date->format('d');
                            $monthYear = $date->format('Y M');
                        ?>
                        <article class="news-item border-b border-gray-200 pb-8">
                            <a href="<?php echo BASE_URL; ?>/pages/notices/logistics-news-detail.php?id=<?php echo $news['id']; ?>" class="group block">
                                <div class="flex gap-6">
                                    <!-- 날짜 -->
                                    <div class="text-center">
                                        <div class="news-date-month"><?php echo $monthYear; ?></div>
                                        <div class="news-date-day"><?php echo $day; ?></div>
                                    </div>
                                    
                                    <!-- 콘텐츠 -->
                                    <div class="flex-1">
                                        <h3 class="news-item-title mb-2 group-hover:text-red-600 transition-colors">
                                            <?php echo e($news['title']); ?>
                                        </h3>
                                        <p class="news-item-desc">
                                            <?php echo e($news['description']); ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </article>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="text-gray-500 text-center py-8">
                            No news available at this time.
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- More View 버튼 -->
                    <div class="text-right mt-12">
                        <a href="<?php echo BASE_URL; ?>/pages/notices/logistics-news.php" class="more-view inline-flex items-center gap-2 hover:gap-3 transition-all">
                            MORE VIEW
                            <span>+</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // 비디오 컨트롤
    document.addEventListener('DOMContentLoaded', function() {
        // 영상 설정
        const videos = [
            {
                sources: {
                    '1080p': '<?php echo BASE_URL; ?>/assets/images/index/hero_1080p.mp4',
                    '720p': '<?php echo BASE_URL; ?>/assets/images/index/hero_720p.mp4',
                    '480p': '<?php echo BASE_URL; ?>/assets/images/index/hero_480p.mp4'
                },
                poster: '<?php echo BASE_URL; ?>/assets/images/placeholder/hero-bg.jpg',
                duration: 15 // 초 단위
            },
            {
                sources: {
                    '1080p': '<?php echo BASE_URL; ?>/assets/images/index/hero_2_1080p.mp4',
                    '720p': '<?php echo BASE_URL; ?>/assets/images/index/hero_2_720p.mp4',
                    '480p': '<?php echo BASE_URL; ?>/assets/images/index/hero_2_480p.mp4'
                },
                poster: '<?php echo BASE_URL; ?>/assets/images/placeholder/hero-bg.jpg',
                duration: 20
            },
            {
                sources: {
                    '1080p': '<?php echo BASE_URL; ?>/assets/images/index/hero_3_1080p.mp4',
                    '720p': '<?php echo BASE_URL; ?>/assets/images/index/hero_3_720p.mp4',
                    '480p': '<?php echo BASE_URL; ?>/assets/images/index/hero_3_480p.mp4'
                },
                poster: '<?php echo BASE_URL; ?>/assets/images/placeholder/hero-bg.jpg',
                duration: 18
            },
            {
                sources: {
                    '1080p': '<?php echo BASE_URL; ?>/assets/images/index/hero_4_1080p.mp4',
                    '720p': '<?php echo BASE_URL; ?>/assets/images/index/hero_4_720p.mp4',
                    '480p': '<?php echo BASE_URL; ?>/assets/images/index/hero_4_480p.mp4'
                },
                poster: '<?php echo BASE_URL; ?>/assets/images/placeholder/hero-bg.jpg',
                duration: 22
            }
        ];
        
        let currentVideoIndex = 0;
        let isPlaying = true;
        let progressInterval = null;
        let currentVideo = null;
        let nextVideo = null;
        
        const container = document.getElementById('heroVideoContainer');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const playIcon = playPauseBtn.querySelector('.play-icon');
        const pauseIcon = playPauseBtn.querySelector('.pause-icon');
        const progressBars = document.querySelectorAll('.video-progress-item');
        
        // 비디오 엘리먼트 생성
        function createVideoElement() {
            const video = document.createElement('video');
            video.className = 'absolute inset-0 w-full h-full object-cover';
            video.muted = true;
            video.playsInline = true;
            video.setAttribute('webkit-playsinline', '');
            video.style.position = 'absolute';
            video.style.top = '0';
            video.style.left = '0';
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.objectFit = 'cover';
            video.style.opacity = '0';
            video.style.zIndex = '0';
            return video;
        }
        
        // 현재 비디오와 다음 비디오 준비
        currentVideo = createVideoElement();
        nextVideo = createVideoElement();
        container.appendChild(currentVideo);
        container.appendChild(nextVideo);
        
        // 초기 상태 설정
        currentVideo.style.opacity = '1';
        currentVideo.style.zIndex = '1';
        
        // 최적 화질 선택
        function getOptimalQuality() {
            const width = window.innerWidth;
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            
            // 모바일 기기
            if (width <= 768) {
                return '480p';
            }
            
            // 네트워크 속도 확인
            if (connection && connection.effectiveType) {
                if (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
                    return '480p';
                } else if (connection.effectiveType === '3g') {
                    return '720p';
                }
            }
            
            // 태블릿
            if (width <= 1024) {
                return '720p';
            }
            
            // 데스크탑
            return '1080p';
        }
        
        // 비디오 로드 및 재생
        function loadAndPlayVideo(index) {
            const videoData = videos[index];
            const videoElement = currentVideo;
            const quality = getOptimalQuality();
            
            // 프로그레스 바 초기화
            resetProgressBars();
            
            // 현재 비디오 프로그레스 바 활성화
            const currentProgressBar = progressBars[index];
            currentProgressBar.classList.add('active');
            
            // 비디오 로드
            videoElement.src = videoData.sources[quality];
            videoElement.poster = videoData.poster;
            
            videoElement.onloadeddata = function() {
                if (isPlaying) {
                    videoElement.play().catch(e => {
                        console.error('Video play error:', e);
                        // 자동재생 실패 시 수동 재생 요구
                        showPlayButton();
                    });
                    startProgressAnimation(index, videoData.duration);
                }
                
                // 비디오 종료 이벤트 설정
                setupVideoEndHandler(videoElement, index);
            };
            
            // 다음 비디오 미리 로드
            const nextIndex = (index + 1) % videos.length;
            const nextQuality = getOptimalQuality();
            nextVideo.src = videos[nextIndex].sources[nextQuality];
            nextVideo.load();
        }
        
        // 비디오 종료 이벤트 처리
        function setupVideoEndHandler(videoElement, index) {
            videoElement.onended = function() {
                if (isPlaying) {
                    playNextVideo();
                }
            };
        }
        
        // 다음 비디오 재생
        function playNextVideo() {
            // 현재 프로그레스 완료 표시
            const currentProgressFill = progressBars[currentVideoIndex].querySelector('.video-progress-fill');
            currentProgressFill.style.transform = 'scaleX(1)';
            
            // 다음 인덱스로 이돕
            const prevIndex = currentVideoIndex;
            currentVideoIndex = (currentVideoIndex + 1) % videos.length;
            
            // 이전 프로그레스 바 비활성화
            progressBars[prevIndex].classList.remove('active');
            
            // 페이드 전환 효과 준비
            nextVideo.style.opacity = '0';
            nextVideo.style.transition = 'opacity 0.8s ease-in-out';
            currentVideo.style.transition = 'opacity 0.8s ease-in-out';
            
            // 비디오 교체 전 다음 비디오 준비
            [currentVideo, nextVideo] = [nextVideo, currentVideo];
            
            // z-index 설정
            currentVideo.style.zIndex = '1';
            nextVideo.style.zIndex = '0';
            
            // 다음 비디오 로드
            loadAndPlayVideo(currentVideoIndex);
            
            // 부드러운 전환 효과
            setTimeout(() => {
                currentVideo.style.opacity = '1';
                nextVideo.style.opacity = '0';
            }, 100);
        }
        
        // 프로그레스 바 초기화
        function resetProgressBars() {
            progressBars.forEach((bar, idx) => {
                bar.classList.remove('active');
                const fill = bar.querySelector('.video-progress-fill');
                fill.style.transform = 'scaleX(0)';
                fill.style.transition = 'none';
                
                // 완료된 비디오는 채워진 상태로 유지
                if (idx < currentVideoIndex) {
                    fill.style.transform = 'scaleX(1)';
                }
            });
        }
        
        // 재생/일시정지 토글
        playPauseBtn.addEventListener('click', function() {
            if (isPlaying) {
                pauseVideo();
            } else {
                resumeVideo();
            }
        });
        
        function pauseVideo() {
            isPlaying = false;
            currentVideo.pause();
            clearInterval(progressInterval);
            showPlayButton();
        }
        
        function resumeVideo() {
            isPlaying = true;
            currentVideo.play();
            const videoData = videos[currentVideoIndex];
            startProgressAnimation(currentVideoIndex, videoData.duration);
            showPauseButton();
        }
        
        function showPlayButton() {
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
        }
        
        function showPauseButton() {
            playIcon.classList.add('hidden');
            pauseIcon.classList.remove('hidden');
        }
        
        // 프로그레스 애니메이션 수정
        function startProgressAnimation(index, duration, startProgress = 0) {
            const progressBar = progressBars[index].querySelector('.video-progress-fill');
            const videoElement = currentVideo;
            
            // 트랜지션 활성화
            setTimeout(() => {
                progressBar.style.transition = 'transform 0.1s linear';
            }, 50);
            
            clearInterval(progressInterval);
            
            // 비디오 시간 기반 프로그레스 업데이트
            progressInterval = setInterval(() => {
                if (!isPlaying || !videoElement.duration) return;
                
                const progress = (videoElement.currentTime / videoElement.duration) * 100;
                progressBar.style.transform = `scaleX(${progress / 100})`;
                
                // 비디오가 거의 끝났을 때
                if (progress >= 99.5) {
                    progressBar.style.transform = 'scaleX(1)';
                    clearInterval(progressInterval);
                }
            }, 100);
        }
        
        // 초기 비디오 로드
        currentVideo.style.opacity = '1';
        currentVideo.style.zIndex = '1';
        nextVideo.style.opacity = '0';
        nextVideo.style.zIndex = '0';
        
        // 모든 프로그레스 바 초기화
        progressBars.forEach(bar => {
            const fill = bar.querySelector('.video-progress-fill');
            fill.style.transform = 'scaleX(0)';
            fill.style.transition = 'none';
        });
        
        loadAndPlayVideo(0);
        showPauseButton();
        
        // 화면 크기 변경 시 화질 재조정
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newQuality = getOptimalQuality();
                const currentSrc = currentVideo.src;
                const needsQualityChange = 
                    (newQuality === '480p' && !currentSrc.includes('480p')) ||
                    (newQuality === '720p' && !currentSrc.includes('720p')) ||
                    (newQuality === '1080p' && !currentSrc.includes('1080p'));
                
                if (needsQualityChange) {
                    const currentTime = currentVideo.currentTime;
                    currentVideo.src = videos[currentVideoIndex].sources[newQuality];
                    currentVideo.currentTime = currentTime;
                    if (isPlaying) currentVideo.play();
                }
            }, 500);
        });
    });
    </script>
</body>
</html>