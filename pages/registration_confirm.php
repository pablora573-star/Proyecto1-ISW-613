<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - Aventones</title>
    <link rel="stylesheet" href="../css/registration_confirm.css">
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <h1>¡Registro Exitoso!</h1>
        
        <?php if (isset($_GET['email'])): ?>
            <p>Tu cuenta ha sido creada exitosamente.</p>
            <p>Hemos enviado un correo de activación a:</p>
            <div class="email"><?= htmlspecialchars($_GET['email']) ?></div>
            
            <?php if (isset($_GET['warning']) && $_GET['warning'] === 'email_failed'): ?>
                <div class="warning">
                    ⚠️ <strong>Advertencia:</strong> Hubo un problema al enviar el correo de activación. 
                    Por favor contacta con soporte.
                </div>
            <?php endif; ?>
            
            <p>Por favor revisa tu bandeja de entrada (y la carpeta de spam) y haz clic en el enlace de activación.</p>
            <p><small>El enlace expirará en 24 horas.</small></p>
        <?php else: ?>
            <p>Tu registro se completó correctamente.</p>
        <?php endif; ?>
        
        <a href="../index.php">Ir al Login</a>
    </div>
</body>
</html>