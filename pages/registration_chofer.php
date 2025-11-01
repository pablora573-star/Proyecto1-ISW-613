<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Chofer</title>
    <link rel="stylesheet" href="../css/chofer_style.css">
</head>

<body>
    <nav>
        <h2>Aventones</h2>
        <div class="nav-links">
            <a href="./registration_pasajero.php" class="btn-primary">Registrar Pasajero</a>
            <a href="../index.php">Volver al Login</a>
        </div>
    </nav>

    <div class="container">
        <h1>Registro de Usuario</h1>
        <p class="subtitle">Registrándose como: <span class="badge">CHOFER</span></p>
        
        <form action="../actions/insertUser.php" method="post" enctype="multipart/form-data">
           
            <!-- Campo oculto con el rol quemado -->
            <input type="hidden" name="rol" value="chofer">

            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="lastName">Apellido:</label>
            <input type="text" id="lastName" name="lastName" required>

            <label for="cedula">Número de cédula:</label>
            <input type="text" id="cedula" name="cedula" required>

            <label for="nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="nacimiento" name="nacimiento" required>

            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" name="correo" required>

            <label for="telefono">Número de teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required>

            <label for="foto">Fotografía personal:</label>
            <input type="file" id="foto" name="foto" accept="image/*" required>
            <p class="file-info">Formatos aceptados: JPG, PNG, GIF (máx. 5MB)</p>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="password2">Repetir Contraseña:</label>
            <input type="password" id="password2" name="password2" required>

            <button type="submit">Registrar como Chofer</button>
        </form>
    </div>
</body>

</html>