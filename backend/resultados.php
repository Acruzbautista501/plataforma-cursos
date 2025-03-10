<?php
include 'db.php';

// Función para registrar un intento
function registrarIntento($usuario_id, $evaluacion_id, $intento, $puntaje) {
    global $pdo;
    
    // Verificar si el usuario ha agotado los 3 intentos
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

    // Registrar el intento en la base de datos
    $stmt = $pdo->prepare("INSERT INTO intentos (usuario_id, evaluacion_id, intento, puntaje, fecha_intento) 
                           VALUES (:usuario_id, :evaluacion_id, :intento, :puntaje, NOW())");
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'evaluacion_id' => $evaluacion_id,
        'intento' => $intento,
        'puntaje' => $puntaje
    ]);

    // Verificar si el puntaje es suficiente para aprobar (60% mínimo)
    $evaluacion = getEvaluacionPorId($evaluacion_id);
    $puntaje_minimo = 60;
    if ($puntaje >= $puntaje_minimo) {
        return ['success' => 'Evaluación aprobada.'];
    } else {
        return ['error' => 'Evaluación no aprobada. El puntaje mínimo es ' . $puntaje_minimo . '%.'];
    }
}

// Función para obtener los intentos y puntajes de un usuario
function obtenerIntentos($usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT i.id, i.evaluacion_id, e.titulo, i.intento, i.puntaje, i.fecha_intento
                           FROM intentos i
                           JOIN evaluaciones e ON i.evaluacion_id = e.id
                           WHERE i.usuario_id = :usuario_id
                           ORDER BY i.fecha_intento DESC");
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener el puntaje total de un usuario en todas las evaluaciones
function obtenerPuntajeTotal($usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(puntaje) AS puntaje_total FROM intentos WHERE usuario_id = :usuario_id");
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['puntaje_total'];
}

// Función para obtener los intentos y puntajes de una evaluación específica
function obtenerResultadosEvaluacion($evaluacion_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT i.usuario_id, u.nombre, i.intento, i.puntaje, i.fecha_intento
                           FROM intentos i
                           JOIN usuarios u ON i.usuario_id = u.id
                           WHERE i.evaluacion_id = :evaluacion_id
                           ORDER BY i.fecha_intento DESC");
    $stmt->execute(['evaluacion_id' => $evaluacion_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener los detalles de una evaluación por su ID
function getEvaluacionPorId($evaluacion_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM evaluaciones WHERE id = :evaluacion_id");
    $stmt->execute(['evaluacion_id' => $evaluacion_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
