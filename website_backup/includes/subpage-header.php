<?php
/**
 * 서브페이지 공통 헤더 컴포넌트
 * 
 * 사용법:
 * $page_header = [
 *     'category' => '대카테고리명',
 *     'title' => '페이지 제목',
 *     'background' => '배경 이미지 경로' (선택사항)
 * ];
 * include 'includes/subpage-header.php';
 */

// 기본 배경 이미지들 (카테고리별)
$default_backgrounds = [
    'about us' => BASE_URL . '/assets/images/placeholder/about-hero.jpg',
    'networks' => BASE_URL . '/assets/images/placeholder/networks-hero.jpg',
    'services' => BASE_URL . '/assets/images/placeholder/services-hero.jpg',
    'service' => BASE_URL . '/assets/images/placeholder/services-hero.jpg',
    'resources' => BASE_URL . '/assets/images/placeholder/resources-hero.jpg',
    'support' => BASE_URL . '/assets/images/placeholder/support-hero.jpg',
    'notice' => BASE_URL . '/assets/images/placeholder/notice-hero.jpg'
];

// 카테고리명 소문자로 변환하여 기본 배경 이미지 선택
$category_lower = strtolower($page_header['category'] ?? '');
$background_image = $page_header['background'] ?? $default_backgrounds[$category_lower] ?? $default_backgrounds['about us'];
?>

<!-- 서브페이지 헤더 -->
<section class="subpage-header">
    <!-- 카테고리와 제목 -->
    <div class="subpage-header-text">
        <div class="container mx-auto px-4">
            <p class="subpage-category"><?php echo htmlspecialchars($page_header['category'] ?? ''); ?></p>
            <h1 class="subpage-title"><?php echo htmlspecialchars($page_header['title'] ?? ''); ?></h1>
        </div>
    </div>
    
    <!-- 배경 이미지 -->
    <div class="subpage-header-image">
        <img src="<?php echo $background_image; ?>" 
             alt="<?php echo htmlspecialchars($page_header['title'] ?? ''); ?>"
             class="w-full h-full object-cover"
             onerror="this.style.display='none'">
    </div>
</section>

<!-- Styles moved to /assets/css/global.css -->