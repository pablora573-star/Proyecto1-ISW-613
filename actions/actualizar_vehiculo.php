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
    $vehicle_id = (int)$_POST['vehicle_id'];
    $placa = mysqli_real_escape_string($conn, $_POST['placa']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $marca = mysqli_real_escape_string($conn, $_POST['marca']);
    $modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
    $anio = (int)$_POST['anio'];
    $capacidad = (int)$_POST['capacidad_asientos'];
    
    // Verificar que el vehículo pertenezca al usuario
    $checkSql = "SELECT foto_url FROM vehiculos WHERE id = ? AND user_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 'ii', $vehicle_id, $user_id);
    mysqli_stmt_execute($checkStmt);
    $resultCheck = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($resultCheck) === 0) {
        mysqli_stmt_close($checkStmt);
        mysqli_close($conn);
        header("Location: /pages/vehiculos.php?error=unauthorized");
        exit();
    }
    
    $vehicleData = mysqli_fetch_assoc($resultCheck);
    $currentFoto = $vehicleData['foto_url'];
    mysqli_stmt_close($checkStmt);
    
    // Procesar nueva foto (si se subió)
    $fotoRuta = $currentFoto; // Mantener foto actual por defecto
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto'];
        $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Validar tamaño (5MB máximo)
            if ($foto['size'] <= 5 * 1024 * 1024) {
                $nombreArchivo = uniqid('vehicle_') . '.' . $extension;
                $carpetaDestino = '../uploads/vehicles/';
                
                if (!file_exists($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }
                
                if (move_uploaded_file($foto['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                    // Eliminar foto anterior si existe
                    if ($currentFoto && file_exists('../' . $currentFoto)) {
                        unlink('../' . $currentFoto);
                    }
                    $fotoRuta = 'uploads/vehicles/' . $nombreArchivo;
                }
            }
        }
    }
    
    // Actualizar vehículo
    $sql = "UPDATE vehiculos 
            SET placa = ?, 
                color = ?, 
                marca = ?, 
                modelo = ?, 
                anio = ?, 
                capacidad_asientos = ?, 
                foto_url = ?
            WHERE id = ? AND user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssisisii', 
        $placa, 
        $color, 
        $marca, 
        $modelo, 
        $anio, 
        $capacidad, 
        $fotoRuta,
        $vehicle_id,
        $user_id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: /pages/vehiculos.php?success=updated");
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: /pages/editar_vehiculo.php?id=$vehicle_id&error=update_failed");
        exit();
    }
    
} else {
    header("Location: /pages/vehiculos.php");
    exit();
}
?>