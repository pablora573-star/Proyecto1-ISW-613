<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: /index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: /pages/vehiculos.php");
    exit();
}

$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);
include($parentDir . '/common/connection.php');

$user_id = $_SESSION['user_id'];
$vehicle_id = (int)$_GET['id'];

// Obtener datos del vehículo
$sqlVehicle = "SELECT * FROM vehicles WHERE id = ? AND user_id = ?";
$stmtVehicle = mysqli_prepare($conn, $sqlVehicle);
mysqli_stmt_bind_param($stmtVehicle, 'ii', $vehicle_id, $user_id);
mysqli_stmt_execute($stmtVehicle);
$resultVehicle = mysqli_stmt_get_result($stmtVehicle);

if (mysqli_num_rows($resultVehicle) === 0) {
    mysqli_close($conn);
    header("Location: /pages/vehiculos.php?error=vehicle_not_found");
    exit();
}

$vehicle = mysqli_fetch_assoc($resultVehicle);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vehículo - Aventones</title>
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
        nav .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
        }
        nav .nav-links a:hover {
            background-color: #555;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
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
        .current-photo {
            margin-bottom: 20px;
            text-align: center;
        }
        .current-photo img {
            max-width: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .photo-note {
            font-size: 12px;
            color: #666;
            margin-top: -15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav>
        <h2> Aventones - Editar Vehículo</h2>
        <div class="nav-links">
            <a href="/pages/vehiculos.php">← Volver a Vehículos</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1> Editar Vehículo</h1>
            
            <?php if ($vehicle['foto_url']): ?>
                <div class="current-photo">
                    <p style="margin-bottom:10px;color:#666;"><strong>Foto actual:</strong></p>
                    <img src="/<?= htmlspecialchars($vehicle['foto_url']) ?>" alt="Foto actual del vehículo">
                </div>
            <?php endif; ?>
            
            <form action="/actions/actualizar_vehiculo.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                
                <label for="placa">Placa:</label>
                <input type="text" id="placa" name="placa" 
                       value="<?= htmlspecialchars($vehicle['placa']) ?>" 
                       required>
                
                <label for="color">Color:</label>
                <input type="text" id="color" name="color" 
                       value="<?= htmlspecialchars($vehicle['color']) ?>" 
                       required>
                       
                <label for="marca">Marca:</label>
                <input type="text" id="marca" name="marca" 
                       value="<?= htmlspecialchars($vehicle['marca']) ?>" 
                       required>
                
                <label for="modelo">Modelo:</label>
                <input type="text" id="modelo" name="modelo" 
                       value="<?= htmlspecialchars($vehicle['modelo']) ?>" 
                       required>
                
                <label for="anio">Año:</label>
                <input type="number" id="anio" name="anio" 
                       value="<?= $vehicle['anio'] ?>" 
                       min="1900" max="2025" required>
                
                
                
                <label for="capacidad_asientos">Capacidad de Asientos:</label>
                <input type="number" id="capacidad_asientos" name="capacidad_asientos" 
                       value="<?= $vehicle['capacidad_asientos'] ?>" 
                       min="1" max="50" required>
                
                <label for="foto">Nueva Fotografía (opcional):</label>
                <input type="file" id="foto" name="foto" accept="image/*">
                <p class="photo-note">* Deja vacío si no deseas cambiar la foto actual</p>
                
                <button type="submit">Actualizar Vehículo</button>
            </form>
        </div>
    </div>
</body>
</html>