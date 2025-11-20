<?php
/**
 * 공통 함수 라이브러리
 */

// XSS 방지를 위한 이스케이프 함수
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 개행 문자를 HTML <br> 태그로 변환하는 함수
function e_nl2br($string) {
    return nl2br(htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8'));
}

// 현재 페이지가 활성화된 메뉴인지 확인
function isActiveMenu($menu_url, $current_path) {
    $menu_path = parse_url($menu_url, PHP_URL_PATH);
    return strpos($current_path, $menu_path) !== false;
}

// 메타 태그 생성 함수
function generateMetaTags($page_info) {
    $html = '';
    $html .= '<title>' . e($page_info['title']) . '</title>' . "\n";
    $html .= '<meta name="description" content="' . e($page_info['description']) . '">' . "\n";
    $html .= '<meta name="keywords" content="' . e($page_info['keywords']) . '">' . "\n";
    $html .= '<meta name="author" content="' . e($page_info['author']) . '">' . "\n";
    
    // Open Graph 태그
    $html .= '<meta property="og:title" content="' . e($page_info['title']) . '">' . "\n";
    $html .= '<meta property="og:description" content="' . e($page_info['description']) . '">' . "\n";
    $html .= '<meta property="og:image" content="' . e($page_info['og_image']) . '">' . "\n";
    $html .= '<meta property="og:type" content="' . e($page_info['og_type']) . '">' . "\n";
    
    // Twitter Card 태그
    $html .= '<meta name="twitter:card" content="' . e($page_info['twitter_card']) . '">' . "\n";
    $html .= '<meta name="twitter:title" content="' . e($page_info['title']) . '">' . "\n";
    $html .= '<meta name="twitter:description" content="' . e($page_info['description']) . '">' . "\n";
    $html .= '<meta name="twitter:image" content="' . e($page_info['og_image']) . '">' . "\n";
    
    return $html;
}

// 날짜 포맷 함수
function formatDate($date, $format = 'Y.m.d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// 파일 업로드 함수
function uploadFile($file, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    if (empty($file['name'])) {
        return ['success' => false, 'error' => '파일이 선택되지 않았습니다.'];
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'error' => '허용되지 않은 파일 형식입니다.'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB 제한
        return ['success' => false, 'error' => '파일 크기가 너무 큽니다. (최대 5MB)'];
    }
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename, 'path' => $upload_path];
    } else {
        return ['success' => false, 'error' => '파일 업로드에 실패했습니다.'];
    }
}

// 페이지네이션 생성 함수
function generatePagination($current_page, $total_pages, $url_pattern) {
    $html = '<div class="pagination flex justify-center gap-2 mt-8">';
    
    // 이전 버튼
    if ($current_page > 1) {
        $html .= '<a href="' . sprintf($url_pattern, $current_page - 1) . '" class="px-3 py-1 border rounded hover:bg-gray-100">&laquo;</a>';
    }
    
    // 페이지 번호
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . sprintf($url_pattern, 1) . '" class="px-3 py-1 border rounded hover:bg-gray-100">1</a>';
        if ($start > 2) {
            $html .= '<span class="px-3 py-1">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $class = $i == $current_page ? 'bg-red-600 text-white' : 'hover:bg-gray-100';
        $html .= '<a href="' . sprintf($url_pattern, $i) . '" class="px-3 py-1 border rounded ' . $class . '">' . $i . '</a>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span class="px-3 py-1">...</span>';
        }
        $html .= '<a href="' . sprintf($url_pattern, $total_pages) . '" class="px-3 py-1 border rounded hover:bg-gray-100">' . $total_pages . '</a>';
    }
    
    // 다음 버튼
    if ($current_page < $total_pages) {
        $html .= '<a href="' . sprintf($url_pattern, $current_page + 1) . '" class="px-3 py-1 border rounded hover:bg-gray-100">&raquo;</a>';
    }
    
    $html .= '</div>';
    return $html;
}

// 이메일 전송 함수 (PHPMailer 사용 시)
function sendEmail($to, $subject, $body, $from = null) {
    // 간단한 mail() 함수 사용 (실제로는 PHPMailer 추천)
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . ($from ?: ADMIN_EMAIL) . "\r\n";
    
    return mail($to, $subject, $body, $headers);
}

// 관리자 로그 기록 함수
function logAdminAction($pdo, $admin_id, $action, $table_name = null, $record_id = null) {
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs (admin_id, action, table_name, record_id, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $admin_id,
        $action,
        $table_name,
        $record_id,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// 안전한 리다이렉트 함수
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header('Location: ' . $url);
    exit;
}

// 세션 메시지 표시 함수
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        
        $type = strpos($message, '성공') !== false ? 'success' : 'error';
        $bg_class = $type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        
        return '<div class="alert alert-auto-hide ' . $bg_class . ' p-4 rounded mb-4">' . e($message) . '</div>';
    }
    return '';
}

// JSON 응답 함수
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 문자열 자르기 함수
function truncateString($string, $length = 100, $append = '...') {
    if (mb_strlen($string) > $length) {
        return mb_substr($string, 0, $length) . $append;
    }
    return $string;
}

// 브레드크럼 생성 함수
function generateBreadcrumb($items) {
    $html = '<nav class="breadcrumb mb-6">';
    $html .= '<ol class="flex items-center space-x-2 text-sm">';
    
    foreach ($items as $index => $item) {
        if ($index > 0) {
            $html .= '<li class="before:content-[\'/\'] before:mx-2 before:text-gray-400">';
        } else {
            $html .= '<li>';
        }
        
        if (isset($item['url']) && $index < count($items) - 1) {
            $html .= '<a href="' . $item['url'] . '" class="text-gray-600 hover:text-gray-900">' . e($item['title']) . '</a>';
        } else {
            $html .= '<span class="text-gray-900 font-medium">' . e($item['title']) . '</span>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}

// 언어 감지 함수
function detectLanguage() {
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'ko';
}

// 다국어 텍스트 가져오기
if (!function_exists('getText')) {
    function getText($ko_text, $en_text = null) {
        $lang = detectLanguage();
        if ($lang == 'en' && $en_text) {
            return $en_text;
        }
        return $ko_text;
    }
}

// office name에서 (Satellite Office), (Affiliated Agency) 및 Agent 패턴 텍스트를 span으로 감싸는 함수
function formatOfficeName($office_name) {
    // 줄바꿈 처리를 먼저 적용
    $office_name = nl2br(e($office_name));
    
    // (Satellite Office) 패턴 처리
    if (strpos($office_name, '(Satellite Office)') !== false) {
        $office_name = str_replace('(Satellite Office)', '<span class="satellite-text">(Satellite Office)</span>', $office_name);
    }
    
    // (Affiliated Agency) 패턴 처리
    if (strpos($office_name, '(Affiliated Agency)') !== false) {
        $office_name = str_replace('(Affiliated Agency)', '<span class="satellite-text">(Affiliated Agency)</span>', $office_name);
    }
    
    // Agent 패턴 처리 (Agent for ~ FMC Lic. # 번호까지 전체)
    $office_name = preg_replace('/Agent for [^<]+FMC Lic\. # [0-9A-Za-z]+/', '<span class="satellite-text">$0</span>', $office_name);
    
    return $office_name;
}
?>