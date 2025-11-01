<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ../index.php");
    exit();
}

$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);
include($parentDir . '/common/connection.php');

if (isset($_GET['id'])) {
    $ride_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar que el ride pertenezca al usuario
    $checkSql = "SELECT id FROM rides WHERE id = ? AND user_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'ii', $ride_id, $user_id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Verificar si tiene reservas activas (opcional)
        // Si tienes tabla de reservations, puedes validar aquí
        
        // Eliminar ride
        $deleteSql = "DELETE FROM rides WHERE id = ? AND user_id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($deleteStmt, 'ii', $ride_id, $user_id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            mysqli_stmt_close($deleteStmt);
            mysqli_close($conn);
            header("Location: ../pages/dashboard_chofer.php?success=ride_deleted");
            exit();
        } else {
            mysqli_stmt_close($deleteStmt);
            mysqli_close($conn);
            header("Location: ../pages/dashboard_chofer.php?error=delete_failed");
            exit();
        }
    } else {
        mysqli_stmt_close($checkStmt);
        mysqli_close($conn);
        header("Location: ../pages/dashboard_chofer.php?error=unauthorized");
        exit();
    }
    
} else {
    header("Location: ../pages/dashboard_chofer.php");
    exit();
}
?>