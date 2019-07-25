<?php
namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloNotas;
use Parzibyte\Servicios\SesionService;

class ControladorNotas
{
    public static function index()
    {
        return view("notas/mostrar", ["notas" => ModeloNotas::deUsuario(SesionService::leer("idUsuario"))]);
    }

    public static function agregar()
    {
        return view("notas/agregar");
    }

    public static function editar($idNota)
    {
        return view("notas/editar", ["nota" => ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"))]);
    }

    public static function confirmarEliminacion($idNota)
    {
        return view("notas/eliminar", ["nota" => ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"))]);
    }

    public static function guardarCambios()
    {
        $idNota = $_POST["idNota"];
        $contenido = $_POST["contenido"];
        $mensaje = "Nota guardada";
        $tipo = "success";
        if (!ModeloNotas::actualizarDeUsuario($idNota, SesionService::leer("idUsuario"), $contenido)) {
            $mensaje = "Error guardando nota";
            $tipo = "warning";
        }
        SesionService::flash(["tipo" => $tipo, "mensaje" => $mensaje]);
        redirect("/notas");
    }

    public static function eliminar()
    {
        $idNota = $_POST["idNota"];
        $mensaje = "Nota eliminada";
        $tipo = "success";
        if (!ModeloNotas::eliminarDeUsuario($idNota, SesionService::leer("idUsuario"))) {
            $mensaje = "Error eliminando nota";
            $tipo = "warning";
        }
        SesionService::flash(["tipo" => $tipo, "mensaje" => $mensaje]);
        redirect("/notas");
    }

    public static function guardar()
    {
        $contenido = $_POST["contenido"];
        $mensaje = "Nota agregada";
        $tipo = "success";
        if (!ModeloNotas::agregar(SesionService::leer("idUsuario"), $contenido)) {
            $mensaje = "Error agregando nota";
            $tipo = "warning";
        }
        SesionService::flash(["tipo" => $tipo, "mensaje" => $mensaje]);
        redirect("/notas");
    }
}
