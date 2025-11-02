<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=sesion_expirada");
    exit();
}

include('../common/connection.php');

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'];

// Obtener información actual del usuario
$sql = "SELECT nombre, apellido, cedula, fecha_nacimiento, correo, telefono, foto_url 
        FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

mysqli_close($conn);

// Determinar el dashboard de retorno según el rol
$dashboardUrl = match($rol) {
    'pasajero' => './dashboard_pasajero.php',
    'chofer' => './dashboard_chofer.php',
    'administrador' => './dashboard_admin.php',
    default => '../index.php'
};

// Estilos de badge según rol
$badgeClass = match($rol) {
    'pasajero' => 'badge-pasajero',
    'chofer' => 'badge-chofer',
    'administrador' => 'badge-admin',
    default => 'badge'
};

//stylos segun rol
$stylesrol =  match($rol) {
    'pasajero' => 'pasajero_style',
    'chofer' => 'chofer_style',
    'administrador' => 'admin_style',
    };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Aventones</title>
    <link rel="stylesheet" href="../css/<?= $stylesrol ?>.css">
</head>
<style> 

.foto-perfil {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #000;
  margin-bottom: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s;

  &:hover {
    transform: scale(1.05);
  }
}</style>
<body>
    <nav>
        <h2>Aventones - Editar Perfil</h2>
        <div class="nav-links">
            <a href="<?= $dashboardUrl ?>">← Volver al Dashboard</a>
            <a href="../actions/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h1>Editar mi Perfil</h1>
        <p class="subtitle">Actualiza tu información personal como <span class="badge <?= $badgeClass ?>"><?= strtoupper($rol) ?></span></p>

        <?php
        // Mensajes de error
        if (isset($_GET['error'])) {
            $errorMessages = [
                'password_mismatch' => '❌ Las contraseñas no coinciden.',
                'invalid_image' => '❌ Formato de imagen no válido. Use JPG, PNG o GIF.',
                'image_too_large' => '❌ La imagen es muy grande. Máximo 5MB.',
                'upload_failed' => '❌ Error al subir la imagen.',
                'update_failed' => '❌ Error al actualizar el perfil.',
                'current_password_wrong' => '❌ La contraseña actual es incorrecta.',
                'password_fields_incomplete' => '❌ Debes completar todos los campos de contraseña.'
            ];
            $message = $errorMessages[$_GET['error']] ?? '❌ Error desconocido.';
            echo '<div class="alert error">' . $message . '</div>';
        }
        ?>
        
        <form action="../actions/actualizar_perfil.php" method="post" enctype="multipart/form-data" class="edit-form">
            
            <div class="form-section">
                <h2> Información Personal</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" 
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName">Apellido:</label>
                        <input type="text" id="lastName" name="lastName" 
                               value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                    </div>
                </div>

                <div class="form-group readonly">
                    <label for="cedula">Número de cédula:</label>
                    <input type="text" id="cedula" name="cedula" 
                           value="<?= htmlspecialchars($usuario['cedula']) ?>" 
                           readonly disabled>
                    <p class="field-info">La cédula no puede ser modificada</p>
                </div>

                <div class="form-group">
                    <label for="nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="nacimiento" name="nacimiento" 
                           value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" 
                           value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Número de teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h2>Fotografía Personal</h2>
                
                <div class="foto-preview">
                    <?php if (!empty($usuario['foto_url']) && file_exists($usuario['foto_url'])): ?>
                        <img src="<?= htmlspecialchars($usuario['foto_url']) ?>" 
                             alt="Foto actual" class="foto-perfil" id="preview-img">
                        <p class="photo-label" id="photo-label">Foto actual</p>
                    <?php else: ?>
                        <img src="../images/default_user.png" 
                             alt="Sin foto" class="foto-perfil" id="preview-img">
                        <p class="photo-label" id="photo-label">Sin foto</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="foto">Cambiar fotografía (opcional):</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                    <p class="file-info">Formatos aceptados: JPG, PNG, GIF (máx. 5MB)</p>
                    <p class="file-info">Si no seleccionas una imagen, se mantendrá la actual</p>
                </div>
            </div>

            <div class="form-section">
                <h2>Cambiar Contraseña (Opcional)</h2>
                <p class="section-info">Deja estos campos vacíos si no deseas cambiar tu contraseña</p>
                
                <div class="form-group">
                    <label for="current_password">Contraseña actual:</label>
                    <input type="password" id="current_password" name="current_password">
                    <p class="field-info">Requerida solo si deseas cambiar la contraseña</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Nueva contraseña:</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="form-group">
                        <label for="password2">Repetir nueva contraseña:</label>
                        <input type="password" id="password2" name="password2">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="<?= $dashboardUrl ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // Validación de formulario
        document.querySelector('.edit-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const password2 = document.getElementById('password2').value;
            const currentPassword = document.getElementById('current_password').value;

            // Si se intenta cambiar contraseña
            if (password || password2 || currentPassword) {
                // Verificar que la contraseña actual esté presente
                if (!currentPassword) {
                    e.preventDefault();
                    alert('⚠️ Debes ingresar tu contraseña actual para cambiarla');
                    return;
                }

                // Verificar que las nuevas contraseñas coincidan
                if (password !== password2) {
                    e.preventDefault();
                    alert('⚠️ Las nuevas contraseñas no coinciden');
                    return;
                }

                // Verificar longitud mínima
                if (password.length < 6) {
                    e.preventDefault();
                    alert('⚠️ La nueva contraseña debe tener al menos 6 caracteres');
                    return;
                }
            }
        });

        // Preview de imagen
        document.getElementById('foto').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('photo-label').textContent = 'Nueva foto (sin guardar)';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>