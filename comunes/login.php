<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <?php
    require './auxiliar.php';

    head();

    $login = recoger_post('login');
    $password = recoger_post('password');

    $error = [
        'login' => [],
        'password' => [],
    ];
    $error_vacio = $error;

    if (isset($login, $password)) {
        $pdo = conectar();
        $sent = $pdo->prepare('SELECT *
                                 FROM usuarios
                                WHERE login = :login');
        $sent->execute(['login' => $login]);
        $fila = $sent->fetch();
        if ($fila === false) {
            $error['login'][] = 'Las credenciales son incorrectas';
        } else {
            if (!password_verify($password, $fila['password'])) {
                $error['login'][] = 'Las credenciales son incorrectas';
            }
        }
        if ($error === $error_vacio) {
            // Loguear
            $_SESSION['login'] = [];
            $_SESSION['login']['id'] = $fila['id'];
            $_SESSION['login']['nombre'] = $fila['login'];
            volver();
        } else {
            mostrar_errores($error);
        }
    }
    ?>
    <form action="" method="post">
        <p>
            <label for="login">Login:</label>
            <input type="text" name="login" id="login"
                   value="<?= hh($login) ?>">
        </p>
        <p>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password">
        </p>
        <button type="submit">Login</button>
    </form>
</body>
</html>