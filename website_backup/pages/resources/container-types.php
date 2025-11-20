<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';
require_once '../../config/db-config.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/resources/container-types.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// DB에서 컨테이너 타입 가져오기
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM container_types WHERE is_active = 1 ORDER BY sort_order ASC");
    $containers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $containers = [];
    error_log("Container types fetch error: " . $e->getMessage());
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
        
        /* 컨테이너 박스 스타일 */
        .container-box {
            background: white;
            margin-bottom: 3rem;
        }
        
        .container-title {
            color: #000;
            font-family: Poppins;
            font-size: 20px;
            font-style: normal;
            font-weight: 700;
            line-height: 28px; /* 140% */
            letter-spacing: -0.6px;
            margin-bottom: 0.5rem;
        }
        
        .container-description {
            color: #777986;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px; /* 142.857% */
            letter-spacing: -0.42px;
            margin-bottom: 1.5rem;
        }
        
        /* 컨테이너 수치 섹션 */
        .container-specs {
            display: flex;
            gap: 2rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .container-image-wrapper {
            flex-shrink: 0;
            width: 180px;
        }
        
        .container-image {
            width: 100%;
            height: auto;
            object-fit: contain;
            border: 1px solid #F3F4F8;
            border-radius: 8px;
        }
        
        /* 테이블 스타일 */
        .specs-table-wrapper {
            flex: 1;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            display: flex;
            flex-direction: column;
            gap: 0; /* 테이블 간격 제거 */
        }
        
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            table-layout: fixed; /* 고정 레이아웃 사용 */
        }
        
        .dimensions-table {
            margin-bottom: -1px; /* 테이블 경계선 겹침 */
        }
        
        /* 기본 열 너비 */
        .specs-table th,
        .specs-table td {
            width: 10%;
            border: 1px solid #000;
            padding: 0.5rem 0.75rem;
            text-align: center;
        }
        
        /* colspan="2" 적용된 셀 */
        .specs-table th[colspan="2"],
        .specs-table td[colspan="2"] {
            width: 20%; /* 2개 열 병합 = 10% × 2 */
        }
        
        /* APX Container Type 열 (첫 번째 열) */
        .specs-table th.type-header,
        .specs-table td.type-cell {
            width: 20%; /* 다른 colspan="2"와 동일한 너비 */
        }
        
        .specs-table th {
            background-color: #ffffff;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .specs-table th.section-header {
            background-color: #ffffff;
            font-weight: 700;
        }
        
        .specs-table th.type-header {
            color: #000;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 600;
            line-height: 20px; /* 142.857% */
            letter-spacing: -0.42px;
            text-align: left;
        }
        
        .specs-table td.type-cell {
            color: #000;
            text-align: left; /* 첫 번째 열만 left, 나머지는 center */
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px; /* 142.857% */
            letter-spacing: -0.42px;
        }
        
        .specs-table td {
            white-space: nowrap;
            color: #000;
            font-family: Poppins;
            font-size: 14px;
            font-style: normal;
            font-weight: 400;
            line-height: 20px; /* 142.857% */
            letter-spacing: -0.42px;
        }
        
        .specs-table .label-cell {
            background-color: #f9fafb;
            font-weight: 500;
            text-align: left;
        }
        
        /* 스크롤바 스타일 */
        .container-specs::-webkit-scrollbar,
        .specs-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        
        .container-specs::-webkit-scrollbar-track,
        .specs-table-wrapper::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }
        
        .container-specs::-webkit-scrollbar-thumb,
        .specs-table-wrapper::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        
        .container-specs::-webkit-scrollbar-thumb:hover,
        .specs-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* 모바일 스타일 */
        @media (max-width: 768px) {
            .container-box {
                margin-bottom: 3rem;
                padding: 0;
            }
            
            .container-title {
                font-size: 18px;
                font-weight: 700;
                line-height: 24px;
                letter-spacing: -0.54px;
                margin-bottom: 8px;
                padding: 0;
            }
            
            .container-description {
                font-size: 13px;
                font-weight: 400;
                line-height: 18px;
                letter-spacing: -0.39px;
                margin-bottom: 20px;
                padding: 0;
                color: #6B7280;
            }
            
            .container-specs {
                flex-direction: column;
                gap: 10px;
            }
            
            .container-image-wrapper {
                width: 100%;
                flex-shrink: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0;
                border: 1px solid #e5e7eb; /* cg100 */
            }
            
            .container-image {
                width: 100%;
                height: auto;
                border: none;
            }
            
            .specs-table-wrapper {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin-top: 0;
            }
            
            .specs-table {
                font-size: 11px;
                min-width: 600px;
            }
            
            .specs-table th,
            .specs-table td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            
            .specs-table th {
                font-size: 11px;
                font-weight: 600;
            }
            
            .specs-table th.type-header {
                font-size: 11px;
                font-style: normal;
                font-weight: 600;
                line-height: 20px; /* 166.667% */
                letter-spacing: -0.36px;
            }
            
            .specs-table td {
                font-size: 11px;
            }
            
            /* 테이블 간격 */
            .weight-table {
                margin-top: 0;
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
        'title' => 'Container Types',
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
            ['title' => 'Knowledge Base', 'url' => BASE_URL . '/pages/resources/knowledge-base.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>
    
    <!-- 탭 네비게이션 -->
    <section class="border-b z-40 lg:mt-[61px]" style="background-color: #F3F4F8;">
        <div class="container mx-auto px-4">
            <div class="flex gap-2 overflow-x-auto py-4 -mx-4 px-4 scrollbar-hide">
                <a href="<?php echo BASE_URL; ?>/pages/resources/incoterms.php" 
                   class="tab-button">
                    Incoterms
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/resources/container-types.php" 
                   class="tab-button active">
                    Container Types
                </a>
            </div>
        </div>
    </section>
    
    <!-- 메인 콘텐츠 -->
    <section class="py-12 lg:py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-7xl mx-auto">
                <?php if (empty($containers)): ?>
                    <!-- 하드코딩된 기본 데이터 (DB가 비어있을 경우) -->
                    <div class="container-box">
                        <h3 class="container-title">20' x 8'6" Dry Height ISO Containers</h3>
                        <p class="container-description">
                            CONTAINERS THAT ARE DESIGNED TO CARRY GENERAL CARGO SUCH AS BOXES, CARTONS, CASES, SACKS, BALES, ETC.
                        </p>
                        
                        <div class="container-specs">
                            <!-- 컨테이너 이미지 -->
                            <div class="container-image-wrapper">
                                <img src="<?php echo BASE_URL; ?>/assets/images/container-type/1.webp" 
                                     alt="20' Dry Container"
                                     class="container-image">
                            </div>
                            
                            <!-- 수치 테이블 -->
                            <div class="specs-table-wrapper">
                                <!-- 첫 번째 테이블: 치수 정보 -->
                                <table class="specs-table dimensions-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="type-header">APX Container Type</th>
                                            <th colspan="2" class="section-header">Length</th>
                                            <th colspan="2" class="section-header">Height</th>
                                            <th colspan="2" class="section-header">Width</th>
                                            <th colspan="2" class="section-header">Door Opening</th>
                                        </tr>
                                        <tr>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Height</th>
                                            <th>Width</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="type-cell">20' DRY FREIGHT</td>
                                            <td>19'10 ½"</td>
                                            <td>19'4 ¼"</td>
                                            <td>8'6"</td>
                                            <td>7'9 7/8"</td>
                                            <td>8'0"</td>
                                            <td>7'8 ½"</td>
                                            <td>7'5 5/8"</td>
                                            <td>7'5 5/8"</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- 두 번째 테이블: 무게 및 용량 정보 -->
                                <table class="specs-table weight-table">
                                    <thead>
                                        <tr>
                                            <th class="type-header">APX Container Type</th>
                                            <th colspan="2">Tare Weight<br>in pounds</th>
                                            <th colspan="2">Payload<br>in pounds</th>
                                            <th colspan="2">Gross Weight<br>in pounds</th>
                                            <th colspan="2">Cubic Capacity<br>in cubic feet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="type-cell">20' DRY FREIGHT</td>
                                            <td colspan="2">5,015</td>
                                            <td colspan="2">47,895</td>
                                            <td colspan="2">52,910</td>
                                            <td colspan="2">1,166</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- DB 데이터 출력 -->
                    <?php foreach ($containers as $container): ?>
                    <div class="container-box">
                        <h3 class="container-title"><?php echo htmlspecialchars($container['type_name']); ?></h3>
                        <p class="container-description">
                            <?php echo htmlspecialchars($container['description'] ?? 'Containers that are designed to carry general cargo like boxes, cartons, cases, sacks, bales, etc.'); ?>
                        </p>
                        
                        <div class="container-specs">
                            <!-- 컨테이너 이미지 -->
                            <div class="container-image-wrapper">
                                <img src="<?php echo BASE_URL . htmlspecialchars($container['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($container['type_name']); ?>"
                                     class="container-image">
                            </div>
                            
                            <!-- 수치 테이블 -->
                            <div class="specs-table-wrapper">
                                <!-- 첫 번째 테이블: 치수 정보 -->
                                <table class="specs-table dimensions-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="type-header">APX Container Type</th>
                                            <th colspan="2" class="section-header">Length</th>
                                            <th colspan="2" class="section-header">Height</th>
                                            <th colspan="2" class="section-header">Width</th>
                                            <th colspan="2" class="section-header">Door Opening</th>
                                        </tr>
                                        <tr>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Exterior</th>
                                            <th>Interior</th>
                                            <th>Height</th>
                                            <th>Width</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="type-cell"><?php echo htmlspecialchars($container['type_code']); ?></td>
                                            <td><?php echo htmlspecialchars($container['external_length']); ?></td>
                                            <td><?php echo htmlspecialchars($container['internal_length']); ?></td>
                                            <td><?php echo htmlspecialchars($container['external_height']); ?></td>
                                            <td><?php echo htmlspecialchars($container['internal_height']); ?></td>
                                            <td><?php echo htmlspecialchars($container['external_width']); ?></td>
                                            <td><?php echo htmlspecialchars($container['internal_width']); ?></td>
                                            <td><?php echo htmlspecialchars($container['door_height']); ?></td>
                                            <td><?php echo htmlspecialchars($container['door_width']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- 두 번째 테이블: 무게 및 용량 정보 -->
                                <table class="specs-table weight-table">
                                    <thead>
                                        <tr>
                                            <th class="type-header">APX Container Type</th>
                                            <th colspan="2">Tare Weight<br>in pounds</th>
                                            <th colspan="2">Payload<br>in pounds</th>
                                            <th colspan="2">Gross Weight<br>in pounds</th>
                                            <th colspan="2">Cubic Capacity<br>in cubic feet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="type-cell"><?php echo htmlspecialchars($container['type_code']); ?></td>
                                            <td colspan="2"><?php echo number_format($container['tare_weight_lbs'] ?? 0); ?></td>
                                            <td colspan="2"><?php echo number_format($container['payload_lbs'] ?? 0); ?></td>
                                            <td colspan="2"><?php echo number_format($container['gross_weight_lbs'] ?? 0); ?></td>
                                            <td colspan="2"><?php echo number_format($container['cubic_capacity_ft'] ?? 0); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>