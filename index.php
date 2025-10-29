<?php
  //check if user is logged in
  //if not, redirect to login page

  session_start();
  if (isset($_SESSION['cedula'])) {
      header("Location:./pages/dashboard.php");
      exit();
  }

  $error_message = "";
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case "cuenta_pendiente":
                $error_message = " Tu cuenta está pendiente de aprobación. Por favor espera la activación.";
                break;
            case "cuenta_inactiva":
                $error_message = " Tu cuenta está inactiva. Contacta con el administrador.";
                break;
            case "credenciales_invalidas":
                $error_message = " Cédula o contraseña incorrecta.";
                break;
            case "estado_invalido":
                $error_message = " El estado de tu cuenta no es válido.";
                break;
            default:
                $error_message = "";
        }
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aventones</title>
    <link rel="stylesheet" href="./css/index_style.css">

</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <h2>Aventones</h2>

        <?php if (!empty($error_message)): ?>
        <div class="error-alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

        <form action="./actions/login.php" method="post">
            <input type="text" name="cedula" placeholder="Cedula" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="register-links">
            <p>¿No tienes cuenta?<br> Regístrate como:</p>
            <a href="./pages/registration_pasajero.php" class="pasajero">Pasajero</a>
            <a href="./pages/registration_chofer.php" class="chofer">Chofer</a>
        </div>
    </div>
</body>
</html>