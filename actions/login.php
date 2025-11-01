<?php
$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);

// ✅ Incluir conexión correctamente
include($parentDir . '/common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);
    $password = $_POST['password'];
    
    // Buscar usuario por cédula 
    $sql = "SELECT id, cedula, nombre, apellido, foto_url, contra, rol, estado 
            FROM users 
            WHERE cedula = ? 
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $cedula);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Verificar la contraseña encriptada
        $passwordMatch = password_verify($password, $row['contra']);
        
        if ($passwordMatch) {
            // Verificar el estado de la cuenta
            if ($row['estado'] === 'activa') {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['cedula'] = $row['cedula'];
                $_SESSION['nombre'] = $row['nombre'];
                $_SESSION['apellido'] = $row['apellido'];
                $_SESSION['rol'] = $row['rol'];
                $_SESSION['foto'] = $row['foto_url'];
                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                // ✅ Redirigir según el rol
                if ($row['rol'] === 'chofer') {
                    header("Location: ../pages/dashboard_chofer.php");
                } elseif ($row['rol'] === 'pasajero') {
                    header("Location: ../pages/dashboard_pasajero.php");
                } elseif ($row['rol'] === 'administrador') {
                    header("Location: ../pages/dashboard_admin.php");
                } else {
                    header("Location: ../index.php?error=estado_invalido");
                }
                exit();
                
            } elseif ($row['estado'] === 'pendiente') {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ../index.php?error=cuenta_pendiente");
                exit();
                
            } elseif ($row['estado'] === 'inactiva') {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ../index.php?error=cuenta_inactiva");
                exit();
                
            } else {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: ../index.php?error=estado_invalido");
                exit();
            }
            
        } else {
            // Contraseña incorrecta
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: ../index.php?error=credenciales_invalidas");
            exit();
        }
        
    } else {
        // Usuario no encontrado
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../index.php?error=credenciales_invalidas");
        exit();
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>
