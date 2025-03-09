<?php
require_once 'db.php';

// Crear una nueva evaluación
function crearEvaluacion($curso_id, $titulo, $descripcion, $preguntas) {
    global $pdo;
    
    // Insertar la evaluación
    $sql = "INSERT INTO evaluaciones (curso_id, titulo, descripcion) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$curso_id, $titulo, $descripcion]);
    
    // Obtener el ID de la evaluación recién insertada
    $evaluacion_id = $pdo->lastInsertId();
    
    // Insertar las preguntas asociadas a la evaluación
    foreach ($preguntas as $pregunta) {
        $sql = "INSERT INTO preguntas (evaluacion_id, pregunta) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evaluacion_id, $pregunta]);
    }

    return $evaluacion_id;
}


// Obtener todas las evaluaciones de un curso
function obtenerEvaluacionesPorCurso($curso_id) {
    global $pdo;
    $sql = "SELECT * FROM evaluaciones WHERE curso_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

// Obtener una evaluación por ID
function obtenerEvaluacionPorId($id) {
    global $pdo;
    $sql = "SELECT * FROM evaluaciones WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Actualizar una evaluación
function actualizarEvaluacion($id, $titulo, $descripcion) {
    global $pdo;
    $sql = "UPDATE evaluaciones SET titulo = ?, descripcion = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titulo, $descripcion, $id]);
}

// Eliminar una evaluación
function eliminarEvaluacion($id) {
    global $pdo;
    $sql = "DELETE FROM evaluaciones WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}
?>
