<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'db.php';

// Registro de usuario
function registrarUsuario($nombre, $email, $password, $rol = 'usuario') {
    global $pdo;
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nombre, $email, $passwordHash, $rol])) {
        enviarCorreoConfirmacion($email);
        return true;
    }
    return false;
}

// Inicio de sesión
function iniciarSesion($email, $password) {
    global $pdo;
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        if (!$usuario['confirmado']) {
            return 'Usuario no confirmado';
        }
        
        // Aquí verificamos si el usuario es un administrador
        if ($usuario['rol'] === 'admin') {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            return $usuario; // Devolver el usuario administrador
        } else {
            return 'Acceso no autorizado: solo administradores pueden acceder al dashboard de administrador';
        }
    }
    return false;
}


// Confirmación de usuario
function confirmarUsuario($email) {
    global $pdo;
    $sql = "UPDATE usuarios SET confirmado = 1 WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$email]);
}

// Enviar correo de confirmación
function enviarCorreoConfirmacion($email) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.tuservidor.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tuemail@tuservidor.com';
        $mail->Password = 'tucontraseña';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('tuemail@tuservidor.com', 'Plataforma Cursos');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta';
        $link = "http://tusitio.com/confirmar.php?email=$email";
        $mail->Body = "<p>Haz clic en el siguiente enlace para confirmar tu cuenta:</p><p><a href='$link'>$link</a></p>";

        $mail->send();
    } catch (Exception $e) {
        error_log('Error al enviar correo de confirmación: ' . $mail->ErrorInfo);
    }
}

// Recuperación de contraseña
function enviarCorreoRecuperacion($email) {
    global $pdo;

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        return 'No se encontró el usuario';
    }

    $token = bin2hex(random_bytes(50));
    $sql = "UPDATE usuarios SET token = ? WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token, $email]);

    $link = "http://tusitio.com/restablecer.php?token=$token";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.tuservidor.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tuemail@tuservidor.com';
        $mail->Password = 'tucontraseña';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('tuemail@tuservidor.com', 'Plataforma Cursos');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña';
        $mail->Body = "<p>Para restablecer tu contraseña, haz clic en el siguiente enlace:</p><p><a href='$link'>$link</a></p>";

        $mail->send();
        return 'Correo enviado con éxito';
    } catch (Exception $e) {
        return 'Error al enviar el correo: ' . $mail->ErrorInfo;
    }
}

// Eliminar usuario
function eliminarUsuario($usuario_id) {
    global $pdo;
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$usuario_id]);
}

?>