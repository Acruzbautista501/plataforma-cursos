<?php
// index.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Content-Type: application/json');

header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/db.php'; // Asegúrate de que la ruta es correcta
require_once 'routes/api.php'; // Asegúrate de que api.php está en la carpeta correcta

?>


