<?php
// 더미 메뉴 데이터 (나중에 DB로 전환)
$menu_items = [
    ['title' => 'About Us', 'url' => BASE_URL . '/pages/about/', 'submenu' => [
        ['title' => 'About IMPEX GLS', 'url' => BASE_URL . '/pages/about/'],
        ['title' => 'Clients', 'url' => BASE_URL . '/pages/about/clients.php'],
        ['title' => 'Certificates', 'url' => BASE_URL . '/pages/about/certificates.php'],
        ['title' => 'History', 'url' => BASE_URL . '/pages/about/history.php'],
        ['title' => 'ESG', 'url' => BASE_URL . '/pages/about/esg.php']
    ]],
    ['title' => 'Networks', 'url' => BASE_URL . '/pages/networks/', 'submenu' => [
        ['title' => 'Headquarters', 'url' => BASE_URL . '/pages/networks/headquarters.php'],
        ['title' => 'USA', 'url' => BASE_URL . '/pages/networks/usa.php'],
        ['title' => 'Global', 'url' => BASE_URL . '/pages/networks/global.php']
    ]],
    ['title' => 'Services', 'url' => BASE_URL . '/pages/service/', 'submenu' => [
        ['title' => 'International Transportation', 'url' => BASE_URL . '/pages/service/international-transportation.php'],
        ['title' => 'Contract Logistics', 'url' => BASE_URL . '/pages/service/contract-logistics.php'],
        ['title' => 'Supply Chain Management', 'url' => BASE_URL . '/pages/service/supply-chain-management.php'],
        ['title' => 'Special Products', 'url' => BASE_URL . '/pages/service/special-products.php'],
        ['title' => 'Other Activities & Services', 'url' => BASE_URL . '/pages/service/other-activities.php']
    ]],
    ['title' => 'Resources', 'url' => BASE_URL . '/pages/resources/quick-links.php', 'submenu' => [
        ['title' => 'Quick Links', 'url' => BASE_URL . '/pages/resources/quick-links.php'],
        ['title' => 'Knowledge Base', 'url' => BASE_URL . '/pages/resources/incoterms.php'],
        ['title' => 'Terms & Conditions of Service', 'url' => BASE_URL . '/pages/resources/terms.php']
    ]],
    ['title' => 'Notices', 'url' => BASE_URL . '/pages/notices/logistics-news.php', 'submenu' => [
        ['title' => 'Logistics News', 'url' => BASE_URL . '/pages/notices/logistics-news.php']
    ]],
    // ['title' => 'Support', 'url' => BASE_URL . '/pages/support/faq.php', 'submenu' => [
    //     ['title' => 'Request A Quote', 'url' => BASE_URL . '/pages/support/request-quote.php'],
    //     ['title' => 'FAQ', 'url' => BASE_URL . '/pages/support/faq.php'],
    //     ['title' => 'Tracking', 'url' => BASE_URL . '/pages/support/tracking.php']
    // ]]
];

// 현재 페이지 경로 확인
$current_path = $_SERVER['REQUEST_URI'];

