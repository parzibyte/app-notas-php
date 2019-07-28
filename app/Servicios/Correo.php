<?php
namespace Parzibyte\Servicios;

use Parzibyte\Servicios\Comun;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Correo
{
    public static function enviarDeRestablecimiento($destinatario, $token)
    {
        try {
            $correo = new PHPMailer(true);
            $correo->setFrom(Comun::env("DIRECCION_CORREO_REMITENTE"), Comun::env("NOMBRE_REMITENTE"));
            $correo->addAddress($destinatario);
            $correo->isHTML(true);
            $correo->Subject = "Restablecer password";
            $enlace = URL_RAIZ . "/usuarios/restablecer-password/$token";
            $correo->Body = getview("correos/restablecer", ["enlace" => $enlace]);
            $correo->AltBody = "Restablece tu contraseña en el siguiente enlace: $enlace";
            $correo->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo de restablecimiento de contraseña:" . $e->getMessage());
            return false;
        }
    }

    
    public static function enviarDeVerificacion($destinatario, $token)
    {
        try {
            $correo = new PHPMailer(true);
            $correo->setFrom(Comun::env("DIRECCION_CORREO_REMITENTE"), Comun::env("NOMBRE_REMITENTE"));
            $correo->addAddress($destinatario);
            $correo->isHTML(true);
            $correo->Subject = "Verifica tu correo para usar la app";
            $enlace = URL_RAIZ . "/usuarios/verificar/$token";
            $correo->Body = getview("correos/verificar", ["enlace" => $enlace]);
            $correo->AltBody = "Verifica tu correo pegando el siguiente enlace en el navegador: $enlace";
            $correo->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo de verificación: " . $e->getMessage());
            return false;
        }
    }
}
