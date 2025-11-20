<?php
/**
 * 모바일 서브 네비게이션 컴포넌트
 * 
 * 사용법:
 * $subnav_config = [
 *     'category' => '대카테고리명',
 *     'current_page' => '현재 페이지명',
 *     'current_url' => '현재 페이지 URL',
 *     'items' => [
 *         ['title' => '페이지명', 'url' => 'URL'],
 *         ...
 *     ]
 * ];
 * include 'includes/mobile-subnav.php';
 */

// 현재 페이지가 맞는지 확인하는 함수
function isCurrentSubPage($page_url, $current_url) {
    return basename($page_url) === basename($current_url);
}
?>

<!-- 모바일 서브 네비게이션 -->
<nav class="mobile-subnav lg:hidden bg-white border-b sticky top-[76px] z-40" style="border-color: var(--cg100, #F3F4F8);">
    <div class="px-4 py-3">
        <button class="w-full flex items-center justify-between" onclick="toggleMobileSubnav()">
            <div class="flex items-center">
                <span class="text-sm" style="color: var(--g900, #131313); font-weight: 600;"><?php echo htmlspecialchars($subnav_config['category'] ?? ''); ?></span>
                <span class="mx-3" style="width: 1px; height: 16px; background-color: var(--cg100, #F3F4F8); display: inline-block;"></span>
                <span class="text-sm font-medium" style="color: var(--color-primary, #E31E24);"><?php echo htmlspecialchars($subnav_config['current_page'] ?? ''); ?></span>
            </div>
            <div style="width: 32px; height: 32px; background-color: var(--cg100, #F3F4F8); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg class="w-4 h-4 transition-transform duration-300" id="subnavArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--cg900, #101223);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>
    </div>
    
    <!-- 드롭다운 메뉴 -->
    <div id="mobileSubnavDropdown" class="hidden bg-white border-t border-gray-100">
        <div class="px-4 py-2">
            <?php if (!empty($subnav_config['items'])): ?>
                <?php foreach ($subnav_config['items'] as $item): ?>
                    <?php $isActive = isCurrentSubPage($item['url'], $subnav_config['current_url']); ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 mobile-subnav-link <?php echo $isActive ? 'active' : ''; ?>">
                        <span class="mobile-subnav-text" style="color: <?php echo $isActive ? 'var(--color-primary, #E31E24)' : 'var(--g700, #374151)'; ?>;"><?php echo htmlspecialchars($item['title']); ?></span>
                        <?php if (!$isActive): ?>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- 데스크톱 서브 네비게이션 (기존) -->
<nav class="desktop-subnav hidden lg:block z-40 sticky" id="desktopSubnav" style="top: 90px;">
    <div class="container mx-auto px-4">
        <ul class="flex space-x-8">
            <?php if (!empty($subnav_config['items'])): ?>
                <?php foreach ($subnav_config['items'] as $item): ?>
                    <?php $isActive = isCurrentSubPage($item['url'], $subnav_config['current_url']); ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" 
                           class="<?php echo $isActive ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Styles moved to /assets/css/global.css -->

<script>
function toggleMobileSubnav() {
    const dropdown = document.getElementById('mobileSubnavDropdown');
    const arrow = document.getElementById('subnavArrow');
    const button = event.currentTarget;
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        dropdown.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
    } else {
        dropdown.classList.remove('hidden');
        dropdown.classList.add('show');
        button.setAttribute('aria-expanded', 'true');
    }
}

// 헤더 높이에 따른 데스크톱 서브 네비게이션 위치 동적 조정
function updateDesktopSubnavPosition() {
    const header = document.getElementById('mainHeader') || document.querySelector('.pc-header');
    const desktopSubnav = document.getElementById('desktopSubnav');
    
    if (header && desktopSubnav) {
        const headerHeight = header.offsetHeight;
        desktopSubnav.style.top = headerHeight + 'px';
    }
}

// 페이지 로드 시 드롭다운 닫기 및 데스크톱 네비게이션 위치 설정
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('mobileSubnavDropdown');
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    
    // 데스크톱 서브 네비게이션 위치 초기 설정
    updateDesktopSubnavPosition();
    
    // 창 크기 변경 시에도 위치 재조정
    window.addEventListener('resize', function() {
        // 디바운스를 위한 타이머
        clearTimeout(window.resizeTimer);
        window.resizeTimer = setTimeout(function() {
            updateDesktopSubnavPosition();
        }, 100);
    });
    
    // 스크롤 이벤트에서도 헤더 높이 변화 감지 (홈페이지의 경우)
    window.addEventListener('scroll', function() {
        // 스크롤에 의한 헤더 변화 후 위치 재조정
        requestAnimationFrame(function() {
            updateDesktopSubnavPosition();
        });
    });
});
</script>