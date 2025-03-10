<?php
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate de que PHPMailer esté configurado correctamente

// Función para registrar un nuevo usuario
function registrarUsuario($nombre, $email, $password) {
    global $pdo;
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $email, $password_hash]);
    // El nuevo usuario no está confirmado por defecto
    enviarEmailConfirmacion($email);
}

// Función para iniciar sesión
function loginUsuario($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Verificar si el usuario está confirmado
        if ($usuario['confirmado']) {
            return $usuario;
        } else {
            return ['error' => 'Usuario no confirmado.'];
        }
    } else {
        return ['error' => 'Credenciales incorrectas.'];
    }
}

// Función para confirmar un usuario (con enlace enviado por email)
function confirmarUsuario($email) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE usuarios SET confirmado = TRUE WHERE email = ?");
    $stmt->execute([$email]);
}

// Función para enviar un email de confirmación
function enviarEmailConfirmacion($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // PHPMailer para enviar el correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';  // Cambiar con tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com';  // Cambiar con tu correo
            $mail->Password = 'your-password';  // Cambiar con tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@example.com', 'Plataforma Cursos');
            $mail->addAddress($usuario['email']);
            
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de cuenta';
            $mail->Body    = 'Haz clic en el siguiente enlace para confirmar tu cuenta: <a href="http://example.com/confirmar?email='.$usuario['email'].'">Confirmar cuenta</a>';
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    return false;
}

// Función para recuperar contraseña
function recuperarContraseña($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        // PHPMailer para enviar el correo de restablecimiento
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';  // Cambiar con tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'your-email@example.com';  // Cambiar con tu correo
            $mail->Password = 'your-password';  // Cambiar con tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@example.com', 'Plataforma Cursos');
            $mail->addAddress($usuario['email']);
            
            $mail->isHTML(true);
            $mail->Subject = 'Restablecimiento de contraseña';
            $mail->Body    = 'Haz clic en el siguiente enlace para restablecer tu contraseña: <a href="http://example.com/reset-password?email='.$usuario['email'].'">Restablecer contraseña</a>';
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    return false;
}

// Función para restablecer la contraseña
function restablecerContraseña($email, $nueva_contraseña) {
    global $pdo;
    $password_hash = password_hash($nueva_contraseña, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    $stmt->execute([$password_hash, $email]);
}
?>
