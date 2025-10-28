<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - Aventones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            text-align: center;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .email {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        a:hover {
            background-color: #45a049;
        }
    </style>
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