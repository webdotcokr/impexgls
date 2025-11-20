<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/support/request-quote.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 폼 제출 처리
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        // 데이터 검증 및 정리
        $company_name = trim($_POST['company_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $service_type = $_POST['service_type'] ?? '';
        $origin_country = trim($_POST['origin_country'] ?? '');
        $destination_country = trim($_POST['destination_country'] ?? '');
        $cargo_type = trim($_POST['cargo_type'] ?? '');
        $cargo_weight = trim($_POST['cargo_weight'] ?? '');
        $cargo_volume = trim($_POST['cargo_volume'] ?? '');
        $expected_date = $_POST['expected_date'] ?? null;
        $additional_info = trim($_POST['additional_info'] ?? '');
        
        // 이메일 유효성 검사
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // 데이터베이스에 저장
        $stmt = $pdo->prepare("
            INSERT INTO quote_requests (
                company_name, contact_person, email, phone, service_type,
                origin_country, destination_country, cargo_type, cargo_weight,
                cargo_volume, expected_date, additional_info, created_at
            ) VALUES (
                :company_name, :contact_person, :email, :phone, :service_type,
                :origin_country, :destination_country, :cargo_type, :cargo_weight,
                :cargo_volume, :expected_date, :additional_info, NOW()
            )
        ");
        
        $stmt->execute([
            ':company_name' => $company_name,
            ':contact_person' => $contact_person,
            ':email' => $email,
            ':phone' => $phone,
            ':service_type' => $service_type,
            ':origin_country' => $origin_country,
            ':destination_country' => $destination_country,
            ':cargo_type' => $cargo_type,
            ':cargo_weight' => $cargo_weight,
            ':cargo_volume' => $cargo_volume,
            ':expected_date' => $expected_date,
            ':additional_info' => $additional_info
        ]);
        
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
        /* CSS variables now defined in global.css */
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .form-label .required {
            color: var(--color-primary);
            margin-left: 0.25rem;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(227, 30, 36, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-secondary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .submit-btn {
            background: var(--color-primary);
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #d11920;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(227, 30, 36, 0.3);
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
        
        .contact-info {
            background: #f9fafb;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .contact-item:last-child {
            margin-bottom: 0;
        }
        
        .contact-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
    <section class="py-20">
        <div class="container mx-auto px-4">
                <!-- 헤더 텍스트 -->
                <div class="section-header">
                    <h2>Get Your Custom Quote</h2>
                    <p>Fill out the form below and our logistics experts will provide you with a competitive quote within 24 hours.</p>
                </div>
                
                <!-- 연락처 정보 -->
                <div class="contact-info">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Call Us</p>
                                <p class="text-gray-600">+1 (555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Email Us</p>
                                <p class="text-gray-600">quote@impexgls.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">Response Time</p>
                                <p class="text-gray-600">Within 24 hours</p>
                            </div>
                        </div>
                    </div>
                </div>
                
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
                <form method="POST" action="">
                    <!-- 회사 정보 -->
                    <div class="form-section">
                        <h3 class="section-title">Company Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Company Name
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="company_name" class="form-input" required 
                                       value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Contact Person
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="contact_person" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Email Address
                                    <span class="required">*</span>
                                </label>
                                <input type="email" name="email" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Phone Number
                                    <span class="required">*</span>
                                </label>
                                <input type="tel" name="phone" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 서비스 정보 -->
                    <div class="form-section">
                        <h3 class="section-title">Service Requirements</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Service Type
                                    <span class="required">*</span>
                                </label>
                                <select name="service_type" class="form-select" required>
                                    <option value="">Select a service</option>
                                    <option value="Air Freight" <?php echo (($_POST['service_type'] ?? '') === 'Air Freight') ? 'selected' : ''; ?>>Air Freight</option>
                                    <option value="Ocean Freight" <?php echo (($_POST['service_type'] ?? '') === 'Ocean Freight') ? 'selected' : ''; ?>>Ocean Freight</option>
                                    <option value="Ground Transportation" <?php echo (($_POST['service_type'] ?? '') === 'Ground Transportation') ? 'selected' : ''; ?>>Ground Transportation</option>
                                    <option value="Contract Logistics" <?php echo (($_POST['service_type'] ?? '') === 'Contract Logistics') ? 'selected' : ''; ?>>Contract Logistics</option>
                                    <option value="Supply Chain Management" <?php echo (($_POST['service_type'] ?? '') === 'Supply Chain Management') ? 'selected' : ''; ?>>Supply Chain Management</option>
                                    <option value="Special Products" <?php echo (($_POST['service_type'] ?? '') === 'Special Products') ? 'selected' : ''; ?>>Special Products</option>
                                    <option value="Other" <?php echo (($_POST['service_type'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Origin Country
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="origin_country" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['origin_country'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Destination Country
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="destination_country" class="form-input" required
                                       value="<?php echo htmlspecialchars($_POST['destination_country'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Expected Shipping Date
                                </label>
                                <input type="date" name="expected_date" class="form-input"
                                       value="<?php echo htmlspecialchars($_POST['expected_date'] ?? ''); ?>"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 화물 정보 -->
                    <div class="form-section">
                        <h3 class="section-title">Cargo Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Cargo Type
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="cargo_type" class="form-input" required
                                       placeholder="e.g., Electronics, Machinery, Textiles"
                                       value="<?php echo htmlspecialchars($_POST['cargo_type'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Estimated Weight (kg)
                                </label>
                                <input type="text" name="cargo_weight" class="form-input"
                                       placeholder="e.g., 5000"
                                       value="<?php echo htmlspecialchars($_POST['cargo_weight'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label text-body-sm">
                                    Estimated Volume (m³)
                                </label>
                                <input type="text" name="cargo_volume" class="form-input"
                                       placeholder="e.g., 50"
                                       value="<?php echo htmlspecialchars($_POST['cargo_volume'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Additional Information
                            </label>
                            <textarea name="additional_info" class="form-textarea" rows="4"
                                      placeholder="Please provide any additional details about your shipment requirements..."><?php echo htmlspecialchars($_POST['additional_info'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- 제출 버튼 -->
                    <div class="text-center">
                        <button type="submit" class="submit-btn">
                            Submit Quote Request
                        </button>
                        <p class="text-sm text-gray-600 mt-4">
                            By submitting this form, you agree to our 
                            <a href="#" class="text-red-600 hover:underline">Terms of Service</a> and 
                            <a href="#" class="text-red-600 hover:underline">Privacy Policy</a>.
                        </p>
                    </div>
                </form>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>