<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/resources/shipping-terms.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;
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
    
    <style>
        /* CSS variables now defined in global.css */
        
        .term-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .term-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .term-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .term-abbr {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        
        .term-full {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .incoterm-diagram {
            background: #f9fafb;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
        }
        
        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto 3rem;
        }
        
        .search-box input {
            width: 100%;
            padding: 1rem 3rem 1rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .search-box svg {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .category-filter {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.5rem 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 9999px;
            background: white;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .filter-btn:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .filter-btn.active:hover {
            background: var(--color-primary);
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <!-- 페이지 헤더 -->
    <section class="page-header relative h-[400px] flex items-end" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo BASE_URL; ?>/assets/images/placeholder/shipping-terms-hero.jpg') center/cover;">
        <div class="container mx-auto px-4 pb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Shipping Terms</h1>
        </div>
    </section>
    
    <!-- 서브 네비게이션 -->
    <nav class="sub-nav bg-white border-b border-gray-200 py-4 sticky top-[80px] z-40">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 overflow-x-auto">
                <li><a href="<?php echo BASE_URL; ?>/pages/resources/container-specification.php" class="block py-2 font-medium text-gray-600 hover:text-red-600">Container Specification</a></li>
                <li><a href="<?php echo BASE_URL; ?>/pages/resources/shipping-terms.php" class="block py-2 font-medium text-red-600 border-b-2 border-red-600">Shipping Terms</a></li>
                <li><a href="<?php echo BASE_URL; ?>/pages/resources/useful-links.php" class="block py-2 font-medium text-gray-600 hover:text-red-600">Useful Links</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- 메인 콘텐츠 -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <!-- 헤더 텍스트 -->
            <div class="section-header">
                <h2>International Shipping Terms & Definitions</h2>
                <p>Essential terminology for international trade and logistics.</p>
            </div>
            
            <!-- 검색 박스 -->
            <div class="search-box">
                <input type="text" id="searchTerm" placeholder="Search for a shipping term..." onkeyup="filterTerms()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <!-- 카테고리 필터 -->
            <div class="category-filter">
                <button class="filter-btn active" onclick="filterCategory('all')">All Terms</button>
                <button class="filter-btn" onclick="filterCategory('incoterms')">Incoterms</button>
                <button class="filter-btn" onclick="filterCategory('shipping')">Shipping</button>
                <button class="filter-btn" onclick="filterCategory('documentation')">Documentation</button>
                <button class="filter-btn" onclick="filterCategory('financial')">Financial</button>
            </div>
            
            <!-- Incoterms 2020 섹션 -->
            <div class="mb-16" id="incoterms-section">
                <h3 class="text-3xl font-bold mb-8">Incoterms® 2020</h3>
                <div class="incoterm-diagram">
                    <p class="text-center mb-6 text-gray-600">International Commercial Terms defining responsibilities between buyers and sellers</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Group E -->
                        <div>
                            <h4 class="font-bold text-lg mb-3 text-blue-600">Group E - Departure</h4>
                            <div class="term-card" data-category="incoterms">
                                <div class="term-header">
                                    <span class="term-abbr">EXW</span>
                                </div>
                                <p class="term-full">Ex Works</p>
                                <p class="text-sm text-gray-600 mt-2">Seller makes goods available at their premises. Buyer bears all costs and risks.</p>
                            </div>
                        </div>
                        
                        <!-- Group F -->
                        <div>
                            <h4 class="font-bold text-lg mb-3 text-green-600">Group F - Main Carriage Unpaid</h4>
                            <div class="space-y-3">
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">FCA</span>
                                    </div>
                                    <p class="term-full">Free Carrier</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers to carrier nominated by buyer.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">FAS</span>
                                    </div>
                                    <p class="term-full">Free Alongside Ship</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers alongside vessel at port.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">FOB</span>
                                    </div>
                                    <p class="term-full">Free on Board</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers goods on board vessel.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Group C -->
                        <div>
                            <h4 class="font-bold text-lg mb-3 text-purple-600">Group C - Main Carriage Paid</h4>
                            <div class="space-y-3">
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">CFR</span>
                                    </div>
                                    <p class="term-full">Cost and Freight</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller pays for carriage to port.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">CIF</span>
                                    </div>
                                    <p class="term-full">Cost, Insurance and Freight</p>
                                    <p class="text-sm text-gray-600 mt-2">CFR plus insurance coverage.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">CPT</span>
                                    </div>
                                    <p class="term-full">Carriage Paid To</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller pays for carriage to destination.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">CIP</span>
                                    </div>
                                    <p class="term-full">Carriage and Insurance Paid To</p>
                                    <p class="text-sm text-gray-600 mt-2">CPT plus insurance coverage.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Group D -->
                        <div>
                            <h4 class="font-bold text-lg mb-3 text-red-600">Group D - Arrival</h4>
                            <div class="space-y-3">
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">DAP</span>
                                    </div>
                                    <p class="term-full">Delivered at Place</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers to named place.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">DPU</span>
                                    </div>
                                    <p class="term-full">Delivered at Place Unloaded</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers and unloads at destination.</p>
                                </div>
                                <div class="term-card" data-category="incoterms">
                                    <div class="term-header">
                                        <span class="term-abbr">DDP</span>
                                    </div>
                                    <p class="term-full">Delivered Duty Paid</p>
                                    <p class="text-sm text-gray-600 mt-2">Seller delivers with all duties paid.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 기타 용어들 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Shipping Terms -->
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">B/L</span>
                    </div>
                    <p class="term-full">Bill of Lading</p>
                    <p class="text-sm text-gray-600 mt-2">Document issued by carrier acknowledging receipt of cargo for shipment.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">AWB</span>
                    </div>
                    <p class="term-full">Air Waybill</p>
                    <p class="text-sm text-gray-600 mt-2">Contract of carriage and receipt for air freight shipments.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">FCL</span>
                    </div>
                    <p class="term-full">Full Container Load</p>
                    <p class="text-sm text-gray-600 mt-2">Shipment that fills an entire container.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">LCL</span>
                    </div>
                    <p class="term-full">Less than Container Load</p>
                    <p class="text-sm text-gray-600 mt-2">Shipment that doesn't fill a container, consolidated with other cargo.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">TEU</span>
                    </div>
                    <p class="term-full">Twenty-foot Equivalent Unit</p>
                    <p class="text-sm text-gray-600 mt-2">Standard measurement for container capacity.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">FEU</span>
                    </div>
                    <p class="term-full">Forty-foot Equivalent Unit</p>
                    <p class="text-sm text-gray-600 mt-2">Measurement equal to two TEUs.</p>
                </div>
                
                <!-- Documentation Terms -->
                <div class="term-card" data-category="documentation">
                    <div class="term-header">
                        <span class="term-abbr">C/O</span>
                    </div>
                    <p class="term-full">Certificate of Origin</p>
                    <p class="text-sm text-gray-600 mt-2">Document certifying the country where goods were manufactured.</p>
                </div>
                
                <div class="term-card" data-category="documentation">
                    <div class="term-header">
                        <span class="term-abbr">P/L</span>
                    </div>
                    <p class="term-full">Packing List</p>
                    <p class="text-sm text-gray-600 mt-2">Detailed list of items in a shipment.</p>
                </div>
                
                <div class="term-card" data-category="documentation">
                    <div class="term-header">
                        <span class="term-abbr">C/I</span>
                    </div>
                    <p class="term-full">Commercial Invoice</p>
                    <p class="text-sm text-gray-600 mt-2">Document showing the value of goods for customs purposes.</p>
                </div>
                
                <div class="term-card" data-category="documentation">
                    <div class="term-header">
                        <span class="term-abbr">SLI</span>
                    </div>
                    <p class="term-full">Shipper's Letter of Instruction</p>
                    <p class="text-sm text-gray-600 mt-2">Document providing shipping instructions to freight forwarder.</p>
                </div>
                
                <!-- Financial Terms -->
                <div class="term-card" data-category="financial">
                    <div class="term-header">
                        <span class="term-abbr">L/C</span>
                    </div>
                    <p class="term-full">Letter of Credit</p>
                    <p class="text-sm text-gray-600 mt-2">Bank guarantee of payment to seller upon meeting specified conditions.</p>
                </div>
                
                <div class="term-card" data-category="financial">
                    <div class="term-header">
                        <span class="term-abbr">T/T</span>
                    </div>
                    <p class="term-full">Telegraphic Transfer</p>
                    <p class="text-sm text-gray-600 mt-2">Electronic funds transfer between banks.</p>
                </div>
                
                <div class="term-card" data-category="financial">
                    <div class="term-header">
                        <span class="term-abbr">CAD</span>
                    </div>
                    <p class="term-full">Cash Against Documents</p>
                    <p class="text-sm text-gray-600 mt-2">Payment method where documents are released upon payment.</p>
                </div>
                
                <!-- Additional Shipping Terms -->
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">ETD</span>
                    </div>
                    <p class="term-full">Estimated Time of Departure</p>
                    <p class="text-sm text-gray-600 mt-2">Expected departure time of vessel or aircraft.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">ETA</span>
                    </div>
                    <p class="term-full">Estimated Time of Arrival</p>
                    <p class="text-sm text-gray-600 mt-2">Expected arrival time at destination.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">POL</span>
                    </div>
                    <p class="term-full">Port of Loading</p>
                    <p class="text-sm text-gray-600 mt-2">Port where cargo is loaded onto vessel.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">POD</span>
                    </div>
                    <p class="term-full">Port of Discharge</p>
                    <p class="text-sm text-gray-600 mt-2">Port where cargo is unloaded from vessel.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">THC</span>
                    </div>
                    <p class="term-full">Terminal Handling Charges</p>
                    <p class="text-sm text-gray-600 mt-2">Fees for handling containers at terminals.</p>
                </div>
                
                <div class="term-card" data-category="shipping">
                    <div class="term-header">
                        <span class="term-abbr">BAF</span>
                    </div>
                    <p class="term-full">Bunker Adjustment Factor</p>
                    <p class="text-sm text-gray-600 mt-2">Surcharge to compensate for fuel price fluctuations.</p>
                </div>
            </div>
            
            <!-- 다운로드 섹션 -->
            <div class="mt-16 p-8 bg-gray-50 rounded-lg text-center">
                <h3 class="text-2xl font-bold mb-4">Complete Shipping Terms Guide</h3>
                <p class="text-gray-600 mb-6">Download our comprehensive guide with over 200 shipping and logistics terms.</p>
                <a href="<?php echo BASE_URL; ?>/downloads/shipping-terms-guide.pdf" 
                   class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF Guide
                </a>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        function filterTerms() {
            const searchInput = document.getElementById('searchTerm').value.toLowerCase();
            const cards = document.querySelectorAll('.term-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchInput)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function filterCategory(category) {
            const cards = document.querySelectorAll('.term-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // 버튼 활성화 상태 변경
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // 카드 필터링
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>