<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/resources/incoterms.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;
?>
<!DOCTYPE html>
<html lang="en">
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
    <!-- 반응형 스타일 -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <style>
        /* CSS variables now defined in global.css */
        
        .incoterms-table {
            font-size: 0.875rem;
        }
        
        .incoterms-table th {
            background-color: #f8f9fa;
            font-weight: 500;
            text-align: center;
            padding: 0.75rem 0.5rem;
        }
        
        .incoterms-table td {
            text-align: center;
            padding: 0.5rem;
            border: 1px solid #e9ecef;
        }
        
        .incoterms-table .term-name {
            font-weight: 500;
            text-align: left;
            padding-left: 1rem;
        }
        
        .icon-check {
            color: #28a745;
        }
        
        /* Chart of Responsibility 스타일 */
        .responsibility-table {
            border-collapse: collapse;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px; /* 142.857% */
            letter-spacing: -0.42px;
            width: 100%;
            min-width: 1200px;
        }
        
        .responsibility-table th,
        .responsibility-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: center;
            background-color: white;
        }
        
        .transport-header {
            font-weight: 600;
            padding: 12px 8px;
        }
        
        .term-header {
            font-weight: 600;
            font-size: 13px;
            padding: 10px 4px;
        }
        
        .responsibility-header {
            text-align: left;
            font-weight: 600;
            padding-left: 16px;
            white-space: nowrap;
        }
        
        .desc-cell {
            font-size: 12px;
            color: #000000;
            padding: 6px;
        }
        
        /* 빈 셀 스타일 */
        .responsibility-table td:empty {
            background-color: #F1F1F1;
        }
        
        .buyer {
            color: #BF3F4C;
            font-weight: 400;
        }
        
        .seller {
            color: #3457AD;
            font-weight: 400;
        }
        
        .buyer-seller {
            color: #3457AD;
            font-weight: 400;
        }
        
        .seller-star {
            color: #3457AD;
            font-weight: 400;
        }

        .seller-dagger {
            color: #3457AD;
            font-weight: 400;
        }

        .seller-ddagger {
            color: #3457AD;
            font-weight: 400;
        }
        
        /* Sticky column for horizontal scroll */
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: white;
            text-align: left !important;
        }
        
        /* 제목 및 설명 스타일 */
        .incoterms-title {
            color: var(--g900, #131313);
            font-family: Poppins;
            font-size: 28px;
            font-style: normal;
            font-weight: 700;
            line-height: 40px;
            letter-spacing: -0.84px;
            flex-shrink: 0;
        }
        
        .incoterms-description {
            color: var(--g600, #5B616E);
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 24px;
            letter-spacing: -0.48px;
            flex: 1;
        }
        
        /* 모바일 스타일 */
        @media (max-width: 768px) {
            .incoterms-title {
                font-size: 24px;
                line-height: 32px;
            }
            
            .incoterms-description {
                font-size: 14px;
                line-height: 20px;
            }
        }
        
        /* Incoterms Group Table 스타일 */
        .incoterms-group-table {
            border-collapse: collapse;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px;
            letter-spacing: -0.42px;
            width: 100%;
            min-width: 900px;
        }
        
        .incoterms-group-table th,
        .incoterms-group-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: left;
        }
        
        .group-header {
            font-weight: 600;
            background-color: white;
            text-transform: uppercase;
        }
        
        /* 헤더 세로선 제거 */
        .group-header-first {
            border-right: none;
        }
        
        .group-header-middle {
            border-left: none;
            border-right: none;
        }
        
        .group-header-last {
            border-left: none;
        }
        
        .group-cell {
            background-color: white;
            vertical-align: top;
        }
        
        .group-name {
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        
        .note-cell {
            padding: 16px;
            line-height: 1.6;
        }
        
        .note-marker {
            font-weight: 700;
            margin-right: 4px;
        }
        
        .group-separator {
            border-top: 2px solid #000;
        }
        
        /* Terms Details Table 스타일 */
        .terms-details-table {
            border-collapse: collapse;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px;
            letter-spacing: -0.42px;
            width: 100%;
        }
        
        .terms-details-table td {
            border: 1px solid #e5e7eb;
            padding: 16px;
            vertical-align: top;
        }
        
        .term-detail-header {
            font-weight: 600;
            width: 150px;
            text-align: center;
        }
        
        .term-detail-content {
            background-color: white;
            line-height: 1.6;
        }
        
        .term-detail-content p {
            margin: 0;
        }
        
        .term-detail-content p.mt-2 {
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .incoterms-table {
                font-size: 0.75rem;
            }
            
            .incoterms-table th,
            .incoterms-table td {
                padding: 0.25rem;
            }
            
            .responsibility-table {
                font-size: 12px;
            }
            
            .responsibility-table th,
            .responsibility-table td {
                padding: 4px;
            }
            
            .incoterms-group-table {
                font-size: 12px;
            }
            
            .incoterms-group-table th,
            .incoterms-group-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Resources',
        'title' => 'Knowledge Base',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Useful Information.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Resources',
        'current_page' => 'Knowledge Base',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Quick Links', 'url' => BASE_URL . '/pages/resources/quick-links.php'],
            ['title' => 'Knowledge Base', 'url' => BASE_URL . '/pages/resources/knowledge-base.php'],
            ['title' => 'Terms & Conditions of Service', 'url' => BASE_URL . '/pages/resources/terms.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 탭 네비게이션 -->
    <section class="border-b z-40 lg:mt-[61px]" style="background-color: #F3F4F8;">
        <div class="container mx-auto px-4">
            <div class="flex gap-2 overflow-x-auto py-4 -mx-4 px-4 scrollbar-hide">
                <a href="<?php echo BASE_URL; ?>/pages/resources/incoterms.php" 
                   class="tab-button active">
                    Incoterms
                </a>
            </div>
        </div>
    </section>
    
    <!-- Incoterms 콘텐츠 -->
    <section class="py-12 lg:py-20">
        <div class="container mx-auto px-4">
            <!-- 제목 및 설명 -->
            <div class="mb-10 flex flex-col lg:flex-row gap-4 lg:gap-20 items-start">
                <h2 class="incoterms-title">Incoterms</h2>
                <p class="incoterms-description">
                    Incoterms® define who does what, pays what, and bears risk at each handoff in an international sale of goods. The 2020 edition replaces DAT with DPU (Delivered at Place Unloaded), clarifies the FCA option for an on-board bill of lading, and distinguishes default insurance levels under CIF and CIP. This page is a practical summary; always confirm contract specifics against the official ICC text.
                </p>
            </div>
            
            <!-- Chart of Responsibility -->
            <div class="mb-12">
                <h3 style="color: #000; font-family: Poppins; font-size: 20px; font-style: normal; font-weight: 700; line-height: 28px; letter-spacing: -0.6px; margin-bottom: 24px;">Chart of Responsibility</h3>
                <p class="incoterms-description">
                    Legend: Seller = seller responsibility · Buyer = buyer responsibility. “Any mode” vs. “Sea/Waterway” reflects ICC scope.
                </p>
                
                <!-- 데스크톱 테이블 -->
                <div class="overflow-x-auto mt-6">
                    <table class="w-full responsibility-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col">Charges / Tasks</th>
                                <th colspan="2" class="transport-header">Any Transport Mode</th>
                                <th colspan="5" class="transport-header">Sea / Inland Waterway Transport</th>
                                <th colspan="4" class="transport-header">Any Transport Mode</th>
                            </tr>
                            <tr>
                                <th class="term-header">EXW</th>
                                <th class="term-header">FCA</th>
                                <th class="term-header">FAS</th>
                                <th class="term-header">FOB</th>
                                <th class="term-header">CFR</th>
                                <th class="term-header">CIF</th>
                                <th class="term-header">CPT</th>
                                <th class="term-header">CIP</th>
                                <th class="term-header">DPU</th>
                                <th class="term-header">DAP</th>
                                <th class="term-header">DDP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="responsibility-header sticky-col">Packaging / marking</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Loading at seller's site</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller-star">Seller*</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Delivery to port / place of export</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Export clearance (duties, taxes)</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Origin terminal charges (OTHC)</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Loading on main carriage</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Main carriage (freight)</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Cargo insurance</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller-dagger">Seller†</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller-ddagger">Seller‡</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Destination terminal charges (DTHC)</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Delivery to final place</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Import clearance (duties, taxes)</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                            </tr>
                            <tr>
                                <td class="responsibility-header sticky-col">Unloading at named place</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                                <td class="seller">Seller</td>
                                <td class="buyer">Buyer</td>
                                <td class="buyer">Buyer</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footnotes -->
                <div class="mt-4 text-sm text-gray-600 space-y-1">
                    <p><span class="font-semibold">*</span> Under FCA at the seller’s premises, the seller loads the buyer’s collecting vehicle. If FCA delivery is at another place, the seller is not responsible for unloading there. <br/>† CIF: Seller procures marine insurance to destination; default minimum remains ICC(C). <br/>‡ CIP: Seller procures insurance to destination; default minimum raised to ICC(A) under Incoterms® 2020.</p>
                </div>
            </div>

            
            <!-- 상세 설명 섹션 -->
            <div class="mt-12">
                <h3 style="color: #000; font-family: Poppins; font-size: 20px; font-style: normal; font-weight: 700; line-height: 28px; letter-spacing: -0.6px; margin-bottom: 24px;">Rule Summaries (Incoterms® 2020)</h3>
                <div class="grid grid-cols-4 max-md:grid-cols-1 gap-6">
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">EXW - Ex Works (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: at seller's premises, not loaded. <br/>Buyer arranges loading, export, freight, insurance, import.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">FCA - Free Carrier (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: when handed to buyer's carrier at named place.<br/>Seller clears for export. Optional: buyer may instruct carrier to issue an on-board B/L to the seller after loading (for L/C needs).</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">FAS - Free Alongside Ship (sea/iw)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: alongside vessel at port of shipment. <br/>Seller clears export; buyer loads and arranges main carriage.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">FOB - Free On Board (sea/iw)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: when on board the vessel at port of shipment.<br/>Seller clears export and loads on board.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">CFR - Cost and Freight (sea/iw)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: on board at shipment port.<br/>Seller pays freight to destination port;<br/>buyer bears risk after loading.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">CIF - Cost, Insurance and Freight (sea/iw)</h4>
                        <p class="mt-2 text-sm text-gray-500">Same as CFR + insurance.<br/>Seller procures insurance (default<br/>ICC(C)) to destination port.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">CPT — Carriage Paid To (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: when given to first carrier.<br/>Seller pays carriage to named place; buyer bears risk after delivery to carrier.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">CIP - Carriage & Insurance <br/>Paid To (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">As CPT + insurance.<br/>Seller procures insurance (default<br/>ICC(A)) to named place of destination.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">DPU — Delivered at Place <br/>Unloaded (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: when goods are unloaded at named place.<br/>Seller handles unloading; buyer clears import.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">DAP — Delivered at Place (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: at named place, ready for unloading.<br/>Buyer unloads and clears import.</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h4 class="text-base font-bold">DDP - Delivered Duty Paid (any mode)</h4>
                        <p class="mt-2 text-sm text-gray-500">Delivery/Risk: at named place, ready for unloading.<br/>Seller handles import clearance, duties, taxes as well.</p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-gray-400">Incoterms® is a registered trademark of ICC. This summary is for general guidance only and does not replace the official ICC rules or legal advice. Last updated: 12 Aug 2025.</p>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
