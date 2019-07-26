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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", "correo");
        $v->rule("email", "correo");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect("/usuarios/solicitar-nueva-password");
        }
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", ["token", "palabraSecreta", "palabraSecretaConfirm"]);
        $v->rule("equals", "palabraSecreta", "palabraSecretaConfirm");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
        $token = $_POST["token"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", "correo");
        $v->rule("email", "correo");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", ["correo", "palabraSecreta", "palabraSecretaConfirm"]);
        $v->rule("equals", "palabraSecreta", "palabraSecretaConfirm");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $administrador = isset($_POST["administrador"]);
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        if (ModeloUsuarios::unoPorCorreo($correo)) {
            SesionService::flash([
                "mensaje" => "El correo que intentas registrar ya existe",
                "tipo" => "warning",
            ]);
            redirect_back();
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", ["correo", "palabraSecreta", "palabraSecretaConfirm"]);
        $v->rule("equals", "palabraSecreta", "palabraSecretaConfirm");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
        $correo = $_POST["correo"];
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", "idUsuario");
        $v->rule("numeric", "idUsuario");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
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
        $v = new \Valitron\Validator($_POST);
        $v->rule("required", ["palabraSecreta", "palabraSecretaConfirm", "palabraSecretaActual"]);
        $v->rule("equals", "palabraSecreta", "palabraSecretaConfirm");
        if (!$v->validate()) {
            SesionService::flash(["errores_formulario" => $v->errors()]);
            redirect_back();
        }
        $palabraSecreta = $_POST["palabraSecreta"];
        $palabraSecretaConfirm = $_POST["palabraSecretaConfirm"];
        $palabraSecretaActual = $_POST["palabraSecretaActual"];

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
}
