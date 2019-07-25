<?php
namespace Parzibyte\Servicios;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Correo
{
    public static function enviarDeRestablecimiento($correo, $token)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->setFrom("contacto@parzibyte.me", "Parzibyte");
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = "Restablecer password";
            $enlace = URL_RAIZ . "/usuarios/restablecer-password/$token";
            $mail->Body = getview("correos/restablecer", ["enlace" => $enlace]);
            $mail->AltBody = "Restablece tu contraseña en el siguiente enlace: $enlace";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo de restablecimiento de contraseña:" . $e->getMessage());
            return false;
        }
    }
    public static function enviarDeVerificacion($correo, $token)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->setFrom("contacto@parzibyte.me", "Parzibyte");
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->Subject = "Verifica tu correo para usar la app";
            $enlace = URL_RAIZ . "/usuarios/verificar/$token";
            $mail->Body = getview("correos/verificar", ["enlace" => $enlace]);
            $mail->AltBody = "Verifica tu correo pegando el siguiente enlace en el navegador: $enlace";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo de verificación: " . $e->getMessage());
            return false;
        }
    }
}
