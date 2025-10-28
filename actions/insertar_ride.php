<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: /index.php");
    exit();
}

$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);
include($parentDir . '/common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $origen = mysqli_real_escape_string($conn, $_POST['origen']);
    $destino = mysqli_real_escape_string($conn, $_POST['destino']);
    $fecha_viaje = mysqli_real_escape_string($conn, $_POST['fecha_viaje']);
    $hora_viaje = mysqli_real_escape_string($conn, $_POST['hora_viaje']);
    $costo_espacio = (float)$_POST['costo_espacio'];
    $cantidad_espacios = (int)$_POST['cantidad_espacios'];
    $vehicle_id = (int)$_POST['vehicle_id'];
    
    // Validar datos
    if (empty($nombre) || empty($origen) || empty($destino) || empty($fecha_viaje) || 
        empty($hora_viaje) || $costo_espacio < 0 || $cantidad_espacios < 1 || $vehicle_id < 1) {
        header("Location: /pages/crear_ride.php?error=invalid_data");
        exit();
    }
    
    // Verificar que el vehículo pertenezca al usuario
    $checkVehicle = "SELECT id FROM vehicles WHERE id = ? AND user_id = ?";
    $stmtCheck = mysqli_prepare($conn, $checkVehicle);
    mysqli_stmt_bind_param($stmtCheck, 'ii', $vehicle_id, $user_id);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    
    if (mysqli_num_rows($resultCheck) === 0) {
        mysqli_stmt_close($stmtCheck);
        mysqli_close($conn);
        header("Location: /pages/crear_ride.php?error=invalid_vehicle");
        exit();
    }
    mysqli_stmt_close($stmtCheck);
    
    // Insertar ride
    $sql = "INSERT INTO rides (user_id, nombre, origen, destino, fecha_viaje, hora_viaje, costo_espacio, cantidad_espacios, vehicle_id, fecha_creado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isssssdii', 
        $user_id, 
        $nombre, 
        $origen, 
        $destino, 
        $fecha_viaje, 
        $hora_viaje, 
        $costo_espacio, 
        $cantidad_espacios, 
        $vehicle_id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: /pages/dashboard_chofer.php?success=ride_created");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: /pages/crear_ride.php?error=create_failed");
        exit();
    }
    
} else {
    header("Location: /pages/dashboard_chofer.php");
    exit();
}
?>