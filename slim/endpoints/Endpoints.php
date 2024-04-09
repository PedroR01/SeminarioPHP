<?php
abstract class Endpoints
{
    protected $direccionBase;

    public function __construct($direccionBase)
    {
        $this->direccionBase = $direccionBase;
    }


    protected abstract function crear($app);
    protected abstract function editar($app);
    protected abstract function eliminar($app);
    protected abstract function listar($app);

    public function getConnection() // Esto podria ir en otro archivo aparte para org mejor el codigo.
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
    protected function getDireccion()
    {
        return $this->direccionBase;
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