// 홈페이지 여부 확인 - 배포 환경 고려
$currentFile = $_SERVER['SCRIPT_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];
$isHomePage = (
    // 기본 경로들
    $currentFile === '/impex/corporate-website/index.php' || 
    $currentFile === '/corporate-website/index.php' ||
    $currentFile === '/index.php' ||
    // 파일명 기반 체크
    (basename($currentFile) === 'index.php' && dirname($currentFile) === '/impex/corporate-website') ||
    // URI 기반 체크 (배포 환경 고려)
    $requestUri === '/' ||
    $requestUri === '/index.php' ||
    explode('?', $requestUri)[0] === '/' ||
    explode('?', $requestUri)[0] === '/index.php'
);

// isActiveMenu 함수는 functions.php에서 이미 선언됨
?>

<!-- 듀얼 헤더: 모바일 + PC -->
<header class="header-wrapper">
    <!-- 모바일 헤더 -->
    <div id="mobileHeader" class="mobile-header lg:hidden fixed top-0 left-0 right-0 z-50 <?php echo $isHomePage ? 'homepage-header' : ''; ?>" style="background: <?php echo $isHomePage ? 'transparent' : 'rgba(255, 255, 255, 0.80)'; ?>; backdrop-filter: <?php echo $isHomePage ? 'none' : 'blur(10px)'; ?>; transition: background 0.3s ease, backdrop-filter 0.3s ease;">
        <div class="container mx-auto" style="padding-left: 1rem; padding-right: 0; padding-top: 12px;">
            <div class="relative flex items-center justify-between h-16">
                <a href="<?php echo BASE_URL; ?>" class="logo-mobile">
                    <img class="mobile-logo-img" src="<?php echo BASE_URL; ?>/assets/images/<?php echo $isHomePage ? 'logo-w.png' : 'logo.png'; ?>" alt="IMPEX GLS" style="width: 164.813px; height: auto;">
                </a>
                <button class="mobile-menu-toggle flex items-center justify-center" style="padding: 13px 30px 13px 11px; background-color: #B21525;" aria-label="메뉴 열기">
                    <img src="<?php echo BASE_URL; ?>/assets/images/hamburger.svg" alt="메뉴" style="width: 20px; height: 20px;">
                </button>
            </div>
        </div>
        
        <!-- 모바일 메뉴 -->
        <nav class="mobile-menu hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100vh; padding: 0; background-color: #ffffff; z-index: 9999; overflow-y: auto; opacity: 0; transition: opacity 0.3s ease-in-out;">
            <!-- 모바일 메뉴 헤더 -->
            <div class="mobile-menu-header flex items-center justify-between bg-white h-16" style="padding-left: 1rem; padding-right: 0; padding-top: 24px;">
                <a href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="IMPEX GLS" style="width: 164.813px; height: auto;">
                </a>
                <button class="mobile-menu-close flex items-center justify-center" style="padding: 13px 30px 13px 11px; background-color: #4a5568;">
                    <svg class="text-white" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- 모바일 메뉴 리스트 -->
            <ul class="mobile-nav-list pt-8">
                <?php foreach($menu_items as $index => $item): ?>
                <li class="mobile-nav-item">
                    <?php if(!empty($item['submenu'])): ?>
                    <div class="mobile-nav-accordion">
                        <?php 
                        $isActive = false;
                        foreach($item['submenu'] as $subitem) {
                            if(isActiveMenu($subitem['url'], $current_path)) {
                                $isActive = true;
                                break;
                            }
                        }
                        ?>
                        <button class="mobile-nav-toggle w-full flex items-center justify-between px-6 py-5 text-left" data-index="<?php echo $index; ?>">
                            <span class="mobile-menu-title <?php echo $isActive ? 'text-services' : ''; ?>">
                                <?php echo strip_tags($item['title']); ?>
                            </span>
                            <span class="toggle-icon text-2xl font-light <?php echo $isActive ? 'text-services' : ''; ?>" style="font-size: 24px;">
                                <?php echo $isActive ? '−' : '+'; ?>
                            </span>
                        </button>
                        <ul class="mobile-submenu <?php echo $isActive ? '' : 'hidden'; ?>" style="background-color: #F3F4F8;">
                            <?php foreach($item['submenu'] as $subitem): ?>
                            <li class="mobile-submenu-item" style="background-color: #F3F4F8;">
                                <a href="<?php echo $subitem['url']; ?>" class="mobile-submenu-link block px-12" style="padding-top: 1.25rem; padding-bottom: 1.25rem;">
                                    <?php echo strip_tags($subitem['title']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="<?php echo $item['url']; ?>" class="mobile-nav-link block px-6 py-5">
                        <span class="mobile-menu-title"><?php echo strip_tags($item['title']); ?></span>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>

    <!-- PC 헤더 -->
    <?php 
    // 상단에서 정의한 $isHomePage 변수를 사용
    ?>
    <div class="pc-header hidden lg:block fixed top-0 left-0 right-0 z-50 <?php echo $isHomePage ? 'homepage-header' : ''; ?> bg-white transition-all duration-300" id="mainHeader">
        <div class="header-container">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>" class="logo-pc">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="IMPEX GLS" class="logo-default">
                    <?php if($isHomePage): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo-w.png" alt="IMPEX GLS" class="logo-white">
                    <?php endif; ?>
                </a>
                
                <!-- Nav and Contact Button Container -->
                <div class="flex items-center">
                    <nav class="pc-nav">
                        <ul class="pc-nav-list flex items-center">
                            <?php foreach($menu_items as $index => $item): ?>
                            <li class="pc-nav-item group h-full flex items-center relative" data-menu="<?php echo strtolower(str_replace(' ', '-', $item['title'])); ?>">
                                <a href="<?php echo $item['url']; ?>" 
                                   class="pc-nav-link h-full flex items-center text-base font-medium text-gray-900 hover:text-red-600 transition-colors
                                   <?php echo isActiveMenu($item['url'], $current_path) ? 'text-red-600' : ''; ?>">
                                    <?php echo $item['title']; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    <!-- <div class="header-actions">
                        <a href="<?php echo BASE_URL; ?>/pages/support/request-quote.php" class="header-cta-btn">
                            Contact Us
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
        
        <!-- 메가 메뉴 (Hero Menu) -->
        <div class="mega-menu-container fixed left-0 right-0 bg-gray-50 border-t border-gray-200 opacity-0 invisible transition-all duration-300" style="z-index: 45;">
            <div class="mega-menu-content mx-auto">
                <div class="grid grid-cols-5 gap-12">
                    <?php foreach($menu_items as $item): ?>
                    <div class="mega-menu-column">
                        <h3 class="text-base font-semibold text-gray-900 mb-6"><?php echo $item['title']; ?></h3>
                        <?php if(!empty($item['submenu'])): ?>
                        <ul class="space-y-3">
                            <?php foreach($item['submenu'] as $subitem): ?>
                            <li>
                                <a href="<?php echo $subitem['url']; ?>" class="text-sm text-gray-600 hover:text-red-600 transition-colors block">
                                    <?php echo $subitem['title']; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- 헤더 높이만큼 여백 추가 -->
<div class="header-spacer h-16 lg:h-[125px]"></div>

<!-- 반응형 헤더 스타일 -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header-responsive.css">

<style>
/* PC 헤더 스타일 */
.pc-header {
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    background-color: #fff;
    transition: all 0.3s ease;
}

/* 홈페이지 헤더 초기 상태 - 스크롤 전 */
.pc-header.homepage-header:not(.scrolled) {
    background-color: transparent !important;
    box-shadow: none !important;
}

.homepage-header:not(.scrolled) .pc-nav-link {
    color: #fff !important;
}

.homepage-header:not(.scrolled) .pc-nav-link:hover {
    color: #e31e24 !important;
}

.homepage-header:not(.scrolled) .pc-nav-link.text-red-600 {
    color: #e31e24 !important;
}

.homepage-header:not(.scrolled) .header-cta-btn {
    background: rgba(178, 21, 37, 0.9);
}

.homepage-header:not(.scrolled) .header-cta-btn:hover {
    background: #B21525;
}

/* 로고 전환 */
.logo-pc .logo-default {
    display: block;
}

.logo-pc .logo-white {
    display: none;
}

.homepage-header:not(.scrolled) .logo-pc .logo-default {
    display: none;
}

.homepage-header:not(.scrolled) .logo-pc .logo-white {
    display: block;
}

/* 스크롤 시 홈페이지 헤더 */
.homepage-header.scrolled {
    background-color: #fff !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
}

.homepage-header.scrolled .pc-nav-link {
    color: #000 !important;
}

.homepage-header.scrolled .pc-nav-link:hover {
    color: #e31e24 !important;
}

.homepage-header.scrolled .pc-nav-link.text-red-600 {
    color: #e31e24 !important;
}

.pc-nav-link {
    color: #000;
    font-family: Poppins;
    font-size: 18px;
    font-style: normal;
    font-weight: 600;
    line-height: normal;
    letter-spacing: -0.36px;
    text-transform: capitalize;
    position: relative;
    padding: 0 1rem;
    transition: color 0.3s ease;
}

.pc-nav-link:hover {
    color: #e31e24;
}

.header-cta-btn {
    color: #FFF;
    font-family: Poppins;
    font-size: 15px;
    font-style: normal;
    font-weight: 500;
    line-height: normal;
    letter-spacing: -0.3px;
    text-transform: capitalize;
    background: #B21525;
    display: inline-flex;
    padding: 8px 16px;
    transition: background-color 0.3s ease;
}

.header-cta-btn:hover {
    background: #9a1220;
}

.pc-nav-item {
    position: relative;
}

.pc-nav-item::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background-color: #e31e24;
    transition: width 0.3s ease;
}

.pc-nav-item:hover::after {
    width: 100%;
}

.pc-nav-item.active::after {
    width: 100%;
}

/* 메가 메뉴 스타일 */
.mega-menu-container.active {
    opacity: 1;
    visibility: visible;
}

.mega-menu-column {
    opacity: 1;
}

/* 모바일 스타일 */
.mobile-header {
    /* box-shadow removed */
}

.mobile-menu {
    display: none;
}

.mobile-menu.hidden {
    display: none !important;
}

.mobile-menu-title {
    color: #000;
    font-family: Poppins;
    font-size: 20px;
    font-style: normal;
    font-weight: 600;
    line-height: normal;
    letter-spacing: -0.8px;
}

.mobile-menu-title.text-services {
    color: #B21525;
}

.toggle-icon {
    font-weight: 300;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-icon.text-services {
    color: #B21525;
}

.mobile-submenu-link {
    color: #000;
    font-family: Poppins;
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: normal;
    letter-spacing: -0.64px;
}

.mobile-nav-item {
    position: relative;
}

.mobile-submenu {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}

.mobile-submenu:not(.hidden) {
    max-height: 500px;
    opacity: 1;
}

/* 기존 반응형 스타일은 header-responsive.css로 이동 */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 홈페이지 헤더 스크롤 처리 (PC + 모바일)
    const pcHeader = document.getElementById('mainHeader');
    const mobileHeader = document.getElementById('mobileHeader');
    const isPCHomepage = pcHeader && pcHeader.classList.contains('homepage-header');
    const isMobileHomepage = mobileHeader && mobileHeader.classList.contains('homepage-header');
    
    // 디버깅 로그 - 배포 환경에서 문제 진단용
    console.log('=== Header Debug Info ===');
    console.log('Current URL:', window.location.href);
    console.log('Script Name (PHP):', '<?php echo $_SERVER["SCRIPT_NAME"]; ?>');
    console.log('Request URI (PHP):', '<?php echo $_SERVER["REQUEST_URI"]; ?>');
    console.log('isHomePage (PHP):', <?php echo $isHomePage ? 'true' : 'false'; ?>);
    console.log('PC Header Element:', !!pcHeader);
    console.log('Mobile Header Element:', !!mobileHeader);
    console.log('isPCHomepage:', isPCHomepage);
    console.log('isMobileHomepage:', isMobileHomepage);
    console.log('Initial ScrollY:', window.scrollY);
    console.log('========================');
    
    // PC 헤더 처리 - DOM 요소 존재 확인 강화
    if (isPCHomepage && pcHeader) {
        console.log('PC Header: Setting up scroll handling');
        // 초기 상태 설정
        if (window.scrollY === 0) {
            pcHeader.classList.remove('scrolled');
            console.log('PC Header: Initial state - transparent');
        } else {
            pcHeader.classList.add('scrolled');
            console.log('PC Header: Initial state - scrolled');
        }
    } else {
        if (!pcHeader) {
            console.warn('PC Header: Element not found!');
        } else {
            console.log('PC Header: NOT homepage, skip scroll handling');
        }
    }
    
    // 모바일 헤더 처리
    if (isMobileHomepage) {
        const mobileLogo = mobileHeader.querySelector('.mobile-logo-img');
        
        // 초기 상태 설정
        if (window.scrollY === 0) {
            mobileHeader.style.setProperty('background', 'transparent', 'important');
            mobileHeader.style.setProperty('backdrop-filter', 'none', 'important');
            mobileHeader.style.setProperty('-webkit-backdrop-filter', 'none', 'important');
            if (mobileLogo) {
                mobileLogo.src = '<?php echo BASE_URL; ?>/assets/images/logo-w.png';
            }
        } else {
            mobileHeader.style.setProperty('background', 'rgba(255, 255, 255, 0.80)', 'important');
            mobileHeader.style.setProperty('backdrop-filter', 'blur(10px)', 'important');
            mobileHeader.style.setProperty('-webkit-backdrop-filter', 'blur(10px)', 'important');
            if (mobileLogo) {
                mobileLogo.src = '<?php echo BASE_URL; ?>/assets/images/logo.png';
            }
        }
    }
    
    // 스크롤 이벤트 (성능 최적화를 위한 throttle 추가)
    if (isPCHomepage || isMobileHomepage) {
        console.log('Setting up scroll event listener');
        let ticking = false;
        let scrollEventCount = 0;
        
        function updateHeaders() {
            const scrolled = window.scrollY > 0;
            scrollEventCount++;
            
            // 처음 몇 번의 스크롤 이벤트만 로그 출력
            if (scrollEventCount <= 5) {
                console.log(`Scroll event #${scrollEventCount}: scrollY=${window.scrollY}, scrolled=${scrolled}`);
            }
            
            // PC 헤더 업데이트 - DOM 요소 재확인
            if (isPCHomepage && pcHeader) {
                const wasScrolled = pcHeader.classList.contains('scrolled');
                if (scrolled) {
                    pcHeader.classList.add('scrolled');
                    if (!wasScrolled && scrollEventCount <= 5) {
                        console.log('PC Header: Added scrolled class');
                    }
                } else {
                    pcHeader.classList.remove('scrolled');
                    if (wasScrolled && scrollEventCount <= 5) {
                        console.log('PC Header: Removed scrolled class');
                    }
                }
            } else if (isPCHomepage && !pcHeader && scrollEventCount <= 5) {
                console.error('PC Header: Element lost during scroll event!');
            }
            
            // 모바일 헤더 업데이트
            if (isMobileHomepage) {
                const mobileLogo = mobileHeader.querySelector('.mobile-logo-img');
                if (scrolled) {
                    mobileHeader.style.setProperty('background', 'rgba(255, 255, 255, 0.80)', 'important');
                    mobileHeader.style.setProperty('backdrop-filter', 'blur(10px)', 'important');
                    mobileHeader.style.setProperty('-webkit-backdrop-filter', 'blur(10px)', 'important');
                    if (mobileLogo) {
                        mobileLogo.src = '<?php echo BASE_URL; ?>/assets/images/logo.png';
                    }
                } else {
                    mobileHeader.style.setProperty('background', 'transparent', 'important');
                    mobileHeader.style.setProperty('backdrop-filter', 'none', 'important');
                    mobileHeader.style.setProperty('-webkit-backdrop-filter', 'none', 'important');
                    if (mobileLogo) {
                        mobileLogo.src = '<?php echo BASE_URL; ?>/assets/images/logo-w.png';
                    }
                }
            }
            
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeaders);
                ticking = true;
            }
        });
        
        console.log('Scroll event listener registered successfully');
    } else {
        console.log('No scroll event listener registered (not homepage)');
    }
    
    // Fallback: 3초 후 상태 재확인 및 보정
    setTimeout(function() {
        console.log('=== Fallback Check (3s after load) ===');
        const finalPcHeader = document.getElementById('mainHeader');
        const finalIsPCHomepage = finalPcHeader && finalPcHeader.classList.contains('homepage-header');
        
        console.log('Final PC Header Element:', !!finalPcHeader);
        console.log('Final isPCHomepage:', finalIsPCHomepage);
        console.log('Current ScrollY:', window.scrollY);
        
        if (finalIsPCHomepage && finalPcHeader) {
            const hasScrolledClass = finalPcHeader.classList.contains('scrolled');
            const shouldHaveScrolledClass = window.scrollY > 0;
            
            console.log('Has scrolled class:', hasScrolledClass);
            console.log('Should have scrolled class:', shouldHaveScrolledClass);
            
            // 상태가 맞지 않으면 보정
            if (hasScrolledClass !== shouldHaveScrolledClass) {
                console.warn('Fallback: Correcting header state');
                if (shouldHaveScrolledClass) {
                    finalPcHeader.classList.add('scrolled');
                    console.log('Fallback: Added scrolled class');
                } else {
                    finalPcHeader.classList.remove('scrolled');
                    console.log('Fallback: Removed scrolled class');
                }
            } else {
                console.log('Fallback: Header state is correct');
            }
        }
        console.log('=================================');
    }, 3000);
    
    // PC 메가메뉴 호버 효과
    const pcNav = document.querySelector('.pc-nav');
    const megaMenuContainer = document.querySelector('.mega-menu-container');
    const navItems = document.querySelectorAll('.pc-nav-item');
    let isMenuOpen = false;
    let currentActiveItem = null;
    
    if (pcNav && megaMenuContainer) {
        // 네비게이션 호버 시 메가메뉴 표시
        pcNav.addEventListener('mouseenter', function() {
            megaMenuContainer.classList.add('active');
            isMenuOpen = true;
        });
        
        // 메가메뉴를 포함한 헤더 영역을 벗어날 때
        const headerContainer = document.querySelector('.pc-header');
        let leaveTimeout;
        
        if (headerContainer) {
            headerContainer.addEventListener('mouseleave', function(e) {
                // 마우스가 헤더와 메가메뉴 영역을 완전히 벗어났는지 확인
                leaveTimeout = setTimeout(() => {
                    megaMenuContainer.classList.remove('active');
                    isMenuOpen = false;
                    if (currentActiveItem) {
                        currentActiveItem.classList.remove('active');
                        currentActiveItem = null;
                    }
                }, 100);
            });
            
            headerContainer.addEventListener('mouseenter', function() {
                clearTimeout(leaveTimeout);
            });
        }
        
        // 메가메뉴 컨테이너에도 이벤트 추가
        megaMenuContainer.addEventListener('mouseenter', function() {
            clearTimeout(leaveTimeout);
        });
        
        megaMenuContainer.addEventListener('mouseleave', function(e) {
            // 헤더로 다시 돌아가는 경우가 아니면 메뉴 닫기
            const rect = headerContainer.getBoundingClientRect();
            if (e.clientY > rect.bottom) {
                megaMenuContainer.classList.remove('active');
                isMenuOpen = false;
                if (currentActiveItem) {
                    currentActiveItem.classList.remove('active');
                    currentActiveItem = null;
                }
            }
        });
        
        // 각 메뉴 아이템 호버 시 active 클래스 추가
        navItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                // 이전 active 제거
                if (currentActiveItem && currentActiveItem !== this) {
                    currentActiveItem.classList.remove('active');
                }
                // 새로운 active 추가
                this.classList.add('active');
                currentActiveItem = this;
            });
        });
    }
    
    // 모바일 메뉴 토글
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Mobile menu opening...');
            mobileMenu.classList.remove('hidden');
            mobileMenu.style.display = 'block';
            
            // Force reflow to ensure the transition works
            mobileMenu.offsetHeight;
            
            // Animate opacity
            setTimeout(() => {
                mobileMenu.style.opacity = '1';
            }, 10);
            
            document.body.style.overflow = 'hidden';
        });
    }
    
    function closeMobileMenu() {
        if (mobileMenu) {
            console.log('Mobile menu closing...');
            mobileMenu.style.opacity = '0';
            
            // Wait for transition to complete before hiding
            setTimeout(() => {
                mobileMenu.classList.add('hidden');
                mobileMenu.style.display = 'none';
                document.body.style.overflow = '';
            }, 300); // Match the transition duration
        }
    }
    
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileMenu();
        });
    }
    
    // 모바일 메뉴 아코디언
    const mobileNavToggles = document.querySelectorAll('.mobile-nav-toggle');
    mobileNavToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const submenu = this.nextElementSibling;
            const icon = this.querySelector('.toggle-icon');
            
            // 토글 서브메뉴
            if (submenu.classList.contains('hidden')) {
                // 열기
                submenu.classList.remove('hidden');
                // Force reflow
                submenu.offsetHeight;
                // 애니메이션 시작
                setTimeout(() => {
                    submenu.style.maxHeight = '500px';
                    submenu.style.opacity = '1';
                }, 10);
                icon.textContent = '−';
            } else {
                // 닫기
                submenu.style.maxHeight = '0';
                submenu.style.opacity = '0';
                setTimeout(() => {
                    submenu.classList.add('hidden');
                }, 300);
                icon.textContent = '+';
            }
            
            // 다른 서브메뉴 닫기
            mobileNavToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherSubmenu = otherToggle.nextElementSibling;
                    const otherIcon = otherToggle.querySelector('.toggle-icon');
                    if (!otherSubmenu.classList.contains('hidden')) {
                        otherSubmenu.style.maxHeight = '0';
                        otherSubmenu.style.opacity = '0';
                        setTimeout(() => {
                            otherSubmenu.classList.add('hidden');
                        }, 300);
                        otherIcon.textContent = '+';
                    }
                }
            });
        });
    });
});
</script>