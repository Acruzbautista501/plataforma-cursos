<?php

include_once 'models/Usuario.php';

class AuthController {
    private $usuario;

    public function __construct($db) {
        $this->usuario = new Usuario($db);
    }

    // Iniciar Sesión
    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['error' => 'El correo y la contraseña son obligatorios'];
        }

        return $this->usuario->login($data['email'], $data['password']);
    }

    // Registro de usuario
    public function registrar($data) {
        if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
            return ['error' => 'Todos los campos son obligatorios.'];
        }

        $nombre = $data['nombre'];
        $email = $data['email'];
        $password = $data['password'];

        if ($this->usuario->registrar($nombre, $email, $password)) {
            return ['success' => 'Usuario registrado correctamente. Por favor, revisa tu correo para confirmar tu cuenta.'];
        }

        return ['error' => 'No se pudo registrar al usuario.'];
    }

    // Confirmar cuenta
    public function confirmarCuenta($data) {
        if (empty($data['token'])) {
            return ['error' => 'El token de confirmación es requerido.'];
        }

        $token = $data['token'];

        if ($this->usuario->confirmarCuenta($token)) {
            return ['success' => 'Cuenta confirmada correctamente.'];
        }

        return ['error' => 'Token inválido o expirado.'];
    }

    // Editar perfil
    public function editarPerfil($data) {
        if (empty($data['id'])) {
            return ['error' => 'El ID del usuario es obligatorio.'];
        }

        $id = $data['id'];
        $apellidoPaterno = $data['apellidoPaterno'];
        $apellidoMaterno = $data['apellidoMaterno'];
        $fechaNacimiento = $data['fechaNacimiento'];
        $pais = $data['pais'];
        $estado = $data['estado'];
        $ciudad = $data['ciudad'];
        $direccion = $data['direccion'];
        $codigoPostal = $data['codigoPostal'];
        $telefono = $data['telefono'];

        if ($this->usuario->actualizarPerfil($id, $apellidoPaterno, $apellidoMaterno, $fechaNacimiento, $pais, $estado, $ciudad, $direccion, $codigoPostal, $telefono)) {
            return ['success' => 'Perfil actualizado correctamente.'];
        }

        return ['error' => 'No se pudo actualizar el perfil.'];
    }


    // Restablecer contraseña
    public function restablecerContrasena($data) {
        if (empty($data['email'])) {
            return ['error' => 'El email es requerido.'];
        }

        $email = $data['email'];

        if ($this->usuario->restablecerContrasena($email)) {
            return ['success' => 'Se ha enviado un correo para restablecer tu contraseña.'];
        }

        return ['error' => 'No se encontró un usuario con ese email.'];
    }

    // Cambiar contraseña
    public function cambiarContrasena($data) {
        if (empty($data['token']) || empty($data['nuevaContrasena'])) {
            return ['error' => 'El token y la nueva contraseña son obligatorios.'];
        }

        $token = $data['token'];
        $nuevaContrasena = $data['nuevaContrasena'];

        if ($this->usuario->cambiarContrasena($token, $nuevaContrasena)) {
            return ['success' => 'Contraseña cambiada correctamente.'];
        }

        return ['error' => 'Token inválido o expirado.'];
    }

    // Obtener todos los usuarios (excepto los administradores)
    public function obtenerUsuarios() {
        $usuarios = $this->usuario->obtenerUsuarios();
        
        if (!empty($usuarios)) {
            return ['success' => 'Usuarios obtenidos correctamente.', 'usuarios' => $usuarios];
        }

        return ['error' => 'No se encontraron usuarios.'];
    }

    // Obtener usuarios por mes
    public function obtenerUsuariosPorMes() {
        $usuariosPorMes = $this->usuario->obtenerUsuariosPorMes();

        if (!empty($usuariosPorMes)) {
            return ['success' => 'Usuarios por mes obtenidos correctamente.', 'usuariosPorMes' => $usuariosPorMes];
        }

        return ['error' => 'No se encontraron registros de usuarios por mes.'];
    }

    // Eliminar cuenta
    public function eliminarCuenta($data) {
        if (empty($data['id'])) {
            return ['error' => 'El ID del usuario es obligatorio.'];
        }

        if ($this->usuario->eliminarCuenta($data['id'])) {
            return ['success' => 'Cuenta eliminada correctamente.'];
        }

        return ['error' => 'No se pudo eliminar la cuenta.'];
    }

    // Eliminar usuario (solo admin)
    public function eliminarUsuario($data) {
        if (empty($data['id'])) {
            return ['error' => 'El ID del usuario es obligatorio.'];
        }

        if ($this->usuario->eliminarUsuario($data['id'])) {
            return ['success' => 'Usuario eliminado correctamente.'];
        }

        return ['error' => 'No se pudo eliminar el usuario.'];
    }  
}
?>


