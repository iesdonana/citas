<?php
session_start();

require './auxiliar.php';

$_SESSION = [];
// Destruir la cookie que almacena el ID de la sesión
$params = session_get_cookie_params();
setcookie(
    session_name(),
    '',
    1,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
);
session_destroy();
volver();