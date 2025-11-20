<?php
// 데이터베이스 설정 포함
require_once __DIR__ . '/db-config.php';

define('BASE_URL', 'https://impexgls.com');
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);

// 이메일 설정
define('ADMIN_EMAIL', 'admin@impexgls.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_PORT', 587);

// 컬러 시스템 (PHP 상수로 정의)
define('COLOR_PRIMARY', '#E31E24');     // IMPEX Red
define('COLOR_SECONDARY', '#1B2951');   // Navy Blue
define('COLOR_SECONDARY_DARK', '#212328');
define('COLOR_CG100', '#F8F9FA');
define('COLOR_CG200', '#d2d4da');
define('COLOR_CG300', '#b3b5bd');
define('COLOR_CG400', '#9496a1');
define('COLOR_CG500', '#777986');
define('COLOR_CG600', '#5b5d6b');
define('COLOR_CG700', '#404252');
define('COLOR_CG800', '#282a3a');
define('COLOR_CG900', '#101223');
define('COLOR_G500', '#999999');
define('COLOR_G600', '#666666');
define('COLOR_G700', '#333333');
define('COLOR_G800', '#222222');
define('COLOR_G900', '#131313');
define('COLOR_BLACK', '#000000');
define('COLOR_WHITE', '#FFFFFF');

// Bitnami 스택 기반 경로 설정
if (strpos($_SERVER['SERVER_SOFTWARE'], 'Bitnami') !== false) {
    define('BITNAMI_ROOT', '/opt/bitnami/apache/htdocs');
}
?>