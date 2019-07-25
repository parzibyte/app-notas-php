<?php
namespace Parzibyte\Servicios;

class Seguridad
{
    public static function tokenAleatorioSeguro($longitud)
    {
        if ($longitud < 4) {
            $longitud = 4;
        }
        return bin2hex(random_bytes(($longitud - ($longitud % 2)) / 2));
    }

    public static function cifrarPalabraSecreta($palabraSecreta)
    {
        return password_hash(md5($palabraSecreta), PASSWORD_BCRYPT);
    }

    public static function coinciden($plana, $hasheada){
        return password_verify(md5($plana), $hasheada);
    }
}
