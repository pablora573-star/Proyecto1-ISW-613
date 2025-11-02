<?php
$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);

include($parentDir . '/common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos del formulario
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);
    $nacimiento = mysqli_real_escape_string($conn, $_POST['nacimiento']);
    $correo = mysqli_real_escape_string($conn, $_POST['correo']);
    $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
    $rol1 = mysqli_real_escape_string($conn, $_POST['rol']); // "chofer" o "pasajero"
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

   $rol = match($rol1) {
    'pasajero' => 'pasajero',
    'chofer' => 'chofer',
    'administrador' => 'admin'

    };
    // Validar que las contraseñas coincidan
    if ($password !== $password2) {
        header("Location: ../pages/registration_$rol.php?error=password_mismatch");
        exit();
    }

    // Verificar si la cédula ya existe (cada persona solo puede tener una cuenta)
    $checkCedula = mysqli_prepare($conn, "SELECT id FROM users WHERE cedula = ?");
    mysqli_stmt_bind_param($checkCedula, 's', $cedula);
    mysqli_stmt_execute($checkCedula);
    $resultCheck = mysqli_stmt_get_result($checkCedula);
    
    if (mysqli_num_rows($resultCheck) > 0) {
        header("Location: ../pages/registration_$rol.php?error=cedula_exists");
        exit();
    }
    mysqli_stmt_close($checkCedula);

    // Hash de la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Generar token de activación único
    $activationToken = bin2hex(random_bytes(32)); // Token de 64 caracteres
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // Expira en 24 horas

    // Procesar la imagen
    $fotoRuta = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        $foto = $_FILES['foto'];
        $nombreOriginal = $foto['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $extensionesPermitidas)) {
            header("Location: ../pages/registration_$rol.php?error=invalid_image");
            exit();
        }
        
        // Validar tamaño (5MB máximo)
        if ($foto['size'] > 5 * 1024 * 1024) {
            header("Location: ../pages/registration_$rol.php?error=image_too_large");
            exit();
        }
        
        // Generar nombre único
        $nombreArchivo = uniqid('user_') . '.' . $extension;
        $carpetaDestino = '../uploads/fotos/';
        $rutaDestino = $carpetaDestino . $nombreArchivo;
        
        // Crear carpeta si no existe
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }
        
        // Mover archivo
        if (move_uploaded_file($foto['tmp_name'], $rutaDestino)) {
            $fotoRuta = '../uploads/fotos/' . $nombreArchivo;
        } else {
            header("Location: ../pages/registration_$rol.php?error=upload_failed");
            exit();
        }
        
    } else {
        header("Location: ../pages/registration_$rol.php?error=no_photo");
        exit();
    }

    // Insertar usuario con estado "pendiente"
    $sql = "INSERT INTO users (nombre, apellido, cedula, fecha_nacimiento, correo, telefono, foto_url, rol, contra, estado, activation_token, token_expiry, fecha_creado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssssssss', 
        $name, 
        $lastName, 
        $cedula, 
        $nacimiento, 
        $correo, 
        $telefono, 
        $fotoRuta, 
        $rol, 
        $passwordHash,
        $activationToken,
        $tokenExpiry
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $userId = mysqli_insert_id($conn);
        
        // Preparar y enviar correo de activación
        $activationLink = "http://" . $_SERVER['HTTP_HOST'] . "/Proyecto1-ISW-613/actions/activarCuenta.php?token=$activationToken";
        
        $subject = "Activa tu cuenta en Aventones";
        $body = "Hola $name $lastName,\n\n"
              . "¡Gracias por registrarte en Aventones como $rol!\n\n"
              . "Para activar tu cuenta, por favor haz clic en el siguiente enlace:\n\n"
              . "$activationLink\n\n"
              . "Este enlace expirará en 24 horas.\n\n"
              . "Si no solicitaste esta cuenta, puedes ignorar este mensaje.\n\n"
              . "Saludos,\nEquipo Aventones";
        
        $headers = "From: Aventones <jpr12cr@gmail.com>\r\n";
        $headers .= "Reply-To: jpr12cr@gmail.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($correo, $subject, $body, $headers)) {
            // Correo enviado exitosamente
            header("Location: ../pages/registration_success.php?email=" . urlencode($correo));
            exit();
        } else {
            // Error al enviar correo, pero usuario creado
            header("Location: ../pages/registration_success.php?email=" . urlencode($correo) . "&warning=email_failed");
            exit();
        }
        
    } else {
        // Error al insertar en base de datos
        header("Location: ../pages/register_$rol.php?error=registration_failed");
        exit();
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
