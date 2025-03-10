<?php

class Leccion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear lección
    public function crearLeccion($data) {
        $query = "INSERT INTO lecciones (curso_id, titulo, contenido, tipo_contenido, orden, video, pdf) VALUES (:curso_id, :titulo, :contenido, :tipo_contenido, :orden, :video, :pdf)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':curso_id' => $data['curso_id'],
            ':titulo' => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':tipo_contenido' => $data['tipo_contenido'],
            ':orden' => $data['orden'],
            ':video' => $data['video'],
            ':pdf' => $data['pdf']
        ]);
    }

    // Editar lección
    public function editarLeccion($id, $data) {
        $query = "UPDATE lecciones SET titulo = :titulo, contenido = :contenido, tipo_contenido = :tipo_contenido, orden = :orden, video = :video, pdf = :pdf WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':id' => $id,
            ':titulo' => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':tipo_contenido' => $data['tipo_contenido'],
            ':orden' => $data['orden'],
            ':video' => $data['video'],
            ':pdf' => $data['pdf']
        ]);
    }

    // Eliminar lección
    public function eliminarLeccion($id) {
        $query = "DELETE FROM lecciones WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Obtener lecciones de un curso
    public function obtenerLeccionesPorCurso($curso_id) {
        $query = "SELECT * FROM lecciones WHERE curso_id = :curso_id ORDER BY orden";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
