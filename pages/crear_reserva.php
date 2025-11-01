<?php
session_start();

// Solo pasajeros pueden reservar
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['ride_id'])) {
    header("Location: ./buscar_rides.php");
    exit();
}

include('../common/connection.php');

$ride_id = (int)$_GET['ride_id'];
$pasajero_id = $_SESSION['user_id'];

// Obtener información del ride
$sql = "SELECT r.*, 
        u.nombre AS chofer_nombre, u.apellido AS chofer_apellido,
        v.marca, v.modelo, v.anio, v.placa,
        (SELECT COUNT(*) FROM reservations res 
         WHERE res.ride_id = r.id 
         AND res.estado IN ('pendiente', 'aceptada')) AS asientos_reservados
        FROM rides r
        INNER JOIN users u ON r.user_id = u.id
        LEFT JOIN vehicles v ON r.vehicle_id = v.id
        WHERE r.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $ride_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_close($conn);
    header("Location: ./buscar_rides.php?error=ride_not_found");
    exit();
}

$ride = mysqli_fetch_assoc($result);
$asientos_disponibles = $ride['cantidad_espacios'] - $ride['asientos_reservados'];

// Verificar si ya tiene una reserva para este ride
$checkReserva = "SELECT id, estado FROM reservations 
                 WHERE ride_id = ? AND pasajero_id = ? 
                 AND estado IN ('pendiente', 'aceptada')";
$stmtCheck = mysqli_prepare($conn, $checkReserva);
mysqli_stmt_bind_param($stmtCheck, 'ii', $ride_id, $pasajero_id);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);
$yaReservado = mysqli_num_rows($resultCheck) > 0;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reserva - Aventones</title>
    <link rel="stylesheet" href="../css/crear_reserva.css">
</head>
<body>
    <nav>
        <h2> Aventones - Crear Reserva</h2>
        <div class="nav-links">
            <a href="./buscar_rides.php">← Volver a Búsqueda</a>
            <a href="../pages/dashboard_pasajero.php">Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="reservation-container">
            <h1>Confirmar Reserva</h1>

            <!-- Información del Ride -->
            <div class="ride-summary">
                <h2> Detalles del Viaje</h2>
                
                <div class="detail-row">
                    <strong>Ride:</strong>
                    <span><?= htmlspecialchars($ride['nombre']) ?></span>
                </div>
                
                <div class="detail-row route">
                    <div>
                        <strong>Origen:</strong>
                        <span><?= htmlspecialchars($ride['origen']) ?></span>
                    </div>
                    <div class="arrow">→</div>
                    <div>
                        <strong>Destino:</strong>
                        <span><?= htmlspecialchars($ride['destino']) ?></span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <strong>Fecha:</strong>
                    <span><?= date('d/m/Y', strtotime($ride['fecha_viaje'])) ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Hora:</strong>
                    <span><?= date('H:i', strtotime($ride['hora_viaje'])) ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Vehículo:</strong>
                    <span><?= htmlspecialchars($ride['marca'] . ' ' . $ride['modelo'] . ' (' . $ride['anio'] . ')') ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Costo por asiento:</strong>
                    <span class="price">₡<?= number_format($ride['costo_espacio'], 0) ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Asientos disponibles:</strong>
                    <span class="<?= $asientos_disponibles > 0 ? 'available' : 'full' ?>">
                        <?= $asientos_disponibles ?> de <?= $ride['cantidad_espacios'] ?>
                    </span>
                </div>
            </div>

            <?php if ($yaReservado): ?>
                <div class="alert warning">
                     Ya tienes una reserva activa para este ride.
                    <a href="../pages/mis_reservas_pasajero.php">Ver mis reservas</a>
                </div>
            <?php elseif ($asientos_disponibles <= 0): ?>
                <div class="alert error">
                     Este ride ya no tiene asientos disponibles.
                </div>
                <a href="./buscar_rides.php" class="btn btn-secondary">Buscar otros rides</a>
            <?php else: ?>
                <!-- Formulario de Reserva -->
                <form action="../actions/reservar.php" method="POST" class="reservation-form">
                    <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>">
                    <input type="hidden" name="chofer_id" value="<?= $ride['user_id'] ?>">
                    
                    <label for="cantidad_asientos">¿Cuántos asientos deseas reservar?</label>
                    <select name="cantidad_asientos" id="cantidad_asientos" required onchange="calcularTotal()">
                        <option value="">Selecciona cantidad</option>
                        <?php for ($i = 1; $i <= min(4, $asientos_disponibles); $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> asiento<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                    
                    <div class="total-container">
                        <strong>Total a pagar:</strong>
                        <span id="total" class="total-price">₡0</span>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Confirmar Reserva</button>
                        <a href="./buscar_rides.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>

                <script>
                    const costoEspacio = <?= $ride['costo_espacio'] ?>;
                    
                    function calcularTotal() {
                        const cantidad = document.getElementById('cantidad_asientos').value;
                        const total = cantidad * costoEspacio;
                        document.getElementById('total').textContent = '₡' + total.toLocaleString('es-CR');
                    }
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>