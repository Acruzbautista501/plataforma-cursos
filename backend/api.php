<?php
header('Content-Type: application/json');
require_once 'cursos.php';
require_once 'lecciones.php';
require_once 'evaluaciones.php';
require_once 'resultados.php';
require_once 'auth.php';
require_once 'db.php';

// Aquí puedes agregar las rutas para manejar la API en formato JSON para Vue.js

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'cursos':
                echo json_encode(obtenerCursos());
                break;
            case 'lecciones':
                $curso_id = $_GET['curso_id'] ?? 0;
                echo json_encode(obtenerLeccionesPorCurso($curso_id));
                break;
            case 'evaluaciones':
                $curso_id = $_GET['curso_id'] ?? 0;
                echo json_encode(obtenerEvaluacionesPorCurso($curso_id));
                break;
            case 'resultados':
                $usuario_id = $_GET['usuario_id'] ?? 0;
                echo json_encode(obtenerResultadosPorUsuario($usuario_id));
                break;
            default:
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'crear_curso':
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                if (crearCurso($titulo, $descripcion)) {
                    echo json_encode(['mensaje' => 'Curso creado exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo crear el curso']);
                }
                break;
            case 'crear_leccion':
                $curso_id = $_POST['curso_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $contenido = $_POST['contenido'] ?? '';
                $video_url = $_POST['video_url'] ?? '';
                if (crearLeccion($curso_id, $titulo, $contenido, $video_url)) {
                    echo json_encode(['mensaje' => 'Lección creada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo crear la lección']);
                }
                break;
            case 'crear_evaluacion':
                $curso_id = $_POST['curso_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $preguntas = $_POST['preguntas'] ?? [];
                if (crearEvaluacion($curso_id, $titulo, $descripcion, $preguntas)) {
                    echo json_encode(['mensaje' => 'Evaluación creada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo crear la evaluación']);
                }
                break;
            case 'registro':
                $nombre = $_POST['nombre'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                if (registrarUsuario($nombre, $email, $password)) {
                    echo json_encode(['mensaje' => 'Usuario registrado exitosamente. Por favor, revisa tu correo para confirmar tu cuenta.']);
                } else {
                    echo json_encode(['error' => 'No se pudo registrar el usuario']);
                }
                break;
            case 'login':
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $usuario = iniciarSesion($email, $password);
                if ($usuario === 'Usuario no confirmado') {
                    echo json_encode(['error' => 'Usuario no confirmado. Por favor, confirma tu cuenta.']);
                } elseif ($usuario) {
                    echo json_encode(['mensaje' => 'Inicio de sesión exitoso', 'usuario' => $usuario]);
                } else {
                    echo json_encode(['error' => 'Credenciales incorrectas']);
                }
                break;
            case 'recuperar_contraseña':
                $email = $_POST['email'] ?? '';
                $mensaje = enviarCorreoRecuperacion($email);
                echo json_encode(['mensaje' => $mensaje]);
                break;
            default:
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'editar_curso':
                $curso_id = $_GET['curso_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                if (editarCurso($curso_id, $titulo, $descripcion)) {
                    echo json_encode(['mensaje' => 'Curso actualizado exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo actualizar el curso']);
                }
                break;
            case 'editar_leccion':
                $leccion_id = $_GET['leccion_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $contenido = $_POST['contenido'] ?? '';
                $video_url = $_POST['video_url'] ?? '';
                if (editarLeccion($leccion_id, $titulo, $contenido, $video_url)) {
                    echo json_encode(['mensaje' => 'Lección actualizada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo actualizar la lección']);
                }
                break;
            case 'editar_evaluacion':
                $evaluacion_id = $_GET['evaluacion_id'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $preguntas = $_POST['preguntas'] ?? [];
                if (editarEvaluacion($evaluacion_id, $titulo, $descripcion, $preguntas)) {
                    echo json_encode(['mensaje' => 'Evaluación actualizada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo actualizar la evaluación']);
                }
                break;
            default:
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'eliminar_curso':
                $curso_id = $_GET['curso_id'] ?? 0;
                if (eliminarCurso($curso_id)) {
                    echo json_encode(['mensaje' => 'Curso eliminado exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar el curso']);
                }
                break;
            case 'eliminar_leccion':
                $leccion_id = $_GET['leccion_id'] ?? 0;
                if (eliminarLeccion($leccion_id)) {
                    echo json_encode(['mensaje' => 'Lección eliminada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar la lección']);
                }
                break;
            case 'eliminar_evaluacion':
                $evaluacion_id = $_GET['evaluacion_id'] ?? 0;
                if (eliminarEvaluacion($evaluacion_id)) {
                    echo json_encode(['mensaje' => 'Evaluación eliminada exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar la evaluación']);
                }
                break;
            case 'eliminar_usuario':
                // Verificar si el usuario autenticado es un administrador
                if ($_SESSION['rol'] != 'admin') {
                    echo json_encode(['error' => 'No tienes permiso para realizar esta acción']);
                    break;
                }

                $usuario_id = $_GET['usuario_id'] ?? 0;

                if (eliminarUsuario($usuario_id)) {
                    echo json_encode(['mensaje' => 'Usuario eliminado exitosamente']);
                } else {
                    echo json_encode(['error' => 'No se pudo eliminar el usuario']);
                }
                break;
            default:
                echo json_encode(['error' => 'Acción no válida']);
        }
    }
}
?>



