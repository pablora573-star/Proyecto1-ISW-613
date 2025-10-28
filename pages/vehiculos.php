<?php
session_start();

// Verificar que el usuario esté logueado y sea chofer
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: /index.php?error=sesion_expirada");
    exit();
}

$currentDir = dirname(__FILE__);
$parentDir = dirname($currentDir);
include($parentDir . '/common/connection.php');

$user_id = $_SESSION['user_id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];

// Obtener vehículos del chofer
$sqlVehicles = "SELECT * FROM vehicles WHERE user_id = ? ORDER BY fecha_creado DESC";
$stmtVehicles = mysqli_prepare($conn, $sqlVehicles);
mysqli_stmt_bind_param($stmtVehicles, 'i', $user_id);
mysqli_stmt_execute($stmtVehicles);
$resultVehicles = mysqli_stmt_get_result($stmtVehicles);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Vehículos - Aventones</title>
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
        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
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
        .vehicle-photo {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .success-message {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav>
        <h2> Aventones - Mis Vehículos</h2>
        <div class="nav-links">
            <a href="/pages/dashboard_chofer.php">Dashboard</a>
            <a href="/actions/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="section">
            <h2> Mis Vehículos</h2>
            
            <?php
            // Mostrar mensajes de éxito o error
            if (isset($_GET['success'])) {
                $successMessages = [
                    'created' => ' Vehículo creado exitosamente.',
                    'updated' => ' Vehículo actualizado exitosamente.',
                    'deleted' => ' Vehículo eliminado exitosamente.'
                ];
                $message = $successMessages[$_GET['success']] ?? '';
                if ($message) {
                    echo "<div class='success-message'>$message</div>";
                }
            }
            
            if (isset($_GET['error'])) {
                $errorMessages = [
                    'delete_failed' => ' Error al eliminar el vehículo.',
                    'vehicle_in_use' => ' No se puede eliminar. El vehículo está asignado a rides activos.'
                ];
                $message = $errorMessages[$_GET['error']] ?? '';
                if ($message) {
                    echo "<div class='error-message'>$message</div>";
                }
            }
            ?>
            
            <a href="/pages/crear_vehiculo.php" class="btn"> Registrar Nuevo Vehículo</a>
            
            <?php if (mysqli_num_rows($resultVehicles) > 0): ?>
                <table>
                    <tr>
                        <th>Foto</th>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Color</th>
                        <th>Capacidad</th>
                        <th>Acciones</th>
                    </tr>
                    <?php while ($vehicle = mysqli_fetch_assoc($resultVehicles)): ?>
                        <tr>
                            <td>
                                <?php if ($vehicle['foto_url']): ?>
                                    <img src="/<?= htmlspecialchars($vehicle['foto_url']) ?>" 
                                         alt="Foto del vehículo" 
                                         class="vehicle-photo">
                                <?php else: ?>
                                    <div style="width:80px;height:60px;background:#ddd;display:flex;align-items:center;justify-content:center;border-radius:4px;">
                                        Sin foto
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($vehicle['placa']) ?></strong></td>
                            <td><?= htmlspecialchars($vehicle['marca']) ?></td>
                            <td><?= htmlspecialchars($vehicle['modelo']) ?></td>
                            <td><?= htmlspecialchars($vehicle['anio']) ?></td>
                            <td><?= htmlspecialchars($vehicle['color']) ?></td>
                            <td><?= $vehicle['capacidad_asientos'] ?> asientos</td>
                            <td class="actions">
                                <a href="/pages/editar_vehiculo.php?id=<?= $vehicle['id'] ?>">Editar</a>
                                <a href="/actions/eliminar_vehiculo.php?id=<?= $vehicle['id'] ?>" 
                                   class="delete" 
                                   onclick="return confirm('¿Estás seguro de eliminar este vehículo?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <div class="no-data">
                    No tienes vehículos registrados aún. ¡Registra tu primer vehículo!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>