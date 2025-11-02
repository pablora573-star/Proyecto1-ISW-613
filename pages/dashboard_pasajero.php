<?php
session_start();

// Verificar que el usuario esté logueado y sea pasajero
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: ../index.php?error=sesion_expirada");
    exit();
}

include('../common/connection.php');

$user_id = $_SESSION['user_id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];
$foto = $_SESSION['foto'];

// Obtener estadísticas del pasajero
$sqlEstadisticas = "SELECT 
    COUNT(*) as total_reservas,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'aceptada' THEN 1 ELSE 0 END) as aceptadas,
    SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas
    FROM reservations 
    WHERE pasajero_id = ?";
$stmtStats = mysqli_prepare($conn, $sqlEstadisticas);
mysqli_stmt_bind_param($stmtStats, 'i', $user_id);
mysqli_stmt_execute($stmtStats);
$resultStats = mysqli_stmt_get_result($stmtStats);
$estadisticas = mysqli_fetch_assoc($resultStats);

// Obtener próximas reservas aceptadas
$sqlProximas = "SELECT r.*, ri.nombre as ride_nombre, ri.origen, ri.destino, 
                ri.fecha_viaje, ri.hora_viaje, u.nombre as chofer_nombre, 
                u.apellido as chofer_apellido
                FROM reservations r
                INNER JOIN rides ri ON r.ride_id = ri.id
                INNER JOIN users u ON ri.user_id = u.id
                WHERE r.pasajero_id = ? 
                AND r.estado = 'aceptada'
                AND ri.fecha_viaje >= CURDATE()
                ORDER BY ri.fecha_viaje ASC, ri.hora_viaje ASC
                LIMIT 3";
$stmtProximas = mysqli_prepare($conn, $sqlProximas);
mysqli_stmt_bind_param($stmtProximas, 'i', $user_id);
mysqli_stmt_execute($stmtProximas);
$resultProximas = mysqli_stmt_get_result($stmtProximas);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasajero - Aventones</title>
    <link rel="stylesheet" href="../css/dashboard_pasajero.css">
</head>
<body>
    <nav>
        <h2> Aventones - Dashboard Pasajero</h2>
        <div class="nav-links">
            <a href="./editar_perfil.php"> Editar Perfil</a>
            <a href="./buscar_rides.php">Buscar Rides</a>
            <a href="./mis_reservas_pasajero.php"> Mis Reservas</a>
            <a href="../actions/logout.php"> Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <?php if (!empty($foto) && file_exists($foto)): ?>
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto de perfil" class="foto-perfil">
            <?php else: ?>
                <img src="../images/default_user.png" alt="Foto de perfil" class="foto-perfil">
            <?php endif; ?>

            <h1> Bienvenido, <?= htmlspecialchars($nombre . ' ' . $apellido) ?>!</h1>
            <p>Gestiona tus reservas y encuentra nuevos rides desde este panel.</p>
            
            
                
            
        </div>

        <?php
        // Mensajes de éxito o error
        if (isset($_GET['success'])) {
            $successMessages = [
                'reserva_created' => '✅ Reserva creada exitosamente.',
                'reserva_cancelled' => '✅ Reserva cancelada exitosamente.',
                'profile_updated' => '✅ Perfil actualizado exitosamente.'
            ];
            $message = $successMessages[$_GET['success']] ?? '';
            if ($message) {
                echo '<div class="alert success">' . $message . '</div>';
            }
        }
        
        if (isset($_GET['error'])) {
            $errorMessages = [
                'reserva_failed' => '❌ Error al crear la reserva.',
                'unauthorized' => '❌ No tienes permiso para realizar esta acción.',
                'profile_update_failed' => '❌ Error al actualizar el perfil.'
            ];
            $message = $errorMessages[$_GET['error']] ?? '';
            if ($message) {
                echo '<div class="alert error">' . $message . '</div>';
            }
        }
        ?>

        <!-- Estadísticas -->
        <div class="stats-section">
            <h2> Mis Estadísticas</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?= $estadisticas['total_reservas'] ?? 0 ?></div>
                    <div class="stat-label">Total Reservas</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?= $estadisticas['pendientes'] ?? 0 ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-card accepted">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= $estadisticas['aceptadas'] ?? 0 ?></div>
                    <div class="stat-label">Aceptadas</div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value"><?= $estadisticas['rechazadas'] ?? 0 ?></div>
                    <div class="stat-label">Rechazadas</div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="actions-grid">
                <a href="./buscar_rides.php" class="action-card">
                    <div class="action-icon">🔍</div>
                    <h3>Buscar Rides</h3>
                    <p>Encuentra rides disponibles para tus destinos</p>
                </a>
                <a href="./mis_reservas_pasajero.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <h3>Mis Reservas</h3>
                    <p>Revisa todas tus reservas activas y pasadas</p>
                </a>
                <a href="./editar_perfil_pasajero.php" class="action-card">
                    <div class="action-icon">✏️</div>
                    <h3>Editar Perfil</h3>
                    <p>Actualiza tu información personal</p>
                </a>
            </div>
        </div>

        <!-- Próximas Reservas -->
        <div class="section">
            <h2>Próximas Reservas Confirmadas</h2>
            
            <?php if (mysqli_num_rows($resultProximas) > 0): ?>
                <div class="reservations-grid">
                    <?php while ($reserva = mysqli_fetch_assoc($resultProximas)): ?>
                        <div class="reservation-card">
                            <div class="reservation-header">
                                <h3><?= htmlspecialchars($reserva['ride_nombre']) ?></h3>
                                <span class="badge accepted">Confirmada</span>
                            </div>
                            
                            <div class="reservation-route">
                                <div class="location">
                                    <strong>Origen:</strong>
                                    <span><?= htmlspecialchars($reserva['origen']) ?></span>
                                </div>
                                <div class="arrow">→</div>
                                <div class="location">
                                    <strong>Destino:</strong>
                                    <span><?= htmlspecialchars($reserva['destino']) ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-info">
                                <div class="info-item">
                                    <strong>Fecha:</strong>
                                    <?= date('d/m/Y', strtotime($reserva['fecha_viaje'])) ?>
                                </div>
                                <div class="info-item">
                                    <strong>Hora:</strong>
                                    <?= date('H:i', strtotime($reserva['hora_viaje'])) ?>
                                </div>
                            </div>
                            
                            <div class="reservation-driver">
                                <strong>Chofer:</strong>
                                <?= htmlspecialchars($reserva['chofer_nombre'] . ' ' . $reserva['chofer_apellido']) ?>
                            </div>
                            
                            <div class="reservation-seats">
                                <strong>Asientos reservados:</strong>
                                <?= $reserva['cantidad_asientos'] ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="view-all">
                    <a href="./mis_reservas_pasajero.php" class="btn">Ver todas mis reservas →</a>
                </div>
            <?php else: ?>
                <div class="no-data">
                    No tienes reservas confirmadas próximamente.
                    <a href="./buscar_rides.php" class="btn">Buscar rides disponibles</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>