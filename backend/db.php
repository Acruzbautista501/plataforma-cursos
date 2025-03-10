<?php
$host = 'localhost';
$dbname = 'plataforma_cursos';
$username = 'root';  // O tu nombre de usuario de la base de datos
$password = '';      // O tu contraseña de la base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
