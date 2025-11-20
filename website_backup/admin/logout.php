<?php
/**
 * 관리자 로그아웃 처리
 */

require_once 'includes/auth.php';

// 로그아웃 처리
logout();

// 로그인 페이지로 리다이렉트
header('Location: index.php');
exit;
?>