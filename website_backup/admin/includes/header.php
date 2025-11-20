<?php
/**
 * 관리자 페이지 공통 헤더
 */

// 로그인 체크
requireLogin();

// 현재 페이지 정보
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = $page_title ?? '관리자';

// 알림 메시지 가져오기
$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - IMPEX GLS 관리자</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background: linear-gradient(180deg, #1B2951 0%, #2C3E50 100%);
        }
        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #E31E24;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- 사이드바 -->
        <aside class="sidebar w-64 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">IMPEX GLS</h1>
                <p class="text-sm text-gray-300">관리자 시스템</p>
            </div>
            
            <nav class="mt-8">
                <!-- 대시보드 -->
                <a href="<?php echo getAdminUrl('/dashboard.php'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'dashboard' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    대시보드
                </a>
                
                <!-- 사이트 설정 -->
                <div class="mt-6 px-4 text-xs uppercase text-gray-400">사이트 관리</div>
                
                <a href="<?php echo getAdminUrl('/settings/site.php'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'site' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-cog mr-3"></i>
                    사이트 설정
                </a>
                
                <!-- <a href="<?php echo getAdminUrl('/settings/email.php'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'email' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-envelope mr-3"></i>
                    이메일 설정
                </a>
                 -->
                <!-- 문의 관리 -->
                <!-- <div class="mt-6 px-4 text-xs uppercase text-gray-400">문의 관리</div>
                
                <a href="<?php echo getAdminUrl('/inquiries/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'index' && strpos($_SERVER['REQUEST_URI'], '/inquiries/') !== false ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-inbox mr-3"></i>
                    문의하기
                    <?php
                    // 새 문의 카운트
                    try {
                        $pdo = getDBConnection();
                        $stmt = $pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'pending'");
                        $pending_count = $stmt->fetchColumn();
                        if ($pending_count > 0) {
                            echo '<span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">' . $pending_count . '</span>';
                        }
                    } catch (Exception $e) {}
                    ?>
                </a> -->
                
                <!-- 콘텐츠 관리 -->
                <div class="mt-6 px-4 text-xs uppercase text-gray-400">콘텐츠 관리</div>
                
                <a href="<?php echo getAdminUrl('/news/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'news' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-newspaper mr-3"></i>
                    뉴스 관리
                </a>
                
                <!-- <a href="<?php echo getAdminUrl('/faq/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'faq' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-question-circle mr-3"></i>
                    FAQ 관리
                </a> -->
                
                <a href="<?php echo getAdminUrl('/certificates/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'certificates' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-certificate mr-3"></i>
                    인증서 관리
                </a>
                
                <a href="<?php echo getAdminUrl('/clients/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'clients' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-users mr-3"></i>
                    클라이언트 관리
                </a>
                
                <a href="<?php echo getAdminUrl('/links/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'useful-links' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-link mr-3"></i>
                    퀵 링크
                </a>
                
                <a href="<?php echo getAdminUrl('/locations/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'locations' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-map-marker-alt mr-3"></i>
                    네트워크 위치
                </a>
                
                <!-- 로그 -->
                <div class="mt-6 px-4 text-xs uppercase text-gray-400">시스템</div>
                
                <a href="<?php echo getAdminUrl('/logs/'); ?>" 
                   class="sidebar-item <?php echo $current_page == 'activity' ? 'active' : ''; ?> block px-4 py-3 transition duration-200">
                    <i class="fas fa-history mr-3"></i>
                    활동 로그
                </a>
            </nav>
            
            <!-- 하단 사용자 정보 -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-600" style="background: #fff;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color:#000;"><?php echo e($_SESSION['admin_name'] ?? '관리자'); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($_SESSION['admin_username'] ?? ''); ?></p>
                    </div>
                    <a href="<?php echo getAdminUrl('/logout.php'); ?>" 
                       class="text-gray-400 hover:text-white transition duration-200"
                       onclick="return confirm('로그아웃 하시겠습니까?');">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </aside>
        
        <!-- 메인 콘텐츠 -->
        <main class="flex-1 overflow-y-auto" style="margin-bottom:60px;">
            <!-- 상단 헤더 -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-800"><?php echo e($page_title); ?></h2>
                    
                    <div class="flex items-center space-x-4">
                        <!-- 사이트 바로가기 -->
                        <a href="<?php echo BASE_URL; ?>" 
                           target="_blank"
                           class="text-gray-600 hover:text-gray-800 transition duration-200">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            사이트 보기
                        </a>
                        
                        <!-- 현재 시간 -->
                        <span class="text-sm text-gray-500">
                            <?php echo date('Y-m-d H:i'); ?>
                        </span>
                    </div>
                </div>
            </header>
            
            <!-- 알림 메시지 -->
            <?php if ($alert): ?>
            <div class="px-6 py-4">
                <div class="<?php echo $alert['type'] == 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e($alert['message']); ?></span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 페이지 콘텐츠 -->
            <div class="p-6">