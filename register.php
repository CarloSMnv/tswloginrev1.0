<?php
require_once "config.php"; 
require_once "session.php"; 

$error = '';
$success = '';
$result = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $fullname = trim($_POST['name']);
    $email = trim($_POST['email']); 
    $password = trim($_POST['password']); 
    $confirm_password = trim($_POST["confirm_password"]); 
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    if ($query->rowCount() > 0) { 
        $error .= '<p class="error">El correo electronico ya esta registrado!</p>';
    } else { 
        if (strlen($password) < 6) {
            $error .= '<p class="error">La contraseña debe ser mayor a 6 caracteres.</p>'; 
        } 

        if (empty($confirm_password)) { 
            $error .= '<p class="error">Por favor confirme la contraseña.</p>'; 
        } else { 
            if (empty($error) && ($password != $confirm_password)) { 
                $error .= '<p class="error">La contraseña no coicide.</p>'; 
            } 
        } 

        if (empty($error)) { 
            $insertQuery = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?);");
            $result = $insertQuery->execute([$fullname, $email, $password_hash]); 
            
            if ($result) {
                // Verificación del captcha
                $captcha_response = $_POST['g-recaptcha-response'];
                $secret_key = "6LfPnBUmAAAAALuoBJlghT0K3Rk1vtk2Qiq704zk";
                $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$captcha_response}");
                $captcha_success = json_decode($verify);

                if ($captcha_success->success == false) {
                    $error .= '<p class="error">Por favor intente de nuevo el Captcha!</p>';
                    $deleteQuery = $pdo->prepare("DELETE FROM users WHERE email = ?");
                    $deleteQuery->execute([$email]); // elimina el registro de la base de datos si el captcha no es válido
                } else {
                    $success .= '<p class="success annimation">Registro Exitoso!</p>';
                }
            } else {
                $error .= '<p class="error">Intentalo mas tarde!</p>';
            }
        }
    }
    $query = null;
    $insertQuery = null;
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/CarloSMnv/imagess/main/icon.png"/>
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">  
    
</head>
<body>
<section class="vh-100" style="background-color: #eee;">
  <div class="container h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-12 col-xl-11">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-sm-8 col-md-6 col-lg-4">
              <div class="alert alert-danger <?php if (empty($error)) { echo 'd-none'; } ?> text-center" role="alert">
                <?php echo $error; ?>
              </div>
              <div class="alert alert-success <?php if (empty($success)) { echo 'd-none'; } ?> text-center" role="alert">
                <?php echo $success; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="card text-black" style="border-radius: 25px;">
          <div class="card-body p-md-5">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                <div class="modal-header">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href='index.html'" style="position: absolute; top: 15px; right: 15px;"></button>
                </div>
                <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Registrate!</p>
                <form class="mx-1 mx-md-4" method="post" action="">
                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="text" id="form3Example1c" class="form-control" name="name" required />
                      <label class="form-label" for="form3Example1c">Nombre Completo</label>
                    </div>
                  </div>
                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="email" id="form3Example3c" class="form-control" name="email" required />
                      <label class="form-label" for="form3Example3c">Correo Electronico</label>
                    </div>
                  </div>
                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="password" id="form3Example4c" class="form-control" name="password" required />
                      <label class="form-label" for="form3Example4c">Contraseña</label>
                    </div>
                  </div>
                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="password" id="form3Example4cd" class="form-control" name="confirm_password"  required />
                      <label class="form-label" for="form3Example4cd">Confirmar contraseña</label>
                    </div>
                  </div>
                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                  <div class="g-recaptcha" data-sitekey="6LfPnBUmAAAAAMVb-pBwmm7CvbPER0nHP1cAGsKC"></div>
                  </div>
                  
                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                    <input type="submit" name="submit" class="btn btn-primary btn-lg" value="Enviar">
                  </div>
                  <div class="form-check d-flex justify-content-center mb-5">
                    <label class="form-check-label" for="form2Example3">
                     <a href="login.php">Ya tienes cuenta? Inicia Sesión Aqui</a>
                    </label>
                  </div>
                </form>
              </div>
              <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">
                <img src="/tswlogin/recursos/iconf.png"
                  class="img-fluid" alt="Sample image">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</body>
</html>