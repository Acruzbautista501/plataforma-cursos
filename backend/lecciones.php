<?php
include 'db.php';

// Función para crear una nueva lección (solo administrador)
function createLesson($curso_id, $titulo, $descripcion, $contenido_texto, $video_url, $pdf_url) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO lecciones (curso_id, titulo, descripcion, contenido_texto, video_url, pdf_url) 
                           VALUES (:curso_id, :titulo, :descripcion, :contenido_texto, :video_url, :pdf_url)");
    $stmt->execute([
        'curso_id' => $curso_id,
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'contenido_texto' => $contenido_texto,
        'video_url' => $video_url,
        'pdf_url' => $pdf_url
    ]);
    return ['success' => 'Lección creada exitosamente.'];
}

// Función para actualizar una lección (solo administrador)
function updateLesson($leccion_id, $titulo, $descripcion, $contenido_texto, $video_url, $pdf_url) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE lecciones SET titulo = :titulo, descripcion = :descripcion, contenido_texto = :contenido_texto, 
                           video_url = :video_url, pdf_url = :pdf_url WHERE id = :leccion_id");
    $stmt->execute([
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'contenido_texto' => $contenido_texto,
        'video_url' => $video_url,
        'pdf_url' => $pdf_url,
        'leccion_id' => $leccion_id
    ]);
    return ['success' => 'Lección actualizada exitosamente.'];
}

// Función para eliminar una lección (solo administrador)
function deleteLesson($leccion_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM lecciones WHERE id = :leccion_id");
    $stmt->execute(['leccion_id' => $leccion_id]);
    return ['success' => 'Lección eliminada exitosamente.'];
}

// Función para obtener las lecciones de un curso
function getLessonsByCourse($curso_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lecciones WHERE curso_id = :curso_id");
    $stmt->execute(['curso_id' => $curso_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
