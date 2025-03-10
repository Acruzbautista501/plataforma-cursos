<?php
header('Content-Type: application/json');
include 'db.php';  // Conexión a la base de datos
include 'auth.php'; // Autenticación
include 'cursos.php'; // CRUD de cursos
include 'lecciones.php'; // CRUD de lecciones
include 'evaluaciones.php'; // CRUD de evaluaciones
include 'resultados.php'; // Registro de intentos y puntajes

// Obtener el método HTTP utilizado
$method = $_SERVER['REQUEST_METHOD'];

// Obtener la URI de la solicitud
$request = $_SERVER['REQUEST_URI'];
$request = explode('/', $request);

// Procesar las solicitudes según la URI y el método
if ($request[1] == 'auth') {
    switch ($method) {
        case 'POST': // Registrar o iniciar sesión
            if (isset($request[2]) && $request[2] == 'login') {
                // Lógica de login
                $data = json_decode(file_get_contents('php://input'), true);
                $email = $data['email'];
                $password = $data['password'];
                echo json_encode(login($email, $password));
            } elseif (isset($request[2]) && $request[2] == 'register') {
                // Lógica de registro
                $data = json_decode(file_get_contents('php://input'), true);
                $nombre = $data['nombre'];
                $email = $data['email'];
                $password = $data['password'];
                echo json_encode(register($nombre, $email, $password));
            }
            break;
        case 'PUT': // Cambiar contraseña
            if (isset($request[2]) && $request[2] == 'reset_password') {
                // Lógica de restablecer la contraseña
                $data = json_decode(file_get_contents('php://input'), true);
                $email = $data['email'];
                echo json_encode(restablecerContrasena($email));
            }
            break;
    }
} elseif ($request[1] == 'cursos') {
    switch ($method) {
        case 'GET':
            // Obtener todos los cursos o los cursos de un usuario específico
            if (isset($request[2]) && is_numeric($request[2])) {
                // Obtener un curso por su ID
                echo json_encode(getCursoById($request[2]));
            } else {
                // Obtener todos los cursos
                echo json_encode(getCursos());
            }
            break;
        case 'POST':
            // Crear un nuevo curso (solo administrador)
            echo json_encode(createCurso($_POST));
            break;
        case 'PUT':
            // Actualizar un curso (solo administrador)
            echo json_encode(updateCurso($request[2], $_POST));
            break;
        case 'DELETE':
            // Eliminar un curso (solo administrador)
            echo json_encode(deleteCurso($request[2]));
            break;
    }
} elseif ($request[1] == 'lecciones') {
    switch ($method) {
        case 'GET':
            // Obtener lecciones de un curso
            if (isset($request[2]) && is_numeric($request[2])) {
                echo json_encode(getLeccionesByCurso($request[2]));
            }
            break;
        case 'POST':
            // Crear una lección (solo administrador)
            echo json_encode(createLeccion($_POST));
            break;
        case 'PUT':
            // Actualizar una lección (solo administrador)
            echo json_encode(updateLeccion($request[2], $_POST));
            break;
        case 'DELETE':
            // Eliminar una lección (solo administrador)
            echo json_encode(deleteLeccion($request[2]));
            break;
    }
} elseif ($request[1] == 'evaluaciones') {
    switch ($method) {
        case 'GET':
            // Obtener todas las evaluaciones de un curso
            if (isset($request[2]) && is_numeric($request[2])) {
                echo json_encode(getEvaluacionesByCurso($request[2]));
            }
            break;
        case 'POST':
            // Crear una evaluación (solo administrador)
            echo json_encode(createEvaluacion($_POST));
            break;
        case 'PUT':
            // Actualizar una evaluación (solo administrador)
            echo json_encode(updateEvaluacion($request[2], $_POST));
            break;
        case 'DELETE':
            // Eliminar una evaluación (solo administrador)
            echo json_encode(deleteEvaluacion($request[2]));
            break;
    }
} elseif ($request[1] == 'resultados') {
    switch ($method) {
        case 'GET':
            // Obtener los resultados de un usuario
            if (isset($request[2]) && is_numeric($request[2])) {
                echo json_encode(obtenerIntentos($request[2]));
            }
            break;
        case 'POST':
            // Registrar un intento de evaluación
            $data = json_decode(file_get_contents('php://input'), true);
            $usuario_id = $data['usuario_id'];
            $evaluacion_id = $data['evaluacion_id'];
            $intento = $data['intento'];
            $puntaje = $data['puntaje'];
            echo json_encode(registrarIntento($usuario_id, $evaluacion_id, $intento, $puntaje));
            break;
    }
} else {
    echo json_encode(['error' => 'Endpoint no encontrado.']);
}
?>
