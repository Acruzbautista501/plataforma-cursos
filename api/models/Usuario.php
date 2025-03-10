<?php
// models/Usuario.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Iniciar sesión
    public function login($email, $password) {
        $query = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $usuario['password'])) {
                if ($usuario['confirmado'] == 1) {
                    return [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'email' => $usuario['email'],
                        'rol' => $usuario['rol']
                    ];
                } else {
                    return ['error' => 'Cuenta no confirmada. Por favor revisa tu correo.'];
                }
            } else {
                return ['error' => 'Contraseña incorrecta.'];
            }
        }

        return ['error' => 'No se encontró un usuario con ese correo.'];
    }
    

    // Registrar un nuevo usuario con confirmación por email
    public function registrar($nombre, $email, $password, $rol = 'usuario') {
        $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";
        $stmt = $this->conn->prepare($query);

        // Cifrar la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);

        if ($stmt->execute()) {
            // Enviar correo de confirmación
            $this->enviarConfirmacion($email);
            return true;
        }
        return false;
    }

    // Función para enviar el correo de confirmación
    private function enviarConfirmacion($email) {
        $token = bin2hex(random_bytes(50)); // Token de confirmación

        // Guardar el token en la base de datos (opcional)
        $query = "UPDATE usuarios SET confirm_token = :token WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Enviar correo de confirmación
        $mail = new PHPMailer(true);

        try {
            // Configurar el servidor de correo
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Cambia esto según tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'aldair.cruz.bauti@gmail.com'; // Tu correo electrónico
            $mail->Password = 'xfxiphqksqiluzph'; // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('no-reply@tudominio.com', 'Plataforma de Cursos');
            $mail->addAddress($email); // El correo del usuario que se registró
            $mail->isHTML(true);
            $mail->Subject = 'Confirmacion de Registro';
            $mail->Body = 'Gracias por registrarte en nuestra plataforma. Haz clic en el siguiente enlace para confirmar tu cuenta: <a href="http://localhost/plataforma-cursos/api/confirmar?token='.$token.'">Confirmar Cuenta</a>';

            $mail->send();
        } catch (Exception $e) {
            echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }

    // Confirmar la cuenta
    public function confirmarCuenta($token) {
        $query = "SELECT * FROM usuarios WHERE confirm_token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $query = "UPDATE usuarios SET confirm_token = NULL, confirmado = 1 WHERE confirm_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }

    // Función para actualizar perfil
    public function actualizarPerfil($id, $apellidoPaterno, $apellidoMaterno, $fechaNacimiento, $pais, $estado, $ciudad, $direccion, $codigoPostal, $telefono) {
        $query = "UPDATE usuarios SET apellido_paterno = :apellidoPaterno, apellido_materno = :apellidoMaterno, 
                fecha_nacimiento = :fechaNacimiento, pais = :pais, estado = :estado, ciudad = :ciudad, 
                direccion = :direccion, codigo_postal = :codigoPostal, telefono = :telefono WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':apellidoPaterno', $apellidoPaterno);
        $stmt->bindParam(':apellidoMaterno', $apellidoMaterno);
        $stmt->bindParam(':fechaNacimiento', $fechaNacimiento);
        $stmt->bindParam(':pais', $pais);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':ciudad', $ciudad);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':codigoPostal', $codigoPostal);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Función para obtener todos los usuarios (excepto los administradores)
    public function obtenerUsuarios() {
        $query = "SELECT * FROM usuarios WHERE rol != 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos los usuarios encontrados
        }

        return []; // Si no hay usuarios, retorna un arreglo vacío
    }

    // Función para obtener la cantidad de usuarios registrados por mes
    public function obtenerUsuariosPorMes() {
        $query = "SELECT YEAR(fecha_creacion) AS anio, MONTH(fecha_creacion) AS mes, COUNT(*) AS cantidad
                FROM usuarios
                GROUP BY YEAR(fecha_creacion), MONTH(fecha_creacion)
                ORDER BY anio DESC, mes DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve los datos de los usuarios agrupados por mes
        }

        return []; // Si no se encuentran usuarios, retorna un arreglo vacío
    }


    // Restablecer la contraseña
    public function restablecerContrasena($email) {
        $query = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $token = bin2hex(random_bytes(50)); // Generar token para el restablecimiento

            // Guardar el token en la base de datos
            $query = "UPDATE usuarios SET reset_token = :token WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Enviar correo de restablecimiento de contraseña
            $this->enviarRestablecimiento($email, $token);
            return true;
        }

        return false; // No existe el email en la base de datos
    }

    // Función para enviar el correo de restablecimiento de contraseña
    private function enviarRestablecimiento($email, $token) {
        $mail = new PHPMailer(true);

        try {
            // Configurar el servidor de correo
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Cambia esto según tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'tucorreo@gmail.com'; // Tu correo electrónico
            $mail->Password = 'tucontraseña'; // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('no-reply@tudominio.com', 'Plataforma de Cursos');
            $mail->addAddress($email); // El correo del usuario que quiere restablecer la contraseña
            $mail->isHTML(true);
            $mail->Subject = 'Restablecimiento de Contraseña';
            $mail->Body = 'Haz clic en el siguiente enlace para restablecer tu contraseña: <a href="http://localhost/plataforma-cursos/api/restablecer?token='.$token.'">Restablecer Contraseña</a>';

            $mail->send();
        } catch (Exception $e) {
            echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
        }
    }

    // Cambiar la contraseña
    public function cambiarContrasena($token, $nuevaContrasena) {
        $query = "SELECT * FROM usuarios WHERE reset_token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $passwordHash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);

            // Actualizar la contraseña
            $query = "UPDATE usuarios SET password = :password, reset_token = NULL WHERE reset_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return true;
        }

        return false; // Token inválido
    }

    // Función para eliminar cuenta
    public function eliminarCuenta($id) {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Eliminar usuario (solo admin)
    public function eliminarUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>

