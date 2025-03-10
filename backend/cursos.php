<?php
include 'db.php';

// Función para crear un nuevo curso (solo administrador)
function createCourse($titulo, $descripcion, $imagen, $admin_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO cursos (titulo, descripcion, imagen, admin_id) VALUES (:titulo, :descripcion, :imagen, :admin_id)");
    $stmt->execute([
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'imagen' => $imagen,
        'admin_id' => $admin_id
    ]);
    return ['success' => 'Curso creado exitosamente.'];
}

// Función para obtener todos los cursos (para mostrar a los usuarios antes de registrarse o después de registrar)
function getCourses($usuario_id = null) {
    global $pdo;
    // Si el usuario está registrado, mostrar los cursos a los que está inscrito
    if ($usuario_id) {
        $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id IN (SELECT curso_id FROM inscripciones WHERE usuario_id = :usuario_id)");
        $stmt->execute(['usuario_id' => $usuario_id]);
    } else {
        // Si no está registrado, mostrar todos los cursos disponibles para inscripción
        $stmt = $pdo->query("SELECT * FROM cursos");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener un curso específico con las lecciones
function getCourseById($course_id) {
    global $pdo;

    // Obtener los detalles del curso
    $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = :course_id");
    $stmt->execute(['course_id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        // Obtener las lecciones asociadas al curso
        $stmt_lecciones = $pdo->prepare("SELECT * FROM lecciones WHERE curso_id = :course_id");
        $stmt_lecciones->execute(['course_id' => $course_id]);
        $course['lecciones'] = $stmt_lecciones->fetchAll(PDO::FETCH_ASSOC);
    }

    return $course;
}

// Obtener el curso con las lecciones si se ha pasado el ID en la URL
if (isset($_GET['id'])) {
    $curso_id = $_GET['id']; // ID del curso que el usuario ha seleccionado
    $curso = getCourseById($curso_id);

    if ($curso) {
        // Mostrar el curso y sus lecciones
        echo '<h1>' . htmlspecialchars($curso['titulo']) . '</h1>';
        echo '<p>' . htmlspecialchars($curso['descripcion']) . '</p>';

        echo '<h2>Lecciones</h2>';
        foreach ($curso['lecciones'] as $leccion) {
            echo '<h3>' . htmlspecialchars($leccion['titulo']) . '</h3>';
            echo '<p>' . htmlspecialchars($leccion['descripcion']) . '</p>';

            // Si hay un video, mostrar el enlace o el iframe del video
            if ($leccion['video_url']) {
                echo '<a href="' . htmlspecialchars($leccion['video_url']) . '" target="_blank">Ver video</a>';
            }

            // Si hay un archivo PDF, mostrar el enlace para descargar
            if ($leccion['pdf_url']) {
                echo '<a href="' . htmlspecialchars($leccion['pdf_url']) . '" target="_blank">Descargar PDF</a>';
            }

            echo '<hr>';
        }
    } else {
        echo 'Curso no encontrado.';
    }
} else {
    echo 'ID de curso no proporcionado.';
}

// Función para actualizar un curso (solo administrador)
function updateCourse($course_id, $titulo, $descripcion, $imagen) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE cursos SET titulo = :titulo, descripcion = :descripcion, imagen = :imagen WHERE id = :course_id");
    $stmt->execute([
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'imagen' => $imagen,
        'course_id' => $course_id
    ]);
    return ['success' => 'Curso actualizado exitosamente.'];
}

// Función para eliminar un curso (solo administrador)
function deleteCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = :course_id");
    $stmt->execute(['course_id' => $course_id]);
    return ['success' => 'Curso eliminado exitosamente.'];
}

// Función para inscribir a un usuario en un curso
function enrollInCourse($usuario_id, $course_id) {
    global $pdo;
    // Verificar si el usuario ya está inscrito
    $stmt = $pdo->prepare("SELECT * FROM inscripciones WHERE usuario_id = :usuario_id AND curso_id = :course_id");
    $stmt->execute(['usuario_id' => $usuario_id, 'course_id' => $course_id]);
    $existingEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingEnrollment) {
        // Inscribir al usuario en el curso
        $stmt = $pdo->prepare("INSERT INTO inscripciones (usuario_id, curso_id) VALUES (:usuario_id, :course_id)");
        $stmt->execute(['usuario_id' => $usuario_id, 'course_id' => $course_id]);
        return ['success' => 'Inscripción exitosa al curso.'];
    }

    return ['error' => 'Ya estás inscrito en este curso.'];
}

// Función para obtener las lecciones de un curso
function getLessons($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lecciones WHERE curso_id = :course_id");
    $stmt->execute(['course_id' => $course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para redirigir a la página con lecciones de un curso
function redirectToCourseLessons($course_id) {
    $course = getCourseById($course_id);
    if ($course) {
        // Redirigir al usuario a la página donde se listan las lecciones
        header("Location: /curso.php?id=$course_id");
        exit();
    } else {
        return ['error' => 'Curso no encontrado.'];
    }
}
?>

