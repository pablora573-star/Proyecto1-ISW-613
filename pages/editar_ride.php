<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ./index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ./pages/dashboard_chofer.php");
    exit();
}

//$currentDir = dirname(__FILE__);
//$parentDir = dirname($currentDir);
include('./common/connection.php');

$user_id = $_SESSION['user_id'];
$ride_id = (int)$_GET['id'];

// Obtener datos del ride
$sqlRide = "SELECT * FROM rides WHERE id = ? AND user_id = ?";
$stmtRide = mysqli_prepare($conn, $sqlRide);
mysqli_stmt_bind_param($stmtRide, 'ii', $ride_id, $user_id);
mysqli_stmt_execute($stmtRide);
$resultRide = mysqli_stmt_get_result($stmtRide);

if (mysqli_num_rows($resultRide) === 0) {
    mysqli_close($conn);
    header("Location: ./pages/dashboard_chofer.php?error=ride_not_found");
    exit();
}

$ride = mysqli_fetch_assoc($resultRide);

// Obtener vehículos del chofer
$sqlVehicles = "SELECT id, placa, marca, modelo FROM vehicles WHERE user_id = ? ORDER BY marca, modelo";
$stmtVehicles = mysqli_prepare($conn, $sqlVehicles);
mysqli_stmt_bind_param($stmtVehicles, 'i', $user_id);
mysqli_stmt_execute($stmtVehicles);
$resultVehicles = mysqli_stmt_get_result($stmtVehicles);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ride - Aventones</title>
   <link rel="stylesheet" href="../css/editar_ride.css">
</head>
<body>
    <nav>
        <h2> Aventones - Editar Ride</h2>
        <div class="nav-links">
            <a href="./dashboard_chofer.php">← Volver al Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1> Editar Ride</h1>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    $errors = [
                        'invalid_data' => ' Los datos ingresados no son válidos.',
                        'update_failed' => ' Error al actualizar el ride. Intenta nuevamente.'
                    ];
                    echo $errors[$_GET['error']] ?? ' Error desconocido.';
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="./actions/actualizar_ride.php" method="post">
                <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                
                <label for="nombre">Nombre del Ride:</label>
                <input type="text" id="nombre" name="nombre" 
                       value="<?= htmlspecialchars($ride['nombre']) ?>" 
                       required>
                
                <label for="origen">Lugar de Salida (Origen):</label>
                <input type="text" id="origen" name="origen" 
                       value="<?= htmlspecialchars($ride['origen']) ?>" 
                       required>
                
                <label for="destino">Lugar de Llegada (Destino):</label>
                <input type="text" id="destino" name="destino" 
                       value="<?= htmlspecialchars($ride['destino']) ?>" 
                       required>
                
                <div class="row">
                    <div class="col-2">
                        <label for="fecha_viaje">Fecha del Viaje:</label>
                        <input type="date" id="fecha_viaje" name="fecha_viaje" 
                               value="<?= $ride['fecha_viaje'] ?>" 
                               required>
                    </div>
                    <div class="col-2">
                        <label for="hora_viaje">Hora del Viaje:</label>
                        <input type="time" id="hora_viaje" name="hora_viaje" 
                               value="<?= $ride['hora_viaje'] ?>" 
                               required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-2">
                        <label for="costo_espacio">Costo por Espacio (₡):</label>
                        <input type="number" id="costo_espacio" name="costo_espacio" 
                               value="<?= $ride['costo_espacio'] ?>" 
                               min="0" step="100" 
                               required>
                    </div>
                    <div class="col-2">
                        <label for="cantidad_espacios">Cantidad de Espacios:</label>
                        <input type="number" id="cantidad_espacios" name="cantidad_espacios" 
                               value="<?= $ride['cantidad_espacios'] ?>" 
                               min="1" max="50" 
                               required>
                    </div>
                </div>
                
                <label for="vehicle_id">Vehículo:</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Selecciona un vehículo</option>
                    <?php while ($vehicle = mysqli_fetch_assoc($resultVehicles)): ?>
                        <option value="<?= $vehicle['id'] ?>" 
                                <?= $vehicle['id'] == $ride['vehicle_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($vehicle['marca'] . ' ' . $vehicle['modelo']) ?> 
                            (<?= htmlspecialchars($vehicle['placa']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <button type="submit">Actualizar Ride</button>
            </form>
        </div>
    </div>
</body>
</html>