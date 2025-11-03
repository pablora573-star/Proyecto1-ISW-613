<?php
session_start();

// Solo admin puede cambiar estados
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

include('../common/connection.php');

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $user_id = (int)$_GET['id'];
    $nuevo_estado = $_GET['estado'];
    $admin_id = $_SESSION['user_id'];
    

    if (!in_array($nuevo_estado, ['activa', 'inactiva', 'pendiente'])) {
        header("Location: ../pages/dashboard_admin.php?error=invalid_state");
        exit();
    }
    
    // No puede modificarse a sí mismo
    if ($user_id === $admin_id) {
        header("Location: ../pages/dashboard_admin.php?error=cannot_modify_self");
        exit();
    }
    
    // Verificar que el exista
    $checkSql = "SELECT id, nombre FROM users WHERE id = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'i', $user_id);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($checkStmt);
        mysqli_close($conn);
        header("Location: ../pages/dashboard_admin.php?error=user_not_found");
        exit();
    }
    
    mysqli_stmt_close($checkStmt);
    
    // Actualizar estado
    $updateSql = "UPDATE users SET estado = ? WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, 'si', $nuevo_estado, $user_id);
    
    if (mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        mysqli_close($conn);
        
        $success_message = $nuevo_estado === 'activa' ? 'user_activated' : 'user_deactivated';
        header("Location: ../pages/dashboard_admin.php?success=$success_message");
        exit();
    } else {
        mysqli_stmt_close($updateStmt);
        mysqli_close($conn);
        header("Location: ../pages/dashboard_admin.php?error=update_failed");
        exit();
    }
    
} else {
    header("Location: ../pages/dashboard_admin.php");
    exit();
}
?>