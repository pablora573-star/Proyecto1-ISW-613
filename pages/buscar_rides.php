<?php
session_start();
include('./common/connection.php');

// Parámetros de búsqueda y ordenamiento
$origen = isset($_GET['origen']) ? mysqli_real_escape_string($conn, $_GET['origen']) : '';
$destino = isset($_GET['destino']) ? mysqli_real_escape_string($conn, $_GET['destino']) : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_asc';

// Construir query
$sql = "SELECT r.*, 
        u.nombre AS chofer_nombre, 
        v.marca, v.modelo, v.anio, v.capacidad_asientos,
        (SELECT COUNT(*) FROM reservations res 
         WHERE res.ride_id = r.id 
         AND res.estado IN ('pendiente', 'aceptada')) AS asientos_reservados
        FROM rides r
        INNER JOIN users u ON r.user_id = u.id
        LEFT JOIN vehicles v ON r.vehicle_id = v.id
        WHERE r.fecha_viaje >= CURDATE()";

// Filtros de búsqueda
if (!empty($origen)) {
    $sql .= " AND r.origen LIKE '%$origen%'";
}
if (!empty($destino)) {
    $sql .= " AND r.destino LIKE '%$destino%'";
}

// Ordenamiento
switch ($orden) {
    case 'fecha_desc':
        $sql .= " ORDER BY r.fecha_viaje DESC, r.hora_viaje DESC";
        break;
    case 'origen_asc':
        $sql .= " ORDER BY r.origen ASC";
        break;
    case 'origen_desc':
        $sql .= " ORDER BY r.origen DESC";
        break;
    case 'destino_asc':
        $sql .= " ORDER BY r.destino ASC";
        break;
    case 'destino_desc':
        $sql .= " ORDER BY r.destino DESC";
        break;
    default: // fecha_asc
        $sql .= " ORDER BY r.fecha_viaje ASC, r.hora_viaje ASC";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Rides - Aventones</title>
    <link rel="stylesheet" href="../css/buscar_rides_style.css">
</head>
<body>
    <nav>
        <h2>Aventones - Buscar Rides</h2>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['rol'] === 'pasajero'): ?>
                    <a href="./pages/dashboard_pasajero.php">Dashboard</a>
                    <a href="./pages/mis_reservas_pasajero.php">Mis Reservas</a>
                <?php else: ?>
                    <a href="./pages/dashboard_chofer.php">Dashboard</a>
                <?php endif; ?>
                <a href="../actions/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="../index.php">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <!-- Formulario de búsqueda -->
        <div class="search-section">
            <h1>Buscar Rides Disponibles</h1>
            <form method="GET" class="search-form">
                <div class="search-inputs">
                    <input type="text" name="origen" placeholder="¿Desde dónde?" 
                           value="<?= htmlspecialchars($origen) ?>">
                    <input type="text" name="destino" placeholder="¿Hacia dónde?" 
                           value="<?= htmlspecialchars($destino) ?>">
                    <button type="submit">Buscar</button>
                </div>
                
                <div class="sort-options">
                    <label>Ordenar por:</label>
                    <select name="orden" onchange="this.form.submit()">
                        <option value="fecha_asc" <?= $orden === 'fecha_asc' ? 'selected' : '' ?>>
                            Fecha (Más próximo)
                        </option>
                        <option value="fecha_desc" <?= $orden === 'fecha_desc' ? 'selected' : '' ?>>
                            Fecha (Más lejano)
                        </option>
                        <option value="origen_asc" <?= $orden === 'origen_asc' ? 'selected' : '' ?>>
                            Origen (A-Z)
                        </option>
                        <option value="origen_desc" <?= $orden === 'origen_desc' ? 'selected' : '' ?>>
                            Origen (Z-A)
                        </option>
                        <option value="destino_asc" <?= $orden === 'destino_asc' ? 'selected' : '' ?>>
                            Destino (A-Z)
                        </option>
                        <option value="destino_desc" <?= $orden === 'destino_desc' ? 'selected' : '' ?>>
                            Destino (Z-A)
                        </option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="results-section">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <h2>Rides Disponibles (<?= mysqli_num_rows($result) ?>)</h2>
                
                <div class="rides-grid">
                    <?php while ($ride = mysqli_fetch_assoc($result)): 
                        $asientos_disponibles = $ride['cantidad_espacios'] - $ride['asientos_reservados'];
                    ?>
                        <div class="ride-card">
                            <div class="ride-header">
                                <h3><?= htmlspecialchars($ride['nombre']) ?></h3>
                                <span class="ride-price">₡<?= number_format($ride['costo_espacio'], 0) ?></span>
                            </div>
                            
                            <div class="ride-route">
                                <div class="location">
                                    <strong>Origen:</strong>
                                    <span><?= htmlspecialchars($ride['origen']) ?></span>
                                </div>
                                <div class="arrow">→</div>
                                <div class="location">
                                    <strong>Destino:</strong>
                                    <span><?= htmlspecialchars($ride['destino']) ?></span>
                                </div>
                            </div>
                            
                            <div class="ride-info">
                                <div class="info-item">
                                    <strong>Fecha:</strong>
                                    <?= date('d/m/Y', strtotime($ride['fecha_viaje'])) ?>
                                </div>
                                <div class="info-item">
                                    <strong>Hora:</strong>
                                    <?= date('H:i', strtotime($ride['hora_viaje'])) ?>
                                </div>
                            </div>
                            
                            <div class="ride-vehicle">
                                <strong>Vehículo:</strong>
                                <?= htmlspecialchars($ride['marca'] . ' ' . $ride['modelo'] . ' (' . $ride['anio'] . ')') ?>
                            </div>
                            
                            <div class="ride-seats">
                                <strong>Asientos:</strong>
                                <span class="<?= $asientos_disponibles > 0 ? 'available' : 'full' ?>">
                                    <?= $asientos_disponibles ?> disponibles de <?= $ride['cantidad_espacios'] ?>
                                </span>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'pasajero' && $asientos_disponibles > 0): ?>
                                <a href="./pages/crear_reserva.php?ride_id=<?= $ride['id'] ?>" class="btn-reserve">
                                    Reservar Ahora
                                </a>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="../index.php" class="btn-login">
                                    Inicia sesión para reservar
                                </a>
                            <?php elseif ($asientos_disponibles <= 0): ?>
                                <button class="btn-full" disabled>Sin Disponibilidad</button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h2>No se encontraron rides</h2>
                    <p>Intenta con otros criterios de búsqueda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>