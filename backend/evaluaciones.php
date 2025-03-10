<?php
include 'db.php';

// Función para crear una nueva evaluación (solo administrador)
function createEvaluation($curso_id, $titulo, $descripcion, $preguntas) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO evaluaciones (curso_id, titulo, descripcion) 
                           VALUES (:curso_id, :titulo, :descripcion)");
    $stmt->execute([
        'curso_id' => $curso_id,
        'titulo' => $titulo,
        'descripcion' => $descripcion
    ]);
    $evaluacion_id = $pdo->lastInsertId();

    // Insertar preguntas asociadas a la evaluación
    foreach ($preguntas as $pregunta) {
        $stmt = $pdo->prepare("INSERT INTO preguntas (evaluacion_id, pregunta, respuestas_correctas) 
                               VALUES (:evaluacion_id, :pregunta, :respuestas_correctas)");
        $stmt->execute([
            'evaluacion_id' => $evaluacion_id,
            'pregunta' => $pregunta['pregunta'],
            'respuestas_correctas' => $pregunta['respuestas_correctas']
        ]);
    }

    return ['success' => 'Evaluación creada exitosamente.'];
}

// Función para actualizar una evaluación (solo administrador)
function updateEvaluation($evaluacion_id, $titulo, $descripcion, $preguntas) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE evaluaciones SET titulo = :titulo, descripcion = :descripcion WHERE id = :evaluacion_id");
    $stmt->execute([
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'evaluacion_id' => $evaluacion_id
    ]);

    // Actualizar preguntas asociadas a la evaluación
    foreach ($preguntas as $pregunta) {
        $stmt = $pdo->prepare("UPDATE preguntas SET pregunta = :pregunta, respuestas_correctas = :respuestas_correctas 
                               WHERE id = :pregunta_id");
        $stmt->execute([
            'pregunta' => $pregunta['pregunta'],
            'respuestas_correctas' => $pregunta['respuestas_correctas'],
            'pregunta_id' => $pregunta['pregunta_id']
        ]);
    }

    return ['success' => 'Evaluación actualizada exitosamente.'];
}

// Función para eliminar una evaluación (solo administrador)
function deleteEvaluation($evaluacion_id) {
    global $pdo;
    // Eliminar las preguntas asociadas a la evaluación
    $stmt = $pdo->prepare("DELETE FROM preguntas WHERE evaluacion_id = :evaluacion_id");
    $stmt->execute(['evaluacion_id' => $evaluacion_id]);

    // Eliminar la evaluación
    $stmt = $pdo->prepare("DELETE FROM evaluaciones WHERE id = :evaluacion_id");
    $stmt->execute(['evaluacion_id' => $evaluacion_id]);

    return ['success' => 'Evaluación eliminada exitosamente.'];
}

// Función para obtener todas las evaluaciones de un curso
function getEvaluationsByCourse($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM evaluaciones WHERE curso_id = :curso_id");
    $stmt->execute(['curso_id' => $curso_id]);
    $evaluaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener las preguntas de cada evaluación
    foreach ($evaluaciones as &$evaluacion) {
        $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE evaluacion_id = :evaluacion_id");
        $stmt->execute(['evaluacion_id' => $evaluacion['id']]);
        $evaluacion['preguntas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $evaluaciones;
}

// Función para registrar el intento de un usuario en una evaluación
function registerAttempt($usuario_id, $evaluacion_id, $intento, $puntaje) {
    global $pdo;
    // Verificar si el usuario ya tiene 3 intentos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM intentos WHERE usuario_id = :usuario_id AND evaluacion_id = :evaluacion_id");
    $stmt->execute(['usuario_id' => $usuario_id, 'evaluacion_id' => $evaluacion_id]);
    $intentos = $stmt->fetchColumn();

    if ($intentos >= 3) {
        // Si el usuario ya agotó los 3 intentos, verificar el tiempo de espera
        $stmt = $pdo->prepare("SELECT MAX(fecha_intento) FROM intentos WHERE usuario_id = :usuario_id AND evaluacion_id = :evaluacion_id");
        $stmt->execute(['usuario_id' => $usuario_id, 'evaluacion_id' => $evaluacion_id]);
        $ultimo_intento = $stmt->fetchColumn();

        $tiempo_restante = strtotime($ultimo_intento) + 1800 - time(); // 1800 segundos = 30 minutos
        if ($tiempo_restante > 0) {
            return ['error' => 'Has agotado tus 3 intentos. Debes esperar ' . ceil($tiempo_restante / 60) . ' minutos antes de volver a intentarlo.'];
        }
    }

    // Registrar el intento
    $stmt = $pdo->prepare("INSERT INTO intentos (usuario_id, evaluacion_id, intento, puntaje, fecha_intento) 
                           VALUES (:usuario_id, :evaluacion_id, :intento, :puntaje, NOW())");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'evaluacion_id' => $evaluacion_id,
        'intento' => $intento,
        'puntaje' => $puntaje
    ]);

    // Verificar si el puntaje es suficiente para aprobar (60% mínimo)
    $evaluacion = getEvaluationById($evaluacion_id);
    $puntaje_minimo = 60;
    if ($puntaje >= $evaluacion['puntaje_minimo']) {
        return ['success' => 'Evaluación aprobada.'];
    } else {
        return ['error' => 'Evaluación no aprobada. El puntaje mínimo es ' . $puntaje_minimo . '%.'];
    }
}

// Función para obtener los detalles de una evaluación (incluyendo preguntas)
function getEvaluationById($evaluacion_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM evaluaciones WHERE id = :evaluacion_id");
    $stmt->execute(['evaluacion_id' => $evaluacion_id]);
    $evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener las preguntas de la evaluación
    if ($evaluacion) {
        $stmt = $pdo->prepare("SELECT * FROM preguntas WHERE evaluacion_id = :evaluacion_id");
        $stmt->execute(['evaluacion_id' => $evaluacion_id]);
        $evaluacion['preguntas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $evaluacion;
}
?>