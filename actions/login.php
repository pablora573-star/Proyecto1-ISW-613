<?php
$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);

include($parentDir . '/common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);
    $password = $_POST['password'];
    
    // Buscar usuario por cédula con prepared statement (SEGURO)
    $sql = "SELECT id, cedula, nombre, apellido, contra, rol, estado FROM users WHERE cedula = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $cedula);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Verificar la contraseña PRIMERO 
       
        $passwordMatch = (md5($password) === $row['contra']);
        
        
        if ($passwordMatch) {
            // verificar el estado de la cuenta
            
            if ($row['estado'] === 'activo') {
                //  CUENTA ACTIVA - Permitir login
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['cedula'] = $row['cedula'];
                $_SESSION['nombre'] = $row['nombre'];
                $_SESSION['apellido'] = $row['apellido'];
                $_SESSION['rol'] = $row['rol'];
                
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                
                if ($row['rol'] === 'chofer') {
                    header("Location: /pages/dashboard_chofer.php");
                } else {
                    header("Location: /pages/dashboard_pasajero.php");
                }
                exit();
                
            } elseif ($row['estado'] === 'pendiente') {
                // CUENTA PENDIENTE - No permitir login
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ./index.php?error=cuenta_pendiente");
                exit();
                
            } elseif ($row['estado'] === 'inactivo') {
                // CUENTA INACTIVA - No permitir login
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ./index.php?error=cuenta_inactiva");
                exit();
                
            } else {
                // Estado desconocido
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ./index.php?error=estado_invalido");
                exit();
            }
            
        } else {
            // Contraseña incorrecta
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: ./index.php?error=credenciales_invalidas");
            exit();
        }
        
    } else {
        //  Usuario no encontrado
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ./index.php?error=credenciales_invalidas");
        exit();
    }

} else {
    header("Location: ./index.php");
    exit();
}
?>