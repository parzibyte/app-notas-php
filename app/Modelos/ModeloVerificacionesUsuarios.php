<?php

namespace Parzibyte\Modelos;

use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Servicios\BD;
use Parzibyte\Servicios\Correo;
use Parzibyte\Servicios\Seguridad;

class ModeloVerificacionesUsuarios
{

    public static function registrarUsuarioNoVerificado($correo, $palabraSecreta)
    {
        $bd = BD::obtener();
        $palabraSecreta = Seguridad::cifrarPalabraSecreta($palabraSecreta);
        $sentencia = $bd->prepare("insert into usuarios_no_verificados(correo, palabra_secreta) values (?, ?)");
        return $sentencia->execute([$correo, $palabraSecreta]);
    }

    public static function enviarCorreoDeVerificacion($correo)
    {
        $bd = BD::obtener();
        // Ver si realmente está registrado y pendiente
        $usuario = self::obtenerUsuarioPorCorreo($correo);
        if (!$usuario) {
            return false;
        }
        // Ver si ya existía una verificación anterior. Esto en caso de que se quiera
        // reenviar la verificación
        $posibleVerificacion = self::obtenerVerificacionPorCorreo($correo);
        if ($posibleVerificacion) {
            return Correo::enviarDeVerificacion($correo, $posibleVerificacion->token);
        } else {
            // Si es la primera vez, guardamos la verificación
            $token = Seguridad::tokenAleatorioSeguro(20);
            self::agregarVerificacion($usuario->id, $token);
            return Correo::enviarDeVerificacion($correo, $token);
        }
    }

    public static function verificarPorToken($token)
    {
        $bd = BD::obtener();
        $usuarioNoVerificado = self::obtenerUsuarioPorToken($token, true);
        if (!$usuarioNoVerificado) {
            error_log("Intentando verificar usuario con token $token pero no existe");
            return false;
        }
        // "Mover" el usuario de una tabla a otra
        ModeloUsuarios::agregar($usuarioNoVerificado->correo, $usuarioNoVerificado->palabra_secreta, false, false);
        // Eliminarlo como usuario
        $sentencia = $bd->prepare("DELETE FROM usuarios_no_verificados WHERE id = ?");
        $sentencia->execute([$usuarioNoVerificado->id]);
        # Nota: las verificaciones se eliminan automáticamente por el ON CASCADE
        return true;
    }

    public static function obtenerUsuarioPorCorreo($correo, $conPalabraSecreta = false)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo "
            . ($conPalabraSecreta ? ", palabra_secreta " : "")
            . " from usuarios_no_verificados WHERE correo = ?");
        $sentencia->execute([$correo]);
        return $sentencia->fetchObject();
    }

    private static function obtenerUsuarioPorToken($token, $conPalabraSecreta = false)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select u.id, u.correo "
            . ($conPalabraSecreta ? ", u.palabra_secreta " : "")
            . " from usuarios_no_verificados u INNER JOIN verificaciones_pendientes_usuarios v
            ON u.id = v.id_usuario_no_verificado AND v.token = ?");
        $sentencia->execute([$token]);
        return $sentencia->fetchObject();
    }

    private static function agregarVerificacion($idUsuario, $token)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("INSERT INTO verificaciones_pendientes_usuarios(id_usuario_no_verificado, token) VALUES (?, ?)");
        $sentencia->execute([$idUsuario, $token]);
        return $bd->lastInsertId();
    }

    private static function obtenerVerificacionPorCorreo($correo)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT v.id, v.token
        FROM verificaciones_pendientes_usuarios v
        INNER JOIN usuarios_no_verificados u
        ON v.id_usuario_no_verificado = u.id
        AND u.correo = ?");
        $sentencia->execute([$correo]);
        return $sentencia->fetchObject();
    }

}
