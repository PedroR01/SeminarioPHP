<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Inquilino extends Endpoint
{
    private $eFormato = [
        'apellido' => "",
        'nombre' => "",
        'documento' => "",
        'email' => "",
        'activo' => ""
    ];

    public function crear(Request $request, Response $response) // Se puede repetir otra persona con los mismos datos pero dif documento... DUDA
    {


        try {

            $connection = $this->getConnection();

            $nuevoInquilino = $request->getParsedBody();

            $vApellido = $this->validador($this->patronNombreApellido, $nuevoInquilino['apellido']);
            $vNombre = $this->validador($this->patronNombreApellido, $nuevoInquilino['nombre']);
            $vEmail = $this->validador($this->patronEmail, $nuevoInquilino['email']);
            $vDocumento = $this->validador($this->patronDocumento, $nuevoInquilino['documento']);
            $vActivo = ($nuevoInquilino['activo'] != null);

            if ($vApellido && $vNombre && $vEmail && $vDocumento && $vActivo) {
                $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE documento = :documento');
                $tablaInquilinos->execute([':documento' => $nuevoInquilino['documento']]);
                $existe = $tablaInquilinos->fetchColumn();

                if (!$existe) {
                    $tablaInquilinos = $connection->prepare('INSERT INTO inquilinos (apellido, nombre, documento, email, activo) VALUES (:apellido, :nombre, :documento, :email, :activo)');
                    $tablaInquilinos->execute($nuevoInquilino);

                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Inquilino agregado correctamente';
                    $this->data['Data'] = $nuevoInquilino;
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'Error: El inquilino ingresado ya existe en la base de datos.';
                    $this->data['Data'] = $nuevoInquilino;
                    $this->data['Codigo'] = '?';
                }
            } else {
                if (!$vApellido)
                    $eFormato['apellido'] = 'El formato del apellido enviado es incorrecto, no se permite ningun caracter que no sea alfanumerico.';
                if (!$vNombre)
                    $eFormato['nombre'] = 'El formato del nombre enviado es incorrecto, no se permite ningun caracter que no sea alfanumerico.';
                if (!$vDocumento)
                    $eFormato['documento'] = 'El formato del documento enviado es incorrecto, debe seguir el patron xx.xxx.xxx o x.xxx.xxx';
                if (!$vEmail)
                    $eFormato['email'] = 'El formato del email enviado es incorrecto.';
                if (!$vActivo)
                    $eFormato['activo'] = 'El formato del estado del inquilino enviado es incorrecto. Solo admite valores true o false.';

                //var_dump($nuevoInquilino); // Imprime formateado los campos. Sirve para debugg principalmente

                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = $eFormato;
                $this->data['Data'] = $nuevoInquilino;
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
    public function editar(Request $request, Response $response, $args) // No me deja ponerle de valor false a activo... DUDA
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $inquilinos = $connection->prepare('SELECT apellido, nombre, documento, email, activo FROM inquilinos WHERE id = :idURL');
            $inquilinos->execute([':idURL' => $id]);
            $existeID = $inquilinos->fetchColumn();

            if ($existeID) {
                $cambios = $request->getParsedBody();

                $vApellido = $this->validador($this->patronNombreApellido, $cambios['apellido']);
                $vNombre = $this->validador($this->patronNombreApellido, $cambios['nombre']);
                $vEmail = $this->validador($this->patronEmail, $cambios['email']);
                $vDocumento = $this->validador($this->patronDocumento, $cambios['documento']);
                $vActivo = ($cambios['activo'] != null);

                if ($vApellido && $vNombre && $vEmail && $vDocumento && $vActivo) {
                    $inquilinos = $connection->prepare('SELECT apellido, nombre, documento, email, activo FROM inquilinos WHERE documento = :nuevoDocumento AND id <> :idURL');
                    $inquilinos->execute([
                        ':nuevoDocumento' => $cambios['documento'],
                        ':idURL' => $id,
                    ]);
                    $existeInquilino = $inquilinos->fetch();

                    if (!$existeInquilino) {
                        $inquilinos = $connection->prepare('UPDATE inquilinos SET id = :idURL, apellido = :nuevoApellido, nombre = :nuevoNombre, documento = :nuevoDocumento, email = :nuevoEmail, activo = :nuevoActivo WHERE id = :idURL');
                        $inquilinos->execute([
                            ':idURL' => $id,
                            ':nuevoApellido' => $cambios['apellido'],
                            ':nuevoNombre' => $cambios['nombre'],
                            ':nuevoDocumento' => $cambios['documento'],
                            ':nuevoEmail' => $cambios['email'],
                            ':nuevoActivo' => $cambios['activo']
                        ]);

                        $this->data['Status'] = 'Success';
                        $this->data['Mensaje'] = 'Cambios en el Inquilino realizados correctamente';
                        $this->data['Data'] = $cambios;
                        $this->data['Codigo'] = '200';

                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Mensaje'] = 'Ya existe otro Inquilino con el numero de documento ingresado.';
                        $this->data['Data'] = $cambios['documento'];
                        $this->data['Codigo'] = '?'; // Que codigo de error se debe enviar en este tipo de conflictos? DUDA
                    }
                } else {
                    if (!$vApellido)
                        $eFormato['apellido'] = 'El formato del apellido enviado es incorrecto, no se permite ningun caracter que no sea alfanumerico.';
                    if (!$vNombre)
                        $eFormato['nombre'] = 'El formato del nombre enviado es incorrecto, no se permite ningun caracter que no sea alfanumerico.';
                    if (!$vDocumento)
                        $eFormato['documento'] = 'El formato del documento enviado es incorrecto, debe seguir el patron xx.xxx.xxx o x.xxx.xxx';
                    if (!$vEmail)
                        $eFormato['email'] = 'El formato del email enviado es incorrecto.';
                    if (!$vActivo)
                        $eFormato['activo'] = 'El formato del estado del inquilino enviado es incorrecto. Solo admite valores true o false.';

                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = $eFormato;
                    $this->data['Data'] = $cambios;
                    $this->data['Codigo'] = '400';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: El ID ingresado no se encuentra en la base de datos.';
                $this->data['Data'] = $id;
                $this->data['Codigo'] = '404'; // Que codigo de error se debe enviar en este tipo de conflictos? DUDA
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


            $inquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
            $inquilinos->execute([':idURL' => $id]);
            $existeID = $inquilinos->fetchColumn();

            if ($existeID) {
                $inquilinos = $connection->prepare('DELETE FROM inquilinos WHERE id = :idURL');
                $inquilinos->execute([':idURL' => $id]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Inquilino eliminado correctamente de la base de datos.';
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
            $query = $connection->query('SELECT * FROM inquilinos');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Inquilinos recibidos correctamente.';
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

    public function verInquilino(Request $request, Response $response, $args) // Está bien resuelto? DUDA
    {
        try {

            $id = $args['id'];
            $connection = $this->getConnection();
            $inquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
            $inquilinos->execute([':idURL' => $id]);
            $existeInquilino = $inquilinos->fetch(PDO::FETCH_ASSOC); //te da toda la fila siguiente
            // fetchColumn  La primer columna de la siguiente fila
            // fetchall te da todas

            if ($existeInquilino) {
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Historial del inquilino recibido correctamente.';
                $this->data['Data'] = $existeInquilino;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: No existe un inquilino con el ID ingresado.';
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

    public function historial(Request $request, Response $response, $args) // Esta bien resuelto? DUDA
    {
        try {

            $id = $args['idInquilino'];
            $connection = $this->getConnection();
            // Busco inquilino
            $inquilino = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
            $inquilino->execute([':idURL' => $id]);
            $existeInquilino = $inquilino->fetchColumn();
            if ($existeInquilino) {
                // Busco reservas
                $reservasInquilino = $connection->prepare('SELECT * FROM reservas WHERE inquilino_id = :idURL');
                $reservasInquilino->execute([':idURL' => $id]);
                $existenReservas = $reservasInquilino->fetchAll(PDO::FETCH_ASSOC);

                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Historial del inquilino recibido correctamente.';
                $this->data['Data'] = $existenReservas; // Si no hay reservas devuelve un array vacio. TODOS LOS QUE SEAN GENERALES DEBEN DEVOLVER VACIO Y NO ERROR. Solo los que pasan parametros como el ID tiran error.
                $this->data['Codigo'] = '200';

            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: No existe un inquilino con el ID ingresado.';
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
}