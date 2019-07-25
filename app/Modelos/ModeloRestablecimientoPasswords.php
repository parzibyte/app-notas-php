<?php

namespace Parzibyte\Modelos;

use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Servicios\BD;
use Parzibyte\Servicios\Correo;
use Parzibyte\Servicios\Seguridad;

class ModeloRestablecimientoPasswords
{

    public static function enviarCorreoDeRestablecimiento($correo)
    {
        # Si no hay usuario registrado, no podemos restablecer su contraseña
        $usuario = ModeloUsuarios::unoPorCorreo($correo);
        if (!$usuario) {
            return false;
        }

        $posibleRestablecimiento = self::obtenerRestablecimientoDeUsuario($usuario->id);
        # Si ya existe, volver a enviar el correo que ya estaba
        if ($posibleRestablecimiento) {
            return Correo::enviarDeRestablecimiento($correo, $posibleRestablecimiento->token);
        } else {
            # Si no, insertar
            $token = Seguridad::tokenAleatorioSeguro(20);
            self::agregarRestablecimiento($usuario->id, $token);
            return Correo::enviarDeRestablecimiento($correo, $token);
        }
    }

    public static function restablecer($token, $palabraSecreta)
    {
        $bd = BD::obtener();
        # Obtener usuario solo si existía un token de restablecimiento para él
        $usuario = self::obtenerUsuarioPorToken($token);
        # Token inválido, o no existía
        if (!$usuario) {
            return false;
        }

        # Hasta aquí todo bien. La cambiamos
        ModeloUsuarios::cambiarPalabraSecreta($usuario->id, $palabraSecreta);

        # Y eliminamos este restablecimiento
        $sentencia = $bd->prepare("DELETE FROM restablecimientos_passwords_usuarios WHERE token = ?");
        $sentencia->execute([$token]);

        # También cerramos las sesiones

        ModeloUsuarios::eliminarSesiones($usuario->id);
        return true;
    }

    private static function agregarRestablecimiento($idUsuario, $token)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("INSERT INTO restablecimientos_passwords_usuarios(id_usuario, token) VALUES (?, ?)");
        $sentencia->execute([$idUsuario, $token]);
        return $bd->lastInsertId();
    }

    private static function obtenerRestablecimientoDeUsuario($idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT r.token
        FROM restablecimientos_passwords_usuarios r
        INNER JOIN usuarios u
        ON u.id = r.id_usuario
        AND r.id_usuario = ?");
        $sentencia->execute([$idUsuario]);
        return $sentencia->fetchObject();
    }

    private static function obtenerUsuarioPorToken($token)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT u.id, u.correo, r.token
        FROM restablecimientos_passwords_usuarios r
        INNER JOIN usuarios u
        ON u.id = r.id_usuario
        AND r.token = ?");
        $sentencia->execute([$token]);
        return $sentencia->fetchObject();
    }

}
