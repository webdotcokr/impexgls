<?php
require_once '../../config/db-config.php';
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/support/faq.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 데이터베이스에서 FAQ 가져오기
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faqs = [];
}

// 더미 FAQ 데이터 (데이터베이스에 데이터가 없을 경우)
$dummy_faqs = [
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'If you can always contact us at HQ and we will direct you to the right branch. Or, you can always contact us at HQ and we will direct you to the right branch.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ],
    [
        'question_ko' => 'How do I know which branch I should contact for my shipment?',
        'answer_ko' => 'Each of our branch offices has designated service areas based on geographic location and logistics infrastructure. For general inquiries, you can contact our HQ in Irvine, CA. For operations in specific states, please contact the respective branch office. Our support team will help route your inquiry to the most appropriate office to ensure the fastest and most efficient service delivery.'
    ]
];

// 데이터베이스에 데이터가 없으면 더미 데이터 사용
if (empty($faqs)) {
    $faqs = $dummy_faqs;
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
        
        /* FAQ 아이템 스타일 */
        .faq-item {
            border-bottom: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-item:last-child {
            border-bottom: none;
        }
        
        /* FAQ Question 스타일 */
        .faq-question {
            padding: 2.5rem 1.25rem 1.25rem 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            color: var(--color-primary);
        }
        
        /* Icon 스타일 */
        .faq-icon {
            width: 28px;
            height: 28px;
            background: #111;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .faq-item.active .faq-icon {
            background: var(--color-primary);
            color: white;
        }
        
        .faq-question-text {
            flex: 1;
            color: #1f2937;
        }
        
        .faq-toggle-icon {
            width: 20px;
            height: 20px;
            color: #D4D4D8;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        
        .faq-item.active .faq-toggle-icon {
            transform: rotate(180deg);
            color: var(--color-primary);
        }
        
        /* Answer 스타일 */
        .faq-answer {
            padding: 0 1.25rem 1.25rem calc(1.25rem + 28px + 1rem);
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: opacity 0.3s ease, max-height 0.3s ease;
        }
        
        .faq-item.active .faq-answer {
            opacity: 1;
            max-height: 1000px;
        }
        
        .faq-answer-text {
            color: #4b5563;
            line-height: 1.75;
        }
        
        .faq-answer-text {
            flex: 1;
            color: #4b5563;
            line-height: 1.75;
        }
        
        @media (max-width: 768px) {
            .faq-title {
                font-size: 1.5rem;
                margin-bottom: 2rem;
            }
            
            .faq-question {
                padding: 1.25rem 0.625rem 0.625rem 0.625rem;
            }
            
            .faq-icon {
                width: 24px;
                height: 24px;
                font-size: 14px;
            }
            
            .faq-q-icon, .faq-a-icon {
                width: 24px;
                height: 24px;
                font-size: 14px;
            }
            
            .faq-answer {
                padding: 0 0 1.25rem 3rem;
            }
            
            .faq-answer-text {
                color: var(--cg600, #5B5D6B);
                font-family: Poppins;
                font-size: 14px;
                font-style: normal;
                font-weight: 400;
                line-height: 20px; /* 142.857% */
                letter-spacing: -0.42px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Support',
        'title' => 'FAQ',
        'background' => BASE_URL . '/assets/images/subpage-header-image/FAQ.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Support',
        'current_page' => 'FAQ',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Request Quote', 'url' => BASE_URL . '/pages/support/request-quote.php'],
            ['title' => 'FAQ', 'url' => BASE_URL . '/pages/support/faq.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 메인 콘텐츠 -->
    <section class="pt-[60px] pb-12 lg:py-20">
        <div class="container mx-auto px-4">
                <!-- FAQ 제목 -->
                <div class="section-header">
                    <h2>FAQ</h2>
                </div>
                
                <!-- FAQ 목록 -->
                <div id="faqContent">
                    <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            <span class="faq-icon">Q</span>
                            <span class="faq-question-text text-body-lg"><?php echo htmlspecialchars($faq['question_ko']); ?></span>
                            <svg class="faq-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div class="faq-answer">
                            <p class="faq-answer-text"><?php echo nl2br(htmlspecialchars($faq['answer_ko'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const wasActive = faqItem.classList.contains('active');
            const icon = element.querySelector('.faq-icon');
            
            // 모든 FAQ 항목 닫기
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-icon').textContent = 'Q';
            });
            
            // 클릭한 항목이 이미 열려있지 않았다면 열기
            if (!wasActive) {
                faqItem.classList.add('active');
                icon.textContent = 'A';
            }
        }
    </script>
</body>
</html>