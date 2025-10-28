<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Chofer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        nav {
            background-color: #333;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        nav h2 {
            color: white;
            font-size: 20px;
        }
        nav .nav-links {
            display: flex;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav a:hover {
            background-color: #555;
        }
        nav a.btn-primary {
            background-color: #FF9800;
        }
        nav a.btn-primary:hover {
            background-color: #F57C00;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #FF9800;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #F57C00;
        }
        .file-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }
        .badge {
            display: inline-block;
            background-color: #FF9800;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
        }
    </style>
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
        
        <form action="./actions/insertUser.php" method="post" enctype="multipart/form-data">
           
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