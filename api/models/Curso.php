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

    // Obtener el progreso de un usuario en un curso
    public function obtenerProgresoCurso($usuario_id, $curso_id) {
        // Obtener todas las lecciones del curso
        $query = "SELECT l.id FROM lecciones l WHERE l.curso_id = :curso_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->execute();
        $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Contar cuántas lecciones ha completado el usuario
        $lecciones_completadas = 0;
        foreach ($lecciones as $leccion) {
            $query = "SELECT COUNT(*) FROM progreso_lecciones WHERE usuario_id = :usuario_id AND leccion_id = :leccion_id AND completado = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':leccion_id', $leccion['id']);
            $stmt->execute();
            $completado = $stmt->fetchColumn();
            if ($completado > 0) {
                $lecciones_completadas++;
            }
        }

        // Calcular el porcentaje de progreso
        $total_lecciones = count($lecciones);
        $progreso = $total_lecciones > 0 ? ($lecciones_completadas / $total_lecciones) * 100 : 0;

        return $progreso;
    }

    // Obtener usuarios que han terminado un curso
    public function obtenerUsuariosCompletaronCurso($curso_id) {
        // Obtener todas las lecciones del curso
        $query = "SELECT l.id FROM lecciones l WHERE l.curso_id = :curso_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->execute();
        $lecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener los usuarios que han completado todas las lecciones
        $usuarios_completados = [];
        foreach ($lecciones as $leccion) {
            $query = "SELECT DISTINCT pl.usuario_id 
                    FROM progreso_lecciones pl 
                    WHERE pl.leccion_id = :leccion_id AND pl.completado = TRUE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':leccion_id', $leccion['id']);
            $stmt->execute();
            $usuarios_completados = array_merge($usuarios_completados, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        // Filtrar usuarios que han completado todas las lecciones
        $usuarios_completados_unicos = array_map('json_encode', $usuarios_completados);
        $usuarios_completados_unicos = array_unique($usuarios_completados_unicos);
        $usuarios_completados_unicos = array_map('json_decode', $usuarios_completados_unicos);

        return $usuarios_completados_unicos;
    }

    // Obtener los cursos con más usuarios
    public function obtenerCursosConMasUsuarios() {
        // Obtener los cursos y contar la cantidad de usuarios inscritos en cada uno
        $query = "SELECT c.id, c.nombre, COUNT(i.usuario_id) AS cantidad_usuarios 
                FROM cursos c 
                LEFT JOIN inscripciones i ON c.id = i.curso_id
                GROUP BY c.id
                ORDER BY cantidad_usuarios DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
