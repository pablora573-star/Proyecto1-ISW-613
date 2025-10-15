<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>
</head>

<body>
    <h1>Registro</h1>
    <form action="/actions/insertUser.php" method="post">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="lastName">Apellido:</label>
        <input type="text" id="lastName" name="lastName" required><br><br>

        <label for="cedula">Número de cédula:</label>
        <input type="text" id="cedula" name="cedula" required><br><br>

        <label for="nacimiento">Fecha Nacimiento:</label>
        <input type="text" id="nacimiento" name="nacimiento" required><br><br>

        <label for="telefono">Número de teléfono:</label>
        <input type="text" id="telefono" name="telefono" required><br><br>

        <label for="correo">Correo electrónico:</label>
        <input type="text" id="correo" name="correo" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="password2">Repetir Contraseña:</label>
        <input type="password2" id="password2" name="password2" required><br><br>

        <button type="submit">Registrar</button>
    </form>
</body>

</html>