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
        try {

            $connection = $this->getConnection();

            $nuevoTipo = $request->getParsedBody();

            $tablaTipos = $connection->prepare('SELECT * FROM tipo_propiedades WHERE nombre = :nuevoTipo');
            $tablaTipos->execute([':nuevoTipo' => $nuevoTipo['nuevoTipo']]);
            $existe = $tablaTipos->fetchColumn();

            if (!$existe) {
                $tablaTipos = $connection->prepare('INSERT INTO tipo_propiedades (nombre) VALUES (:nuevoTipo)');
                $tablaTipos->execute([':nuevoTipo' => $nuevoTipo['nuevoTipo']]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Tipo de propiedad agregado correctamente';
                $this->data['Data'] = $nuevoTipo;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: El tipo de propiedad ingresado ya existe en la base de datos.';
                $this->data['Data'] = $nuevoTipo;
                $this->data['Codigo'] = '?';
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

            $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE id = :idURL');
            $tipoPropiedad->execute([':idURL' => $id]);
            $existeID = $tipoPropiedad->fetchColumn();

            if ($existeID) {
                $cambioNombre = $request->getParsedBody();

                $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE nombre = :nuevoNombre AND id <> :idURL');
                $tipoPropiedad->execute([':nuevoNombre' => $cambioNombre]);
                $existeNombre = $tipoPropiedad->fetchColumn();

                if (!$existeNombre) {
                    $tipoPropiedad = $connection->prepare('UPDATE tipo_propiedades SET id = :idURL, nombre = :nuevoNombre WHERE id = :idURL');
                    $tipoPropiedad->execute([':idURL' => $id, ':nuevoNombre' => $cambioNombre]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Cambios en el tipo de propiedad realizados correctamente';
                    $this->data['Data'] = $cambioNombre['nuevoNombre'];
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'Ya existe otro tipo de propiedad con el mismo nombre.';
                    $this->data['Data'] = $cambioNombre['nuevoNombre'];
                    $this->data['Codigo'] = '?';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: El ID ingresado no se encuentra en la base de datos.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = '404';
            }
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


            $tipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :num');
            $tipoPropiedad->execute([':num' => $id]);
            $existeID = $tipoPropiedad->fetchColumn();

            if ($existeID) {
                $tipoPropiedad = $connection->prepare('DELETE FROM tipo_propiedades WHERE id = :num');
                $tipoPropiedad->execute([':num' => $id]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Tipo de propiedad eliminado correctamente de la base de datos.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: El ID ingresado no se encuentra en la base de datos.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = '404';
            }
        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }
        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function listar(Request $request, Response $response, $args)
    {
        try {

            $connection = $this->getConnection();
            $query = $connection->query('SELECT * FROM tipo_propiedades');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Tipos de propiedades recibidos correctamente.';
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
