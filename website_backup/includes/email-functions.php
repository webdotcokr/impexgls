<?php
/**
 * 이메일 발송 관련 함수들
 * 문의 접수 시 관리자에게 알림 이메일 발송
 */

// DB 연결 함수 포함
require_once dirname(__FILE__) . '/../config/db-config.php';

/**
 * 설정 값 가져오기 (프론트엔드용)
 */
if (!function_exists('getSetting')) {
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
}

/**
 * 문의 접수 알림 이메일 발송
 */
function sendQuoteNotification($quoteData) {
    try {
        // 이메일 설정 가져오기
        $smtp_host = getSetting('smtp_host');
        $smtp_port = getSetting('smtp_port', 587);
        $smtp_username = getSetting('smtp_username');
        $smtp_password = getSetting('smtp_password');
        $smtp_encryption = getSetting('smtp_encryption', 'tls');
        $from_email = getSetting('email_from_address', 'noreply@impexgls.com');
        $from_name = getSetting('email_from_name', 'IMPEX GLS');
        
        // 수신자 목록
        $recipients = json_decode(getSetting('email_notification_recipients', '[]'), true);
        if (empty($recipients)) {
            // 기본 수신자
            $recipients = [getSetting('company_email', 'contact@impexgls.com')];
        }
        
        // PHPMailer 사용 가능 여부 확인
        $phpmailer_path = dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        
        if (file_exists($phpmailer_path) && !empty($smtp_host) && !empty($smtp_username)) {
            // PHPMailer로 발송
            return sendQuoteNotificationSMTP($quoteData, $recipients);
        } else {
            // 기본 mail() 함수로 발송
            return sendQuoteNotificationBasic($quoteData, $recipients);
        }
        
    } catch (Exception $e) {
        error_log("Quote notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * PHPMailer를 사용한 이메일 발송
 */
function sendQuoteNotificationSMTP($quoteData, $recipients) {
    require_once dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP 설정
        $mail->isSMTP();
        $mail->Host = getSetting('smtp_host');
        $mail->SMTPAuth = true;
        $mail->Username = getSetting('smtp_username');
        $mail->Password = getSetting('smtp_password');
        $mail->SMTPSecure = getSetting('smtp_encryption', 'tls');
        $mail->Port = getSetting('smtp_port', 587);
        $mail->CharSet = 'UTF-8';
        
        // 발송자
        $mail->setFrom(
            getSetting('email_from_address', 'noreply@impexgls.com'),
            getSetting('email_from_name', 'IMPEX GLS')
        );
        
        // 답장 주소는 문의자 이메일로
        $mail->addReplyTo($quoteData['email'], $quoteData['contact_name']);
        
        // 수신자
        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient);
        }
        
        // 제목
        $mail->Subject = '[IMPEX GLS] 새로운 견적 문의가 접수되었습니다 #' . str_pad($quoteData['id'], 6, '0', STR_PAD_LEFT);
        
        // 내용
        $mail->isHTML(true);
        $mail->Body = getQuoteNotificationHTML($quoteData);
        $mail->AltBody = getQuoteNotificationText($quoteData);
        
        // 발송
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

/**
 * 기본 mail() 함수를 사용한 이메일 발송
 */
function sendQuoteNotificationBasic($quoteData, $recipients) {
    $to = implode(', ', $recipients);
    $subject = '[IMPEX GLS] 새로운 견적 문의가 접수되었습니다 #' . str_pad($quoteData['id'], 6, '0', STR_PAD_LEFT);
    $message = getQuoteNotificationHTML($quoteData);
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . getSetting('email_from_name', 'IMPEX GLS') . ' <' . getSetting('email_from_address', 'noreply@impexgls.com') . '>',
        'Reply-To: ' . $quoteData['contact_name'] . ' <' . $quoteData['email'] . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * 이메일 HTML 템플릿
 */
function getQuoteNotificationHTML($data) {
    $admin_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                 "://{$_SERVER['HTTP_HOST']}" . dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/admin';
    
    $html = '
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>새로운 견적 문의</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1B2951; color: white; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; }
        .info-group { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info-group h2 { color: #1B2951; font-size: 18px; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #E31E24; padding-bottom: 10px; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { font-weight: bold; color: #666; min-width: 120px; }
        .info-value { flex: 1; color: #333; }
        .message-box { background: #fff; padding: 15px; border-left: 4px solid #E31E24; margin-top: 10px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; background: #E31E24; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #FEF3C7; color: #92400E; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>새로운 견적 문의가 접수되었습니다</h1>
            <p style="margin: 5px 0;">접수번호: #' . str_pad($data['id'], 6, '0', STR_PAD_LEFT) . '</p>
        </div>
        
        <div class="content">
            <!-- 고객 정보 -->
            <div class="info-group">
                <h2>고객 정보</h2>
                <div class="info-row">
                    <span class="info-label">회사명:</span>
                    <span class="info-value">' . htmlspecialchars($data['company_name'] ?: '-') . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">담당자:</span>
                    <span class="info-value">' . htmlspecialchars($data['contact_name']) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">이메일:</span>
                    <span class="info-value"><a href="mailto:' . htmlspecialchars($data['email']) . '">' . htmlspecialchars($data['email']) . '</a></span>
                </div>
                <div class="info-row">
                    <span class="info-label">전화번호:</span>
                    <span class="info-value">' . htmlspecialchars($data['phone'] ?: '-') . '</span>
                </div>
            </div>
            
            <!-- 화물 정보 -->
            <div class="info-group">
                <h2>화물 정보</h2>
                <div class="info-row">
                    <span class="info-label">문의 유형:</span>
                    <span class="info-value">' . htmlspecialchars($data['request_type'] ?: '일반문의') . '</span>
                </div>';
    
    if (!empty($data['departure_country']) || !empty($data['departure_city'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">출발지:</span>
                    <span class="info-value">' . 
                        htmlspecialchars($data['departure_country'] ?: '') . 
                        (!empty($data['departure_city']) ? ' - ' . htmlspecialchars($data['departure_city']) : '') . 
                    '</span>
                </div>';
    }
    
    if (!empty($data['destination_country']) || !empty($data['destination_city'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">도착지:</span>
                    <span class="info-value">' . 
                        htmlspecialchars($data['destination_country'] ?: '') . 
                        (!empty($data['destination_city']) ? ' - ' . htmlspecialchars($data['destination_city']) : '') . 
                    '</span>
                </div>';
    }
    
    if (!empty($data['cargo_type'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">화물 종류:</span>
                    <span class="info-value">' . htmlspecialchars($data['cargo_type']) . '</span>
                </div>';
    }
    
    if (!empty($data['cargo_weight'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">중량:</span>
                    <span class="info-value">' . number_format($data['cargo_weight'], 2) . ' kg</span>
                </div>';
    }
    
    if (!empty($data['cargo_volume'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">부피:</span>
                    <span class="info-value">' . number_format($data['cargo_volume'], 2) . ' m³</span>
                </div>';
    }
    
    if (!empty($data['incoterms'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">인코텀즈:</span>
                    <span class="info-value">' . htmlspecialchars($data['incoterms']) . '</span>
                </div>';
    }
    
    if (!empty($data['expected_date'])) {
        $html .= '
                <div class="info-row">
                    <span class="info-label">희망 운송일:</span>
                    <span class="info-value">' . date('Y-m-d', strtotime($data['expected_date'])) . '</span>
                </div>';
    }
    
    $html .= '
            </div>
            
            <!-- 문의 내용 -->
            <div class="info-group">
                <h2>문의 내용</h2>
                <div class="message-box">
                    ' . nl2br(htmlspecialchars($data['message'] ?: '내용 없음')) . '
                </div>
            </div>
            
            <!-- 기타 정보 -->
            <div class="info-group">
                <h2>기타 정보</h2>
                <div class="info-row">
                    <span class="info-label">접수일시:</span>
                    <span class="info-value">' . date('Y-m-d H:i:s') . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">IP 주소:</span>
                    <span class="info-value">' . htmlspecialchars($data['ip_address'] ?: 'Unknown') . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">상태:</span>
                    <span class="info-value"><span class="status-badge status-pending">대기</span></span>
                </div>
            </div>
            
            <!-- 관리자 링크 -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="' . $admin_url . '/inquiries/view.php?id=' . $data['id'] . '" class="button">
                    관리자 페이지에서 확인
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>이 이메일은 IMPEX GLS 웹사이트에서 자동으로 발송되었습니다.</p>
            <p>문의사항이 있으시면 ' . htmlspecialchars(getSetting('company_email', 'contact@impexgls.com')) . '으로 연락주세요.</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

/**
 * 이메일 텍스트 버전
 */
function getQuoteNotificationText($data) {
    $text = "새로운 견적 문의가 접수되었습니다\n";
    $text .= "접수번호: #" . str_pad($data['id'], 6, '0', STR_PAD_LEFT) . "\n\n";
    
    $text .= "[ 고객 정보 ]\n";
    $text .= "회사명: " . ($data['company_name'] ?: '-') . "\n";
    $text .= "담당자: " . $data['contact_name'] . "\n";
    $text .= "이메일: " . $data['email'] . "\n";
    $text .= "전화번호: " . ($data['phone'] ?: '-') . "\n\n";
    
    $text .= "[ 화물 정보 ]\n";
    $text .= "문의 유형: " . ($data['request_type'] ?: '일반문의') . "\n";
    
    if (!empty($data['departure_country']) || !empty($data['departure_city'])) {
        $text .= "출발지: " . $data['departure_country'] . " - " . $data['departure_city'] . "\n";
    }
    if (!empty($data['destination_country']) || !empty($data['destination_city'])) {
        $text .= "도착지: " . $data['destination_country'] . " - " . $data['destination_city'] . "\n";
    }
    if (!empty($data['cargo_type'])) {
        $text .= "화물 종류: " . $data['cargo_type'] . "\n";
    }
    if (!empty($data['cargo_weight'])) {
        $text .= "중량: " . number_format($data['cargo_weight'], 2) . " kg\n";
    }
    if (!empty($data['cargo_volume'])) {
        $text .= "부피: " . number_format($data['cargo_volume'], 2) . " m³\n";
    }
    if (!empty($data['incoterms'])) {
        $text .= "인코텀즈: " . $data['incoterms'] . "\n";
    }
    if (!empty($data['expected_date'])) {
        $text .= "희망 운송일: " . date('Y-m-d', strtotime($data['expected_date'])) . "\n";
    }
    
    $text .= "\n[ 문의 내용 ]\n";
    $text .= $data['message'] ?: '내용 없음';
    $text .= "\n\n";
    
    $text .= "접수일시: " . date('Y-m-d H:i:s') . "\n";
    $text .= "IP 주소: " . ($data['ip_address'] ?: 'Unknown') . "\n";
    
    return $text;
}

/**
 * 고객에게 접수 확인 이메일 발송 (선택사항)
 */
function sendQuoteConfirmation($quoteData) {
    try {
        // 이메일 설정 확인
        $smtp_host = getSetting('smtp_host');
        $from_email = getSetting('email_from_address', 'noreply@impexgls.com');
        $from_name = getSetting('email_from_name', 'IMPEX GLS');
        
        // 이메일 내용
        $subject = '[IMPEX GLS] 견적 문의가 접수되었습니다';
        
        $html = '
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>문의 접수 확인</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1B2951; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 30px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>문의해 주셔서 감사합니다</h1>
        </div>
        
        <div class="content">
            <p>안녕하세요, ' . htmlspecialchars($quoteData['contact_name']) . '님</p>
            
            <p>IMPEX GLS에 견적 문의를 주셔서 감사합니다.<br>
            고객님의 문의가 정상적으로 접수되었으며, 담당자가 확인 후 빠른 시일 내에 연락드리겠습니다.</p>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin-top: 0;">접수 정보</h3>
                <p style="margin: 5px 0;"><strong>접수번호:</strong> #' . str_pad($quoteData['id'], 6, '0', STR_PAD_LEFT) . '</p>
                <p style="margin: 5px 0;"><strong>접수일시:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <p>문의사항이 있으시면 아래 연락처로 문의해 주세요.</p>
            
            <div style="margin-top: 30px;">
                <p style="margin: 5px 0;"><strong>전화:</strong> ' . htmlspecialchars(getSetting('company_phone', '630-227-9300')) . '</p>
                <p style="margin: 5px 0;"><strong>이메일:</strong> ' . htmlspecialchars(getSetting('company_email', 'contact@impexgls.com')) . '</p>
            </div>
        </div>
        
        <div class="footer">
            <p>IMPEX GLS - The New Standard in Global Logistics</p>
            <p>' . htmlspecialchars(getSetting('company_address', '2475 Touhy Avenue Suite 100 Elk Grove Village, IL 60007')) . '</p>
        </div>
    </div>
</body>
</html>';
        
        // PHPMailer 사용 가능 여부 확인
        $phpmailer_path = dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        
        if (file_exists($phpmailer_path) && !empty($smtp_host)) {
            // PHPMailer로 발송
            require_once $phpmailer_path;
            require_once dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once dirname(dirname(__FILE__)) . '/vendor/phpmailer/phpmailer/src/Exception.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = getSetting('smtp_host');
            $mail->SMTPAuth = true;
            $mail->Username = getSetting('smtp_username');
            $mail->Password = getSetting('smtp_password');
            $mail->SMTPSecure = getSetting('smtp_encryption', 'tls');
            $mail->Port = getSetting('smtp_port', 587);
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($quoteData['email'], $quoteData['contact_name']);
            
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            
            $mail->send();
            return true;
            
        } else {
            // 기본 mail() 함수로 발송
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $from_name . ' <' . $from_email . '>',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            return mail($quoteData['email'], $subject, $html, implode("\r\n", $headers));
        }
        
    } catch (Exception $e) {
        error_log("Quote confirmation error: " . $e->getMessage());
        return false;
    }
}
?>