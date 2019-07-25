<?php
namespace Parzibyte\Modelos;

use Parzibyte\Servicios\BD;
use \PDO;

class ModeloNotas
{

    public static function agregar($idUsuario, $contenido)
    {
        $bd = BD::obtener();
        $fechaYHora = date("Y-m-d H:i:s");
        $sentencia = $bd->prepare("insert into notas(fecha_hora, id_usuario, contenido) VALUES(?, ?, ?);");
        return $sentencia->execute([$fechaYHora, $idUsuario, $contenido]);
    }

    public static function deUsuario($idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT id, fecha_hora, contenido FROM notas WHERE id_usuario = ? ORDER BY fecha_hora DESC");
        $sentencia->execute([$idUsuario]);
        return $sentencia->fetchAll(PDO::FETCH_OBJ);
    }

    public static function eliminarDeUsuario($idNota, $idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("DELETE FROM notas WHERE id = ? AND id_usuario = ?");
        return $sentencia->execute([$idNota, $idUsuario]);
    }

    public static function actualizarDeUsuario($idNota, $idUsuario, $contenido)
    {
        $bd = BD::obtener();
        $fechaYHora = date("Y-m-d H:i:s");
        $sentencia = $bd->prepare("UPDATE notas SET fecha_hora = ?, contenido = ? WHERE id = ? AND id_usuario = ?");
        return $sentencia->execute([$fechaYHora, $contenido, $idNota, $idUsuario]);
    }

    public static function unaDeUsuario($idNota, $idUsuario)
    {
        $bd = BD::obtener();
        $sentencia = $bd->prepare("SELECT id, fecha_hora, contenido FROM notas WHERE id_usuario = ? AND id = ?");
        $sentencia->execute([$idUsuario, $idNota]);
        return $sentencia->fetchObject();
    }
}
