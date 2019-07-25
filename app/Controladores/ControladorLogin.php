<?php

namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Servicios\SesionService;

class ControladorLogin
{

    public static function index()
    {
        if (SesionService::leer("idUsuario")) {
            redirect("/usuarios");
        }
        return view("login");
    }

    public static function login()
    {
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $respuesta = ModeloUsuarios::login($correo, $palabraSecreta);
        if ($respuesta) {
            redirect("/usuarios");
        } else {
            SesionService::flash("mensaje", "Datos incorrectos");
            SesionService::flash("tipo", "warning");
            redirect("/login");
        }
    }

    public static function logout()
    {
        SesionService::destruir();
        redirect("/login");
    }
}
