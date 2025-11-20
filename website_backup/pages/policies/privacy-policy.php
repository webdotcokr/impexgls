<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 설정
$page_meta_info = [
    'title' => 'Privacy Policy - IMPEX GLS',
    'description' => 'Privacy Policy for IMPEX Global Logistics Services. Learn how we collect, use, and protect your personal information.',
    'keywords' => 'privacy policy, data protection, personal information, IMPEX GLS',
    'author' => 'IMPEX GLS',
    'og_title' => 'Privacy Policy - IMPEX GLS',
    'og_description' => 'Learn how IMPEX GLS collects, uses, and protects your personal information.',
    'og_image' => BASE_URL . '/assets/images/og-image.jpg',
    'og_type' => 'website',
    'twitter_card' => 'summary_large_image'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo generateMetaTags($page_meta_info); ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <style>
        .policy-content {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: #333;
        }
        
        .policy-content h2 {
            font-size: 24px;
            font-weight: 600;
            margin-top: 32px;
            margin-bottom: 16px;
            color: #1B2951;
        }
        
        .policy-content h3 {
            font-size: 20px;
            font-weight: 500;
            margin-top: 24px;
            margin-bottom: 12px;
            color: #1B2951;
        }
        
        .policy-content p {
            margin-bottom: 16px;
        }
        
        .policy-content ul {
            list-style-type: disc;
            margin-left: 32px;
            margin-bottom: 16px;
        }
        
        .policy-content li {
            margin-bottom: 8px;
        }
        
        .policy-content a {
            color: #3457AD;
            text-decoration: underline;
        }
        
        .policy-content a:hover {
            color: #E31E24;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // Subpage header settings
    $page_header = [
        'category' => 'Legal',
        'title' => 'Privacy Policy',
        'background' => BASE_URL . '/assets/images/subpage-header-image/about.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <!-- Privacy Policy Content -->
    <section class="py-12 lg:py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto policy-content">
                <p class="text-gray-600 mb-8">
                    <strong>Effective Date:</strong> January 1, 2024<br>
                    <strong>Last Updated:</strong> January 1, 2024
                </p>
                
                <p>
                    IMPEX Global Logistics Services ("IMPEX GLS," "we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
                </p>
                
                <h2>1. Information We Collect</h2>
                
                <h3>1.1 Personal Information</h3>
                <p>We may collect personal information that you provide to us, including but not limited to:</p>
                <ul>
                    <li>Name and job title</li>
                    <li>Contact information (email address, phone number, mailing address)</li>
                    <li>Company name and business information</li>
                    <li>Shipping and billing addresses</li>
                    <li>Payment information</li>
                    <li>Communications you send to us</li>
                </ul>
                
                <h3>1.2 Automatically Collected Information</h3>
                <p>When you visit our website, we may automatically collect certain information about your device, including:</p>
                <ul>
                    <li>IP address</li>
                    <li>Browser type and version</li>
                    <li>Operating system</li>
                    <li>Access times and dates</li>
                    <li>Pages viewed and links clicked</li>
                    <li>Referring website addresses</li>
                </ul>
                
                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide and manage our logistics services</li>
                    <li>Process transactions and send related information</li>
                    <li>Respond to your comments, questions, and requests</li>
                    <li>Send you technical notices and support messages</li>
                    <li>Communicate about products, services, and events</li>
                    <li>Monitor and analyze trends, usage, and activities</li>
                    <li>Detect, investigate, and prevent fraudulent activities</li>
                    <li>Comply with legal obligations</li>
                </ul>
                
                <h2>3. How We Share Your Information</h2>
                <p>We may share your information in the following situations:</p>
                
                <h3>3.1 Service Providers</h3>
                <p>We may share your information with third-party service providers who perform services on our behalf, such as payment processing, shipping, data analysis, and customer service.</p>
                
                <h3>3.2 Business Partners</h3>
                <p>We may share information with our business partners to provide you with requested services or to offer products and services that may interest you.</p>
                
                <h3>3.3 Legal Requirements</h3>
                <p>We may disclose your information if required to do so by law or in response to valid requests by public authorities.</p>
                
                <h3>3.4 Business Transfers</h3>
                <p>We may share or transfer your information in connection with, or during negotiations of, any merger, sale of company assets, financing, or acquisition of all or a portion of our business.</p>
                
                <h2>4. Data Security</h2>
                <p>We implement appropriate technical and organizational security measures to protect your personal information against accidental or unlawful destruction, loss, alteration, unauthorized disclosure, or access. However, no method of transmission over the Internet or electronic storage is 100% secure.</p>
                
                <h2>5. Data Retention</h2>
                <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law.</p>
                
                <h2>6. Your Rights and Choices</h2>
                <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
                <ul>
                    <li>Access to your personal information</li>
                    <li>Correction of inaccurate or incomplete information</li>
                    <li>Deletion of your personal information</li>
                    <li>Objection to or restriction of certain processing</li>
                    <li>Data portability</li>
                    <li>Withdrawal of consent</li>
                </ul>
                
                <p>To exercise these rights, please contact us using the information provided below.</p>
                
                <h2>7. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar tracking technologies to track activity on our website and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
                
                <h2>8. International Data Transfers</h2>
                <p>Your information may be transferred to and maintained on computers located outside of your state, province, country, or other governmental jurisdiction where data protection laws may differ. We ensure appropriate safeguards are in place for such transfers.</p>
                
                <h2>9. Children's Privacy</h2>
                <p>Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal information from a child under 18, we will take steps to delete such information.</p>
                
                <h2>10. Updates to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. The updated version will be indicated by an updated "Last Updated" date. We encourage you to review this Privacy Policy periodically.</p>
                
                <h2>11. Contact Us</h2>
                <p>If you have questions or concerns about this Privacy Policy or our privacy practices, please contact us at:</p>
                
                <div class="mt-8 p-6 bg-gray-100 rounded-lg">
                    <p class="font-semibold">IMPEX Global Logistics Services</p>
                    <p>2475 Touhy Avenue Suite 100<br>
                    Elk Grove Village, IL 60007<br>
                    United States</p>
                    <p class="mt-4">
                        Email: <a href="mailto:privacy@impexgls.com">privacy@impexgls.com</a><br>
                        Phone: <a href="tel:+16302279300">+1 (630) 227-9300</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>