<?php
// Redirect to incoterms.php by default
header('Location: ' . dirname($_SERVER['REQUEST_URI']) . '/incoterms.php');
exit();
?>