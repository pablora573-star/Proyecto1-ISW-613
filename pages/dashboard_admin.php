<?php
session_start();


// Verificar que el usuario sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../index.php?error=sesion_expirada");
    exit();
}

include('../common/connection.php');

$user_id = $_SESSION['user_id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];
$foto = $_SESSION['foto'];

// Filtros
$filtroRol = isset($_GET['rol']) ? $_GET['rol'] : '';
$filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : '';

// query con filtros
$sql = "SELECT id, nombre, apellido, cedula, fecha_nacimiento, correo, foto_url, rol, estado 
        FROM users 
        WHERE 1=1";

if (!empty($filtroRol)) {
    $sql .= " AND rol = '" . mysqli_real_escape_string($conn, $filtroRol) . "'";
}

if (!empty($filtroEstado)) {
    $sql .= " AND estado = '" . mysqli_real_escape_string($conn, $filtroEstado) . "'";
}

$sql .= " ORDER BY fecha_creado DESC";

$result = mysqli_query($conn, $sql);

mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Aventones</title>
    <link rel="stylesheet" href="../css/dashboard_admin.css">
</head>
<body>
    <nav>
        <h2>Aventones - Dashboard Admin</h2>
        <div class="nav-links">
            <a href="./registration_admin.php">Nuevo Usuario</a>
            <a href="./editar_perfil.php">Editar Perfil</a>
            <a href="../actions/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">

        <div class="welcome">
           
           <?php 

            if (!empty($foto) && file_exists($foto)): ?>
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto de perfil" class="foto-perfil">
            <?php else: ?>
                <img src="../images/default_user.png" alt="Foto de perfil" class="foto-perfil">
            <?php endif; ?>

            <h1>Bienvenido, <?= htmlspecialchars($nombre . ' ' . $apellido) ?>!</h1>
            <p>Gestiona todos los usuarios de la plataforma desde este panel.</p>
        </div>

 
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <?php
                $successMessages = [
                    'user_activated' => '✅ Usuario activado exitosamente.',
                    'user_deactivated' => '✅ Usuario desactivado exitosamente.',
                    'user_deleted' => '✅ Usuario eliminado exitosamente.'
                ];
                echo $successMessages[$_GET['success']] ?? '✅ Operación exitosa.';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <?php
                $errorMessages = [
                    'update_failed' => '❌ Error al actualizar el usuario.',
                    'unauthorized' => '⚠️ No tienes permiso para realizar esta acción.',
                    'user_not_found' => '❌ Usuario no encontrado.'
                ];
                echo $errorMessages[$_GET['error']] ?? '❌ Error desconocido.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Filtrar por Rol:</label>
                    <select name="rol" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="administrador" <?= $filtroRol === 'administrador' ? 'selected' : '' ?>>Admin</option>
                        <option value="chofer" <?= $filtroRol === 'chofer' ? 'selected' : '' ?>>Chofer</option>
                        <option value="pasajero" <?= $filtroRol === 'pasajero' ? 'selected' : '' ?>>Pasajero</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Filtrar por Estado:</label>
                    <select name="estado" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="activa" <?= $filtroEstado === 'activa' ? 'selected' : '' ?>>Activo</option>
                        <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="inactiva" <?= $filtroEstado === 'inactiva' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <?php if (!empty($filtroRol) || !empty($filtroEstado)): ?>
                    <a href="./dashboard_admin.php" class="btn-clear">Limpiar Filtros</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="section">
            <h2>Gestión de Usuarios (<?= mysqli_num_rows($result) ?>)</h2>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cédula</th>
                                <th>Fecha Nac.</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="td-foto">
                                        <?php
                                        $userFoto = $user['foto_url'] ?? '';
                                        $userFotoPath = $userFoto;

                                            if (!empty($user['foto_url']) && file_exists($user['foto_url'])): ?>
                                                <img src="<?= htmlspecialchars($user['foto_url']) ?>" 
                                                    alt="Foto actual" class="foto-perfil" id="preview-img">
                                                <p class="photo-label" id="photo-label">Foto actual</p>
                                        <?php else: ?>
                                            <img src="../images/default_user.png" 
                                                alt="Sin foto" class="foto-perfil" id="preview-img">
                                            <p class="photo-label" id="photo-label">Sin foto</p>
                                        <?php endif; ?>

                                    </td>
                                    <td><?= htmlspecialchars($user['nombre']) ?></td>
                                    <td><?= htmlspecialchars($user['apellido']) ?></td>
                                    <td><?= htmlspecialchars($user['cedula']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($user['fecha_nacimiento'])) ?></td>
                                    <td><?= htmlspecialchars($user['correo']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= htmlspecialchars($user['rol']) ?>">
                                            <?= ucfirst($user['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-estado-<?= htmlspecialchars($user['estado']) ?>">
                                            <?= ucfirst($user['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <?php if ($user['id'] !== $user_id): ?>
                                            <?php if ($user['estado'] === 'activa'): ?>
                                                <a href="../actions/actualizar_estado.php?id=<?= $user['id'] ?>&estado=inactiva" 
                                                   class="btn-action btn-deactivate"
                                                   onclick="return confirm('¿Desactivar usuario <?= htmlspecialchars($user['nombre']) ?>?')">
                                                    Desactivar
                                                </a>
                                            <?php else: ?>
                                                <a href="../actions/actualizar_estado.php?id=<?= $user['id'] ?>&estado=activa" 
                                                   class="btn-action btn-activate"
                                                   onclick="return confirm('¿Activar usuario <?= htmlspecialchars($user['nombre']) ?>?')">
                                                    Activar
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Tú</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    No se encontraron usuarios con los filtros aplicados.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
