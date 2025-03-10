<?php

class Evaluacion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Métodos relacionados con la evaluación
    public function crearEvaluacion($data) {
        $query = "INSERT INTO evaluaciones (leccion_id, titulo, descripcion, minimo_aprobatorio) 
                  VALUES (:leccion_id, :titulo, :descripcion, :minimo_aprobatorio)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':leccion_id' => $data['leccion_id'],
            ':titulo' => $data['titulo'],
            ':descripcion' => $data['descripcion'],
            ':minimo_aprobatorio' => $data['minimo_aprobatorio']
        ]);
    }

    // Obtener evaluaciones por lección
    public function obtenerEvaluacionesPorLeccion($leccion_id) {
        $query = "SELECT * FROM evaluaciones WHERE leccion_id = :leccion_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':leccion_id', $leccion_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class IntentoEvaluacion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar intento de evaluación
    public function registrarIntento($data) {
        $query = "INSERT INTO intentos_evaluaciones (evaluacion_id, usuario_id, puntaje, intentos, ultima_intento, aprobado) 
                  VALUES (:evaluacion_id, :usuario_id, :puntaje, :intentos, :ultima_intento, :aprobado)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':evaluacion_id' => $data['evaluacion_id'],
            ':usuario_id' => $data['usuario_id'],
            ':puntaje' => $data['puntaje'],
            ':intentos' => $data['intentos'],
            ':ultima_intento' => $data['ultima_intento'],
            ':aprobado' => $data['aprobado']
        ]);
    }

    // Verificar intentos previos
    public function verificarIntentos($evaluacion_id, $usuario_id) {
        $query = "SELECT COUNT(*) as total_intentos FROM intentos_evaluaciones 
                  WHERE evaluacion_id = :evaluacion_id AND usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_intentos'];
    }

    // Verificar si debe esperar
    public function debeEsperar($evaluacion_id, $usuario_id) {
        $query = "SELECT TIMESTAMPDIFF(MINUTE, ultima_intento, NOW()) as tiempo_espera 
                  FROM intentos_evaluaciones 
                  WHERE evaluacion_id = :evaluacion_id AND usuario_id = :usuario_id 
                  ORDER BY ultima_intento DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':evaluacion_id', $evaluacion_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['tiempo_espera'] < 30); // Si el tiempo de espera es menor a 30 minutos, debe esperar
    }
}

?>

