<?php

include('./common/connection.php');

if ($argc < 2) {
    echo "Usage: php notificaciones_pendientes_reservas.php <minutos>\n";
    exit(1);
}

$minutes = (int)$argv[1];

if ($minutes <= 0) {
    echo "Error: El valor de minutos debe ser mayor a 0\n";
    exit(1);
}



// Buscar reservas pendientes sin notificar
$sql = "
    SELECT 
        r.id AS N_reservacion,
        r.fecha_creado,
        r.cantidad_asientos,
        r.notified,
        u_ch.correo AS chofer_email,
        u_ch.nombre AS chofer_nombre,
        u_pa.nombre AS pasajero_nombre,
        rides.nombre AS ride_nombre,
        rides.origen,
        rides.destino,
        rides.fecha_viaje,
        rides.hora_viaje
    FROM reservations r
    INNER JOIN users u_ch ON u_ch.id = r.chofer_id
    INNER JOIN users u_pa ON u_pa.id = r.pasajero_id
    INNER JOIN rides ON rides.id = r.ride_id
    WHERE r.estado = 'pendiente'
      AND r.notified = 0
      AND r.fecha_creado <= (NOW() - INTERVAL ? MINUTE)
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $minutes);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "No hay reservas pendientes sin notificar con más de $minutes minutos.\n";
    exit(0);
}

$emails_sent = 0;
mysqli_begin_transaction($conn);

while ($row = mysqli_fetch_assoc($result)) {
    $reservationId = $row['N_reservacion'];
    $choferEmail = $row['chofer_email'];
    $choferNombre = $row['chofer_nombre'];
    $pasajeroNombre = $row['pasajero_nombre'];
    $rideNombre = $row['ride_nombre'];
    $origen = $row['origen'];
    $destino = $row['destino'];
    $fechaViaje = $row['fecha_viaje'];
    $horaViaje = $row['hora_viaje'];

    $subject = "Reserva pendiente (ID: $reservationId)";
    $body = "Hola $choferNombre,\n\n"
          . "Tienes una solicitud de reserva pendiente de $pasajeroNombre "
          . "para el ride '$rideNombre'.\n\n"
          . "Origen: $origen\nDestino: $destino\n"
          . "Fecha: $fechaViaje\nHora: $horaViaje\n\n"
          . "Por favor revisa tu panel de reservas para aceptar o rechazar la solicitud.\n\n"
          . "Saludos,\nEquipo Aventones";

    $headers = "From: Aventones <jpr12cr@gmail.com>\r\n";
    $headers .= "Reply-To: jpr12cr@gmail.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($choferEmail, $subject, $body, $headers)) {
            $update = mysqli_prepare($conn, "UPDATE reservations SET notified = 1 WHERE id = ?");
            mysqli_stmt_bind_param($update, 'i', $reservationId);
            mysqli_stmt_execute($update);
            $emails_sent++;
    } else {
            echo " Error enviando correo a $choferEmail\n";
        }
}

if ($emails_sent > 0) {
    mysqli_commit($conn);
} else {
    mysqli_rollback($conn);
}

mysqli_close($conn);
echo " Proceso finalizado. Correos enviados: $emails_sent\n";
?>

