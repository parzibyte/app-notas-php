<?php
namespace Parzibyte;

use Parzibyte\Servicios\SesionService;

class Redirect
{
    static $ruta = "";
    static $esto;
    static $goBack;

    private static function esto()
    {
        if (!self::$esto) {
            self::$esto = new self();
        }
        return self::$esto;
    }

    private static function redirect($ruta, $absoluta = false)
    {
        $verdaderaRuta = $absoluta ? $ruta : URL_RAIZ . $ruta;
        header("Location: " . $verdaderaRuta);
        exit;
    }

    public function do() {
        if (self::$goBack) {
            if (isset($_SERVER["HTTP_REFERER"])) {
                self::redirect($_SERVER["HTTP_REFERER"], true);
            } else {
                echo '<script type="text/javascript">history.go(-1)</script>';
                exit;
            }
        }
        self::redirect(self::$ruta);
    }

    public static function to($ruta)
    {
        self::$ruta = $ruta;
        return self::esto();
    }

    public static function back()
    {
        self::$goBack = true;
        return self::esto();
    }

    public static function with($datos)
    {
        SesionService::flash($datos);
        return self::esto();
    }
}
