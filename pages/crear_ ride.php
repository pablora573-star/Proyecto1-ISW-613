<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ../index.php");
    exit();
}

//$currentDir = dirname(__FILE__);
//$parentDir = dirname($currentDir);
include('./common/connection.php');

$user_id = $_SESSION['user_id'];

// Obtener vehículos para el select
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
    <title>Crear Ride - Aventones</title>
    <link rel="stylesheet" href="../css/crear_ride.css">
</head>
<body>
    <nav>
        <h2> Aventones - Crear Ride</h2>
        <div class="nav-links">
            <a href="./dashboard_chofer.php">← Volver al Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1> Crear Nuevo Ride</h1>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php
                    $errors = [
                        'no_vehicles' => ' Debes registrar al menos un vehículo antes de crear un ride.',
                        'invalid_data' => ' Los datos ingresados no son válidos.',
                        'create_failed' => ' Error al crear el ride. Intenta nuevamente.'
                    ];
                    echo $errors[$_GET['error']] ?? ' Error desconocido.';
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (mysqli_num_rows($resultVehicles) === 0): ?>
                <div class="warning">
                    <strong> Atención:</strong> No tienes vehículos registrados. 
                    <a href="./crear_vehiculo.php">Registra un vehículo primero</a>.
                </div>
            <?php else: ?>
                <form action="./actions/insertar_ride.php" method="post">
                    <label for="nombre">Nombre del Ride:</label>
                    <input type="text" id="nombre" name="nombre" 
                           placeholder="Ej: Heredia - San José (Lunes mañana)" 
                           required>
                    
                    <label for="origen">Lugar de Salida (Origen):</label>
                    <input type="text" id="origen" name="origen" 
                           placeholder="Ej: Heredia" 
                           required>
                    
                    <label for="destino">Lugar de Llegada (Destino):</label>
                    <input type="text" id="destino" name="destino" 
                           placeholder="Ej: San José" 
                           required>
                    
                    <div class="row">
                        <div class="col-2">
                            <label for="fecha_viaje">Fecha del Viaje:</label>
                            <input type="date" id="fecha_viaje" name="fecha_viaje" 
                                   min="<?= date('Y-m-d') ?>" 
                                   required>
                        </div>
                        <div class="col-2">
                            <label for="hora_viaje">Hora del Viaje:</label>
                            <input type="time" id="hora_viaje" name="hora_viaje" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-2">
                            <label for="costo_espacio">Costo por Espacio (₡):</label>
                            <input type="number" id="costo_espacio" name="costo_espacio" 
                                   min="0" step="100" 
                                   placeholder="Ej: 2000" 
                                   required>
                        </div>
                        <div class="col-2">
                            <label for="cantidad_espacios">Cantidad de Espacios:</label>
                            <input type="number" id="cantidad_espacios" name="cantidad_espacios" 
                                   min="1" max="8" 
                                   placeholder="Ej: 3" 
                                   required>
                        </div>
                    </div>
                    
                    <label for="vehicle_id">Vehículo:</label>
                    <select id="vehicle_id" name="vehicle_id" required>
                        <option value="">Selecciona un vehículo</option>
                        <?php while ($vehicle = mysqli_fetch_assoc($resultVehicles)): ?>
                            <option value="<?= $vehicle['id'] ?>">
                                <?= htmlspecialchars($vehicle['marca'] . ' ' . $vehicle['modelo']) ?> 
                                (<?= htmlspecialchars($vehicle['placa']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit">Crear Ride</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>