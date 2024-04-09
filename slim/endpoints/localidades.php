<?php

// ¿Vamos a ver todo lo que es la configuracion APACHE para un servidor? Yo no termino de entender que es lo que nos está permitiendo tener un servidor propio local desde Docker. Osea por ej yo se que con react haces un npm run dev y te levanta el localHost con los archivos pero con docker nose como funciona.
// ¿Por que el $request aunque no lo llame o use lo necesito igual?¿Se hara desde el propio metodo de la app (como post o get)?
// ¿A que se le dice Container en SLIM?
// Estas dos importaciones o usos de Psr convendria tenerlos en un archivo aparte, asi como tambien la funcion getConnection(), para accederlos desde todos los endpoints de todos los archivos????
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Localidades extends Endpoints
{

    // !PREGUNTA: Preguntar por el codigo repetido entre funciones...
    public static function crear($app)
    {
        $direccion = '/localidades';
        $app->post("$direccion/crear", function (Request $request, Response $response) {
            $msg = '';
            try {

                $connection = $this->getConnection();

                $data = $request->getParsedBody();
                $nuevaLocalidad = $data['nuevaLocalidad'];

                $tablaLocalidades = $connection->prepare('SELECT * FROM localidades WHERE nombre = :nuevoNombre');
                $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad]);
                $existe = $tablaLocalidades->fetchColumn();

                if (!$existe) {
                    $tablaLocalidades = $connection->prepare('INSERT INTO localidades (nombre) VALUES (:nuevoNombre)');
                    $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad]);
                    $msg = 'La localidad ha sido agregada correctamente';
                } else {
                    $msg = 'Error: La localidad ingresada ya existe en la base de datos.';
                }

            } catch (Exception $e) {
                $msg = [
                    'Status ' => 'ERROR',
                    'Mensaje ' => $e->getMessage(),
                    'Codigo ' => $e->getCode(),
                    'HTTP Code ' => http_response_code(),
                ];

            }

            $response->getBody()->write(json_encode($msg));
            return $response;
        });
    }

    public function editar($app)
    {
        $direccion = '/localidades';
        $app->put("$direccion/{id}/editar", function (Request $request, Response $response) {
            try {
                $url = $_SERVER['REQUEST_URI'];
                $parts = explode('/', $url);
                $id = $parts[count($parts) - 2];
                $connection = $this->getConnection();

                $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE id = :num');
                $localidad->execute([':num' => $id]);
                $existeID = $localidad->fetchColumn(); // !PREGUNTA: Al buscar un unico valor, hay una mejor forma de comprobar si existe o mejorar el codigo?

                if ($existeID) {
                    $data = $request->getParsedBody();
                    $cambioNombre = $data['cambioNombre'];

                    $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre');
                    $localidad->execute([':nuevoNombre' => $cambioNombre]);
                    $existeNombre = $localidad->fetchColumn();

                    if (!$existeNombre) {
                        $localidad = $connection->prepare('UPDATE localidades SET id = :num, nombre = :nuevoNombre WHERE id = :num');
                        $localidad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre]); // !PREGUNTA: Hay alguna forma de no tener que pasarle todas las columnas? Estoy reescribiendo el valor del ID cuando no hace falta...
                        $msg = 'Cambio de nombre de localidad realizado.';
                    } else
                        $msg = 'Error: El nombre ya existe.';
                } else
                    $msg = [
                        'Error ' => 'La localidad correspondiente al ID ingresado no existe.',
                        'ID buscado ' => $id,
                    ];

            } catch (Exception $e) {
                $msg = [
                    'Status ' => 'ERROR',
                    'Mensaje ' => $e->getMessage(),
                    'Codigo ' => $e->getCode(),
                    'HTTP Code ' => http_response_code(),
                ];
            }

            $response->getBody()->write(json_encode($msg));
            return $response;
        });
    }

    public function eliminar($app)
    {
        $direccion = '/localidades';

        // !PREGUNTA: Dice que el nombre debe ser único, pero no dice nada del ID... ¿Puede haber ID repetidos? ¿La busqueda debe ser por ID o por nombre en tal caso?
        $app->delete("$direccion/{id}/eliminar", function (Request $request, Response $response) {
            try {
                $url = $_SERVER['REQUEST_URI'];
                $parts = explode('/', $url);
                $id = $parts[count($parts) - 2];
                $connection = $this->getConnection();


                $localidad = $connection->prepare('SELECT * FROM localidades WHERE id = :num');
                $localidad->execute([':num' => $id]);
                $existeID = $localidad->fetchColumn();

                if ($existeID) {
                    $localidad = $connection->prepare('DELETE FROM localidades WHERE id = :num');
                    $localidad->execute([':num' => $id]);
                    $msg = 'La localidad buscada ha sido eliminada correctamente.';
                } else
                    $msg = [
                        'Error ' => 'La localidad correspondiente al ID ingresado no existe.',
                        'ID buscado ' => $id,
                    ];

            } catch (Exception $e) {
                $msg = [
                    'Status ' => 'ERROR',
                    'Mensaje ' => $e->getMessage(),
                    'Codigo ' => $e->getCode(),
                    'HTTP Code ' => http_response_code(),
                ];
            }
            $response->getBody()->write(json_encode($msg));
            return $response;
        });
    }

    public function listar($app) // !PREGUNTA: Si elimino una localidad, y no es la ultima en ser añadida, su ID es perdido. Osea al listar la tabla quedaria 1:.. 3:.. 4:... ¿Deberia reordenarlos en este caso con el metodo listar, cambiar algo desde el metodo crear o que se cree si el ID no se encuentra en el metodo Editar?
    {
        $direccion = '/localidades'; // habria que hacerla global a la clase
        $app->get("$direccion/listar", function (Request $request, Response $response) { // El primer parametro es la dirección del endpoint, URL por el cual se accede a la funcion luego desarrollada en el segundo parametro.

            // En el try entra cuando todo funciona bien.
            try {

                $connection = $this->getConnection(); // Contiene la conexion con la base de datos. Metodos PDO

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

}