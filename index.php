<?php
  //check if user is logged in
  //if not, redirect to login page

  session_start();
  if (isset($_SESSION['cedula'])) {
      header("Location: ./pages/dashboard.php");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ffffffff;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            color: black;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #0d00ffff;
        }
        .register-links {
            margin-top: 20px;
            text-align: center;
        }
        .register-links p {
            color: #000000ff;
            margin-bottom: 10px;
        }
        .register-links a {
            display: inline-block;
            margin: 5px 10px;
            padding: 8px 20px;
            text-decoration: none;
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }
        .register-links a.pasajero {
            background-color: #45a049;
        }
        .register-links a.pasajero:hover {
            background-color: #0d00ffff;
        }
        .register-links a.chofer {
            background-color: #FF9800;
        }
        .register-links a.chofer:hover {
            background-color: #0d00ffff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <h2>Aventones</h2>
        <form action=" ./actions/login.php" method="post">
            <input type="cedula" name="cedula" placeholder="Cedula" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="register-links">
            <p>¿No tienes cuenta?<br> Regístrate como:</p>
            <a href=" ./pages/registration_pasajero.php" class="pasajero">Pasajero</a>
            <a href=" ./pages/registration_chofer.php" class="chofer">Chofer</a>
        </div>
    </div>
</body>
</html>