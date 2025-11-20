<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// Service 메인 페이지는 international-transportation으로 리다이렉트
header('Location: ' . BASE_URL . '/pages/service/international-transportation.php');
exit;
?>