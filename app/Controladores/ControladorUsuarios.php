<?php

namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloRestablecimientoPasswords;
use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Modelos\ModeloVerificacionesUsuarios;
use Parzibyte\Redirect;
use Parzibyte\Servicios\SesionService;
use Parzibyte\Validator;

class ControladorUsuarios
{

    public static function index()
    {
        return view("usuarios/mostrar", ["usuarios" => ModeloUsuarios::obtener()]);
    }

    public static function agregar()
    {
        return view("usuarios/agregar");
    }

    public static function registrar()
    {
        if (SesionService::leer("idUsuario")) {
            Redirect::to("/notas")->do();
        }
        return view("usuarios/registro");
    }

    public static function solicitarReenvioCorreo()
    {
        return view("usuarios/reenviar-correo");
    }

    public static function formularioSolicitarNuevaPassword()
    {
        return view("usuarios/solicitar_nueva_password");
    }

    public static function solicitarNuevaPassword()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => "correo",
                "email" => "correo",
            ],
            "/usuarios/solicitar-nueva-password");
        $correo = $_POST["correo"];
        $resultado = ModeloRestablecimientoPasswords::enviarCorreoDeRestablecimiento($correo);
        $mensaje = "Si el correo que proporcionaste existía, se han enviado instrucciones para su restablecimiento a esa dirección";
        $tipo = "info";
        Redirect::to("/usuarios/solicitar-nueva-password")
            ->with(["mensaje" => $mensaje, "tipo" => $tipo])
            ->do();
    }

    public static function formularioRestablecerPassword($token)
    {
        return view("usuarios/restablecer_password", ["token" => $token]);
    }

    public static function restablecerPassword()
    {
        Validator::validateOrRedirect($_POST, [
            "required" => ["token", "palabraSecreta", "palabraSecretaConfirm"],
            "equals" => [
                ["palabraSecreta", "palabraSecretaConfirm"]
            ],
        ]);
        $token = $_POST["token"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        $resultado = ModeloRestablecimientoPasswords::restablecer($token, $palabraSecreta);
        if ($resultado) {
            Redirect::to("/login")
                ->with([
                    "mensaje" => "Contraseña cambiada correctamente. Ahora puedes iniciar sesión",
                    "tipo" => "success",
                ])
                ->do();
        } else {
            Redirect::to("/usuarios/solicitar-nueva-password")
                ->with([
                    "mensaje" => "El token no coincide, intenta de nuevo o solicita uno nuevo",
                    "tipo" => "warning",
                ])
                ->do();
        }
    }

    public static function reenviarCorreo()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => "correo",
                "email" => "correo",
            ]);
        $correo = $_POST["correo"];
        ModeloVerificacionesUsuarios::enviarCorreoDeVerificacion($correo);
        Redirect::to("/usuarios/reenviar-correo")
            ->with([
                "tipo" => "info",
                "mensaje" => "Correcto, se ha reenviado el correo de verificación si el correo que proporcionaste estaba registrado. Verifica la bandeja de SPAM",
            ])
            ->do();
    }

    public static function verificar($token)
    {
        if (ModeloVerificacionesUsuarios::verificarPorToken($token)) {

            Redrect::with([
                "mensaje" => "Verificado correctamente. Ya puedes iniciar sesión",
                "tipo" => "success",
            ])
                ->to("/login")
                ->do();
        } else {
            Redirect::with([
                "mensaje" => "Error verificando. Token inválido.",
                "tipo" => "danger",
            ])
                ->to("/registro")
                ->do();
        }
    }

    public static function guardar()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => ["correo", "palabraSecreta", "palabraSecretaConfirm"],
                "equals" => [
                    ["palabraSecreta", "palabraSecretaConfirm"],
                ],
            ]);
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $administrador = isset($_POST["administrador"]);
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if (ModeloUsuarios::unoPorCorreo($correo)) {
            Redirect::with([
                "mensaje" => "El correo que intentas registrar ya existe",
                "tipo" => "warning",
            ])
                ->back()
                ->do();
        }
        $resultado = ModeloUsuarios::agregar($correo, $palabraSecreta, $administrador);
        $mensaje = "Usuario guardado correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error al insertar. Intente de nuevo";
            $tipo = "danger";
        }
        Redirect::with([
            "mensaje" => $mensaje,
            "tipo" => $tipo,
        ])
            ->to("/usuarios/agregar")
            ->do();
    }

    public static function registro()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => ["correo", "palabraSecreta", "palabraSecretaConfirm"],
                "equals" => [
                    ["palabraSecreta", "palabraSecretaConfirm"],
                ],
            ]);
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if (ModeloVerificacionesUsuarios::obtenerUsuarioPorCorreo($correo)) {
            Redirect::with([
                "mensaje" => "El correo que intentas registrar ya ha sido registrado anteriormente, pero no ha sido confirmado",
                "tipo" => "warning",
            ])
                ->to("/registro")
                ->do();
        }

        if (ModeloUsuarios::existePorCorreo($correo)) {
            Redirect::with([
                "mensaje" => "El correo ya está en uso por otro usuario",
                "tipo" => "warning",
            ])
                ->to("/registro")
                ->do();
        }

        $resultado = ModeloVerificacionesUsuarios::registrarUsuarioNoVerificado($correo, $palabraSecreta);
        $mensaje = "Registro correcto. Hemos enviado una confirmación a tu correo. No olvides revisar la carpeta SPAM";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error al registrar. Intenta más tarde";
            $tipo = "danger";
        } else {
            if (!ModeloVerificacionesUsuarios::enviarCorreoDeVerificacion($correo)) {
                $mensaje = "Error enviando correo de verificación. Intenta más tarde o solicita a un administrador que lo verifique";
                $tipo = "danger";
            }
        }
        Redirect::to("/registro")
            ->with([
                "mensaje" => $mensaje,
                "tipo" => $tipo,
            ])
            ->do();
    }

    public static function perfilCambiarPassword()
    {
        return view("perfil/cambiar_password");
    }

    public static function confirmarEliminacion($idUsuario)
    {
        $usuario = ModeloUsuarios::uno($idUsuario);
        return view("usuarios/confirmar_eliminacion", ["usuario" => $usuario]);
    }

    public static function eliminar()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => "idUsuario",
                "numeric" => "idUsuario",
            ]);
        $idUsuario = $_POST["idUsuario"];
        ModeloUsuarios::eliminarSesiones($idUsuario);
        $resultado = ModeloUsuarios::eliminar($idUsuario);
        $mensaje = "Eliminado correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error eliminando";
            $tipo = "danger";
        }
        Redirect::to("/usuarios")
            ->with(["mensaje" => $mensaje, "tipo" => $tipo])
            ->do();
    }

    public static function perfilGuardarPassword()
    {
        Validator::validateOrRedirect($_POST,
            [
                "required" => ["palabraSecreta", "palabraSecretaConfirm", "palabraSecretaActual"],
                "equals" => [
                    ["palabraSecreta", "palabraSecretaConfirm"],
                ],
            ]);
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        $palabraSecretaActual = $_POST["palabraSecretaActual"];

        $idUsuario = SesionService::leer("idUsuario");

        if (!ModeloUsuarios::coincideUsuarioYPassPorId($idUsuario, $palabraSecretaActual)) {
            Redirect::with(["mensaje" => "La contraseña actual no coincide", "tipo" => "warning"])
                ->to("/perfil/cambiar-password/")
                ->do();
        }

        $resultado = ModeloUsuarios::cambiarPalabraSecreta($idUsuario, $palabraSecreta);
        $mensaje = "Contraseña actualizada correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error actualizando contraseña";
            $tipo = "warning";
        }
        Redirect::with(["mensaje" => $mensaje, "tipo" => $tipo])
            ->to("/perfil/cambiar-password")
            ->do();
    }
}
