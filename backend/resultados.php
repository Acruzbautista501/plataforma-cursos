<?php
require_once 'db.php';

// Registrar un resultado y controlar intentos
function registrarResultado($usuario_id, $evaluacion_id, $respuestas_usuario) {
    global $pdo;

    // Verificar si el usuario ya tiene 3 intentos fallidos
    $sql = "SELECT * FROM resultados WHERE usuario_id = ? AND evaluacion_id = ? ORDER BY fecha DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $evaluacion_id]);
    $resultado = $stmt->fetch();

    // Si el usuario ya tiene 3 intentos, revisar si debe esperar
    if ($resultado) {
        $intentos = $resultado['intentos'];
        $fecha_intento = new DateTime($resultado['fecha_intento']);
        $ahora = new DateTime();
        $intervalo = $ahora->diff($fecha_intento);

        // Si tiene 3 intentos fallidos y no ha pasado media hora, impedir nuevo intento
        if ($intentos >= 3 && $intervalo->i < 30) {
            return 'Debes esperar 30 minutos antes de intentar de nuevo.';
        }
    }

    // Registrar el resultado (puntaje inicial puede ser 0)
    $sql = "INSERT INTO resultados (usuario_id, evaluacion_id, puntaje, intentos) VALUES (?, ?, 0, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $evaluacion_id]);

    // Obtener el ID del resultado recién insertado
    $resultado_id = $pdo->lastInsertId();
    $puntaje = 0;

    // Registrar las respuestas del usuario y calcular el puntaje
    foreach ($respuestas_usuario as $pregunta_id => $respuesta) {
        $sql = "SELECT * FROM preguntas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pregunta_id]);
        $pregunta = $stmt->fetch();

        // Verificar si la respuesta del usuario es correcta
        $correcta = ($pregunta['respuesta_correcta'] == $respuesta) ? true : false;

        // Registrar la respuesta
        $sql = "INSERT INTO respuestas (resultado_id, pregunta_id, respuesta, correcto) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$resultado_id, $pregunta_id, $respuesta, $correcta]);

        // Sumar el puntaje si la respuesta es correcta
        if ($correcta) {
            $puntaje++;
        }
    }

    // Calcular el puntaje total y actualizar la tabla resultados
    $total_preguntas = count($respuestas_usuario);
    $puntaje_total = ($puntaje / $total_preguntas) * 100;

    // Verificar si el puntaje es suficiente (60%)
    if ($puntaje_total >= 60) {
        $sql = "UPDATE resultados SET puntaje = ?, intentos = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$puntaje_total, $intentos + 1, $resultado_id]);

        return '¡Aprobado!';
    } else {
        // Si no ha aprobado, incrementar los intentos y registrar la fecha
        $sql = "UPDATE resultados SET intentos = ?, fecha_intento = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$intentos + 1, $resultado_id]);

        return 'Reprobado. Inténtalo nuevamente.';
    }
}

?>
