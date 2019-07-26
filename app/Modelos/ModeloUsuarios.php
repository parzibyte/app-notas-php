<?php

namespace Parzibyte\Modelos;

use Parzibyte\Servicios\BD;
use Parzibyte\Servicios\Seguridad;
use Parzibyte\Servicios\SesionService;
use PDO;

class ModeloUsuarios
{

    public static function login($correo, $palabraSecreta)
    {
        if (!self::coincideUsuarioYPassPorCorreo($correo, $palabraSecreta)) {
            return false;
        }

        $usuario = self::unoPorCorreo($correo);
        SesionService::escribir("correoUsuario", $correo);
        SesionService::escribir("idUsuario", $usuario->id);
        SesionService::escribir("administrador", boolval($usuario->administrador));
        return true;
    }

    public static function eliminarSesiones($idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("DELETE FROM sesiones WHERE id IN (SELECT id_sesion FROM sesiones_usuarios WHERE id_usuario = ?)");
        $sentencia->execute([$idUsuario]);
        $sentencia = $bd->prepare("DELETE FROM sesiones_usuarios WHERE id_usuario = ?");
        $sentencia->execute([$idUsuario]);
    }

    public static function actualizarPalabraSecreta($id, $palabraSecretaActual, $palabraSecretaNueva)
    {
        error_log("Se llamó a actualizar palabra secreta pero no debería");
        return false;
        if (!self::coincideUsuarioYPassPorId($id, $palabraSecretaActual)) {
            return false;
        }

        $bd = BD::obtener();
        $palabraSecretaCifrada = Seguridad::cifrarPalabraSecreta($palabraSecretaNueva);
        $sentencia = $bd->prepare("update usuarios set palabra_secreta = ? WHERE id = ?");
        return $sentencia->execute([$palabraSecretaCifrada, $id]);
    }

    public static function cambiarPalabraSecreta($id, $palabraSecretaNueva)
    {
        $bd = BD::obtener();
        $palabraSecretaCifrada = Seguridad::cifrarPalabraSecreta($palabraSecretaNueva);
        $sentencia = $bd->prepare("update usuarios set palabra_secreta = ? WHERE id = ?");
        return $sentencia->execute([$palabraSecretaCifrada, $id]);
    }

    public static function agregar($correo, $palabraSecreta, $administrador = false, $cifrarPalabraSecreta = true)
    {
        $bd = BD::obtener();
        # SQLSTATE[HY000]: General error: 1366 Incorrect integer value: '' for column 'administrador' at row 1
        $administrador = $administrador ? 1 : 0;
        if ($cifrarPalabraSecreta) {
            $palabraSecreta = Seguridad::cifrarPalabraSecreta($palabraSecreta);
        }
        $sentencia = $bd->prepare("insert into usuarios(correo, palabra_secreta, administrador) values (?, ?, ?)");
        return $sentencia->execute([$correo, $palabraSecreta, $administrador]);
    }

    private static function coincideUsuarioYPassPorCorreo($correo, $palabraSecreta)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo, palabra_secreta, administrador from usuarios WHERE correo = ?");
        $sentencia->execute([$correo]);
        $usuario = $sentencia->fetchObject();
        if (!$usuario) {
            return false;
        }

        return Seguridad::coinciden($palabraSecreta, $usuario->palabra_secreta);
    }

    public static function existePorCorreo($correo)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $sentencia->execute([$correo]);
        return $sentencia->fetchObject();
    }

    public static function coincideUsuarioYPassPorId($id, $palabraSecreta)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo, palabra_secreta, administrador from usuarios WHERE id = ?");
        $sentencia->execute([$id]);
        $usuario = $sentencia->fetchObject();
        if (!$usuario) {
            return false;
        }

        return Seguridad::coinciden($palabraSecreta, $usuario->palabra_secreta);
    }

    public static function eliminar($idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("delete from usuarios WHERE id = ?");
        return $sentencia->execute([$idUsuario]);
    }

    public static function obtener()
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo, administrador from usuarios");
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    public static function unoPorCorreo($correo)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo, administrador from usuarios WHERE correo = ?");
        $sentencia->execute([$correo]);
        return $sentencia->fetchObject();
    }

    public static function uno($idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("select id, correo, administrador from usuarios WHERE id = ?");
        $sentencia->execute([$idUsuario]);
        return $sentencia->fetchObject();
    }

}
