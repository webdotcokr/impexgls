<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';
require_once '../../includes/email-functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/support/request-quote.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 폼 제출 처리
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        // 데이터 검증 및 정리 - DB 스키마에 맞게 수정
        $request_type = $_POST['mode_of_transport'] ?? 'General';
        $company_name = trim($_POST['company_name'] ?? '');
        $contact_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email_address'] ?? '');
        $phone = trim($_POST['telephone_number'] ?? '');
        $departure_country = trim($_POST['place_of_country'] ?? '');
        $departure_city = trim($_POST['original_point'] ?? '') . (!empty($_POST['departure_air_port']) ? ' (' . trim($_POST['departure_air_port']) . ')' : '');
        $destination_country = trim($_POST['destination'] ?? '');
        $destination_city = trim($_POST['destination_air_port'] ?? '');
        $cargo_type = '';
        $cargo_weight = floatval($_POST['total_gross_weight'] ?? 0);
        $cargo_volume = 0; // 부피 계산 필요시 추가
        $incoterms = trim($_POST['terms_conditions'] ?? '');
        $expected_date = $_POST['estimate_date_of_shipping'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // 메시지 조합
        $message_parts = [];
        
        // 배송 주소 정보
        if (!empty($_POST['shipper_address'])) {
            $message_parts[] = "Shipper Address: " . trim($_POST['shipper_address']);
        }
        if (!empty($_POST['consignee_address'])) {
            $message_parts[] = "Consignee Address: " . trim($_POST['consignee_address']);
        }
        
        // 특별 요청사항
        $special_requests = [];
        if (isset($_POST['special_request_transportation_insurance'])) {
            $special_requests[] = "Transportation Insurance";
        }
        if (isset($_POST['special_request_dangerous_goods'])) {
            $special_requests[] = "Dangerous Goods";
        }
        if (isset($_POST['special_request_handling'])) {
            $special_requests[] = "Special Handling";
        }
        if (!empty($special_requests)) {
            $message_parts[] = "Special Requests: " . implode(', ', $special_requests);
        }
        
        // 위험물 정보
        if (($_POST['dangerous_item'] ?? 'No') !== 'No') {
            $message_parts[] = "Dangerous Item: " . $_POST['dangerous_item'];
        }
        
        // 비고사항
        if (!empty($_POST['remarks_requirements'])) {
            $message_parts[] = "Remarks: " . trim($_POST['remarks_requirements']);
        }
        
        // 단위 정보
        $message_parts[] = "Weight Unit: " . ($_POST['weight_unit'] ?? 'KG');
        $message_parts[] = "Dimension Unit: " . ($_POST['dimension_unit'] ?? 'CM');
        
        // 패키지 정보 수집
        $packages = [];
        if (isset($_POST['packages']) && is_array($_POST['packages'])) {
            foreach ($_POST['packages'] as $package) {
                if (!empty($package['qty']) || !empty($package['length'])) {
                    $packages[] = [
                        'qty' => $package['qty'] ?? '',
                        'length' => $package['length'] ?? '',
                        'width' => $package['width'] ?? '',
                        'height' => $package['height'] ?? '',
                        'commodity' => $package['commodity'] ?? '',
                        'htc_code' => $package['htc_code'] ?? '',
                        'package_type' => $package['package_type'] ?? ''
                    ];
                    
                    // 화물 종류 추가
                    if (!empty($package['commodity']) && empty($cargo_type)) {
                        $cargo_type = $package['commodity'];
                    }
                }
            }
        }
        
        if (!empty($packages)) {
            $message_parts[] = "\n\nPackage Details:\n" . json_encode($packages, JSON_PRETTY_PRINT);
        }
        
        $message = implode("\n\n", $message_parts);
        
        // 이메일 유효성 검사
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // 파일 업로드 처리
        $attachments = [];
        if (isset($_FILES['attached_file']) && $_FILES['attached_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/quotes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['attached_file']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('quote_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['attached_file']['tmp_name'], $upload_path)) {
                $attachments[] = [
                    'name' => $_FILES['attached_file']['name'],
                    'path' => '/uploads/quotes/' . $new_filename,
                    'size' => $_FILES['attached_file']['size']
                ];
            }
        }
        
        // 데이터베이스에 저장
        $stmt = $pdo->prepare("
            INSERT INTO quote_requests (
                request_type, company_name, contact_name, email, phone,
                departure_country, departure_city, destination_country, destination_city,
                cargo_type, cargo_weight, cargo_volume, incoterms,
                expected_date, message, attachments, ip_address, status
            ) VALUES (
                :request_type, :company_name, :contact_name, :email, :phone,
                :departure_country, :departure_city, :destination_country, :destination_city,
                :cargo_type, :cargo_weight, :cargo_volume, :incoterms,
                :expected_date, :message, :attachments, :ip_address, 'pending'
            )
        ");
        
        $stmt->execute([
            ':request_type' => $request_type,
            ':company_name' => $company_name,
            ':contact_name' => $contact_name,
            ':email' => $email,
            ':phone' => $phone,
            ':departure_country' => $departure_country,
            ':departure_city' => $departure_city,
            ':destination_country' => $destination_country,
            ':destination_city' => $destination_city,
            ':cargo_type' => $cargo_type,
            ':cargo_weight' => $cargo_weight,
            ':cargo_volume' => $cargo_volume,
            ':incoterms' => $incoterms,
            ':expected_date' => $expected_date,
            ':message' => $message,
            ':attachments' => json_encode($attachments),
            ':ip_address' => $ip_address
        ]);
        
        // 생성된 문의 ID 가져오기
        $quote_id = $pdo->lastInsertId();
        
        // 이메일 알림 발송
        $quoteData = [
            'id' => $quote_id,
            'request_type' => $request_type,
            'company_name' => $company_name,
            'contact_name' => $contact_name,
            'email' => $email,
            'phone' => $phone,
            'departure_country' => $departure_country,
            'departure_city' => $departure_city,
            'destination_country' => $destination_country,
            'destination_city' => $destination_city,
            'cargo_type' => $cargo_type,
            'cargo_weight' => $cargo_weight,
            'cargo_volume' => $cargo_volume,
            'incoterms' => $incoterms,
            'expected_date' => $expected_date,
            'message' => $message,
            'ip_address' => $ip_address
        ];
        
        // 관리자에게 알림 이메일 발송
        sendQuoteNotification($quoteData);
        
        // 고객에게 접수 확인 이메일 발송 (선택사항)
        if (getSetting('send_customer_confirmation', 'true') === 'true') {
            sendQuoteConfirmation($quoteData);
        }
        
        $message = 'Thank you for your quote request. Our team will contact you within 24 hours.';
        $messageType = 'success';
        
        // 폼 초기화를 위해 POST 데이터 삭제
        $_POST = [];
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
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
    
    <style>
        /* Form specific styles */
        .form-container {
            background: white;
            padding: 0;
        }
        
        .form-wrapper {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        @media (max-width: 768px) {
            .form-wrapper {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
        
        .form-section {
            background: white;
            padding: 0;
        }
        
        .section-title {
            color: var(--g900, #131313);
            font-family: Poppins;
            font-size: 28px;
            font-style: normal;
            font-weight: 700;
            line-height: 40px;
            letter-spacing: -0.84px;
            margin: 0;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--cg700, #404252);
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px;
            letter-spacing: -0.42px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: white;
        }
        
        .form-input::placeholder,
        .form-textarea::placeholder {
            color: #9ca3af;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(227, 30, 36, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-question-gap {
            margin-bottom: 2.5rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 24px;
                font-style: normal;
                font-weight: 700;
                line-height: 40px; /* 166.667% */
                letter-spacing: -0.72px;
            }
            
            .body-lg {
                font-size: 16px;
                font-style: normal;
                font-weight: 500;
                line-height: 26px; /* 162.5% */
                letter-spacing: -0.48px;
            }
            
            .form-question-gap {
                margin-bottom: 1rem !important;
            }
            
            /* Mobile Package Table Styles */
            .package-table {
                display: none !important;
            }
            
            .mobile-package-cards {
                display: block !important;
            }
            
            .mobile-package-card {
                background: #F8F9FA;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                position: relative;
            }
            
            .mobile-package-card input,
            .mobile-package-card select {
                width: 100%;
                padding: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
                font-size: 14px;
                background: white;
            }
            
            .mobile-package-number {
                font-size: 20px;
                font-weight: 700;
                color: #000;
                margin-bottom: 16px;
            }
            
            .mobile-package-field {
                margin-bottom: 16px;
            }
            
            .mobile-package-field label {
                display: block;
                font-size: 14px;
                font-weight: 500;
                color: #374151;
                margin-bottom: 8px;
            }
            
            .mobile-dimension-group {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                margin-bottom: 16px;
            }
            
            .mobile-dimension-field {
                display: flex;
                flex-direction: column;
            }
            
            .mobile-dimension-field label {
                font-size: 12px;
                color: #6B7280;
                margin-bottom: 4px;
            }
            
            .mobile-remove-btn {
                position: absolute;
                top: 20px;
                right: 20px;
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
            }
            
            .volume-container {
                flex-direction: column;
                gap: 16px;
                padding: 16px;
            }
            
            .volume-container .flex {
                flex-direction: column;
                gap: 12px;
            }
            
            .volume-container .flex > div:first-child {
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }
            
            .btn-add-line,
            .btn-calculate {
                width: 100%;
                text-align: center;
                padding: 12px 16px;
            }
            
            .volume-display {
                width: 100%;
                background: white;
                padding: 16px;
                border-radius: 8px;
                text-align: center;
                font-weight: 600;
            }
            
            .btn-submit {
                width: 100%;
            }
        }
        
        .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .radio-label input[type="radio"] {
            cursor: pointer;
        }
        
        input[type="radio"]:checked {
            accent-color: var(--color-primary);
        }
        
        input[type="checkbox"] {
            accent-color: var(--color-primary);
            cursor: pointer;
        }
        
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .package-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        /* Hide mobile package cards on desktop */
        .mobile-package-cards {
            display: none;
        }
        
        .package-table th {
            background: var(--cg100, #F3F4F8);
            padding: 0.75rem;
            text-align: left;
            color: var(--g900, #131313);
            font-family: Poppins;
            font-size: 12px;
            font-style: normal;
            font-weight: 500;
            line-height: 16px;
            letter-spacing: -0.36px;
            border: 1px solid #e5e7eb;
        }
        
        .package-table th.dimension-sub {
            padding: 0.5rem;
            font-weight: 500;
        }
        
        .package-table td {
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .btn-remove-row {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s ease;
        }
        
        .btn-remove-row:hover {
            opacity: 0.8;
        }
        
        .package-table input,
        .package-table select {
            width: 100%;
            padding: 0.375rem 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0;
            font-size: 0.875rem;
        }
        
        .btn-add-line {
            background: white;
            color: #374151;
            padding: 0.625rem 1rem;
            border-radius: 0;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.26px;
            text-transform: capitalize;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #d1d5db;
            height: 40px;
            display: flex;
            align-items: center;
        }
        
        .btn-add-line:hover {
            background: #1f2937;
        }
        
        .btn-calculate {
            background: var(--color-primary);
            color: white;
            padding: 0.625rem 1.5rem;
            border-radius: 0;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.26px;
            text-transform: capitalize;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            height: 40px;
            display: flex;
            align-items: center;
        }
        
        .btn-calculate:hover {
            background: #d11920;
        }
        
        .volume-display {
            background: white;
            padding: 0.625rem 1.5rem;
            border-radius: 0;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.26px;
            text-transform: capitalize;
            text-align: center;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .volume-container {
            background: var(--cg700, #404252);
            padding: 0.5rem;
            display: flex;
        }
        
        .btn-submit {
            background: var(--color-primary);
            color: #FFF;
            padding: 1rem 3rem;
            border-radius: 0;
            font-family: Poppins;
            font-size: 18px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.36px;
            text-transform: capitalize;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            margin-left: auto;
            display: block;
        }
        
        .btn-submit:hover {
            background: #d11920;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(227, 30, 36, 0.3);
        }
        
        .file-upload-container {
            display: flex;
            align-items: stretch;
            border: 1px solid #d1d5db;
            overflow: hidden;
        }
        
        .file-upload-container .file-input-wrapper {
            flex: 1;
            position: relative;
        }
        
        .file-upload-container .form-input {
            border: none;
            border-radius: 0;
            width: 100%;
        }
        
        .file-upload-container .btn-select-file {
            border: none;
            border-left: 1px solid #d1d5db;
            border-radius: 0;
            margin: 0;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .btn-select-file {
            background: var(--secondary, #071537);
            color: #FFF;
            padding: 0.625rem 1.5rem;
            font-family: Poppins;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.26px;
            text-transform: capitalize;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-select-file:hover {
            background: #0a1a3f;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Support',
        'title' => 'Request a Quote',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Request a Quote.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Support',
        'current_page' => 'Request Quote',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Request Quote', 'url' => BASE_URL . '/pages/support/request-quote.php'],
            ['title' => 'FAQ', 'url' => BASE_URL . '/pages/support/faq.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section class="pt-[60px] pb-20 lg:py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <!-- 메시지 표시 -->
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php if ($messageType === 'success'): ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php else: ?>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php endif; ?>
                    <span><?php echo $message; ?></span>
                </div>
                <?php endif; ?>
                
                <!-- 견적 요청 폼 -->
                <form method="POST" action="" enctype="multipart/form-data" class="form-container">
                    <!-- Client Information -->
                    <div class="form-wrapper">
                        <h3 class="section-title">Client<br>Information</h3>
                        <div class="form-section">
                        
                        <div class="form-question-gap">
                            <label class="form-label">Place of Country</label>
                            <select name="place_of_country" class="form-select" required>
                                <option value="">Please Select</option>
                                <option value="United States">United States</option>
                                <option value="South Korea">South Korea</option>
                                <option value="China">China</option>
                                <option value="Japan">Japan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-grid form-question-gap">
                            <div>
                                <label class="form-label">Company name</label>
                                <input type="text" name="company_name" class="form-input" placeholder="Please enter" required>
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email_address" class="form-input" placeholder="Please enter" required>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div>
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-input" placeholder="Please enter" required>
                            </div>
                            <div>
                                <label class="form-label">Telephone Number</label>
                                <input type="tel" name="telephone_number" class="form-input" placeholder="Please enter" required>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Freight Information -->
                    <div class="form-wrapper" style="margin-top: 120px;">
                        <h3 class="section-title">Freight<br>Information</h3>
                        <div class="form-section">
                        
                        <div class="form-question-gap">
                            <label class="form-label">Mode of Transport</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="mode_of_transport" value="AIR" required>
                                    <span class="body-lg">AIR</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="mode_of_transport" value="OCEAN" required>
                                    <span class="body-lg">OCEAN</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-grid form-question-gap">
                            <div>
                                <label class="form-label">Original Point</label>
                                <input type="text" name="original_point" class="form-input" placeholder="Please enter" required>
                            </div>
                            <div>
                                <label class="form-label">Air(sea)Port of Departure</label>
                                <input type="text" name="departure_air_port" class="form-input" placeholder="Please enter">
                            </div>
                        </div>
                        
                        <div class="form-grid form-question-gap">
                            <div>
                                <label class="form-label">Destination</label>
                                <input type="text" name="destination" class="form-input" placeholder="Please enter" required>
                            </div>
                            <div>
                                <label class="form-label">Air(sea)port of destination</label>
                                <input type="text" name="destination_air_port" class="form-input" placeholder="Please enter">
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Weight</label>
                            <div class="mb-3">
                                <label class="form-label text-sm">Weight Unit</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="weight_unit" value="KG" checked>
                                        <span class="body-lg">KG</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="weight_unit" value="LBS">
                                        <span class="body-lg">LBS</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="form-label text-sm">Total Gross Weight</label>
                                <input type="number" name="total_gross_weight" class="form-input" placeholder="Please enter" step="0.01">
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Volume and Dimensions</label>
                            <div class="mb-3">
                                <label class="form-label text-sm">Dimension Unit</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="dimension_unit" value="CM" checked>
                                        <span class="body-lg">CM</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="dimension_unit" value="INCH">
                                        <span class="body-lg">INCH</span>
                                    </label>
                                </div>
                            </div>
                            
                            <table class="package-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2">QTY of Packages</th>
                                        <th colspan="3">Dimension</th>
                                        <th rowspan="2">Commodity</th>
                                        <th rowspan="2">HTC Code</th>
                                        <th rowspan="2">Package Type</th>
                                        <th rowspan="2" style="width: 40px;"></th>
                                    </tr>
                                    <tr>
                                        <th class="dimension-sub">Length</th>
                                        <th class="dimension-sub">Width</th>
                                        <th class="dimension-sub">Height</th>
                                    </tr>
                                </thead>
                                <tbody id="packageRows">
                                    <tr data-row-index="0">
                                        <td><input type="number" name="packages[0][qty]" placeholder="enter"></td>
                                        <td><input type="number" name="packages[0][length]" placeholder="enter"></td>
                                        <td><input type="number" name="packages[0][width]" placeholder="enter"></td>
                                        <td><input type="number" name="packages[0][height]" placeholder="enter"></td>
                                        <td><input type="text" name="packages[0][commodity]" placeholder="enter"></td>
                                        <td><input type="text" name="packages[0][htc_code]" placeholder="enter"></td>
                                        <td>
                                            <select name="packages[0][package_type]">
                                                <option value="">Pallet</option>
                                                <option value="Box">Box</option>
                                                <option value="Crate">Crate</option>
                                                <option value="Bundle">Bundle</option>
                                                <option value="Roll">Roll</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Mobile Package Cards (hidden on desktop) -->
                            <div class="mobile-package-cards" id="mobilePackageCards">
                                <div class="mobile-package-card" data-row-index="0">
                                    <div class="mobile-package-number">01</div>
                                    
                                    <div class="mobile-package-field">
                                        <label>QTY of Packages</label>
                                        <input type="number" name="mobile_packages[0][qty]" class="form-input" placeholder="enter">
                                    </div>
                                    
                                    <div class="mobile-package-field">
                                        <label>Dimension</label>
                                        <div class="mobile-dimension-group">
                                            <div class="mobile-dimension-field">
                                                <label>Length</label>
                                                <input type="number" name="mobile_packages[0][length]" class="form-input" placeholder="enter">
                                            </div>
                                            <div class="mobile-dimension-field">
                                                <label>Width</label>
                                                <input type="number" name="mobile_packages[0][width]" class="form-input" placeholder="enter">
                                            </div>
                                            <div class="mobile-dimension-field">
                                                <label>Height</label>
                                                <input type="number" name="mobile_packages[0][height]" class="form-input" placeholder="enter">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mobile-package-field">
                                        <label>Commodity</label>
                                        <input type="text" name="mobile_packages[0][commodity]" class="form-input" placeholder="enter">
                                    </div>
                                    
                                    <div class="mobile-package-field">
                                        <label>HTC Code</label>
                                        <input type="text" name="mobile_packages[0][htc_code]" class="form-input" placeholder="enter">
                                    </div>
                                    
                                    <div class="mobile-package-field">
                                        <label>Package Type</label>
                                        <select name="mobile_packages[0][package_type]" class="form-select">
                                            <option value="">Pallet</option>
                                            <option value="Box">Box</option>
                                            <option value="Crate">Crate</option>
                                            <option value="Bundle">Bundle</option>
                                            <option value="Roll">Roll</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="volume-container">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn-add-line" onclick="addPackageRow()">+ Add Line</button>
                                        <button type="button" class="btn-calculate" onclick="calculateVolume()">Calculate Package Volume</button>
                                    </div>
                                    <div class="volume-display">
                                        Total Volume: <span id="totalVolume">0.00</span> <span id="volumeUnit">m³</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Shipper Address</label>
                            <input type="text" name="shipper_address" class="form-input" placeholder="Please enter">
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Consignee Address</label>
                            <input type="text" name="consignee_address" class="form-input" placeholder="Please enter">
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Terms and Conditions</label>
                            <div class="checkbox-group">
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="Ex-works" required>
                                    <span class="body-lg">Ex-works</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="FOB">
                                    <span class="body-lg">FOB</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="FCA">
                                    <span class="body-lg">FCA</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="CIF">
                                    <span class="body-lg">CIF</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="CPT">
                                    <span class="body-lg">CPT</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="DOU">
                                    <span class="body-lg">DOU</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="DDP">
                                    <span class="body-lg">DDP</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="terms_conditions" value="Other">
                                    <span class="body-lg">Other</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Special Info. and Request</label>
                            <div class="flex flex-col gap-2">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="special_request_transportation_insurance" value="1">
                                    <span class="body-lg">Transportation Insurance</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="special_request_dangerous_goods" value="1">
                                    <span class="body-lg">Dangerous Goods</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="special_request_handling" value="1">
                                    <span class="body-lg">Special Handling required(Crane, Translating, Weekend, etc)</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Estimate date of shipping</label>
                            <input type="text" name="estimate_date_of_shipping" class="form-input" placeholder="Please enter" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Dangerous item</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="dangerous_item" value="Yes">
                                    <span class="body-lg">Yes</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="dangerous_item" value="No" checked>
                                    <span class="body-lg">No</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-question-gap">
                            <label class="form-label">Remarks / other requirements</label>
                            <textarea name="remarks_requirements" class="form-textarea" rows="5" placeholder="Please enter"></textarea>
                        </div>
                        
                        <div>
                            <label class="form-label">Attach</label>
                            <div class="file-upload-container">
                                <div class="file-input-wrapper flex-1">
                                    <input type="text" class="form-input" placeholder="Please upload the file" readonly id="file-display">
                                    <input type="file" id="attached_file" name="attached_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display: none;">
                                </div>
                                <button type="button" class="btn-select-file" onclick="document.getElementById('attached_file').click()">Select File</button>
                            </div>
                        </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div style="text-align: right; margin-top: 3rem;">
                        <button type="submit" class="btn-submit">SUBMIT →</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <script>
        let packageRowCount = 1;
        
        function addPackageRow() {
            // Add to desktop table
            const tbody = document.getElementById('packageRows');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-row-index', packageRowCount);
            newRow.innerHTML = `
                <td><input type="number" name="packages[${packageRowCount}][qty]" placeholder="enter"></td>
                <td><input type="number" name="packages[${packageRowCount}][length]" placeholder="enter"></td>
                <td><input type="number" name="packages[${packageRowCount}][width]" placeholder="enter"></td>
                <td><input type="number" name="packages[${packageRowCount}][height]" placeholder="enter"></td>
                <td><input type="text" name="packages[${packageRowCount}][commodity]" placeholder="enter"></td>
                <td><input type="text" name="packages[${packageRowCount}][htc_code]" placeholder="enter"></td>
                <td>
                    <select name="packages[${packageRowCount}][package_type]">
                        <option value="">Pallet</option>
                        <option value="Box">Box</option>
                        <option value="Crate">Crate</option>
                        <option value="Bundle">Bundle</option>
                        <option value="Roll">Roll</option>
                        <option value="Other">Other</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn-remove-row" onclick="removePackageRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="12" fill="#9496A1"/>
                            <path d="M7 12H17" stroke="white" stroke-width="2"/>
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
            
            // Add to mobile cards
            const mobileCards = document.getElementById('mobilePackageCards');
            const newCard = document.createElement('div');
            newCard.className = 'mobile-package-card';
            newCard.setAttribute('data-row-index', packageRowCount);
            
            const cardNumber = String(packageRowCount + 1).padStart(2, '0');
            newCard.innerHTML = `
                <div class="mobile-package-number">${cardNumber}</div>
                <button type="button" class="mobile-remove-btn" onclick="removeMobilePackageCard(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="12" fill="#9496A1"/>
                        <path d="M7 12H17" stroke="white" stroke-width="2"/>
                    </svg>
                </button>
                
                <div class="mobile-package-field">
                    <label>QTY of Packages</label>
                    <input type="number" name="mobile_packages[${packageRowCount}][qty]" class="form-input" placeholder="enter">
                </div>
                
                <div class="mobile-package-field">
                    <label>Dimension</label>
                    <div class="mobile-dimension-group">
                        <div class="mobile-dimension-field">
                            <label>Length</label>
                            <input type="number" name="mobile_packages[${packageRowCount}][length]" class="form-input" placeholder="enter">
                        </div>
                        <div class="mobile-dimension-field">
                            <label>Width</label>
                            <input type="number" name="mobile_packages[${packageRowCount}][width]" class="form-input" placeholder="enter">
                        </div>
                        <div class="mobile-dimension-field">
                            <label>Height</label>
                            <input type="number" name="mobile_packages[${packageRowCount}][height]" class="form-input" placeholder="enter">
                        </div>
                    </div>
                </div>
                
                <div class="mobile-package-field">
                    <label>Commodity</label>
                    <input type="text" name="mobile_packages[${packageRowCount}][commodity]" class="form-input" placeholder="enter">
                </div>
                
                <div class="mobile-package-field">
                    <label>HTC Code</label>
                    <input type="text" name="mobile_packages[${packageRowCount}][htc_code]" class="form-input" placeholder="enter">
                </div>
                
                <div class="mobile-package-field">
                    <label>Package Type</label>
                    <select name="mobile_packages[${packageRowCount}][package_type]" class="form-select">
                        <option value="">Pallet</option>
                        <option value="Box">Box</option>
                        <option value="Crate">Crate</option>
                        <option value="Bundle">Bundle</option>
                        <option value="Roll">Roll</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            `;
            mobileCards.appendChild(newCard);
            
            packageRowCount++;
        }
        
        function removePackageRow(button) {
            const row = button.closest('tr');
            const tbody = document.getElementById('packageRows');
            const rowIndex = row.getAttribute('data-row-index');
            
            // 최소 1개의 row는 유지
            if (tbody.children.length > 1) {
                row.remove();
                // Remove corresponding mobile card
                const mobileCard = document.querySelector(`.mobile-package-card[data-row-index="${rowIndex}"]`);
                if (mobileCard) {
                    mobileCard.remove();
                }
            }
        }
        
        function removeMobilePackageCard(button) {
            const card = button.closest('.mobile-package-card');
            const mobileCards = document.getElementById('mobilePackageCards');
            const rowIndex = card.getAttribute('data-row-index');
            
            // 최소 1개의 card는 유지
            if (mobileCards.children.length > 1) {
                card.remove();
                // Remove corresponding table row
                const tableRow = document.querySelector(`#packageRows tr[data-row-index="${rowIndex}"]`);
                if (tableRow) {
                    tableRow.remove();
                }
            }
        }
        
        function calculateVolume() {
            const dimensionUnit = document.querySelector('input[name="dimension_unit"]:checked').value;
            let totalVolume = 0;
            
            // Check if mobile or desktop
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Calculate from mobile cards
                const cards = document.querySelectorAll('.mobile-package-card');
                cards.forEach(card => {
                    const qty = parseFloat(card.querySelector('input[name*="[qty]"]').value) || 0;
                    const length = parseFloat(card.querySelector('input[name*="[length]"]').value) || 0;
                    const width = parseFloat(card.querySelector('input[name*="[width]"]').value) || 0;
                    const height = parseFloat(card.querySelector('input[name*="[height]"]').value) || 0;
                    
                    if (qty && length && width && height) {
                        let volume = qty * length * width * height;
                        
                        // Convert to cubic meters if dimensions are in CM
                        if (dimensionUnit === 'CM') {
                            volume = volume / 1000000; // cm³ to m³
                        } else {
                            // INCH to m³
                            volume = volume * 0.0000163871;
                        }
                        
                        totalVolume += volume;
                    }
                });
            } else {
                // Calculate from desktop table
                const rows = document.querySelectorAll('#packageRows tr');
                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
                    const length = parseFloat(row.querySelector('input[name*="[length]"]').value) || 0;
                    const width = parseFloat(row.querySelector('input[name*="[width]"]').value) || 0;
                    const height = parseFloat(row.querySelector('input[name*="[height]"]').value) || 0;
                    
                    if (qty && length && width && height) {
                        let volume = qty * length * width * height;
                        
                        // Convert to cubic meters if dimensions are in CM
                        if (dimensionUnit === 'CM') {
                            volume = volume / 1000000; // cm³ to m³
                        } else {
                            // INCH to m³
                            volume = volume * 0.0000163871;
                        }
                        
                        totalVolume += volume;
                    }
                });
            }
            
            document.getElementById('totalVolume').textContent = totalVolume.toFixed(2);
        }
        
        // File input handling
        document.getElementById('attached_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('file-display').value = fileName;
        });
        
        // Sync mobile and desktop data on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Copy mobile data to desktop fields for form submission
                const mobileCards = document.querySelectorAll('.mobile-package-card');
                const tableRows = document.querySelectorAll('#packageRows tr');
                
                mobileCards.forEach((card, index) => {
                    if (tableRows[index]) {
                        const row = tableRows[index];
                        // Copy values from mobile to desktop
                        const mobileInputs = card.querySelectorAll('input, select');
                        mobileInputs.forEach(input => {
                            const name = input.name.replace('mobile_packages', 'packages');
                            const desktopInput = row.querySelector(`[name="${name}"]`);
                            if (desktopInput) {
                                desktopInput.value = input.value;
                            }
                        });
                    }
                });
            }
        });
    </script>
    
    <style>
        /* Contact Bottom Section Styles */
        .contact-bottom-section {
            background-size: cover;
            background-position: center;
            position: relative;
            padding: 160px 0;
        }
        
        .contact-us-title {
            color: #FFF;
            font-family: Poppins;
            font-size: 40px;
            font-style: normal;
            font-weight: 700;
            line-height: 56px;
            letter-spacing: -1.2px;
            margin-bottom: 16px;
        }
        
        .contact-info-box {
            background: white;
            padding: 48px;
            width: 50%;
            min-width: 350px;
        }
        
        @media (max-width: 768px) {
            .contact-bottom-section {
                padding-top: 74px;
                padding-bottom: 140px;
            }
            
            .contact-us-title {
                font-size: 24px;
                font-style: normal;
                font-weight: 700;
                line-height: 35px; /* 145.833% */
                letter-spacing: -0.72px;
            }
            
            .contact-info-box {
                padding: 28px;
                width: 100%;
                min-width: unset;
            }
        }
    </style>
    
    <!-- Contact Bottom Section -->
    <section class="contact-bottom-section" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/contact/contact-bottom.webp');">
        <div class="container mx-auto px-4" style="position: relative; z-index: 1;">
            <div class="flex flex-col lg:flex-row items-start justify-between gap-12">
                <!-- Left content -->
                <div class="text-white" style="flex: 1;">
                    <h2 class="contact-us-title">
                        CONTACT US
                    </h2>
                    <p class="body-lg" style="color: rgba(255, 255, 255, 0.8);">
                        Please contact IMPEX Headquarters for<br>
                        any issues or concerns.
                    </p>
                </div>
                
                <!-- Right content - Contact Information Box -->
                <div class="contact-info-box">
                    <h3 style="color: #000; font-family: Poppins; font-size: 22px; font-style: normal; font-weight: 700; line-height: 32px; letter-spacing: -0.66px; margin-bottom: 32px;">
                        Corporate Headquarters
                    </h3>
                    
                    <div class="contact-info-list">
                        <!-- Address -->
                        <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px;">
                            <img src="<?php echo BASE_URL; ?>/assets/images/contact/01.svg" alt="Location" width="20" height="20" style="flex-shrink: 0; margin-top: 2px;" />
                            <span class="text-body-sm" style="color: #000;">
                                2475 Touhy Avenue Suite 100 Elk Grove Village, IL 60007
                            </span>
                        </div>
                        
                        <!-- Email -->
                        <div class="contact-info-item" style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <img src="<?php echo BASE_URL; ?>/assets/images/contact/02.svg" alt="Email" width="20" height="20" style="flex-shrink: 0;" />
                            <a href="mailto:HQ@IMPEXGLS.COM" class="text-body-sm" style="color: #000; text-decoration: none;">
                                HQ@IMPEXGLS.COM
                            </a>
                        </div>
                        
                        <!-- Phone 1 -->
                        <div class="contact-info-item" style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <img src="<?php echo BASE_URL; ?>/assets/images/contact/03.svg" alt="Phone" width="20" height="20" style="flex-shrink: 0;" />
                            <a href="tel:630-227-9300" class="text-body-sm" style="color: #000; text-decoration: none;">
                                630-227-9300
                            </a>
                        </div>
                        
                        <!-- Phone 2 -->
                        <div class="contact-info-item" style="display: flex; align-items: center; gap: 12px;">
                            <img src="<?php echo BASE_URL; ?>/assets/images/contact/03.svg" alt="Phone" width="20" height="20" style="flex-shrink: 0;" />
                            <a href="tel:630-227-9356" class="text-body-sm" style="color: #000; text-decoration: none;">
                                630-227-9356
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>