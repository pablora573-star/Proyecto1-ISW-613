<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: ../index.php");
    exit();
}

include('../common/connection.php');

$pasajero_id = $_SESSION['user_id'];

// Obtener reservas activas
$sqlActivas = "SELECT res.*, 
               r.nombre AS ride_nombre, r.origen, r.destino, r.fecha_viaje, r.hora_viaje, r.costo_espacio,
               u.nombre AS chofer_nombre, u.apellido AS chofer_apellido, u.telefono AS chofer_telefono,
               v.marca, v.modelo, v.placa
               FROM reservations res
               INNER JOIN rides r ON res.ride_id = r.id
               INNER JOIN users u ON res.chofer_id = u.id
               LEFT JOIN vehicles v ON r.vehicle_id = v.id
               WHERE res.pasajero_id = ?
               AND res.estado IN ('pendiente', 'aceptada')
               AND r.fecha_viaje >= CURDATE()
               ORDER BY r.fecha_viaje ASC, r.hora_viaje ASC";

$stmtActivas = mysqli_prepare($conn, $sqlActivas);
mysqli_stmt_bind_param($stmtActivas, 'i', $pasajero_id);
mysqli_stmt_execute($stmtActivas);
$resultActivas = mysqli_stmt_get_result($stmtActivas);

// Obtener reservas pasadas
$sqlPasadas = "SELECT res.*, 
               r.nombre AS ride_nombre, r.origen, r.destino, r.fecha_viaje, r.hora_viaje, r.costo_espacio
               FROM reservations res
               INNER JOIN rides r ON res.ride_id = r.id
               WHERE res.pasajero_id = ?
               AND (r.fecha_viaje < CURDATE() OR res.estado IN ('rechazada', 'cancelada'))
               ORDER BY r.fecha_viaje DESC, r.hora_viaje DESC
               LIMIT 10";

$stmtPasadas = mysqli_prepare($conn, $sqlPasadas);
mysqli_stmt_bind_param($stmtPasadas, 'i', $pasajero_id);
mysqli_stmt_execute($stmtPasadas);
$resultPasadas = mysqli_stmt_get_result($stmtPasadas);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Aventones</title>
    <link rel="stylesheet" href="../css/mis_reservas.css">
</head>
<body>
    <nav>
        <h2>Aventones - Mis Reservas</h2>
        <div class="nav-links">
            <a href="../pages/dashboard_pasajero.php">Dashboard</a>
            <a href="./buscar_rides.php">Buscar Rides</a>
            <a href="../actions/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <!-- Mensajes -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <?php
                $messages = [
                    'reservation_created' => '✅ Reserva creada exitosamente. El chofer recibirá tu solicitud.',
                    'reservation_cancelled' => '✅ Reserva cancelada exitosamente.'
                ];
                echo $messages[$_GET['success']] ?? '✅ Operación exitosa.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Reservas Activas -->
        <div class="section">
            <h2> Reservas Activas</h2>
            
            <?php if (mysqli_num_rows($resultActivas) > 0): ?>
                <div class="reservations-grid">
                    <?php while ($reserva = mysqli_fetch_assoc($resultActivas)): 
                        $total = $reserva['cantidad_asientos'] * $reserva['costo_espacio'];
                    ?>
                        <div class="reservation-card">
                            <div class="card-header <?= $reserva['estado'] ?>">
                                <h3><?= htmlspecialchars($reserva['ride_nombre']) ?></h3>
                                <span class="badge badge-<?= $reserva['estado'] ?>">
                                    <?= ucfirst($reserva['estado']) ?>
                                </span>
                            </div>
                            
                            <div class="card-body">
                                <div class="info-row">
                                    <strong>Ruta:</strong>
                                    <?= htmlspecialchars($reserva['origen']) ?> → <?= htmlspecialchars($reserva['destino']) ?>
                                </div>
                                
                                <div class="info-row">
                                    <strong>Fecha:</strong>
                                    <?= date('d/m/Y', strtotime($reserva['fecha_viaje'])) ?> a las <?= date('H:i', strtotime($reserva['hora_viaje'])) ?>
                                </div>
                                
                                <div class="info-row">
                                    <strong>Asientos:</strong>
                                    <?= $reserva['cantidad_asientos'] ?>
                                </div>
                                
                                <div class="info-row">
                                    <strong>Total:</strong>
                                    <span class="price">₡<?= number_format($total, 0) ?></span>
                                </div>
                                
                                <?php if ($reserva['estado'] === 'aceptada'): ?>
                                    <div class="driver-info">
                                        <strong>Chofer:</strong>
                                        <?= htmlspecialchars($reserva['chofer_nombre'] . ' ' . $reserva['chofer_apellido']) ?>
                                        <br>
                                        <strong>Teléfono:</strong>
                                        <?= htmlspecialchars($reserva['chofer_telefono']) ?>
                                        <br>
                                        <strong>Vehículo:</strong>
                                        <?= htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo']) ?> - <?= htmlspecialchars($reserva['placa']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer">
                                <a href="../actions/cancelar_reserva.php?id=<?= $reserva['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Estás seguro de cancelar esta reserva?')">
                                    Cancelar Reserva
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    No tienes reservas activas.
                    <a href="./buscar_rides.php">Buscar rides</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Historial -->
        <div class="section">
            <h2>Historial de Reservas</h2>
            
            <?php if (mysqli_num_rows($resultPasadas) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ride</th>
                            <th>Fecha</th>
                            <th>Asientos</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reserva = mysqli_fetch_assoc($resultPasadas)): ?>
                            <tr>
                                <td><?= htmlspecialchars($reserva['ride_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($reserva['fecha_viaje'])) ?></td>
                                <td><?= $reserva['cantidad_asientos'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $reserva['estado'] ?>">
                                        <?= ucfirst($reserva['estado']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No hay historial de reservas.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>