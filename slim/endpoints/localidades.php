<?php

// ¿Vamos a ver todo lo que es la configuracion APACHE para un servidor? Yo no termino de entender que es lo que nos está permitiendo tener un servidor propio local desde Docker. Osea por ej yo se que con react haces un npm run dev y te levanta el localHost con los archivos pero con docker nose como funciona.
// ¿Por que el $request aunque no lo llame o use lo necesito igual?¿Se hara desde el propio metodo de la app (como post o get)?
// ¿A que se le dice Container en SLIM?
// Estas dos importaciones o usos de Psr convendria tenerlos en un archivo aparte, asi como tambien la funcion getConnection(), para accederlos desde todos los endpoints de todos los archivos????
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Podria crear una interface de endpoints y que de ahi deriven subClases para cada tipo.

function getConnection() // Esto podria ir en otro archivo aparte para org mejor el codigo.
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

function crear($app)
{
    $direccion = '/localidades';
    $app->post("$direccion/crear", function (Request $request, Response $response) {

        $connection = getConnection();

        $data = $request->getParsedBody();
        $nuevaLocalidad = $data['nuevaLocalidad'];

        $tablaLocalidades = $connection->prepare('SELECT COUNT(*) FROM localidades WHERE nombre = :cadena');
        $tablaLocalidades->execute([':cadena' => $nuevaLocalidad]);
        $existe = $tablaLocalidades->fetchColumn();

        //  ¿Aca como podria usar un try{}catch{}?
        if (!$existe) {
            $tablaLocalidades = $connection->prepare('INSERT INTO localidades (nombre) VALUES (:cadena)');
            $tablaLocalidades->execute([':cadena' => $nuevaLocalidad]);
            $msj = 'La localidad ha sido agregada correctamente';
        } else {
            $msj = 'Error: La localidad ingresada ya existe en la base de datos.';
        }


        return ($response->getBody()->write($msj));
    });
}

function editar($app)
{
    $direccion = '/localidades';
    $id = '';
    $app->put("$direccion/$id/editar", function (Request $request, Response $response) {});
}

function eliminar($app)
{
    $direccion = '/localidades';
    $id = '';
    $app->delete("$direccion/$id/eliminar", function (Request $request, Response $response) {});
}

function listar($app)
{
    $direccion = '/localidades'; // habria que hacerla global a la clase
    $app->get("$direccion/listar", function (Request $request, Response $response) { // El primer parametro es la dirección del endpoint, URL por el cual se accede a la funcion luego desarrollada en el segundo parametro.

        // En el try entra cuando todo funciona bien.
        try {

            $connection = getConnection(); // Contiene la conexion con la base de datos. Metodos PDO

            // Recibir datos a partir del objeto PDO
            $query = $connection->query('SELECT * FROM localidades');

            // Hacer un fetch all del objeto PDO
            $tipos = $query->fetchAll(PDO::FETCH_ASSOC);

            // El fetch devuelve de a 1 fila o algo asi, por lo que hay que ir guardando todo en un array
            $payload = json_encode([
                'status' => 'success',
                'code' => 200,
                'data' => $tipos
            ]);

            // El array hay que hacerle un parse JSON para mostrarlo en el body con el write();
            $response->getBody()->write($payload);
            return $response; //withHeader('Content-Type', 'application/son'); // Si no llega a interpretar correctamente el JSON

        } catch (PDOException $e) {
            $payload = json_encode([
                'status' => 'error',
                'code' => $e->getCode()
            ]);
            $response->getBody()->write($payload);
            return $response; //withHeader(...); // Si no llega a interpretar correctamente el JSON
        }
    });
}

