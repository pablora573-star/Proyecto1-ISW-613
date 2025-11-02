<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=sesion_expirada");
    exit();
}

include('../common/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id = $_SESSION['user_id'];
    $rol = $_SESSION['rol']; // Para redireccionar correctamente
    
    // Obtener datos del formulario
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $nacimiento = mysqli_real_escape_string($conn, $_POST['nacimiento']);
    $correo = mysqli_real_escape_string($conn, $_POST['correo']);
    $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
    
    // Variables para contraseña
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['password']) ? $_POST['password'] : '';
    $newPassword2 = isset($_POST['password2']) ? $_POST['password2'] : '';
    
    // Variable para foto
    $fotoRuta = null;
    $actualizarFoto = false;
    
    // Verificar si se quiere cambiar la contraseña
    $cambiarPassword = !empty($currentPassword) || !empty($newPassword) || !empty($newPassword2);
    
    if ($cambiarPassword) {
        // Validar que todos los campos de contraseña estén llenos
        if (empty($currentPassword) || empty($newPassword) || empty($newPassword2)) {
            header("Location: ../pages/editar_perfil.php?error=password_fields_incomplete");
            exit();
        }
        
        // Validar que las nuevas contraseñas coincidan
        if ($newPassword !== $newPassword2) {
            header("Location: ../pages/editar_perfil.php?error=password_mismatch");
            exit();
        }
        
        // Verificar la contraseña actual
        $sqlCheckPassword = "SELECT contra FROM users WHERE id = ?";
        $stmtCheck = mysqli_prepare($conn, $sqlCheckPassword);
        mysqli_stmt_bind_param($stmtCheck, 'i', $user_id);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);
        $userData = mysqli_fetch_assoc($resultCheck);
        
        if (!password_verify($currentPassword, $userData['contra'])) {
            mysqli_stmt_close($stmtCheck);
            mysqli_close($conn);
            header("Location: ../pages/editar_perfil.php?error=current_password_wrong");
            exit();
        }
        mysqli_stmt_close($stmtCheck);
    }
    
    // Procesar la imagen si se subió una nueva
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        $foto = $_FILES['foto'];
        $nombreOriginal = $foto['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $extensionesPermitidas)) {
            mysqli_close($conn);
            header("Location: ../pages/editar_perfil.php?error=invalid_image");
            exit();
        }
        
        // Validar tamaño (5MB máximo)
        if ($foto['size'] > 5 * 1024 * 1024) {
            mysqli_close($conn);
            header("Location: ../pages/editar_perfil.php?error=image_too_large");
            exit();
        }
        
        // Obtener foto antigua para eliminarla
        $sqlOldPhoto = "SELECT foto_url FROM users WHERE id = ?";
        $stmtOldPhoto = mysqli_prepare($conn, $sqlOldPhoto);
        mysqli_stmt_bind_param($stmtOldPhoto, 'i', $user_id);
        mysqli_stmt_execute($stmtOldPhoto);
        $resultOldPhoto = mysqli_stmt_get_result($stmtOldPhoto);
        $oldPhotoData = mysqli_fetch_assoc($resultOldPhoto);
        $oldPhotoPath = $oldPhotoData['foto_url'];
        mysqli_stmt_close($stmtOldPhoto);
        
        // Generar nombre único
        $nombreArchivo = uniqid('user_') . '.' . $extension;
        $carpetaDestino = '../uploads/fotos/';
        $rutaDestino = $carpetaDestino . $nombreArchivo;
        
        // Crear carpeta si no existe
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }
        
        // Mover archivo
        if (move_uploaded_file($foto['tmp_name'], $rutaDestino)) {
            $fotoRuta = '../uploads/fotos/' . $nombreArchivo;
            $actualizarFoto = true;
            
            // Eliminar foto antigua si existe y no es la default
            if (!empty($oldPhotoPath) && file_exists($oldPhotoPath) && 
                strpos($oldPhotoPath, 'default_user.png') === false) {
                unlink($oldPhotoPath);
            }
        } else {
            mysqli_close($conn);
            header("Location: ../pages/editar_perfil.php?error=upload_failed");
            exit();
        }
    }
    
    // Construir query de actualización
    if ($cambiarPassword && $actualizarFoto) {
        // Actualizar todo: datos personales, foto y contraseña
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nombre = ?, apellido = ?, fecha_nacimiento = ?, 
                correo = ?, telefono = ?, foto_url = ?, contra = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssi', 
            $name, $lastName, $nacimiento, $correo, $telefono, $fotoRuta, $passwordHash, $user_id);
            
    } elseif ($cambiarPassword) {
        // Actualizar datos personales y contraseña (sin foto)
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nombre = ?, apellido = ?, fecha_nacimiento = ?, 
                correo = ?, telefono = ?, contra = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssi', 
            $name, $lastName, $nacimiento, $correo, $telefono, $passwordHash, $user_id);
            
    } elseif ($actualizarFoto) {
        // Actualizar datos personales y foto (sin contraseña)
        $sql = "UPDATE users SET nombre = ?, apellido = ?, fecha_nacimiento = ?, 
                correo = ?, telefono = ?, foto_url = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssi', 
            $name, $lastName, $nacimiento, $correo, $telefono, $fotoRuta, $user_id);
            
    } else {
        // Solo actualizar datos personales
        $sql = "UPDATE users SET nombre = ?, apellido = ?, fecha_nacimiento = ?, 
                correo = ?, telefono = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssi', 
            $name, $lastName, $nacimiento, $correo, $telefono, $user_id);
    }
    
    // Ejecutar actualización
    if (mysqli_stmt_execute($stmt)) {
        // Actualizar variables de sesión
        $_SESSION['nombre'] = $name;
        $_SESSION['apellido'] = $lastName;
        if ($actualizarFoto) {
            $_SESSION['foto'] = $fotoRuta;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        // Redireccionar según el rol
        if ($rol === 'pasajero') {
            header("Location: ../pages/dashboard_pasajero.php?success=profile_updated");
        } elseif ($rol === 'chofer') {
            header("Location: ../pages/dashboard_chofer.php?success=profile_updated");
        } else {
            header("Location: ../pages/dashboard_admin.php?success=profile_updated");
        }
        exit();
        
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../pages/editar_perfil.php?error=update_failed");
        exit();
    }
}
?>