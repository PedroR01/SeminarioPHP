<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Tipo_Propiedad extends Endpoint
{
    public function crear(Request $request, Response $response)
    {
        $msg = '';
        try {

            $connection = $this->getConnection();

            $data = $request->getParsedBody();
            $nuevoTipo = $data['nuevoTipo'];

            $tablaTipos = $connection->prepare('SELECT * FROM tipo_propiedades WHERE nombre = :nuevoNombre');
            $tablaTipos->execute([':nuevoNombre' => $nuevoTipo]);
            $existe = $tablaTipos->fetchColumn();

            if (!$existe) {
                $tablaTipos = $connection->prepare('INSERT INTO tipo_propiedades (nombre) VALUES (:nuevoNombre)');
                $tablaTipos->execute([':nuevoNombre' => $nuevoTipo]);
                $msg = 'El tipo de propiedad ha sido agregada correctamente';
            } else {
                $msg = 'Error: El tipo de propiedad ingresado ya existe en la base de datos.';
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
        $direccion = '/tipos_propiedad';

        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE id = :num');
            $tipoPropiedad->execute([':num' => $id]);
            $existeID = $tipoPropiedad->fetchColumn();

            if ($existeID) {
                $data = $request->getParsedBody();
                $cambioNombre = $data['cambioNombre'];

                $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE nombre = :nuevoNombre');
                $tipoPropiedad->execute([':nuevoNombre' => $cambioNombre]);
                $existeNombre = $tipoPropiedad->fetchColumn();

                if (!$existeNombre) {
                    $tipoPropiedad = $connection->prepare('UPDATE tipo_propiedades SET id = :num, nombre = :nuevoNombre WHERE id = :num');
                    $tipoPropiedad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre]);
                    $msg = 'Cambio de nombre del tipo de propiedad realizado.';
                } else
                    $msg = 'Error: El nombre ya existe.';
            } else
                $msg = [
                    'Error ' => 'El tipo de propiedad correspondiente al ID ingresado no existe.',
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
        $direccion = '/tipos_propiedad';

        try {
            $id = $args['id'];
            $connection = $this->getConnection();


            $tipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :num');
            $tipoPropiedad->execute([':num' => $id]);
            $existeID = $tipoPropiedad->fetchColumn();

            if ($existeID) {
                $tipoPropiedad = $connection->prepare('DELETE FROM tipo_propiedades WHERE id = :num');
                $tipoPropiedad->execute([':num' => $id]);
                $msg = 'El tipo de propiedad buscado ha sido eliminado correctamente.';
            } else
                $msg = [
                    'Error ' => 'El tipo de propiedad correspondiente al ID ingresado no existe.',
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

    public function listar(Request $request, Response $response, $args)
    {
        try {

            $connection = $this->getConnection();
            $query = $connection->query('SELECT * FROM tipo_propiedades');
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
