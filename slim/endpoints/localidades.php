<?php
// ¿Vamos a ver todo lo que es la configuracion APACHE para un servidor? Yo no termino de entender que es lo que nos está permitiendo tener un servidor propio local desde Docker. Osea por ej yo se que con react haces un npm run dev y te levanta el localHost con los archivos pero con docker nose como funciona.

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

            $tablaLocalidades = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre');
            $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad['nombre']]);
            $existe = $tablaLocalidades->fetchColumn();

            if (!$existe && isset($nuevaLocalidad)) {
                $tablaLocalidades = $connection->prepare('INSERT INTO localidades (nombre) VALUES (:nuevoNombre)');
                $tablaLocalidades->execute([':nuevoNombre' => $nuevaLocalidad['nombre']]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'La localidad ha sido agregada correctamente.';
                $this->data['Data'] = $nuevaLocalidad;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevaLocalidad;
                $this->data['Codigo'] = '400';
                if ($existe)
                    $this->data['Mensaje'] = 'La localidad ingresada ya se encuentra en la base da datos.';
                else
                    $this->data['Mensaje'] = 'No se ha ingresado ningún nombre de localidad';
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

            // No se puede editar una localidad si ya esta asociada a la tabla de propiedades
            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE localidad_id = :num');
            $propiedad->execute([':num' => $id]);
            $existePropiedad = $propiedad->fetch(PDO::FETCH_ASSOC);

            if (!$existePropiedad) {
                if ($existeID) {
                    $cambioNombre = $request->getParsedBody();

                    $localidad = $connection->prepare('SELECT nombre FROM localidades WHERE nombre = :nuevoNombre and id <> :num'); // Verifica si el nuevo nombre se encuentra usado ya en otro ID existente.
                    $localidad->execute([':nuevoNombre' => $cambioNombre]);
                    $existeNombre = $localidad->fetchColumn();

                    if (!$existeNombre && isset($cambioNombre)) {
                        $localidad = $connection->prepare('UPDATE localidades SET nombre = :nuevoNombre WHERE id = :num');
                        $localidad->execute([':num' => $id, ':nuevoNombre' => $cambioNombre]);
                        $this->data['Status'] = 'Success';
                        $this->data['Mensaje'] = 'La localidad ha sido agregada correctamente.';
                        $this->data['Data'] = $cambioNombre;
                        $this->data['Codigo'] = '200';
                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Data'] = $cambioNombre;
                        $this->data['Codigo'] = '400';
                        if ($existeNombre)
                            $this->data['Mensaje'] = 'La localidad ingresada ya se encuentra en la base da datos.';
                        else
                            $this->data['Mensaje'] = 'No se ha ingresado ningún nombre de localidad';
                    }
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'La localidad correspondiente al ID ingresado no existe.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = '400';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La localidad no se puede editar porque ya se encuentra asociada a una propiedad.';
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
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'La localidad correspondiente al ID ingresado no existe.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = '400';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La localidad no se puede eliminar porque ya se encuentra asociada a una propiedad.';
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