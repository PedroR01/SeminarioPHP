<?php
namespace Endpoints;

use Exception;
use PDO;
use Endpoints\Endpoint;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Localidades extends Endpoint
{
    public function crear(Request $request, Response $response)
    {
        try {

            $connection = $this->getConnection();
            $nuevaLocalidad = $request->getParsedBody();
            $nombreSeteado = isset($nuevaLocalidad['nombre']);

            if ($nombreSeteado && $this->verificadorEspacios($nuevaLocalidad['nombre']) && $this->validador($this->patronAlfabeticoEspaciado, $nuevaLocalidad['nombre'])) {

                $tablaLocalidades = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre');
                $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad['nombre']]);
                $existe = $tablaLocalidades->fetchColumn();

                if (!$existe) {
                    $tablaLocalidades = $connection->prepare('INSERT INTO localidades (nombre) VALUES (:nuevoNombre)');
                    $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad['nombre']]); // Error: A scalar of type 'null' used as an array.
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'La localidad ha sido agregada correctamente.';
                    $this->data['Data'] = $nuevaLocalidad;
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Data'] = $nuevaLocalidad;
                    $this->data['Codigo'] = 400;
                    $this->data['Mensaje'] = 'La localidad ingresada ya se encuentra en la base da datos.';

                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Codigo'] = 400;
                if (!$nombreSeteado)
                    $this->data['Mensaje'] = 'Falta el campo "nombre" de localidad';
                else
                    $this->data['Mensaje'] = 'Campo "nombre" vacio o formato incorrecto.';
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

            $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE id = :num');
            $localidad->execute([':num' => $id]);
            $existeID = $localidad->fetchColumn();

            if ($existeID) {
                $cambioNombre = $request->getParsedBody();
                $nombreSeteado = isset($cambioNombre['nombre']);

                if ($nombreSeteado && $this->verificadorEspacios($cambioNombre['nombre']) && $this->validador($this->patronAlfabeticoEspaciado, $cambioNombre['nombre'])) {
                    $localidad = $connection->prepare('SELECT * FROM localidades WHERE nombre = :nuevoNombre and id <> :num');
                    $localidad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre['nombre']]);
                    $existeNombre = $localidad->fetch(PDO::FETCH_ASSOC);
                    // Lo deja cambiar al mismo nombre que ya tenia anteriormente
                    if (!$existeNombre) {
                        $localidad = $connection->prepare('UPDATE localidades SET nombre = :nuevoNombre WHERE id = :num');
                        $localidad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre['nombre']]);
                        $this->data['Status'] = 'Success';
                        $this->data['Mensaje'] = 'La localidad ha sido editada correctamente.';
                        $this->data['Data'] = [
                            'Nueva localidad' => $cambioNombre['nombre'],
                            'Anterior localidad' => $existeID
                        ];
                        $this->data['Codigo'] = 200;
                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Data'] = ['Localidad existente con ese nombre' => $existeNombre];
                        $this->data['Codigo'] = 400;
                        $this->data['Mensaje'] = 'La localidad ingresada ya se encuentra en la base da datos.';
                    }
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Codigo'] = 400;
                    if (!$nombreSeteado)
                        $this->data['Mensaje'] = 'Falta el campo "nombre" de localidad.';
                    else
                        $this->data['Mensaje'] = 'Campo "nombre" vacio o formato incorrecto.';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La localidad correspondiente al ID ingresado no existe.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = 400;
            }

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode(); //106

        }

        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);

    }

    public function eliminar(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            // No se puede eliminar una localidad si ya esta asociada a la tabla de propiedades
            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE localidad_id = :num');
            $propiedad->execute([':num' => $id]);
            $existePropiedad = $propiedad->fetch(PDO::FETCH_ASSOC);

            if (!$existePropiedad) {
                $localidad = $connection->prepare('SELECT * FROM localidades WHERE id = :num');
                $localidad->execute([':num' => $id]);
                $existeID = $localidad->fetch(PDO::FETCH_ASSOC);

                if ($existeID) {
                    $this->data['Data'] = $existeID;
                    $localidad = $connection->prepare('DELETE FROM localidades WHERE id = :num');
                    $localidad->execute([':num' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'La localidad ha sido eliminada correctamente.';
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'La localidad correspondiente al ID ingresado no existe.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 404;
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La localidad no se puede eliminar porque ya se encuentra asociada a una propiedad.';
                $this->data['Data'] = ['Propiedad asociada' => $existePropiedad];
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
            $query = $connection->query('SELECT * FROM localidades');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Localidades recibidas correctamente.';
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