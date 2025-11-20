<?php
// DB 설정 - Docker 환경 지원
define('DB_HOST', getenv('DB_HOST') ?: 'localhost'); // Docker: db, Local: localhost
define('DB_NAME', getenv('DB_NAME') ?: 'corporate_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'OvIZQ5TyCQN/');
define('DB_CHARSET', 'utf8mb4');

// PDO 연결 함수
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
}
?>