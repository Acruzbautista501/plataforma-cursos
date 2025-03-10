<?php

include_once 'models/Evaluacion.php';

class EvaluacionController {

    private $evaluacion;
    private $intentoEvaluacion;

    public function __construct($db) {
        $this->evaluacion = new Evaluacion($db);
        $this->intentoEvaluacion = new IntentoEvaluacion($db);
    }

    // Crear una evaluación
    public function crear($data) {
        if (empty($data['leccion_id']) || empty($data['titulo'])) {
            return ['error' => 'La lección y el título son obligatorios.'];
        }
        return $this->evaluacion->crearEvaluacion($data) 
            ? ['success' => 'Evaluación creada correctamente.'] 
            : ['error' => 'Error al crear la evaluación.'];
    }

    // Obtener evaluaciones por lección
    public function obtenerPorLeccion($leccion_id) {
        return $this->evaluacion->obtenerEvaluacionesPorLeccion($leccion_id);
    }

    // Registrar intento de evaluación
    public function registrarIntento($data) {
        // Verificar si el usuario ya hizo tres intentos
        $intentos = $this->intentoEvaluacion->verificarIntentos($data['evaluacion_id'], $data['usuario_id']);
        if ($intentos >= 3) {
            // Verificar si ya pasaron 30 minutos
            if ($this->intentoEvaluacion->debeEsperar($data['evaluacion_id'], $data['usuario_id'])) {
                return ['error' => 'Debes esperar 30 minutos para intentar nuevamente.'];
            }
        }

        // Calcular si ha aprobado (mínimo 60%)
        $aprobado = ($data['puntaje'] >= 60) ? true : false;

        // Registrar el intento
        $data['aprobado'] = $aprobado;
        $data['intentos'] = $intentos + 1;
        $data['ultima_intento'] = date('Y-m-d H:i:s');
        return $this->intentoEvaluacion->registrarIntento($data) 
            ? ['success' => 'Intento registrado correctamente.'] 
            : ['error' => 'Error al registrar el intento.'];
    }
}

?>
