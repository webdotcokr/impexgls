<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// Networks 메인 페이지는 headquarters로 리다이렉트
header('Location: ' . BASE_URL . '/pages/networks/headquarters.php');
exit;
?>