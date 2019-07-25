<?php
require __DIR__ . '/vendor/autoload.php'; #Cargar todas las dependencias

use Parzibyte\Servicios\Comun;
use Parzibyte\Servicios\SesionService;
use Parzibyte\Servicios\Twig;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteCollector;

define("DIRECTORIO_RAIZ", __DIR__);
define("DIRECTORIO_APLICACION", DIRECTORIO_RAIZ . "/app");
define("RUTA_LOGS", __DIR__ . DIRECTORY_SEPARATOR . "logs");
define("URL_RAIZ", Comun::env("URL_RAIZ"));
define("URL_DIRECTORIO_PUBLICO", URL_RAIZ . "/public");
define("RUTA_API", URL_RAIZ . "/api");
define("NOMBRE_APLICACION", "App de notas");
define("AUTOR", "Luis Cabrera Benito a.k.a parzibyte");
define("WEB_AUTOR", "https://parzibyte.me/blog");
ini_set('display_errors', 0);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/logs/" . date("Y-m-d") . ".log");
if (!file_exists(RUTA_LOGS)) {
    mkdir(RUTA_LOGS);
}

function view($nombre, $datos = [])
{
    echo Twig::obtener()->render("$nombre.twig", $datos);
    return;
}

function getview($nombre, $datos = [])
{
    return Twig::obtener()->render("$nombre.twig", $datos);
}

function json($datos)
{
    header("Content-type: application/json");
    echo json_encode($datos);
    return;
}

function redirect($ruta)
{
    header("Location: " . URL_RAIZ . $ruta);
    exit;
}

$enrutador = require_once("rutas.php");

$despachador = new Dispatcher($enrutador->getData());
$rutaCompleta = $_SERVER["REQUEST_URI"];
$metodo = $_SERVER['REQUEST_METHOD'];
$rutaLimpia = parsearUrl($rutaCompleta);
try {
    $despachador->dispatch($metodo, $rutaLimpia);
} catch (HttpRouteNotFoundException $e) {
    echo "Error: Ruta [ $rutaLimpia ] no encontrada";
} catch (HttpMethodNotAllowedException $e) {
    echo "Error: Ruta [ $rutaLimpia ] encontrada pero método [ $metodo ] no permitido";
}
function parsearUrl($uri)
{
    return implode('/',
        array_slice(
            explode('/', $uri), 2));
}
