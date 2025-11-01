<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ../index.php");
    exit();
}

include('../common/connection.php');

if (isset($_GET['id'])) {
    $vehicle_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar que el vehículo pertenezca al usuario
    $checkSql = "SELECT id FROM vehiculos WHERE id = ? AND user_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'ii', $vehicle_id, $user_id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Verificar si está en uso en rides
        $rideSql = "SELECT COUNT(*) as count FROM rides WHERE vehicle_id = ?";
        $rideStmt = mysqli_prepare($conn, $rideSql);
        mysqli_stmt_bind_param($rideStmt, 'i', $vehicle_id);
        mysqli_stmt_execute($rideStmt);
        $rideResult = mysqli_stmt_get_result($rideStmt);
        $rideRow = mysqli_fetch_assoc($rideResult);
        
        if ($rideRow['count'] > 0) {
            header("Location: ../pages/vehiculos.php?error=vehicle_in_use");
            exit();
        }
        
        // Eliminar vehículo
        $deleteSql = "DELETE FROM vehiculos WHERE id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($deleteStmt, 'i', $vehicle_id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            header("Location: ../pages/vehiculos.php?success=deleted");
        } else {
            header("Location: ../pages/vehiculos.php?error=delete_failed");
        }
    } else {
        header("Location: ../pages/vehiculos.php?error=unauthorized");
    }
    
    mysqli_close($conn);
} else {
    header("Location: ../pages/vehiculos.php");
}
?>