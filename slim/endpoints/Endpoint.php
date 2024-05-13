<?php

namespace Endpoints;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Endpoint
{
    protected $patronAlfabeticoEspaciado = "/^[A-Za-záéíóúñ ]{2,}$/";
    protected $patronAlfabetico = "/^[A-Za-záéíóúñ]{2,}$/";
    protected $patronEmail = "/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9]+.[a-zA-Z]{2,6}$/";
    protected $patronDocumento = "/^[1-9]{1}+[0-9]{6,7}$/";

    protected $data = [
        'Status' => '',
        'Mensaje' => [],
        'Codigo' => '',
        'Data' => null,
    ];

    protected abstract function crear(Request $request, Response $response);
    protected abstract function editar(Request $request, Response $response, $args);
    protected abstract function eliminar(Request $request, Response $response, $args);
    protected abstract function listar(Request $request, Response $response, $args);

    public function getConnection()
    {
        $dbhost = "db";
        $dbname = "seminariophp";
        $dbuser = "seminariophp";
        $dbpass = "seminariophp";


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

    protected function validarCantidades($body, $campo, $min)
    {
        return (is_int($body["$campo"]) && $body["$campo"] > $min);
    }

    protected function verificadorEspacios($string)
    {
        $contadorCaract = 0;
        if ($string != "") {
            for ($i = 0; $i < mb_strlen($string); $i++) {
                $caracter = mb_substr($string, $i, 1);

                if ($i > 0) {
                    if ($caracter != " ")
                        $contadorCaract++;
                    else if ($caracter == " ") { // Permite espacios en la cadena cuando hay por los menos 2 caracteres ya ingresados
                        if ($contadorCaract >= 2)
                            $contadorCaract = 0;
                        else
                            return false;
                    }
                } else if ($i == 0 && $caracter == " ") // Si ya hay un espacio al comienzo de la cadena, se toma como un valor incorrecto.
                    return false;
            }
        } else
            return false;

        return true;
    }

    protected function HTTPCodeError(Response $response)
    {
        if ($this->data != 200) {
            $response->getBody()->write(json_encode($this->data));
            return $response->withStatus($this->data['Codigo']);
        }
    }
}