
<?php

include_once 'models/Leccion.php';

class LeccionController {

    private $leccion;

    public function __construct($db) {
        $this->leccion = new Leccion($db);
    }

    // Crear lección
    public function crear($data) {
        if (empty($data['curso_id']) || empty($data['titulo']) || empty($data['contenido']) || empty($data['tipo_contenido'])) {
            return ['error' => 'Todos los campos son obligatorios.'];
        }
        return $this->leccion->crearLeccion($data) ? ['success' => 'Lección creada correctamente.'] : ['error' => 'Error al crear la lección.'];
    }

    // Editar lección
    public function editar($id, $data) {
        if (empty($id) || empty($data['titulo'])) {
            return ['error' => 'ID y título son obligatorios.'];
        }
        return $this->leccion->editarLeccion($id, $data) ? ['success' => 'Lección actualizada correctamente.'] : ['error' => 'Error al actualizar la lección.'];
    }

    // Eliminar lección
    public function eliminar($id) {
        if (empty($id)) {
            return ['error' => 'El ID de la lección es obligatorio.'];
        }
        return $this->leccion->eliminarLeccion($id) ? ['success' => 'Lección eliminada correctamente.'] : ['error' => 'Error al eliminar la lección.'];
    }

    // Obtener lecciones de un curso
    public function obtenerLecciones($curso_id) {
        if (empty($curso_id)) {
            return ['error' => 'El ID del curso es obligatorio.'];
        }
        // Obtener las lecciones con los videos y PDFs
        return $this->curso->obtenerLeccionesPorCurso($curso_id);
    }
}
?>
