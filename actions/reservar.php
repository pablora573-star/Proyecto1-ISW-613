<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: ../index.php");
    exit();
}

include('../common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ride_id = (int)$_POST['ride_id'];
    $chofer_id = (int)$_POST['chofer_id'];
    $pasajero_id = $_SESSION['user_id'];
    $cantidad_asientos = (int)$_POST['cantidad_asientos'];
    
    // Validar cantidad de asientos
    if ($cantidad_asientos < 1 || $cantidad_asientos > 4) {
        header("Location: ../pages/crear_reserva.php?ride_id=$ride_id&error=invalid_quantity");
        exit();
    }
    
    // Verificar asientos disponibles
    $checkSql = "SELECT r.cantidad_espacios,
                 (SELECT COUNT(*) FROM reservations res 
                  WHERE res.ride_id = r.id 
                  AND res.estado IN ('pendiente', 'aceptada')) AS asientos_reservados
                 FROM rides r
                 WHERE r.id = ?";
    $stmtCheck = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($stmtCheck, 'i', $ride_id);
    mysqli_stmt_execute($stmtCheck);
    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    $ride = mysqli_fetch_assoc($resultCheck);
    
    $asientos_disponibles = $ride['cantidad_espacios'] - $ride['asientos_reservados'];
    
    if ($cantidad_asientos > $asientos_disponibles) {
        mysqli_close($conn);
        header("Location: ../pages/crear_reserva.php?ride_id=$ride_id&error=insufficient_seats");
        exit();
    }
    
    // Verificar si ya tiene reserva activa
    $checkReserva = "SELECT id FROM reservations 
                     WHERE ride_id = ? AND pasajero_id = ? 
                     AND estado IN ('pendiente', 'aceptada')";
    $stmtCheckReserva = mysqli_prepare($conn, $checkReserva);
    mysqli_stmt_bind_param($stmtCheckReserva, 'ii', $ride_id, $pasajero_id);
    mysqli_stmt_execute($stmtCheckReserva);
    $resultCheckReserva = mysqli_stmt_get_result($stmtCheckReserva);
    
    if (mysqli_num_rows($resultCheckReserva) > 0) {
        mysqli_close($conn);
        header("Location: ../pages/crear_reserva.php?ride_id=$ride_id&error=already_reserved");
        exit();
    }
    
    // Crear reserva
    $sql = "INSERT INTO reservations (ride_id, pasajero_id, chofer_id, cantidad_asientos, estado, fecha_creado) 
            VALUES (?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iiii', $ride_id, $pasajero_id, $chofer_id, $cantidad_asientos);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../pages/mis_reservas_pasajero.php?success=reservation_created");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../pages/crear_reserva.php?ride_id=$ride_id&error=creation_failed");
        exit();
    }
    
} else {
    header("Location: ../pages/buscar_rides.php");
    exit();
}
?>