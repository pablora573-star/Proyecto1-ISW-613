<?php

$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);

include($parentDir . '/common/connection.php');

// Verificar que se recibió el token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: ./pages/activation_error.php?error=no_token");
    exit();
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

// Buscar usuario con ese token
// CAMBIO: Ajustado a tus nombres de columnas
$sql = "SELECT id, nombre, apellido, correo, estado, token_expiry 
        FROM users 
        WHERE activation_token = ? 
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: ./pages/activation_error.php?error=invalid_token");
    exit();
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Verificar si el token expiró
$now = date('Y-m-d H:i:s');
if ($now > $user['token_expiry']) {
    mysqli_close($conn);
    header("Location: ./pages/activation_error.php?error=token_expired");
    exit();
}

// Verificar si la cuenta ya está activa
if ($user['estado'] === 'activo') {
    mysqli_close($conn);
    header("Location: /login.php?info=already_active");
    exit();
}

// Activar la cuenta
$updateSql = "UPDATE users 
              SET estado = 'activo', 
                  activation_token = NULL, 
                  token_expiry = NULL, 
                  fecha_activacion = NOW() 
              WHERE id = ?";

$updateStmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($updateStmt, 'i', $user['id']);

if (mysqli_stmt_execute($updateStmt)) {
    // Cuenta activada exitosamente
    
    // Enviar correo de bienvenida (opcional)
    $subject = "¡Cuenta activada exitosamente!";
    $body = "Hola {$user['nombre']} {$user['apellido']},\n\n"
          . "¡Tu cuenta en Aventones ha sido activada exitosamente!\n\n"
          . "Ya puedes iniciar sesión con tu correo electrónico y contraseña.\n\n"
          . "¡Bienvenido a Aventones!\n\n"
          . "Saludos,\nEquipo Aventones";
    
    $headers = "From: Aventones <jpr12cr@gmail.com>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($user['correo'], $subject, $body, $headers);
    
    mysqli_stmt_close($updateStmt);
    mysqli_close($conn);
    
    header("Location: ./pages/activation_success.php?name=" . urlencode($user['nombre']));
    exit();
    
} else {
    mysqli_stmt_close($updateStmt);
    mysqli_close($conn);
    header("Location: ./pages/activation_error.php?error=update_failed");
    exit();
}
?>