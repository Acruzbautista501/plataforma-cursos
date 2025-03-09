<?php
require_once 'db.php';

// Crear una nueva lección
function crearLeccion($curso_id, $titulo, $contenido) {
    global $pdo;
    $sql = "INSERT INTO lecciones (curso_id, titulo, contenido) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$curso_id, $titulo, $contenido]);
}

// Obtener todas las lecciones de un curso
function obtenerLeccionesPorCurso($curso_id) {
    global $pdo;
    $sql = "SELECT * FROM lecciones WHERE curso_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$curso_id]);
    return $stmt->fetchAll();
}

// Obtener una lección por ID
function obtenerLeccionPorId($id) {
    global $pdo;
    $sql = "SELECT * FROM lecciones WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Actualizar una lección
function actualizarLeccion($id, $titulo, $contenido) {
    global $pdo;
    $sql = "UPDATE lecciones SET titulo = ?, contenido = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$titulo, $contenido, $id]);
}

// Eliminar una lección
function eliminarLeccion($id) {
    global $pdo;
    $sql = "DELETE FROM lecciones WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}
?>
