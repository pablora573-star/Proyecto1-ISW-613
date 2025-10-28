<?php
session_start();

// Verificar que el usuario esté logueado y sea chofer
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: ../index.php?error=sesion_expirada");
    exit();
}

//$currentDir = dirname(__FILE__);
//$parentDir = dirname($currentDir);
include('./common/connection.php');

$user_id = $_SESSION['user_id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];

// Obtener rides del chofer
$sqlRides = "SELECT r.*, v.placa, v.marca, v.modelo 
             FROM rides r 
             LEFT JOIN vehicles v ON r.vehicle_id = v.id 
             WHERE r.user_id = ? 
             ORDER BY r.fecha_viaje DESC, r.hora_viaje DESC";
$stmtRides = mysqli_prepare($conn, $sqlRides);
mysqli_stmt_bind_param($stmtRides, 'i', $user_id);
mysqli_stmt_execute($stmtRides);
$resultRides = mysqli_stmt_get_result($stmtRides);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Chofer - Aventones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        nav {
            background-color: #333;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        nav h2 {
            color: white;
            font-size: 20px;
        }
        nav .nav-links {
            display: flex;
            gap: 15px;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav a:hover {
            background-color: #555;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .welcome {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .welcome h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #FF9800;
            padding-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FF9800;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #F57C00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #FF9800;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .actions a {
            color: #2196F3;
            text-decoration: none;
            margin-right: 10px;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .actions a.delete {
            color: #f44336;
        }
        .no-data {
            text-align: center;
            color: #666;
            padding: 30px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <nav>
        <h2>Aventones - Dashboard Chofer</h2>
        <div class="nav-links">
            <a href="./vehiculos.php">Mis Vehículos</a>
            <a href="./actions/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h1>Bienvenido, <?= htmlspecialchars($nombre . ' ' . $apellido) ?>!</h1>
            <p>Gestiona tus rides y vehículos desde este panel.</p>
        </div>

        <div class="section">
            <h2>Mis Rides</h2>
            <?php
            // mensajes de éxito o error
            if (isset($_GET['success'])) {
                $successMessages = [
                    'ride_created' => ' Ride creado exitosamente.',
                    'ride_updated' => ' Ride actualizado exitosamente.',
                    'ride_deleted' => ' Ride eliminado exitosamente.'
                ];
                $message = $successMessages[$_GET['success']] ?? '';
                if ($message) {
                    echo '<div style="background-color:#d4edda;border-left:4px solid #28a745;color:#155724;padding:12px;margin-bottom:20px;border-radius:4px;">' . $message . '</div>';
                }
            }
            
            if (isset($_GET['error'])) {
                $errorMessages = [
                    'delete_failed' => ' Error al eliminar el ride.',
                    'unauthorized' => ' No tienes permiso para realizar esta acción.',
                    'ride_not_found' => ' Ride no encontrado.'
                ];
                $message = $errorMessages[$_GET['error']] ?? '';
                if ($message) {
                    echo '<div style="background-color:#f8d7da;border-left:4px solid #dc3545;color:#721c24;padding:12px;margin-bottom:20px;border-radius:4px;">' . $message . '</div>';
                }
            }
            ?>

            <a href="./crear_ride.php" class="btn">Crear Nuevo Ride</a>
            
            <?php 
              if (mysqli_num_rows($resultRides) > 0): ?>
                <table>
                    <tr>
                        <th>Nombre</th>
                        <th>Origen → Destino</th>
                        <th>Fecha y Hora</th>
                        <th>Vehículo</th>
                        <th>Costo</th>
                        <th>Espacios</th>
                        <th>Acciones</th>
                    </tr>
                    <?php while ($ride = mysqli_fetch_assoc($resultRides)): ?>
                        <tr>
                            <td><?= htmlspecialchars($ride['nombre']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($ride['origen']) ?></strong> →
                                <?= htmlspecialchars($ride['destino']) ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($ride['fecha_viaje'])) ?><br>
                                <?= date('H:i', strtotime($ride['hora_viaje'])) ?>
                            </td>
                            <td>
                                <?php if ($ride['placa']): ?>
                                    <?= htmlspecialchars($ride['marca'] . ' ' . $ride['modelo']) ?><br>
                                    <small><?= htmlspecialchars($ride['placa']) ?></small>
                                <?php else: ?>
                                    <em>Sin vehículo</em>
                                <?php endif; ?>
                            </td>
                            <td>₡<?= number_format($ride['costo_espacio'], 0) ?></td>
                            <td><?= $ride['cantidad_espacios'] ?></td>
                            <td class="actions">
                                <a href="./editar_ride.php?id=<?= $ride['id'] ?>">Editar</a>
                                <a href="./actions/eliminar_ride.php?id=<?= $ride['id'] ?>" 
                                   class="delete" 
                                   onclick="return confirm('¿Estás seguro de eliminar este ride?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <div class="no-data">
                    No tienes rides registrados aún. ¡Crea tu primer ride!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>