<?php

include_once 'models/Curso.php';

class CursoController {
    private $curso;

    public function __construct($db) {
        $this->curso = new Curso($db);
    }

    // Crear curso
    public function crear($data) {
        if (empty($data['nombre']) || empty($data['descripcion_corta']) || empty($data['descripcion_larga'])) {
            return ['error' => 'Todos los campos son obligatorios.'];
        }
        return $this->curso->crearCurso($data) ? ['success' => 'Curso creado correctamente.'] : ['error' => 'Error al crear el curso.'];
    }

    // Editar curso
    public function editar($id, $data) {
        if (empty($id) || empty($data['nombre'])) {
            return ['error' => 'ID y nombre son obligatorios.'];
        }
        return $this->curso->editarCurso($id, $data) ? ['success' => 'Curso actualizado correctamente.'] : ['error' => 'Error al actualizar el curso.'];
    }

    // Eliminar curso
    public function eliminar($id) {
        if (empty($id)) {
            return ['error' => 'El ID del curso es obligatorio.'];
        }
        return $this->curso->eliminarCurso($id) ? ['success' => 'Curso eliminado correctamente.'] : ['error' => 'Error al eliminar el curso.'];
    }

    // Obtener cursos disponibles
    public function obtenerDisponibles() {
        return $this->curso->obtenerCursosDisponibles();
    }

    // Obtener cursos inscritos por usuario
    public function obtenerInscritos($usuario_id) {
        if (empty($usuario_id)) {
            return ['error' => 'El ID del usuario es obligatorio.'];
        }
        return $this->curso->obtenerCursosInscritos($usuario_id);
    }

    // Obtener lecciones de un curso
    public function obtenerLecciones($curso_id) {
        if (empty($curso_id)) {
            return ['error' => 'El ID del curso es obligatorio.'];
        }
        return $this->curso->obtenerLeccionesPorCurso($curso_id);
    }
}
?>
