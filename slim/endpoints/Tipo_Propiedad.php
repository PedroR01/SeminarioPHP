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

            if (!$existe && isset($nuevaLocalidad)) {
                $tablaTipos = $connection->prepare('INSERT INTO tipo_propiedades (nombre) VALUES (:nuevoTipo)');
                $tablaTipos->execute([':nuevoTipo' => $nuevoTipo['nuevoTipo']]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Tipo de propiedad agregado correctamente';
                $this->data['Data'] = $nuevoTipo;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevoTipo;
                $this->data['Codigo'] = '400';
                if ($existe)
                    $this->data['Mensaje'] = 'El tipo de localidad ingresada ya se encuentra en la base da datos.';
                else
                    $this->data['Mensaje'] = 'No se ha ingresado ningún nombre para el tipo de propiedad';
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

            // No se puede editar un tipo de propiedad si ya esta asociada a la tabla de propiedades
            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE tipo_propiedad_id = :num');
            $propiedad->execute([':num' => $id]);
            $existePropiedad = $propiedad->fetch(PDO::FETCH_ASSOC);

            if (!$existePropiedad) {
                if ($existeID) {
                    $cambioNombre = $request->getParsedBody();

                    $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE nombre = :nuevoNombre AND id <> :idURL');
                    $tipoPropiedad->execute([':nuevoNombre' => $cambioNombre['nombre']]);
                    $existeNombre = $tipoPropiedad->fetchColumn();

                    if (!$existeNombre && isset($cambioNombre)) {
                        $tipoPropiedad = $connection->prepare('UPDATE tipo_propiedades SET id = :idURL, nombre = :nuevoNombre WHERE id = :idURL');
                        $tipoPropiedad->execute([':idURL' => $id, ':nuevoNombre' => $cambioNombre['nombre']]);
                        $this->data['Status'] = 'Success';
                        $this->data['Mensaje'] = 'Cambios en el tipo de propiedad realizados correctamente';
                        $this->data['Data'] = $cambioNombre['nombre'];
                        $this->data['Codigo'] = '200';
                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Data'] = $cambioNombre['nombre'];
                        $this->data['Codigo'] = '400';
                        if ($existeNombre)
                            $this->data['Mensaje'] = 'Ya existe otro tipo de propiedad con el mismo nombre.';
                        else
                            $this->data['Mensaje'] = 'No se ha ingresado ningún nombre de tipo de propiedad.';
                    }
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = '404';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'El tipo de propiedad no se puede editar porque ya se encuentra asociada a una propiedad.';
                $this->data['Data'] = $existePropiedad;
                $this->data['Codigo'] = '400';
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

            // No se puede eliminar un tipo de propiedad si ya esta asociada a la tabla de propiedades
            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE tipo_propiedad_id = :num');
            $propiedad->execute([':num' => $id]);
            $existePropiedad = $propiedad->fetch(PDO::FETCH_ASSOC);

            if ($existePropiedad) {
                $tipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :num');
                $tipoPropiedad->execute([':num' => $id]);
                $existeID = $tipoPropiedad->fetch(PDO::FETCH_ASSOC);

                if ($existeID) {
                    $this->data['Data'] = $existeID;
                    $tipoPropiedad = $connection->prepare('DELETE FROM tipo_propiedades WHERE id = :num');
                    $tipoPropiedad->execute([':num' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Tipo de propiedad eliminado correctamente de la base de datos.';
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = '404';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'El tipo de propiedad no se puede eliminar porque ya se encuentra asociada a una propiedad.';
                $this->data['Data'] = $existePropiedad;
                $this->data['Codigo'] = '400';
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
