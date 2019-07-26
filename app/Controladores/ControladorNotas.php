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
        $nota = ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"));
        if (!$nota) {
            redirect("/notas");
        }
        return view("notas/editar", ["nota" => $nota]);
    }

    public static function confirmarEliminacion($idNota)
    {
        $nota = ModeloNotas::unaDeUsuario($idNota, SesionService::leer("idUsuario"));
        if (!$nota) {
            redirect("/notas");
        }
        return view("notas/eliminar", ["nota" => $nota]);
    }

    public static function guardarCambios()
    {
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", ["idNota", "contenido"]);
        $v->rule("numeric", "idNota");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect("/notas");
        }
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", "idNota");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect("/notas");
        }
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", "contenido");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect("/notas/agregar");
        }
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
