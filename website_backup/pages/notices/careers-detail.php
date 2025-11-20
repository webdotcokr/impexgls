<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/notices/careers-detail.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;

// 게시물 ID 가져오기
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 데이터베이스에서 게시물 가져오기
try {
    $pdo = getDBConnection();

    // 조회수 증가
    $update_stmt = $pdo->prepare("UPDATE news_posts SET view_count = view_count + 1 WHERE id = :id");
    $update_stmt->bindValue(':id', $post_id, PDO::PARAM_INT);
    $update_stmt->execute();

    // 게시물 가져오기
    $stmt = $pdo->prepare("
        SELECT id, title, content, DATE_FORMAT(published_at, '%Y.%m.%d') as date, author
        FROM news_posts
        WHERE id = :id AND status = 'published' AND category = 'careers'
    ");
    $stmt->bindValue(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        // 게시물이 없으면 목록으로 리다이렉트
        header('Location: ' . BASE_URL . '/pages/notices/careers.php');
        exit;
    }
} catch (Exception $e) {
    // 에러 시 목록으로 리다이렉트
    header('Location: ' . BASE_URL . '/pages/notices/careers.php');
    exit;
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
        /* Notice Detail Page Specific Styles */
        .post-header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .post-title {
            color: #000;
            font-family: Poppins;
            font-size: 20px;
            font-style: normal;
            font-weight: 600;
            line-height: 21px;
            letter-spacing: -0.6px;
            margin-bottom: 12px;
        }

        .post-date {
            color: #666;
            font-size: 14px;
        }

        .post-content {
            color: #282A3A;
            font-family: Poppins;
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 140%;
            letter-spacing: -0.48px;
            min-height: 400px;
        }

        .post-content p {
            margin-bottom: 1em;
        }

        .post-content h1,
        .post-content h2,
        .post-content h3,
        .post-content h4,
        .post-content h5,
        .post-content h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
        }

        .post-content ul,
        .post-content ol {
            margin-left: 1.5em;
            margin-bottom: 1em;
        }

        .post-content img {
            max-width: 100%;
            height: auto;
        }

        .list-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #282A3A;
            font-family: Poppins;
            font-size: 15px;
            font-style: normal;
            font-weight: 500;
            line-height: normal;
            letter-spacing: -0.3px;
            text-transform: capitalize;
            border: 1px solid #D2D4DA;
            background: #FFF;
            padding: 8px 40px;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 60px;
        }

        .list-button:hover {
            background: #f8f9fa;
            border-color: #282A3A;
        }

        @media (max-width: 768px) {
            .post-title {
                font-size: 20px;
                margin-bottom: 8px;
            }

            .post-content {
                font-size: 14px;
                line-height: 1.7;
                min-height: 300px;
            }

            .post-header {
                padding-bottom: 16px;
                margin-bottom: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'NOTICES',
        'title' => 'Careers',
        'background' => BASE_URL . '/assets/images/notice-bg.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>

    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Notice',
        'current_page' => 'Careers',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Logistics News', 'url' => BASE_URL . '/pages/notices/logistics-news.php'],
            ['title' => 'Careers', 'url' => BASE_URL . '/pages/notices/careers.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>

    <!-- 메인 콘텐츠 -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <!-- 게시물 헤더 -->
                <div class="post-header">
                    <h2 class="post-title"><?php echo e($post['title']); ?></h2>
                    <p class="post-date"><?php echo $post['date']; ?></p>
                </div>

                <!-- 게시물 내용 (HTML 지원) -->
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>

                <!-- 목록 버튼 -->
                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>/pages/notices/careers.php" class="list-button">List</a>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
