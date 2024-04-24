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

            $nuevaLocalidad = $request->getParsedBody();

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
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }

        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function editar(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE id = :num');
            $localidad->execute([':num' => $id]);
            $existeID = $localidad->fetchColumn();

            if ($existeID) {
                $cambioNombre = $request->getParsedBody();

                $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre and id <> :num'); // Verifica si el nuevo nombre se encuentra usado ya en otro ID existente.
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
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }

        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function eliminar(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
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
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }
        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function listar(Request $request, Response $response, array $args)
    {
        try {
            $connection = $this->getConnection();
            $query = $connection->query('SELECT * FROM localidades');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Localidades recibidas correctamente.';
            $this->data['Data'] = $datos;
            $this->data['Codigo'] = '200';

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }
        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

}