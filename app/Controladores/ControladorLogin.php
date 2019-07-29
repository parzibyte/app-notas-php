<?php

namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Redirect;
use Parzibyte\Servicios\SesionService;
use Parzibyte\Validator;

class ControladorLogin
{

    public static function index()
    {
        if (SesionService::leer("idUsuario")) {
            Redirect::to("/usuarios")->do();
        }
        return view("login");
    }

    public static function login()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => ["correo", "palabraSecreta"],
                "email" => "correo",
            ],
            "/login");

        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $respuesta = ModeloUsuarios::login($correo, $palabraSecreta);
        if ($respuesta) {
            Redirect::to("/usuarios")->do();
        } else {
            Redirect::to("/login")->with([
                "mensaje" => "Datos incorrectos",
                "tipo" => "warning",
            ])
                ->do();
        }
    }

    public static function logout()
    {
        SesionService::destruir();
        Redirect::to("/login")->do();
    }
}
