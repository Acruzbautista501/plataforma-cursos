<?php
include_once 'config/db.php';

// Incluir controladores
include_once 'controllers/AuthController.php';
include_once 'controllers/CursoController.php';
include_once 'controllers/LeccionController.php';
include_once 'controllers/EvaluacionController.php';
include_once 'controllers/ResultadoController.php';

class Router {
    private $routes = [];

    // Método para añadir rutas
    public function addRoute($method, $route, $callback) {
        $this->routes[] = ['method' => $method, 'route' => $route, 'callback' => $callback];
    }

    // Método para manejar solicitudes POST
    public function post($route, $callback) {
        $this->addRoute('POST', $route, $callback);
    }

    // Método para manejar solicitudes GET
    public function get($route, $callback) {
        $this->addRoute('GET', $route, $callback);
    }

    // Método para manejar solicitudes PUT
    public function put($route, $callback) {
        $this->addRoute('PUT', $route, $callback);
    }

    // Método para manejar solicitudes DELETE
    public function delete($route, $callback) {
        $this->addRoute('DELETE', $route, $callback);
    }

    // Método que maneja la ejecución de rutas
    public function run() {
        $requestUri = str_replace('/plataforma-cursos', '', $_SERVER['REQUEST_URI']); // Eliminar la base de la URL
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Agregar encabezado para asegurar la respuesta en JSON
        header('Content-Type: application/json');

        // Buscar si hay una ruta que coincida con la solicitud
        foreach ($this->routes as $route) {
            $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $route['route']) . '$#';

            if ($requestMethod === $route['method'] && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Eliminar la coincidencia completa
                $data = json_decode(file_get_contents('php://input'), true);

                // Llamar al callback de la ruta correspondiente y obtener la respuesta
                $response = call_user_func($route['callback'], $data, $matches);

                // Si no se devuelve una respuesta, establecer un mensaje de error
                if ($response === null) {
                    $response = ['error' => 'No data returned from the controller'];
                }

                // Devolver la respuesta codificada como JSON
                echo json_encode($response);
                return;
            }
        }

        // Si no se encuentra la ruta, devolver un error 404
        echo json_encode(['error' => 'Route not found']);
    }
}

// Instanciar el enrutador
$router = new Router();

// Instanciar los controladores
$authController = new AuthController($db);
$cursoController = new CursoController($db);
$leccionController = new LeccionController($db);
$evaluacionController = new EvaluacionController($db);
$resultadoController = new ResultadoController($db);

// Rutas de autenticación
$router->post('/api/login', function($data) use ($authController) {
    return $authController->login($data);
});

$router->post('/api/registrar', function($data) use ($authController) {
    return $authController->registrar($data);
});

$router->post('/api/confirmar', function($data) use ($authController) {
    return $authController->confirmarCuenta($data);
});

$router->post('/api/restablecer', function($data) use ($authController) {
    return $authController->restablecerContrasena($data);
});

$router->post('/api/cambiar-contrasena', function($data) use ($authController) {
    return $authController->cambiarContrasena($data);
});

$router->delete('/api/eliminar-usuario', function($data) use ($authController) {
    return $authController->eliminarUsuario($data);
});

$router->put('/api/editar-perfil', function($data) use ($authController) {
    return $authController->editarPerfil($data);
});

$router->delete('/api/eliminar-cuenta', function($data) use ($authController) {
    return $authController->eliminarCuenta($data);
});

// Rutas de usuarios
$router->get('/api/obtener-usuarios', function() use ($authController) {
    return $authController->obtenerUsuarios();
});

$router->get('/api/obtener-usuarios-por-mes', function() use ($authController) {
    return $authController->obtenerUsuariosPorMes();
});

$router->get('/api/usuarios-completaron-curso/{curso_id}', function($curso_id) use ($cursoController) {
    return $cursoController->obtenerUsuariosCompletaron($curso_id);
});

$router->get('/api/cursos-con-mas-usuarios', function() use ($cursoController) {
    return $cursoController->obtenerCursosConMasUsuarios();
});


// Rutas de cursos
$router->post('/api/crear-curso', function($data) use ($cursoController) {
    return $cursoController->crear($data);
});

$router->put('/api/editar-curso/{id}', function($id, $data) use ($cursoController) {
    return $cursoController->editar($id, $data);
});

$router->delete('/api/eliminar-curso/{id}', function($id) use ($cursoController) {
    return $cursoController->eliminar($id);
});

$router->get('/api/cursos-disponibles', function() use ($cursoController) {
    return $cursoController->obtenerDisponibles();
});

$router->get('/api/cursos-inscritos/{usuario_id}', function($usuario_id) use ($cursoController) {
    return $cursoController->obtenerInscritos($usuario_id);
});

// Rutas de lecciones
$router->post('/api/crear-leccion', function($data) use ($leccionController) {
    return $leccionController->crear($data);
});

$router->put('/api/editar-leccion/{id}', function($id, $data) use ($leccionController) {
    return $leccionController->editar($id, $data);
});

$router->delete('/api/eliminar-leccion/{id}', function($id) use ($leccionController) {
    return $leccionController->eliminar($id);
});

$router->get('/api/lecciones-por-curso/{curso_id}', function($curso_id) use ($leccionController) {
    return $leccionController->obtenerPorCurso($curso_id);
});

$router->get('/api/progreso-curso/{usuario_id}/{curso_id}', function($usuario_id, $curso_id) use ($cursoController) {
    return $cursoController->obtenerProgreso($usuario_id, $curso_id);
});

// Rutas de evaluaciones
$router->post('/api/crear-evaluacion', function($data) use ($evaluacionController) {
    return $evaluacionController->crear($data);
});

$router->get('/api/evaluaciones-por-leccion/{leccion_id}', function($leccion_id) use ($evaluacionController) {
    return $evaluacionController->obtenerPorLeccion($leccion_id);
});

$router->post('/api/registrar-intento-evaluacion', function($data) use ($evaluacionController) {
    return $evaluacionController->registrarIntento($data);
});

// Rutas de resultados
$router->post('/api/crear-resultado', function($data) use ($resultadoController) {
    return $resultadoController->crear($data['usuario_id'], $data['curso_id']);
});

$router->get('/api/resultados/{usuario_id}/{curso_id}', function($usuario_id, $curso_id) use ($resultadoController) {
    return $resultadoController->obtener($usuario_id, $curso_id);
});

$router->get('/api/generar-diploma/{usuario_id}/{curso_id}', function($usuario_id, $curso_id) use ($resultadoController) {
    return $resultadoController->generarDiploma($usuario_id, $curso_id);
});

// Ejecutar las rutas
$router->run();
