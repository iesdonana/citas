<?php session_start() ?>
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

    $dia = recoger_get('dia');

    $dia_post = recoger_post('dia');
    $hora_post = recoger_post('hora');
    $hora = $hora_post;

    if (isset($dia_post, $hora_post)) {
        $match = [];
        if (preg_match('/^(\d\d):(\d\d):(\d\d)$/', $hora_post, $match) !== 1) {
            volver();
            return;
        }
        if ($match[1] < '00' || $match[1] > '23'
            && $match[2] < '00' || $match[2] > '59'
            && $match[3] < '00' || $match[3] > '59') {
                volver();
                return;
            }
        // Convertir en UTC la hora antes de insertar
        $sent = $pdo->prepare('INSERT INTO citas (fecha_hora, usuario_id)
                               VALUES (:fecha_hora, :usuario_id)');
        $sent->execute([
            'fecha_hora' => "$dia_post $hora_post",
            'usuario_id' => $usuario_id,
        ]);
        volver();
        return;
    }
    $sent = $pdo->prepare('SELECT *
                            FROM citas
                            WHERE usuario_id = :usuario_id
                            AND fecha_hora > CURRENT_TIMESTAMP');
    $sent->execute(['usuario_id' => $usuario_id]);
    $fila = $sent->fetch();
    if ($fila === false) {
        // Ese usuario no tiene citas vigentes
        // Darle la opción de reservar una
        $sent = $pdo->query('SELECT fecha_hora::date AS fecha
                            FROM citas
                            WHERE fecha_hora::date != CURRENT_DATE
                        GROUP BY 1
                            HAVING COUNT(*) >= 16
                        ORDER BY 1');
        $fechasOcupadas = $sent->fetchAll(PDO::FETCH_COLUMN, 0);
        $fechas = [];
        $intervalo = new DateInterval('P1D');
        $fechaActual = (new DateTime())->add($intervalo);
        $i = 0;
        while ($i < 30) {
            $dow = $fechaActual->format('w');
            $fecha = $fechaActual->format('Y-m-d');
            if (in_array($dow, ['1', '3', '5'])
                && !in_array($fecha, $fechasOcupadas)) {
                $fechas[] = $fecha;
            }
            $i++;
            $fechaActual->add($intervalo);
        } ?>
        <form action="" method="get">
            <label for="dia">Seleccione el día de la cita:</label>
            <select name="dia" id="dia">
                <?php foreach ($fechas as $f): ?>
                    <option value="<?= $f ?>" <?= selected($dia, $f) ?> >
                        <?= $f ?>
                    </option>
                <?php endforeach ?>
            </select>
            <button type="submit">Seleccionar</button>
        </form>
        <?php
        if ($dia !== null) {
            $match = [];
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dia, $match) !== 1) {
                volver();
                return;
            }
            if (!checkdate($match[2], $match[3], $match[1])) {
                volver();
                return;
            }
            $sent = $pdo->prepare('SELECT fecha_hora::time(0) AS hora
                                    FROM citas
                                    WHERE fecha_hora::date = :dia
                                ORDER BY 1');
            $sent->execute(['dia' => $dia]);
            $horasOcupadas = $sent->fetchAll(PDO::FETCH_COLUMN, 0);
            $madrid = new DateTimeZone('Europe/Madrid');
            foreach ($horasOcupadas as $k => $h) {
                $hh = DateTime::createFromFormat('H:i:s', $h);
                $hh->setTimeZone($madrid);
                $horasOcupadas[$k] = $hh->format('H:i:s');
            }
            $horas = [];
            $intervalo = new DateInterval('PT15M');
            $utc = new DateTimeZone('UTC');
            $horaActual = (new DateTime())->setTimezone($madrid)->setTime(16, 0, 0);
            $horaFin = clone $horaActual;
            $horaFin->setTime(20, 0, 0);
            while ($horaActual < $horaFin) {
                if (!in_array($horaActual->format('H:i:s'), $horasOcupadas)) {
                    $horas[] = $horaActual->format('H:i:s');
                }
                $horaActual->add($intervalo);
            } ?>
            <form action="" method="post">
                <input type="hidden" name="dia" value="<?= $dia ?>">
                <label for="hora">Seleccione la hora:</label>
                <select name="hora" id ="hora">
                    <?php foreach ($horas as $h): ?>
                        <option value="<?= $h ?>" <?= selected($h, $hora) ?> >
                            <?= $h ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <button type="submit">Reservar</button>
            </form>
            <?php
        }
    } else {
        echo "Sí tiene citas vigentes";
        // El usuario tiene una cita vigente
        // Darle la opción de anularla
    } ?>
</body>
</html>