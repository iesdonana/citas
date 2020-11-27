<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de citas médicas</title>
</head>
<body>
    <?php
    require './comunes/auxiliar.php';

    comprobar_logueado();

    $pdo = conectar();
    $usuario_id = logueado()['id'];
    $sent = $pdo->prepare('SELECT *
                             FROM citas
                            WHERE usuario_id = :usuario_id
                              AND fecha_hora > CURRENT_TIMESTAMP');
    $sent->execute(['usuario_id' => $usuario_id]);
    $fila = $sent->fetch();
    if ($fila === false) {
        // Ese usuario no tiene citas vigentes
        // Darle la opción de reservar una
    } else {
        // El usuario tiene una cita vigente
        // Darle la opción de anularla
    }
    ?>
</body>
</html>