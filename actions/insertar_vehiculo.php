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
    $placa = mysqli_real_escape_string($conn, $_POST['placa']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $marca = mysqli_real_escape_string($conn, $_POST['marca']);
    $modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
    $anio = (int)$_POST['anio'];
    $capacidad = (int)$_POST['capacidad_asientos'];
    $user_id = $_SESSION['user_id'];

    // Procesar foto
    $fotoRuta = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto'];
        $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $nombreArchivo = uniqid('vehicle_') . '.' . $extension;
            $carpetaDestino = '../uploads/vehicles/';
            
            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }
            
            if (move_uploaded_file($foto['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                $fotoRuta = 'uploads/vehicles/' . $nombreArchivo;
            }
        }
    }
    
    $sql = "INSERT INTO vehiculos (placa, color, marca, modelo, anio, capacidad_asientos, foto_url,user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isssisss', $placa, $color, $marca, $modelo, $anio, $capacidad, $fotoRuta, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ./pages/vehiculos.php?success=created");
    } else {
        header("Location: ./pages/vehiculos.php?error=create_failed");
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>