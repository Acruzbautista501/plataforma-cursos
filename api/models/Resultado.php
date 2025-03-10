<?php
require_once('fpdf/fpdf.php');

class Resultado {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear un nuevo resultado del curso
    public function crearResultado($usuario_id, $curso_id, $puntaje_promedio) {
        $query = "INSERT INTO resultados (usuario_id, curso_id, puntaje, fecha_terminacion) VALUES (:usuario_id, :curso_id, :puntaje, NOW())";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':curso_id' => $curso_id,
            ':puntaje' => $puntaje_promedio
        ]);
    }

    // Obtener las evaluaciones de un curso para un usuario y calcular el puntaje promedio
    public function obtenerPromedio($usuario_id, $curso_id) {
        // Obtener todas las evaluaciones del curso
        $queryEvaluaciones = "SELECT e.id, e.titulo, i.puntaje FROM evaluaciones e
                              INNER JOIN intentos_evaluaciones i ON e.id = i.evaluacion_id
                              WHERE e.leccion_id IN (SELECT id FROM lecciones WHERE curso_id = :curso_id)
                              AND i.usuario_id = :usuario_id";
        
        $stmtEvaluaciones = $this->conn->prepare($queryEvaluaciones);
        $stmtEvaluaciones->bindParam(':usuario_id', $usuario_id);
        $stmtEvaluaciones->bindParam(':curso_id', $curso_id);
        $stmtEvaluaciones->execute();
        
        // Calcular el puntaje promedio
        $puntajes = $stmtEvaluaciones->fetchAll(PDO::FETCH_ASSOC);
        $total_puntaje = 0;
        $num_evaluaciones = count($puntajes);
        
        if ($num_evaluaciones == 0) {
            return 0;  // Si no hay evaluaciones, puntaje promedio es 0
        }

        foreach ($puntajes as $evaluacion) {
            $total_puntaje += $evaluacion['puntaje'];
        }

        return $total_puntaje / $num_evaluaciones;
    }

    // Generar un diploma en PDF
    public function generarDiploma($usuario_id, $curso_id) {
        // Obtener información del usuario y curso
        $usuarioQuery = "SELECT nombre FROM usuarios WHERE id = :usuario_id";
        $cursoQuery = "SELECT nombre FROM cursos WHERE id = :curso_id";
        $resultado = $this->obtenerPromedio($usuario_id, $curso_id);

        $stmtUsuario = $this->conn->prepare($usuarioQuery);
        $stmtUsuario->bindParam(':usuario_id', $usuario_id);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        $stmtCurso = $this->conn->prepare($cursoQuery);
        $stmtCurso->bindParam(':curso_id', $curso_id);
        $stmtCurso->execute();
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        // Crear el diploma en PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Título
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(200, 10, "Diploma de Finalización del Curso", 0, 1, 'C');

        // Información del diploma
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);
        $pdf->Cell(200, 10, "Este diploma certifica que", 0, 1, 'C');
        $pdf->Cell(200, 10, $usuario['nombre'], 0, 1, 'C');
        $pdf->Cell(200, 10, "ha completado satisfactoriamente el curso", 0, 1, 'C');
        $pdf->Cell(200, 10, $curso['nombre'], 0, 1, 'C');
        $pdf->Cell(200, 10, "con un promedio de " . number_format($resultado, 2) . "%", 0, 1, 'C');
        $pdf->Cell(200, 10, "Fecha de finalización: " . date("d/m/Y"), 0, 1, 'C');

        // Salvar el archivo PDF
        $nombreArchivo = "diploma_" . $usuario_id . "_" . $curso_id . ".pdf";
        $pdf->Output('F', 'diplomas/' . $nombreArchivo); // Guardar el PDF en una carpeta "diplomas"

        return $nombreArchivo;
    }
}
?>
