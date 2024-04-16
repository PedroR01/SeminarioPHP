<?php

namespace Endpoints;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Endpoint
{
    // Permite una secuencia de al menos 2 caracteres alfabéticos (mayúsculas o minúsculas) o letras acentuadas (como á, é, í, ó, ú, ñ).
    protected $patronNombreApellido = "/^[A-Za-záéíóúñ]{2,}+$/";

    // [a-zA-Z0-9_.-]+: Permite una secuencia de caracteres alfanuméricos, puntos, guiones bajos o guiones antes del símbolo “@”.
    // [a-zA-Z0-9]+: Luego del @, permite una secuencia de caracteres alfanuméricos.
    // \.: Permite el punto que separa el dominio de nivel superior.
    // [a-zA-Z]{2,6}: Permite de 2 a 6 caracteres alfabéticos (por ejemplo, “com”, “org”, “es”)
    protected $patronEmail = "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,6}$/";

    // Permite una secuencia de caracteres númericos según el criterio del DNI argentino?
    protected $patronDocumento = "/^[0-9]{2}+\.[0-9]{3}\.[0-9]{3}$/";

    protected abstract function crear(Request $request, Response $response);
    protected abstract function editar(Request $request, Response $response, $args);
    protected abstract function eliminar(Request $request, Response $response, $args);
    protected abstract function listar(Request $request, Response $response, array $args);

    public function getConnection() // Contiene la conexion con la base de datos. Metodos PDO
    {
        $dbhost = "db";
        $dbname = "seminariophp";
        $dbuser = "seminariophp";
        $dbpass = "seminariophp";

        // PDO es una clase que nos permite realizar una "conexion" entre la base de datos y PHP. Provee de metodos (docu. PHP)
        $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    protected function validador($patron, $cadena)
    {
        if (preg_match($patron, $cadena))
            return true;

        return false;
    }

    protected function redireccion($errorMsg)
    {
        $redirectUrl = '/';
        //redireccion o mostrar una pag rel a cada error
        if (http_response_code() === 500) {
            header("Location: $redirectUrl");
        }
        if (http_response_code() === 404) {
            header("Location: $redirectUrl");
        }
        $response = json_encode($errorMsg);
        echo $response;
        exit;
    }
}