<?php
/**
 * 사이트 설정 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '사이트 설정';

// 설정 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // CSRF 토큰 검증
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();
        
        // 파비콘 업로드 처리
        if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['ico', 'png', 'jpg', 'jpeg'];
            $upload_result = uploadFile($_FILES['favicon_file'], $allowed_types, PROJECT_ROOT . '/assets/images/');
            
            if ($upload_result['success']) {
                $_POST['site_favicon'] = $upload_result['filepath'];
                // 기존 파비콘 삭제
                $old_favicon = getSetting('site_favicon');
                if ($old_favicon && $old_favicon !== $_POST['site_favicon']) {
                    deleteFile($old_favicon);
                }
            }
        }
        
        // OG 이미지 업로드 처리
        if (isset($_FILES['og_image_file']) && $_FILES['og_image_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            $upload_result = uploadFile($_FILES['og_image_file'], $allowed_types, PROJECT_ROOT . '/assets/images/');
            
            if ($upload_result['success']) {
                $_POST['og_image'] = $upload_result['filepath'];
                // 기존 OG 이미지 삭제
                $old_og_image = getSetting('og_image');
                if ($old_og_image && $old_og_image !== $_POST['og_image']) {
                    deleteFile($old_og_image);
                }
            }
        }
        
        // 설정 저장
        $settings_to_save = [
            // 기본 설정
            'site_title', 'site_tagline', 'site_description', 'site_keywords', 
            'site_author', 'site_favicon',
            
            // 연락처 정보
            'company_name', 'company_address', 'company_phone', 'company_fax', 'company_email',
            
            // SEO 설정
            'og_type', 'og_image', 'google_analytics_id', 'google_site_verification', 
            'naver_site_verification',
            
            // 소셜 미디어
            'social_facebook', 'social_twitter', 'social_linkedin', 
            'social_instagram', 'social_youtube'
        ];
        
        foreach ($settings_to_save as $key) {
            if (isset($_POST[$key])) {
                saveSetting($key, $_POST[$key], $_SESSION['admin_id']);
            }
        }
        
        // 활동 로그
        logAdminAction('update', 'site_settings', null, 'Site settings updated');
        
        $pdo->commit();
        setAlert('success', '설정이 성공적으로 저장되었습니다.');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Site settings error: " . $e->getMessage());
        setAlert('error', '설정 저장 중 오류가 발생했습니다.');
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// 현재 설정 가져오기
$general_settings = getSettingsByCategory('general');
$contact_settings = getSettingsByCategory('contact');
$seo_settings = getSettingsByCategory('seo');
$social_settings = getSettingsByCategory('social');

// CSRF 토큰 생성
$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<form method="POST" action="" enctype="multipart/form-data" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
    
    <!-- 기본 설정 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">기본 설정</h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- 사이트 제목 -->
            <div>
                <label for="site_title" class="block text-sm font-medium text-gray-700 mb-2">
                    사이트 제목 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="site_title" 
                       name="site_title" 
                       value="<?php echo e($general_settings['site_title']['setting_value'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                <p class="mt-1 text-sm text-gray-500">브라우저 탭에 표시되는 제목입니다.</p>
            </div>
            
            <!-- 사이트 부제목 -->
            <div>
                <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-2">
                    사이트 부제목
                </label>
                <input type="text" 
                       id="site_tagline" 
                       name="site_tagline" 
                       value="<?php echo e($general_settings['site_tagline']['setting_value'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 사이트 설명 -->
            <div>
                <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">
                    사이트 설명
                </label>
                <textarea id="site_description" 
                          name="site_description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($general_settings['site_description']['setting_value'] ?? ''); ?></textarea>
                <p class="mt-1 text-sm text-gray-500">검색 엔진에 표시되는 설명입니다. (160자 이내 권장)</p>
            </div>
            
            <!-- 키워드 -->
            <div>
                <label for="site_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                    키워드
                </label>
                <textarea id="site_keywords" 
                          name="site_keywords" 
                          rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($general_settings['site_keywords']['setting_value'] ?? ''); ?></textarea>
                <p class="mt-1 text-sm text-gray-500">SEO 키워드를 쉼표로 구분하여 입력하세요.</p>
            </div>
            
            <!-- 작성자 -->
            <div>
                <label for="site_author" class="block text-sm font-medium text-gray-700 mb-2">
                    사이트 작성자
                </label>
                <input type="text" 
                       id="site_author" 
                       name="site_author" 
                       value="<?php echo e($general_settings['site_author']['setting_value'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 파비콘 -->
            <div>
                <label for="favicon_file" class="block text-sm font-medium text-gray-700 mb-2">
                    파비콘
                </label>
                <div class="flex items-center space-x-4">
                    <?php if (!empty($general_settings['site_favicon']['setting_value'])): ?>
                    <img src="<?php echo BASE_URL . e($general_settings['site_favicon']['setting_value']); ?>" 
                         alt="Favicon" 
                         class="w-8 h-8">
                    <?php endif; ?>
                    <input type="file" 
                           id="favicon_file" 
                           name="favicon_file" 
                           accept=".ico,.png,.jpg,.jpeg"
                           class="flex-1">
                </div>
                <p class="mt-1 text-sm text-gray-500">ICO, PNG, JPG 형식 (32x32px 권장)</p>
            </div>
        </div>
    </div>
    
    <!-- 연락처 정보 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">연락처 정보</h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- 회사명 -->
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                    회사명
                </label>
                <input type="text" 
                       id="company_name" 
                       name="company_name" 
                       value="<?php echo e($contact_settings['company_name']['setting_value'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 주소 -->
            <div>
                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">
                    주소
                </label>
                <textarea id="company_address" 
                          name="company_address" 
                          rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($contact_settings['company_address']['setting_value'] ?? ''); ?></textarea>
            </div>
            
            <!-- 전화/팩스 -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        전화번호
                    </label>
                    <input type="text" 
                           id="company_phone" 
                           name="company_phone" 
                           value="<?php echo e($contact_settings['company_phone']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="company_fax" class="block text-sm font-medium text-gray-700 mb-2">
                        팩스번호
                    </label>
                    <input type="text" 
                           id="company_fax" 
                           name="company_fax" 
                           value="<?php echo e($contact_settings['company_fax']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <!-- 이메일 -->
            <div>
                <label for="company_email" class="block text-sm font-medium text-gray-700 mb-2">
                    대표 이메일
                </label>
                <input type="email" 
                       id="company_email" 
                       name="company_email" 
                       value="<?php echo e($contact_settings['company_email']['setting_value'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
    
    <!-- SEO 설정 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">SEO 설정</h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- OG Type -->
            <div>
                <label for="og_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Open Graph Type
                </label>
                <select id="og_type" 
                        name="og_type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="website" <?php echo ($seo_settings['og_type']['setting_value'] ?? '') == 'website' ? 'selected' : ''; ?>>Website</option>
                    <option value="article" <?php echo ($seo_settings['og_type']['setting_value'] ?? '') == 'article' ? 'selected' : ''; ?>>Article</option>
                </select>
            </div>
            
            <!-- OG 이미지 -->
            <div>
                <label for="og_image_file" class="block text-sm font-medium text-gray-700 mb-2">
                    Open Graph 이미지
                </label>
                <div class="space-y-2">
                    <?php if (!empty($seo_settings['og_image']['setting_value'])): ?>
                    <img src="<?php echo BASE_URL . e($seo_settings['og_image']['setting_value']); ?>" 
                         alt="OG Image" 
                         class="max-w-xs rounded border">
                    <?php endif; ?>
                    <input type="file" 
                           id="og_image_file" 
                           name="og_image_file" 
                           accept=".jpg,.jpeg,.png,.webp"
                           class="w-full">
                </div>
                <p class="mt-1 text-sm text-gray-500">권장 크기: 1200x630px (JPG, PNG, WEBP)</p>
            </div>
            
            <!-- Google Analytics -->
            <div>
                <label for="google_analytics_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Google Analytics ID
                </label>
                <input type="text" 
                       id="google_analytics_id" 
                       name="google_analytics_id" 
                       value="<?php echo e($seo_settings['google_analytics_id']['setting_value'] ?? ''); ?>"
                       placeholder="UA-XXXXX-X 또는 G-XXXXXXXX"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- 사이트 인증 -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="google_site_verification" class="block text-sm font-medium text-gray-700 mb-2">
                        Google Site Verification
                    </label>
                    <input type="text" 
                           id="google_site_verification" 
                           name="google_site_verification" 
                           value="<?php echo e($seo_settings['google_site_verification']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="naver_site_verification" class="block text-sm font-medium text-gray-700 mb-2">
                        Naver Site Verification
                    </label>
                    <input type="text" 
                           id="naver_site_verification" 
                           name="naver_site_verification" 
                           value="<?php echo e($seo_settings['naver_site_verification']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>
    
    <!-- 소셜 미디어 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">소셜 미디어</h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- Facebook -->
            <div>
                <label for="social_facebook" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-facebook mr-2"></i>Facebook URL
                </label>
                <input type="url" 
                       id="social_facebook" 
                       name="social_facebook" 
                       value="<?php echo e($social_settings['social_facebook']['setting_value'] ?? ''); ?>"
                       placeholder="https://facebook.com/yourpage"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Twitter -->
            <div>
                <label for="social_twitter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-twitter mr-2"></i>Twitter URL
                </label>
                <input type="url" 
                       id="social_twitter" 
                       name="social_twitter" 
                       value="<?php echo e($social_settings['social_twitter']['setting_value'] ?? ''); ?>"
                       placeholder="https://twitter.com/yourhandle"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- LinkedIn -->
            <div>
                <label for="social_linkedin" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-linkedin mr-2"></i>LinkedIn URL
                </label>
                <input type="url" 
                       id="social_linkedin" 
                       name="social_linkedin" 
                       value="<?php echo e($social_settings['social_linkedin']['setting_value'] ?? ''); ?>"
                       placeholder="https://linkedin.com/company/yourcompany"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Instagram -->
            <div>
                <label for="social_instagram" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-instagram mr-2"></i>Instagram URL
                </label>
                <input type="url" 
                       id="social_instagram" 
                       name="social_instagram" 
                       value="<?php echo e($social_settings['social_instagram']['setting_value'] ?? ''); ?>"
                       placeholder="https://instagram.com/yourhandle"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- YouTube -->
            <div>
                <label for="social_youtube" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fab fa-youtube mr-2"></i>YouTube URL
                </label>
                <input type="url" 
                       id="social_youtube" 
                       name="social_youtube" 
                       value="<?php echo e($social_settings['social_youtube']['setting_value'] ?? ''); ?>"
                       placeholder="https://youtube.com/channel/yourchannel"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
    
    <!-- 저장 버튼 -->
    <div class="flex justify-end">
        <button type="submit" 
                name="save_settings"
                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-save mr-2"></i>
            설정 저장
        </button>
    </div>
</form>

<?php include '../includes/footer.php'; ?>