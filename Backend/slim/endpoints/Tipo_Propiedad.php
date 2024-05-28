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
            $nombreSeteado = isset($nuevoTipo['nombre']);

            if ($nombreSeteado && $this->verificadorEspacios($nuevoTipo['nombre']) && $this->validador($this->patronAlfabeticoEspaciado, $nuevoTipo['nombre'])) {

                $tablaTipos = $connection->prepare('SELECT * FROM tipo_propiedades WHERE nombre = :nuevoTipo');
                $tablaTipos->execute([':nuevoTipo' => $nuevoTipo['nombre']]);
                $existe = $tablaTipos->fetchColumn();

                if (!$existe) {
                    $tablaTipos = $connection->prepare('INSERT INTO tipo_propiedades (nombre) VALUES (:nuevoTipo)');
                    $tablaTipos->execute([':nuevoTipo' => $nuevoTipo['nombre']]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Tipo de propiedad agregado correctamente';
                    $this->data['Data'] = $nuevoTipo;
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Data'] = $nuevoTipo;
                    $this->data['Codigo'] = 400;
                    $this->data['Mensaje'] = 'El tipo de localidad ingresada ya se encuentra en la base da datos.';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Codigo'] = 400;
                if (!$nombreSeteado)
                    $this->data['Mensaje'] = 'Falta el campo "nombre" de tipo propiedad.';
                else
                    $this->data['Mensaje'] = 'Campo vacio o con formato incorrecto.';
            }

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        } catch (\PDOException $e) {
            $this->data['Status'] = 'Throw Server/DB Exception';
            $this->data['Mensaje'] = $e->getMessage() . " " . $e->getCode();
            $this->data['Codigo'] = 500;
        }

        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);


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
                $nombreSeteado = isset($cambioNombre['nombre']);
                if ($nombreSeteado && $this->verificadorEspacios($cambioNombre['nombre']) && $this->validador($this->patronAlfabeticoEspaciado, $cambioNombre['nombre'])) {

                    $tipoPropiedad = $connection->prepare('SELECT nombre FROM tipo_propiedades WHERE nombre = :nuevoNombre AND id <> :idURL');
                    $tipoPropiedad->execute([':idURL' => $id, ':nuevoNombre' => $cambioNombre['nombre']]);
                    $existeNombre = $tipoPropiedad->fetchColumn();

                    if (!$existeNombre) {
                        $tipoPropiedad = $connection->prepare('UPDATE tipo_propiedades SET id = :idURL, nombre = :nuevoNombre WHERE id = :idURL');
                        $tipoPropiedad->execute([':idURL' => $id, ':nuevoNombre' => $cambioNombre['nombre']]);
                        $this->data['Status'] = 'Success';
                        $this->data['Mensaje'] = 'Cambios en el tipo de propiedad realizados correctamente';
                        $this->data['Data'] = [
                            'Nuevo tipo propiedad' => $cambioNombre['nombre'],
                            'Anterior tipo propiedad' => $existeID
                        ];
                        $this->data['Codigo'] = 200;
                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Data'] = $cambioNombre['nombre'];
                        $this->data['Codigo'] = 400;
                        $this->data['Mensaje'] = 'Ya existe otro tipo de propiedad con el mismo nombre.';
                    }
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Codigo'] = 400;
                    if (!$nombreSeteado)
                        $this->data['Mensaje'] = 'Falta el campo "nombre" de tipo propiedad.';
                    else
                        $this->data['Mensaje'] = 'Campo vacio o con formato incorrecto.';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = 404;
            }

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        } catch (\PDOException $e) {
            $this->data['Status'] = 'Throw Server/DB Exception';
            $this->data['Mensaje'] = $e->getMessage() . " " . $e->getCode();
            $this->data['Codigo'] = 500;
        }

        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);

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

            if (!$existePropiedad) {
                $tipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :num');
                $tipoPropiedad->execute([':num' => $id]);
                $existeID = $tipoPropiedad->fetch(PDO::FETCH_ASSOC);

                if ($existeID) {
                    $this->data['Data'] = ['Tipo' => $existeID];
                    $tipoPropiedad = $connection->prepare('DELETE FROM tipo_propiedades WHERE id = :num');
                    $tipoPropiedad->execute([':num' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Tipo de propiedad eliminado correctamente de la base de datos.';
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 404;
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'El tipo de propiedad no se puede eliminar porque ya se encuentra asociada a una propiedad.';
                $this->data['Data'] = ['Propiedad' => $existePropiedad];
                $this->data['Codigo'] = 400;
            }

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        } catch (\PDOException $e) {
            $this->data['Status'] = 'Throw Server/DB Exception';
            $this->data['Mensaje'] = $e->getMessage() . " " . $e->getCode();
            $this->data['Codigo'] = 500;
        }
        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);

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
            $this->data['Codigo'] = 200;

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        } catch (\PDOException $e) {
            $this->data['Status'] = 'Throw Server/DB Exception';
            $this->data['Mensaje'] = $e->getMessage() . " " . $e->getCode();
            $this->data['Codigo'] = 500;
        }
        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);

    }
}
