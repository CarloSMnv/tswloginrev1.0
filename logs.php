<?php
require_once "config.php"; 
function get_descriptive_os_name($os_info) {
  if (strpos($os_info, 'Windows NT 10.0') !== false) {
      return 'Windows 10';
  } elseif (strpos($os_info, 'Windows NT 6.3') !== false) {
      return 'Windows 8.1';
  } elseif (strpos($os_info, 'Windows NT 6.2') !== false) {
      return 'Windows 8';
  } elseif (strpos($os_info, 'Windows NT 6.1') !== false) {
      return 'Windows 7';
  } elseif (strpos($os_info, 'Windows NT 6.0') !== false) {
      return 'Windows Vista';
  } elseif (strpos($os_info, 'Windows NT 5.1') !== false) {
      return 'Windows XP';
  } elseif (strpos($os_info, 'Windows NT 5.0') !== false) {
      return 'Windows 2000';
  } elseif (strpos($os_info, 'Linux') !== false) {
      return 'Linux';
  } elseif (strpos($os_info, 'Darwin') !== false) {
      return 'macOS';
  } else {
      return $os_info;
  }
}
  function write_login_log($email, $error, $validation) {
    global $pdo;
    $estado = (int)$error;
    $zona_horaria = 'America/Mexico_City'; // Establecer la zona horaria deseada
    date_default_timezone_set($zona_horaria); // Establecer la zona horaria en el script
    $fecha_hora = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $navegador = $_SERVER['HTTP_USER_AGENT'];
    $sistema_operativo = get_descriptive_os_name(php_uname('s') . ' ' . php_uname('r'));
    $log = "$estado|$fecha_hora|$email|$ip|$navegador|$sistema_operativo|$validation\n";
    
    // Insertar registro en la base de datos
    $stmt = $pdo->prepare("INSERT INTO login_logs (estado, fecha_hora, email, ip, navegador, sistema_operativo, validation) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$estado, $fecha_hora, $email, $ip, $navegador, $sistema_operativo, $validation]);
    
    // Escribir registro en archivo log.txt
    file_put_contents('log.txt', $log, FILE_APPEND);
}
function get_failed_login_attempts($ip, $hours) {
    global $pdo;
    $zona_horaria = 'America/Mexico_City'; // Establecer la zona horaria deseada
    date_default_timezone_set($zona_horaria); // Establecer la zona horaria en el script
    $time_limit = date('Y-m-d H:i:s', strtotime("-$hours hour"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_logs WHERE estado = 0 AND ip = ? AND fecha_hora >= ?");
    $stmt->execute([$ip, $time_limit]);
    
    return $stmt->fetchColumn();
}


?>