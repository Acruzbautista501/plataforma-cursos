<?php
class Curso {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear curso
    public function crearCurso($data) {
        $query = "INSERT INTO cursos (nombre, descripcion_corta, descripcion_larga, imagen) VALUES (:nombre, :descripcion_corta, :descripcion_larga, :imagen)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion_corta' => $data['descripcion_corta'],
            ':descripcion_larga' => $data['descripcion_larga'],
            ':imagen' => $data['imagen']
        ]);
    }

    // Editar curso
    public function editarCurso($id, $data) {
        $query = "UPDATE cursos SET nombre = :nombre, descripcion_corta = :descripcion_corta, descripcion_larga = :descripcion_larga, imagen = :imagen WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':descripcion_corta' => $data['descripcion_corta'],
            ':descripcion_larga' => $data['descripcion_larga'],
            ':imagen' => $data['imagen']
        ]);
    }

    // Eliminar curso
    public function eliminarCurso($id) {
        $query = "DELETE FROM cursos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Visualizar cursos disponibles
    public function obtenerCursosDisponibles() {
        $query = "SELECT * FROM cursos";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Visualizar cursos inscritos por usuario
    public function obtenerCursosInscritos($usuario_id) {
        $query = "SELECT c.* FROM cursos c JOIN inscripciones i ON c.id = i.curso_id WHERE i.usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Visualizar lecciones de un curso
    public function obtenerLeccionesPorCurso($curso_id) {
        $query = "SELECT * FROM lecciones WHERE curso_id = :curso_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
