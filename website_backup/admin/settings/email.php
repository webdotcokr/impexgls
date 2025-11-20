<?php
/**
 * 이메일 설정 관리
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '이메일 설정';

// 설정 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 토큰 검증
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // 이메일 테스트
    if (isset($_POST['test_email'])) {
        try {
            // 테스트 이메일 발송
            $test_email = $_POST['test_email_address'] ?? '';
            
            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('유효한 이메일 주소를 입력하세요.');
            }
            
            // PHPMailer 테스트
            $phpmailer_path = PROJECT_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
            
            if (file_exists($phpmailer_path)) {
                require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
                require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php';
                require_once PROJECT_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php';
                
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // SMTP 설정
                $mail->isSMTP();
                $mail->Host = $_POST['smtp_host'] ?? getSetting('smtp_host');
                $mail->SMTPAuth = true;
                $mail->Username = $_POST['smtp_username'] ?? getSetting('smtp_username');
                $mail->Password = $_POST['smtp_password'] ?? getSetting('smtp_password');
                $mail->SMTPSecure = $_POST['smtp_encryption'] ?? getSetting('smtp_encryption');
                $mail->Port = $_POST['smtp_port'] ?? getSetting('smtp_port');
                $mail->CharSet = 'UTF-8';
                
                // 발송 정보
                $mail->setFrom(
                    $_POST['email_from_address'] ?? getSetting('email_from_address'),
                    $_POST['email_from_name'] ?? getSetting('email_from_name')
                );
                $mail->addAddress($test_email);
                
                // 이메일 내용
                $mail->isHTML(true);
                $mail->Subject = 'IMPEX GLS 이메일 설정 테스트';
                $mail->Body = '
                    <h2>이메일 설정 테스트</h2>
                    <p>이 메일은 IMPEX GLS 관리자 시스템에서 발송된 테스트 이메일입니다.</p>
                    <p>이메일이 정상적으로 수신되었다면, SMTP 설정이 올바르게 구성되었습니다.</p>
                    <hr>
                    <p><small>발송 시간: ' . date('Y-m-d H:i:s') . '</small></p>
                ';
                
                $mail->send();
                setAlert('success', '테스트 이메일이 성공적으로 발송되었습니다.');
                
            } else {
                // mail() 함수 사용
                $headers = [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=UTF-8',
                    'From: ' . ($_POST['email_from_name'] ?? getSetting('email_from_name')) . 
                             ' <' . ($_POST['email_from_address'] ?? getSetting('email_from_address')) . '>',
                    'X-Mailer: PHP/' . phpversion()
                ];
                
                $subject = 'IMPEX GLS 이메일 설정 테스트';
                $message = '
                    <h2>이메일 설정 테스트</h2>
                    <p>이 메일은 IMPEX GLS 관리자 시스템에서 발송된 테스트 이메일입니다.</p>
                    <p>이메일이 정상적으로 수신되었다면, 기본 mail() 함수가 작동하고 있습니다.</p>
                    <hr>
                    <p><small>발송 시간: ' . date('Y-m-d H:i:s') . '</small></p>
                ';
                
                if (mail($test_email, $subject, $message, implode("\r\n", $headers))) {
                    setAlert('success', '테스트 이메일이 발송되었습니다. (기본 mail 함수 사용)');
                } else {
                    throw new Exception('이메일 발송에 실패했습니다.');
                }
            }
            
        } catch (Exception $e) {
            setAlert('error', '테스트 이메일 발송 실패: ' . $e->getMessage());
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // 설정 저장
    if (isset($_POST['save_settings'])) {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // 이메일 설정 저장
            $settings_to_save = [
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption',
                'email_from_address', 'email_from_name'
            ];
            
            // SMTP 비밀번호는 입력된 경우에만 저장
            if (!empty($_POST['smtp_password'])) {
                // 실제 운영 환경에서는 암호화 필요
                saveSetting('smtp_password', $_POST['smtp_password'], $_SESSION['admin_id']);
            }
            
            foreach ($settings_to_save as $key) {
                if (isset($_POST[$key])) {
                    saveSetting($key, $_POST[$key], $_SESSION['admin_id']);
                }
            }
            
            // 알림 수신자 목록 저장 (JSON 형식)
            if (isset($_POST['notification_emails'])) {
                $emails = array_filter(array_map('trim', explode("\n", $_POST['notification_emails'])));
                $valid_emails = [];
                
                foreach ($emails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $valid_emails[] = $email;
                    }
                }
                
                saveSetting('email_notification_recipients', json_encode($valid_emails), $_SESSION['admin_id']);
            }
            
            // 활동 로그
            logAdminAction('update', 'site_settings', null, 'Email settings updated');
            
            $pdo->commit();
            setAlert('success', '이메일 설정이 성공적으로 저장되었습니다.');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Email settings error: " . $e->getMessage());
            setAlert('error', '설정 저장 중 오류가 발생했습니다.');
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// 현재 설정 가져오기
$email_settings = getSettingsByCategory('email');

// 알림 수신자 목록 파싱
$notification_recipients = json_decode($email_settings['email_notification_recipients']['setting_value'] ?? '[]', true);
$notification_emails = implode("\n", $notification_recipients);

// CSRF 토큰 생성
$csrf_token = generateCSRFToken();

// PHPMailer 설치 여부 확인
$phpmailer_installed = file_exists(PROJECT_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- PHPMailer 상태 알림 -->
    <?php if (!$phpmailer_installed): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
        <div class="flex">
            <div class="py-1">
                <i class="fas fa-exclamation-triangle mr-2"></i>
            </div>
            <div>
                <p class="font-bold">PHPMailer가 설치되지 않았습니다.</p>
                <p class="text-sm">SMTP 기능을 사용하려면 PHPMailer를 설치해야 합니다. 기본 mail() 함수를 사용할 수 있습니다.</p>
                <p class="text-sm mt-2">
                    설치 명령: <code class="bg-yellow-200 px-2 py-1 rounded">composer require phpmailer/phpmailer</code>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
        
        <!-- SMTP 설정 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">SMTP 설정</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- SMTP 서버 -->
                <div>
                    <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP 서버 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="smtp_host" 
                           name="smtp_host" 
                           value="<?php echo e($email_settings['smtp_host']['setting_value'] ?? ''); ?>"
                           placeholder="smtp.gmail.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500">
                        Gmail: smtp.gmail.com | Naver: smtp.naver.com | AWS SES: email-smtp.region.amazonaws.com
                    </p>
                </div>
                
                <!-- 포트 / 암호화 -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-2">
                            SMTP 포트 <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="smtp_port" 
                               name="smtp_port" 
                               value="<?php echo e($email_settings['smtp_port']['setting_value'] ?? '587'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        <p class="mt-1 text-sm text-gray-500">TLS: 587 | SSL: 465</p>
                    </div>
                    <div>
                        <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 mb-2">
                            암호화 방식 <span class="text-red-500">*</span>
                        </label>
                        <select id="smtp_encryption" 
                                name="smtp_encryption"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="tls" <?php echo ($email_settings['smtp_encryption']['setting_value'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($email_settings['smtp_encryption']['setting_value'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="" <?php echo ($email_settings['smtp_encryption']['setting_value'] ?? '') == '' ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                </div>
                
                <!-- SMTP 인증 정보 -->
                <div>
                    <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP 사용자명 (이메일) <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="smtp_username" 
                           name="smtp_username" 
                           value="<?php echo e($email_settings['smtp_username']['setting_value'] ?? ''); ?>"
                           placeholder="your-email@gmail.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <div>
                    <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-2">
                        SMTP 비밀번호
                    </label>
                    <input type="password" 
                           id="smtp_password" 
                           name="smtp_password" 
                           placeholder="비밀번호를 입력하세요 (변경 시에만)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        Gmail: 앱 비밀번호 사용 필요 (2단계 인증 설정 후)
                    </p>
                </div>
            </div>
        </div>
        
        <!-- 발송 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">발송 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 발송자 이메일 -->
                <div>
                    <label for="email_from_address" class="block text-sm font-medium text-gray-700 mb-2">
                        발송자 이메일 <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email_from_address" 
                           name="email_from_address" 
                           value="<?php echo e($email_settings['email_from_address']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <p class="mt-1 text-sm text-gray-500">
                        SMTP 사용자명과 동일해야 합니다.
                    </p>
                </div>
                
                <!-- 발송자 이름 -->
                <div>
                    <label for="email_from_name" class="block text-sm font-medium text-gray-700 mb-2">
                        발송자 이름 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="email_from_name" 
                           name="email_from_name" 
                           value="<?php echo e($email_settings['email_from_name']['setting_value'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 알림 수신자 -->
                <div>
                    <label for="notification_emails" class="block text-sm font-medium text-gray-700 mb-2">
                        알림 수신 이메일 목록
                    </label>
                    <textarea id="notification_emails" 
                              name="notification_emails" 
                              rows="4"
                              placeholder="email1@example.com&#10;email2@example.com"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($notification_emails); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">
                        문의 접수 시 알림을 받을 이메일 주소를 한 줄에 하나씩 입력하세요.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- 테스트 이메일 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">이메일 테스트</h3>
            </div>
            <div class="p-6">
                <div class="flex items-end space-x-4">
                    <div class="flex-1">
                        <label for="test_email_address" class="block text-sm font-medium text-gray-700 mb-2">
                            테스트 수신 이메일
                        </label>
                        <input type="email" 
                               id="test_email_address" 
                               name="test_email_address" 
                               placeholder="test@example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" 
                            name="test_email"
                            value="1"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-paper-plane mr-2"></i>
                        테스트 발송
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    현재 설정으로 테스트 이메일을 발송합니다.
                </p>
            </div>
        </div>
        
        <!-- 저장 버튼 -->
        <div class="flex justify-end">
            <button type="submit" 
                    name="save_settings"
                    value="1"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                <i class="fas fa-save mr-2"></i>
                설정 저장
            </button>
        </div>
    </form>
</div>

<!-- 도움말 -->
<div class="mt-8 bg-blue-50 rounded-lg p-6 max-w-4xl">
    <h4 class="text-lg font-semibold text-blue-900 mb-3">
        <i class="fas fa-info-circle mr-2"></i>
        이메일 설정 도움말
    </h4>
    <div class="space-y-3 text-sm text-blue-800">
        <div>
            <strong>Gmail 설정:</strong>
            <ol class="ml-6 mt-1 list-decimal">
                <li>Google 계정에서 2단계 인증 활성화</li>
                <li>앱 비밀번호 생성 (계정 설정 > 보안 > 앱 비밀번호)</li>
                <li>SMTP 비밀번호란에 앱 비밀번호 입력</li>
            </ol>
        </div>
        <div>
            <strong>일반적인 SMTP 설정:</strong>
            <ul class="ml-6 mt-1 list-disc">
                <li>Gmail: smtp.gmail.com / 포트 587 (TLS)</li>
                <li>Naver: smtp.naver.com / 포트 587 (TLS)</li>
                <li>Daum: smtp.daum.net / 포트 465 (SSL)</li>
            </ul>
        </div>
        <div>
            <strong>문제 해결:</strong>
            <ul class="ml-6 mt-1 list-disc">
                <li>발송자 이메일은 반드시 SMTP 계정과 동일해야 함</li>
                <li>방화벽에서 SMTP 포트가 차단되지 않았는지 확인</li>
                <li>서버 시간이 정확한지 확인 (SSL 인증서 문제)</li>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>