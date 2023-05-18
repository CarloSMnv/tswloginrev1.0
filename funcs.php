<?php
require_once "config.php"; 
  function get_failed_login_attempts($ip, $hours) {
    global $pdo;
    
    $time_limit = date('Y-m-d H:i:s', strtotime("-$hours hour"));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_logs WHERE estado = 0 AND ip = ? AND fecha_hora >= ?");
    $stmt->execute([$ip, $time_limit]);
    
    return $stmt->fetchColumn();
}
?>