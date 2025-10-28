 <?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'chofer') {
    header("Location: /index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Vehículo</title>
    <!-- Mismo estilo que register -->
</head>
<body>
    <nav>
        <h2>Aventones</h2>
        <div class="nav-links">
            <a href="/pages/vehiculos.php">← Volver</a>
        </div>
    </nav>
    
   
 <div class="container">
        <h1>Registrar Nuevo Vehículo</h1>
        <form action="/actions/insertar_vehiculo.php" method="post" enctype="multipart/form-data">
            <label>Placa:</label>
            <input type="text" name="placa" required>

            <label>Color:</label>
            <input type="text" name="color" required>
            
            <label>Marca:</label>
            <input type="text" name="marca" required>
            
            <label>Modelo:</label>
            <input type="text" name="modelo" required>
            
            <label>Año:</label>
            <input type="number" name="anio" min="1900" max="2025" required>
            
            <label>Capacidad de Asientos:</label>
            <input type="number" name="capacidad_asientos" min="1" max="8" required>
            
            <label>Fotografía del Vehículo:</label>
            <input type="file" name="foto" accept="image/*" required>
            
            <button type="submit">Registrar Vehículo</button>
        </form>
    </div>
</body>
</html>
 
 