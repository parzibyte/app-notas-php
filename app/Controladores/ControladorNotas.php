<?php
namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloNotas;
use Parzibyte\Redirect;
use Parzibyte\Servicios\SesionService;
use Parzibyte\Validator;

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
        $nota = ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"));
        if (!$nota) {
            Redirect::to("/notas")->do();
        }
        return view("notas/editar", ["nota" => $nota]);
    }

    public static function confirmarEliminacion($idNota)
    {
        $nota = ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"));
        if (!$nota) {
            Redirect::to("/notas")->do();
        }
        return view("notas/eliminar", ["nota" => $nota]);
    }

    public static function guardarCambios()
    {
        Validator::validateOrRedirect($_POST, [
            "required" => ["idNota", "contenido"],
            "numeric" => "idNota",
        ]);
        $idNota = $_POST["idNota"];
        $contenido = $_POST["contenido"];
        $mensaje = "Nota guardada";
        $tipo = "success";
        if (!ModeloNotas::actualizarDeUsuario($idNota, SesionService::leer("idUsuario"), $contenido)) {
            $mensaje = "Error guardando nota";
            $tipo = "warning";
        }
        Redirect::to("/notas")->with(["tipo" => $tipo, "mensaje" => $mensaje])->do();
    }

    public static function eliminar()
    {
        Validator::validateOrRedirect($_POST, [
            "required" => "idNota",
        ],
            "/notas");
        $idNota = $_POST["idNota"];
        $mensaje = "Nota eliminada";
        $tipo = "success";
        if (!ModeloNotas::eliminarDeUsuario($idNota, SesionService::leer("idUsuario"))) {
            $mensaje = "Error eliminando nota";
            $tipo = "warning";
        }
        Redirect::to("/notas")->with(["tipo" => $tipo, "mensaje" => $mensaje])->do();
    }

    public static function guardar()
    {

        Validator::validateOrRedirect($_POST, [
            "required" => "contenido",
        ]);
        $contenido = $_POST["contenido"];
        $mensaje = "Nota agregada";
        $tipo = "success";
        if (!ModeloNotas::agregar(SesionService::leer("idUsuario"), $contenido)) {
            $mensaje = "Error agregando nota";
            $tipo = "warning";
        }
        Redirect::to("/notas")->with(["tipo" => $tipo, "mensaje" => $mensaje])->do();
    }
}
