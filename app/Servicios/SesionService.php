<?php
namespace Parzibyte\Servicios;

class SesionService
{
    public static function flash($clave, $valor = null)
    {
        self::init();
        // Si es un array de claves y valores
        if (is_array($clave) && $valor === null) {
            foreach ($clave as $k => $v) {
                $_SESSION[$k] = $v;
            }
        } else if ($valor === null) {
            // Si quieren obtener
            if (!isset($_SESSION[$clave])) {
                return null;
            }
            $temporal = $_SESSION[$clave];
            unset($_SESSION[$clave]);
            return $temporal;
        } else {
            // Si quieren establecer sin arreglo
            $_SESSION[$clave] = $valor;
        }
    }
    public static function escribir($clave, $datos, $sobrescribir = false)
    {
        self::init();
        if (!isset($_SESSION[$clave]) || $sobrescribir) {
            $_SESSION[$clave] = $datos;
        }

    }
    /**
     * Lee una variable almacenada en la sesión.
     * Devuelve la variable, o null si no existe
     * @param $clave
     * @return mixed|null
     */
    public static function leer($clave)
    {
        self::init();
        return $_SESSION[$clave] ?? null;
    }
    private static function init()
    {
        if (!isset($_SESSION)) {
            session_set_save_handler(new Sesion());
        }

        if (!self::laSesionEstaIniciada()) {
            session_start();
        }
    }
    private static function laSesionEstaIniciada()
    {
        return session_status() === PHP_SESSION_ACTIVE ? true : false;
    }

    public static function destruir()
    {
        self::init();
        session_destroy();
    }
}
