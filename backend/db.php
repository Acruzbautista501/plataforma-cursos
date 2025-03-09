<?php

$host = 'localhost';
$db = 'plataforma_cursos';
$user = 'root'; // Cambia esto si tu usuario de MySQL es diferente
$password = ''; // Cambia esto si tienes una contraseña configurada

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

?>
