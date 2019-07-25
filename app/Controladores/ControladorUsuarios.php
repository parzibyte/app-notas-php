<?php

namespace Parzibyte\Controladores;

use Parzibyte\Modelos\ModeloRestablecimientoPasswords;
use Parzibyte\Modelos\ModeloUsuarios;
use Parzibyte\Modelos\ModeloVerificacionesUsuarios;
use Parzibyte\Servicios\SesionService;

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
            redirect("/notas");
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
        $correo = $_POST["correo"];
        $resultado = ModeloRestablecimientoPasswords::enviarCorreoDeRestablecimiento($correo);
        $mensaje = "Si el correo que proporcionaste existía, se han enviado instrucciones para su restablecimiento a esa dirección";
        $tipo = "info";
        SesionService::flash(["mensaje" => $mensaje, "tipo" => $tipo]);
        redirect("/usuarios/solicitar-nueva-password");
    }

    public static function formularioRestablecerPassword($token)
    {
        return view("usuarios/restablecer_password", ["token" => $token]);
    }

    public static function restablecerPassword()
    {
        $token = $_POST["token"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if ($palabraSecreta !== $palabraSecretaConfirm) {
            SesionService::flash([
                "mensaje" => "Las contraseñas no coinciden",
                "tipo" => "warning",
            ]);
            redirect("/usuarios/restablecer-password/$token");
        }
        # Aquí confirmar el token, eliminarlo y cambiar la contraseña
        $resultado = ModeloRestablecimientoPasswords::restablecer($token, $palabraSecreta);
        if ($resultado) {
            SesionService::flash([
                "mensaje" => "Contraseña cambiada correctamente. Ahora puedes iniciar sesión",
                "tipo" => "success",
            ]);
            redirect("/login");
        } else {
            SesionService::flash([
                "mensaje" => "El token no coincide, intenta de nuevo o solicita uno nuevo",
                "tipo" => "warning",
            ]);
            redirect("/usuarios/solicitar-nueva-password");
        }
    }

    public static function reenviarCorreo()
    {
        $correo = $_POST["correo"];
        ModeloVerificacionesUsuarios::enviarCorreoDeVerificacion($correo);
        SesionService::flash([
            "tipo" => "info",
            "mensaje" => "Correcto, se ha reenviado el correo de verificación si el correo que proporcionaste estaba registrado. Verifica la bandeja de SPAM",
        ]);
        redirect("/usuarios/reenviar-correo");
    }

    public static function verificar($token)
    {
        if (ModeloVerificacionesUsuarios::verificarPorToken($token)) {
            SesionService::flash([
                "mensaje" => "Verificado correctamente. Ya puedes iniciar sesión",
                "tipo" => "success",
            ]);
            redirect("/login");
        } else {
            SesionService::flash([
                "mensaje" => "Error verificando. Token inválido.",
                "tipo" => "danger",
            ]);
            redirect("/registro");
        }
    }

    public static function guardar()
    {
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $administrador = isset($_POST["administrador"]);
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if ($palabraSecreta != $palabraSecretaConfirm) {
            SesionService::flash([
                "mensaje" => "Las contraseñas no coinciden",
                "tipo" => "danger",
            ]);
            redirect("/usuarios/agregar");
        }

        $resultado = ModeloUsuarios::agregar($correo, $palabraSecreta, $administrador);
        $mensaje = "Usuario guardado correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error al insertar. Intente de nuevo";
            $tipo = "danger";
        }
        SesionService::flash([
            "mensaje" => $mensaje,
            "tipo" => $tipo,
        ]);
        redirect("/usuarios/agregar");
    }

    public static function registro()
    {
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if ($palabraSecreta != $palabraSecretaConfirm) {
            SesionService::flash([
                "mensaje" => "Las contraseñas no coinciden",
                "tipo" => "warning",
            ]);
            redirect("/registro");
        }
        if (ModeloVerificacionesUsuarios::obtenerUsuarioPorCorreo($correo)) {
            SesionService::flash([
                "mensaje" => "El correo que intentas registrar ya ha sido registrado anteriormente, pero no ha sido confirmado",
                "tipo" => "warning",
            ]);
            redirect("/registro");
        }

        if (ModeloUsuarios::existePorCorreo($correo)) {
            SesionService::flash([
                "mensaje" => "El correo ya está en uso por otro usuario",
                "tipo" => "warning",
            ]);
            redirect("/registro");
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
        SesionService::flash([
            "mensaje" => $mensaje,
            "tipo" => $tipo,
        ]);
        redirect("/registro");
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
        $idUsuario = $_POST["idUsuario"];
        ModeloUsuarios::eliminarSesiones($idUsuario);
        $resultado = ModeloUsuarios::eliminar($idUsuario);
        $mensaje = "Eliminado correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error eliminando";
            $tipo = "danger";
        }
        SesionService::flash(["mensaje" => $mensaje, "tipo" => $tipo]);
        redirect("/usuarios");
    }

    public static function perfilGuardarPassword()
    {
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        $palabraSecretaActual = $_POST["palabraSecretaActual"];
        if ($palabraSecreta !== $palabraSecretaConfirm) {
            SesionService::flash(["mensaje" => "Las contraseñas no coinciden", "tipo" => "warning"]);
            redirect("/perfil/cambiar-password/");
        }

        $idUsuario = SesionService::leer("idUsuario");

        if (!ModeloUsuarios::coincideUsuarioYPassPorId($idUsuario, $palabraSecretaActual)) {
            SesionService::flash(["mensaje" => "La contraseña actual no coincide", "tipo" => "warning"]);
            redirect("/perfil/cambiar-password/");

        }

        $resultado = ModeloUsuarios::cambiarPalabraSecreta($idUsuario, $palabraSecreta);
        $mensaje = "Contraseña actualizada correctamente";
        $tipo = "success";
        if (!$resultado) {
            $mensaje = "Error actualizando contraseña";
            $tipo = "warning";
        }
        SesionService::flash(["mensaje" => $mensaje, "tipo" => $tipo]);
        redirect("/perfil/cambiar-password");
    }

    public static function actualizarPalabraSecreta()
    {
        $datos = json_decode(file_get_contents("php://input"));
        return json(ModeloUsuarios::actualizarPalabraSecreta($datos->id, $datos->palabraSecretaActual));
    }

    public static function insertar()
    {
        $datos = json_decode(file_get_contents("php://input"));
        return json(ModeloUsuarios::agregar($datos->correo, $datos->palabraSecreta));
    }

    public static function obtener()
    {
        return json(ModeloUsuarios::obtener());
    }

}
