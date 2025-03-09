<?php
require_once 'db.php';

// Crear un nuevo curso
function crearCurso($nombre, $descripcion) {
    global $pdo;
    $sql = "INSERT INTO cursos (nombre, descripcion) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $descripcion]);
}

// Obtener todos los cursos
function obtenerCursos() {
    global $pdo;
    $sql = "SELECT * FROM cursos";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

// Obtener un curso por ID
function obtenerCursoPorId($id) {
    global $pdo;
    $sql = "SELECT * FROM cursos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Actualizar un curso
function actualizarCurso($id, $nombre, $descripcion) {
    global $pdo;
    $sql = "UPDATE cursos SET nombre = ?, descripcion = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $descripcion, $id]);
}

// Eliminar un curso
function eliminarCurso($id) {
    global $pdo;
    $sql = "DELETE FROM cursos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}
?>
