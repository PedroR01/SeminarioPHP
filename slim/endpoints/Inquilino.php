<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Inquilino extends Endpoint
{


    public function crear(Request $request, Response $response)
    {
        $msg = '';
        try {

            $connection = $this->getConnection();

            $data = $request->getParsedBody();
            $nuevoInquilino = $data['nuevoInquilino'];
            // validacion de datos ingresados
            if ($this->validador($this->validador($this->patronNombreApellido, $nuevoInquilino[':apellido']) && $this->validador($this->patronNombreApellido, $nuevoInquilino[':nombre']) && $this->patronDocumento, $nuevoInquilino[':documento']) && $this->validador($this->patronEmail, $nuevoInquilino[':email'])) {
                $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE documento = :documento');

                $tablaInquilinos->execute([':documento' => $nuevoInquilino]);
                $existe = $tablaInquilinos->fetchColumn();

                if (!$existe) {
                    $tablaInquilinos = $connection->prepare('INSERT INTO inquilinos (apellido, nombre, documento, email, activo) VALUES (:apellido, :nombre, :documento, :email, :activo)');
                    var_dump($nuevoInquilino); // Imprime formateado los campos. Sirve para debugg principalmente
                    $tablaInquilinos->execute($nuevoInquilino);
                    $msg = 'El nuevo inquilino ha sido agregado correctamente';
                } else {
                    $msg = 'Error: El inquilino ingresado ya existe en la base de datos.';
                }
            } else // Si alguno de los campos con los datos no cumple con el formato deseado
                $msg = 'Error: Revisar el formato de los datos ingresados en los campos del inquilino';


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
        $direccion = '/inquilinos';

        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $inquilinos = $connection->prepare('SELECT nombre FROM inquilinos WHERE id = :num');
            $inquilinos->execute([':num' => $id]);
            $existeID = $inquilinos->fetchColumn();

            if ($existeID) {
                $data = $request->getParsedBody();
                $cambioNombre = $data['cambioNombre'];

                $inquilinos = $connection->prepare('SELECT nombre FROM inquilinos WHERE nombre = :nuevoNombre');
                $inquilinos->execute([':nuevoNombre' => $cambioNombre]);
                $existeNombre = $inquilinos->fetchColumn();

                if (!$existeNombre) {
                    $inquilinos = $connection->prepare('UPDATE inquilinos SET id = :num, nombre = :nuevoNombre WHERE id = :num');
                    $inquilinos->execute([':num' => $id, ':nuevoNombre' => $cambioNombre]);
                    $msg = 'Cambio de nombre del inquilino realizado.';
                } else
                    $msg = 'Error: El nombre ya existe.';
            } else
                $msg = [
                    'Error ' => 'El inquilino correspondiente al ID ingresado no existe.',
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
        $direccion = '/inquilinos';
        try {
            $id = $args['id'];
            $connection = $this->getConnection();


            $inquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :num');
            $inquilinos->execute([':num' => $id]);
            $existeID = $inquilinos->fetchColumn();

            if ($existeID) {
                $inquilinos = $connection->prepare('DELETE FROM inquilinos WHERE id = :num');
                $inquilinos->execute([':num' => $id]);
                $msg = 'El inquilino buscado ha sido eliminado correctamente.';
            } else
                $msg = [
                    'Error ' => 'El inquilino correspondiente al ID ingresado no existe.',
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
            $query = $connection->query('SELECT * FROM inquilinos');
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

    public function verInquilino($app)
    {
        $direccion = "/inquilinos";
        $app->get("$direccion/{id}/verInquilino", function ($request, Response $response, $args) {
            try {
                $id = $args['id'];
                $connection = $this->getConnection();
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

    public function historial($app)
    {
        $direccion = "/inquilinos"; //En la consigna dice $idInquilino, en que se diferencia del Id normal?
        $app->get("$direccion/{id}/reservas", function ($request, Response $response, $args) {
            try {
                $id = $args['id'];
                $connection = $this->getConnection();
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
}