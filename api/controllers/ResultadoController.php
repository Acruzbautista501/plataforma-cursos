<?php

include_once 'models/Resultado.php';

class ResultadoController {
    private $resultado;

    public function __construct($db) {
        $this->resultado = new Resultado($db);
    }

    // Crear un resultado para el usuario
    public function crear($usuario_id, $curso_id) {
        if (empty($usuario_id) || empty($curso_id)) {
            return ['error' => 'Todos los campos son obligatorios.'];
        }

        // Calcular el puntaje promedio del curso
        $puntaje_promedio = $this->resultado->obtenerPromedio($usuario_id, $curso_id);
        
        return $this->resultado->crearResultado($usuario_id, $curso_id, $puntaje_promedio) ? 
            ['success' => 'Resultado registrado correctamente.'] : 
            ['error' => 'Error al registrar el resultado.'];
    }

    // Obtener los resultados de un curso para un usuario
    public function obtener($usuario_id, $curso_id) {
        if (empty($usuario_id) || empty($curso_id)) {
            return ['error' => 'El ID del usuario y el curso son obligatorios.'];
        }
        return $this->resultado->obtenerPromedio($usuario_id, $curso_id);
    }

    // Generar el diploma en PDF
    public function generarDiploma($usuario_id, $curso_id) {
        if (empty($usuario_id) || empty($curso_id)) {
            return ['error' => 'El ID del usuario y el curso son obligatorios.'];
        }
        $diplomaArchivo = $this->resultado->generarDiploma($usuario_id, $curso_id);
        return ['success' => 'Diploma generado correctamente.', 'archivo' => $diplomaArchivo];
    }
}
?>
