<?php
/**
 * 관리자 페이지 공통 함수들
 */

/**
 * XSS 방지를 위한 이스케이프
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 설정 값 가져오기
 */
function getSetting($key, $default = '') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        error_log("Get setting error: " . $e->getMessage());
        return $default;
    }
}

/**
 * 설정 값 저장
 */
function saveSetting($key, $value, $admin_id = null) {
    try {
        $pdo = getDBConnection();
        
        // UPSERT 처리
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value, updated_by) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            updated_by = VALUES(updated_by),
            updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$key, $value, $admin_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Save setting error: " . $e->getMessage());
        return false;
    }
}

/**
 * 카테고리별 설정 가져오기
 */
function getSettingsByCategory($category) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT setting_key, setting_value, setting_type, description 
            FROM site_settings 
            WHERE category = ? 
            ORDER BY id
        ");
        $stmt->execute([$category]);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row;
        }
        
        return $settings;
    } catch (Exception $e) {
        error_log("Get settings by category error: " . $e->getMessage());
        return [];
    }
}

/**
 * 파일 업로드 처리
 */
function uploadFile($file, $allowed_types = null, $upload_path = null) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 실패'];
    }
    
    // 기본 설정
    $max_size = getSetting('upload_max_size', 10485760); // 10MB
    if ($allowed_types === null) {
        $allowed_types = json_decode(getSetting('upload_allowed_types', '["jpg","jpeg","png","gif","webp"]'), true);
    }
    if ($upload_path === null) {
        $upload_path = PROJECT_ROOT . '/uploads/';
    }
    
    // 파일 크기 체크
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => '파일 크기가 너무 큽니다. (최대 ' . ($max_size / 1048576) . 'MB)'];
    }
    
    // 파일 확장자 체크
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'message' => '허용되지 않은 파일 형식입니다.'];
    }
    
    // MIME 타입 체크
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    if (isset($mime_types[$ext]) && $mime_type !== $mime_types[$ext]) {
        return ['success' => false, 'message' => '파일 형식이 일치하지 않습니다.'];
    }
    
    // 업로드 디렉토리 생성
    $year = date('Y');
    $month = date('m');
    $upload_dir = $upload_path . $year . '/' . $month . '/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // 파일명 생성 (중복 방지)
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $filepath = $upload_dir . $filename;
    
    // 파일 이동
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => '파일 저장 실패'];
    }
    
    // 이미지인 경우 리사이즈
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        resizeImage($filepath, $ext);
    }
    
    // 상대 경로 반환
    $relative_path = str_replace(PROJECT_ROOT, '', $filepath);
    
    return [
        'success' => true,
        'filename' => $filename,
        'filepath' => $relative_path,
        'filesize' => $file['size'],
        'filetype' => $ext
    ];
}

/**
 * 이미지 리사이즈
 */
function resizeImage($filepath, $ext) {
    $max_width = getSetting('upload_image_max_width', 2000);
    $max_height = getSetting('upload_image_max_height', 2000);
    $quality = getSetting('upload_image_quality', 85);
    
    list($width, $height) = getimagesize($filepath);
    
    // 리사이즈 필요 없음
    if ($width <= $max_width && $height <= $max_height) {
        return;
    }
    
    // 비율 계산
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // 이미지 생성
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($filepath);
            break;
        case 'png':
            $source = imagecreatefrompng($filepath);
            break;
        case 'gif':
            $source = imagecreatefromgif($filepath);
            break;
        case 'webp':
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return;
    }
    
    $resized = imagecreatetruecolor($new_width, $new_height);
    
    // 투명 배경 처리 (PNG, GIF)
    if ($ext === 'png' || $ext === 'gif') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // 리사이즈
    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // 저장
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($resized, $filepath, $quality);
            break;
        case 'png':
            imagepng($resized, $filepath, 9);
            break;
        case 'gif':
            imagegif($resized, $filepath);
            break;
        case 'webp':
            imagewebp($resized, $filepath, $quality);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($resized);
}

/**
 * 파일 삭제
 */
function deleteFile($filepath) {
    $full_path = PROJECT_ROOT . $filepath;
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    return false;
}

/**
 * 날짜 포맷
 */
function formatDate($date, $format = 'Y-m-d H:i') {
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * 파일 크기 포맷
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * 페이지네이션 생성
 */
function generatePagination($current_page, $total_pages, $url_pattern) {
    $pagination = [];
    $range = 2; // 현재 페이지 좌우 표시할 페이지 수
    
    // 이전 페이지
    if ($current_page > 1) {
        $pagination[] = [
            'label' => '&laquo;',
            'url' => str_replace('{page}', $current_page - 1, $url_pattern),
            'active' => false
        ];
    }
    
    // 첫 페이지
    if ($current_page > $range + 1) {
        $pagination[] = [
            'label' => '1',
            'url' => str_replace('{page}', 1, $url_pattern),
            'active' => false
        ];
        
        if ($current_page > $range + 2) {
            $pagination[] = ['label' => '...', 'url' => '#', 'active' => false];
        }
    }
    
    // 페이지 범위
    for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
        $pagination[] = [
            'label' => $i,
            'url' => str_replace('{page}', $i, $url_pattern),
            'active' => ($i == $current_page)
        ];
    }
    
    // 마지막 페이지
    if ($current_page < $total_pages - $range) {
        if ($current_page < $total_pages - $range - 1) {
            $pagination[] = ['label' => '...', 'url' => '#', 'active' => false];
        }
        
        $pagination[] = [
            'label' => $total_pages,
            'url' => str_replace('{page}', $total_pages, $url_pattern),
            'active' => false
        ];
    }
    
    // 다음 페이지
    if ($current_page < $total_pages) {
        $pagination[] = [
            'label' => '&raquo;',
            'url' => str_replace('{page}', $current_page + 1, $url_pattern),
            'active' => false
        ];
    }
    
    return $pagination;
}

/**
 * 알림 메시지 설정
 */
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * 알림 메시지 가져오기
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * JSON 응답 전송
 */
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 디버그 출력 (개발 환경에서만)
 */
function debug($data, $die = false) {
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

/**
 * 환경 변수 가져오기 (Bitnami 호환)
 */
function env($key, $default = null) {
    // 환경 변수 확인
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // getenv 확인
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // .env 파일 확인 (있는 경우)
    static $env_vars = null;
    if ($env_vars === null) {
        $env_file = PROJECT_ROOT . '/.env';
        if (file_exists($env_file)) {
            $env_vars = parse_ini_file($env_file);
        } else {
            $env_vars = [];
        }
    }
    
    return $env_vars[$key] ?? $default;
}
?>