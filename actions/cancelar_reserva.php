<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: ../index.php");
    exit();
}

include('../common/connection.php');

if (isset($_GET['id'])) {
    $reserva_id = (int)$_GET['id'];
    $pasajero_id = $_SESSION['user_id'];
    
    // Verificar que la reserva pertenezca al pasajero
    $checkSql = "SELECT id FROM reservations 
                 WHERE id = ? AND pasajero_id = ? 
                 AND estado IN ('pendiente', 'aceptada')";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'ii', $reserva_id, $pasajero_id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Cancelar reserva
        $updateSql = "UPDATE reservations SET estado = 'cancelada' WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, 'i', $reserva_id);
        
        if (mysqli_stmt_execute($updateStmt)) {
            header("Location: ../pages/mis_reservas_pasajero.php?success=reservation_cancelled");
        } else {
            header("Location: ../pages/mis_reservas_pasajero.php?error=cancel_failed");
        }
    } else {
        header("Location: ../pages/mis_reservas_pasajero.php?error=unauthorized");
    }
    
    mysqli_close($conn);
} else {
    header("Location: ../pages/mis_reservas_pasajero.php");
}
?>