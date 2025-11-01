<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ./vehiculos.php");
    exit();
}

//$currentDir = dirname(__FILE__);
//$parentDir = dirname($currentDir);
include('../common/connection.php');

$user_id = $_SESSION['user_id'];
$vehicle_id = (int)$_GET['id'];

// Obtener datos del vehículo
$sqlVehicle = "SELECT * FROM vehiculos WHERE id = ? AND user_id = ?";
$stmtVehicle = mysqli_prepare($conn, $sqlVehicle);
mysqli_stmt_bind_param($stmtVehicle, 'ii', $vehicle_id, $user_id);
mysqli_stmt_execute($stmtVehicle);
$resultVehicle = mysqli_stmt_get_result($stmtVehicle);

if (mysqli_num_rows($resultVehicle) === 0) {
    mysqli_close($conn);
    header("Location: ./vehiculos.php?error=vehicle_not_found");
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
     <link rel="stylesheet" href="../css/editar_vehiculo.css">
</head>
<body>
    <nav>
        <h2> Aventones - Editar Vehículo</h2>
        <div class="nav-links">
            <a href="./vehiculos.php">← Volver a Vehículos</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1> Editar Vehículo</h1>
            
            <?php if ($vehicle['foto_url']): ?>
                <div class="current-photo">
                    <p style="margin-bottom:10px;color:#666;"><strong>Foto actual:</strong></p>
                    <img src="../<?= htmlspecialchars($vehicle['foto_url']) ?>" alt="Foto actual del vehículo">
                </div>
            <?php endif; ?>
            
            <form action="../actions/actualizar_vehiculo.php" method="post" enctype="multipart/form-data">
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