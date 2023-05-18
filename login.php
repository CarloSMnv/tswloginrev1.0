<?php
    require_once "config.php"; 
    require_once "session.php";
    require_once "logs.php";
    $error='';
    $disable_login_button = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $email = trim($_POST['email']); 
        $password = trim($_POST['password']);
        $ip = $_SERVER['REMOTE_ADDR'];
        $failed_attempts = get_failed_login_attempts($ip, 1); // Verificar los intentos fallidos en las últimas 1 hora
        // Verificar si ha pasado el tiempo necesario desde el último intento fallido
        $time_limit = date('Y-m-d H:i:s', strtotime("-1 minute")); // Intervalo de 1 hora
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_logs WHERE estado = 0 AND ip = ? AND fecha_hora >= ?");
        $stmt->execute([$ip, $time_limit]);
        $attempts_within_time_limit = $stmt->fetchColumn();
        $max_attempts = 2; // Número máximo de intentos fallidos permitidos
        if ($attempts_within_time_limit === 0 && $failed_attempts >= $max_attempts) {
            // Restablecer contador de intentos fallidos para la dirección IP actual
            $stmt = $pdo->prepare("DELETE FROM login_logs WHERE estado = 0 AND ip = ?");
            $stmt->execute([$ip]);
            // Restablecer el contador a 0
            $failed_attempts = 0;
        }
        if ($failed_attempts >= $max_attempts) {
            $remaining_time = time() - strtotime($time_limit); //opcional  $remaining_time = abs(time() - strtotime($time_limit)); para obtener el valor absoluto a prueba de errores
            $error .= '<p class="error">Has alcanzado el límite de intentos fallidos de inicio de sesión. Por favor, inténtalo más tarde.</p>';
            $disable_login_button = true; // Variable para deshabilitar el botón de "Iniciar Sesión"
        }
         else {
            if (empty($email)) {
        $error .= '<p class="error">Por favor ingrese su Correo!</p>';
    }
    if (empty($password)) {
        $error .= '<p class="error">Por favor ingrese su contraseña!</p>';
    }
    if (empty($error)) {
        // Verificar el reCAPTCHA
        $captcha_response = $_POST['g-recaptcha-response'];
        $secret_key = '6LfPnBUmAAAAALuoBJlghT0K3Rk1vtk2Qiq704zk'; // Reemplazar con su clave secreta de reCAPTCHA
        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha_response);
        $response_data = json_decode($verify_response);
        if ($response_data->success) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bindParam(1, $email); 
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['userid'] = $row['id'];
                    $_SESSION['user'] = $row;
                    write_login_log($email, true, "Login Exitoso");
                    header("Location: dashb.php");
                    exit;
                } else {
                    $error .= '<p class="error">La contraseña no es valida!</p>';
                    write_login_log($email, false, "Contraseña invalida");
                }
            } else {
                $error .= '<p class="error">No se encontro usuario asociado al correo!</p>';
                write_login_log($email, false, "Usuario no encontrado");
            }
        } else {
            $error .= '<p class="error">Por favor intente de nuevo el Captcha!</p>';
            write_login_log($email, false, "Captcha invalido");
        }
    }
}
        }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Inicio de Sesion</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/CarloSMnv/imagess/main/icon.png" />
    </head>
<body> 
    <div class="container-fluid">
        <section class="vh-100" style="background-color: #eee;">
        <div class="container h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-lg-12 col-xl-11">
                    <div class="container">
                    <div class="modal modal-sheet position-static d-block bg-body-secondary p-1 py-md-4" tabindex="-1" role="dialog" id="modalSignin">
                          <div class="modal-dialog " role="document">
                            <div class="modal-content rounded-4 shadow">
                              <div class="modal-header p-5 pb-4 border-bottom-0">
                                <h1 class="fw-bold mb-0 fs-2">Inicia Sesión</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href='index.html'"></button>
                              </div>
                              <div class="modal-body p-5 pt-0">
                                <form class="" method="post" action="">
                                  <div class="form-floating mb-3">
                                    <input type="email" class="form-control rounded-3" id="floatingInput" placeholder="name@example.com" required name="email">
                                    <label for="floatingInput">Correo electronico</label>
                                  </div>
                                  <div class="form-floating mb-3">
                                    <input type="password" class="form-control rounded-3" id="floatingPassword" placeholder="Password" required name="password"> 
                                    <label for="floatingPassword">Contraseña</label>
                                    <?php echo $error; ?>
                                  </div>
                                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                                  <div class="g-recaptcha" data-sitekey="6LfPnBUmAAAAAMVb-pBwmm7CvbPER0nHP1cAGsKC"></div>
                                  </div>
                                  <button id="login_btn" class="w-100 mb-2 btn btn-lg rounded-3 btn-primary" type="submit" name="submit" <?php if($disable_login_button) echo 'disabled'; ?>>Iniciar Sesión </button>
                                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                                      <div id="countdown" class="text-danger"> </div>
                                  </div>
                                  <small class="text-body-secondary">Al hacer clic, acepta los terminos de servicio.</small>
                                  <hr class="my-4">
                                  <h2 class="fs-5 fw-bold mb-3">Aún no tienes cuenta</h2>
                                  <button class="w-100 py-2 mb-2 btn btn-outline-secondary rounded-3" type="submit" onclick="window.location.href='register.php'">
                                    Registrate con tu correo aquí
                                  </button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>  
    </div>
    <script>
      var remaining_time = <?php echo $remaining_time; ?>;
      var countdown_elem = document.getElementById("countdown");
      var countdown_interval = setInterval(function() {
          remaining_time--;
          if (remaining_time <= 0) {
              clearInterval(countdown_interval);
              countdown_elem.innerHTML = "Inicio de sesión disponible";
              document.getElementById("login_btn").disabled = false; // Habilitar el botón de inicio de sesión
          } else {
            countdown_elem.innerHTML = "Tiempo restante: " + remaining_time + " segundos";
          }
      }, 1000);
      document.getElementById("login_btn").disabled = true; // Inhabilitar el botón de inicio de sesión
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>
</html>
