<?php
namespace Parzibyte\Servicios;

use PDO;

/*
Manejador de sesiones propio
No olvides crear las tablas de esquema.sql
@author parzibyte
@see parzibyte.me/blog
@date 2018-06-28
 */
class Sesion implements \SessionHandlerInterface
{
    /**
     * @var $base_de_datos \PDO
     */
    private $base_de_datos; #Aquí vamos a guardar nuestra referencia a la base de datos
    public function open($ruta_de_guardado, $nombre_de_sesion)
    {
        $this->base_de_datos = BD::obtenerParaSesion();
        return true;
    }
    public function close()
    {
        #Eliminamos referencia a la base de datos
        $this->base_de_datos = null;
        return true;
    }

    public function write($id_de_sesion, $datos_de_sesion)
    {

        // error_log("Datos de sesión: " . $datos_de_sesion);
        // error_log("Datos de sesión RAW: " . json_encode($_SESSION));
        $ultimo_acceso = time();
        $sentencia = $this->base_de_datos->prepare("REPLACE INTO sesiones (id, datos, ultimo_acceso) VALUES (?, ?, ?);");
        $sentencia->execute([$id_de_sesion, $datos_de_sesion, $ultimo_acceso]);

        /**
         * Ver si la sesión tiene un id de usuario, para guardarlo
         * en la otra tabla y así llevar un registro
         * de las sesiones que tiene cada usuario, para que
         * más tarde se pueda hacer un "eliminar otras sesiones"
         * o eliminarlas cuando se elimine el usuario
         */
        if (!empty($_SESSION["idUsuario"])) {
            $sentencia = $this->base_de_datos->prepare("REPLACE INTO sesiones_usuarios(id_sesion, id_usuario) VALUES (?, ?)");
            $sentencia->execute([$id_de_sesion, $_SESSION["idUsuario"]]);
        }
        return true;
    }

    public function read($id_de_sesion)
    {
        $sentencia = $this->base_de_datos->prepare("SELECT datos FROM sesiones WHERE id = ?;");
        $sentencia->execute([$id_de_sesion]);
        # Recuperar como objeto (con PDO::FETCH_OBJ), para acceder a $fila->datos
        $fila = $sentencia->fetch(PDO::FETCH_OBJ);
        # Si no existen datos con ese id, fetch devuelve FALSE
        if ($fila === false) {
            return ""; # Cadena vacía
        } else {
            return $fila->datos;
        }
    }
    public function destroy($id_de_sesion)
    {
        $sentencia = $this->base_de_datos->prepare("DELETE FROM sesiones WHERE id = ?;");
        return $sentencia->execute([$id_de_sesion]);
    }
    public function gc($tiempo_de_vida)
    {
        #Calculamos el tiempo actual menos el tiempo de vida.
        $caducidad = time() - $tiempo_de_vida;
        $sentencia = $this->base_de_datos->prepare("DELETE FROM sesiones WHERE ultimo_acceso < ?;");
        return $sentencia->execute([$caducidad]);
    }
}
