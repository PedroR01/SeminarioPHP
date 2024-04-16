<?php
// Container Slim?
// ¿Vamos a ver todo lo que es la configuracion APACHE para un servidor? Yo no termino de entender que es lo que nos está permitiendo tener un servidor propio local desde Docker. Osea por ej yo se que con react haces un npm run dev y te levanta el localHost con los archivos pero con docker nose como funciona.

namespace Endpoints;

use Exception;
use PDO;
use Endpoints\Endpoint;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Localidades extends Endpoint
{

    public function crear(Request $request, Response $response) // ¿Tengo que validar que la localidad no tenga numeros en su nombre? DUDA
    {

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
    }

    public function editar(Request $request, Response $response, $args)
    {
        $direccion = '/localidades';

        try {
            $url = $_SERVER['REQUEST_URI'];
            $parts = explode('/', $url);
            $id = $parts[count($parts) - 2];
            $connection = $this->getConnection();

            $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE id = :num');
            $localidad->execute([':num' => $id]);
            $existeID = $localidad->fetchColumn();

            if ($existeID) {
                $data = $request->getParsedBody();
                $cambioNombre = $data['cambioNombre'];

                $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre and id <> :num');
                $localidad->execute([':nuevoNombre' => $cambioNombre]);
                $existeNombre = $localidad->fetchColumn();

                if (!$existeNombre) {
                    $localidad = $connection->prepare('UPDATE localidades SET nombre = :nuevoNombre WHERE id = :num');
                    $localidad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre]);
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
    }

    public function eliminar(Request $request, Response $response, $args)
    {
        $direccion = '/localidades';

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
    }

    public function listar(Request $request, Response $response, array $args)
    {

        try {

            $connection = $this->getConnection();
            $query = $connection->query('SELECT * FROM localidades');
            $tipos = $query->fetchAll(PDO::FETCH_ASSOC);

            $payload = json_encode([
                'status' => 'success',
                'code' => 200,
                'data' => $tipos
            ]);

            $response->getBody()->write($payload);
            return $response;

        } catch (Exception $e) {
            $payload = json_encode([
                'status' => 'error',
                'code' => $e->getCode()
            ]);
            $response->getBody()->write($payload);
            return $response;
        }
    }

}