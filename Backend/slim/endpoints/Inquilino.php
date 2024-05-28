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
        try {

            $connection = $this->getConnection();
            $nuevoInquilino = $request->getParsedBody();

            $vApellido = isset($nuevoInquilino['apellido']) && $this->validador($this->patronAlfabetico, $nuevoInquilino['apellido']);
            $vNombre = isset($nuevoInquilino['nombre']) && $this->validador($this->patronAlfabetico, $nuevoInquilino['nombre']);
            $vEmail = isset($nuevoInquilino['email']) && $this->validador($this->patronEmail, $nuevoInquilino['email']);
            $vDocumento = isset($nuevoInquilino['documento']) && $this->validador($this->patronDocumento, $nuevoInquilino['documento']);
            $vActivo = isset($nuevoInquilino['activo']) && is_bool($nuevoInquilino['activo']);

            if ($vApellido && $vNombre && $vEmail && $vDocumento && $vActivo) {
                $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE documento = :documento');
                $tablaInquilinos->execute([':documento' => $nuevoInquilino['documento']]);
                $existe = $tablaInquilinos->fetch(PDO::FETCH_ASSOC);

                if (!$existe) {
                    $tablaInquilinos = $connection->prepare('INSERT INTO inquilinos (apellido, nombre, documento, email, activo) VALUES (:apellido, :nombre, :documento, :email, :activo)');
                    $tablaInquilinos->execute([
                        ':apellido' => $nuevoInquilino['apellido'],
                        ':nombre' => $nuevoInquilino['nombre'],
                        ':documento' => $nuevoInquilino['documento'],
                        ':email' => $nuevoInquilino['email'],
                        ':activo' => $nuevoInquilino['apellido'] === true ? 1 : 0,
                    ]);

                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Inquilino agregado correctamente';
                    $this->data['Data'] = $nuevoInquilino;
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'Error: El inquilino ingresado ya existe en la base de datos.';
                    $this->data['Data'] = ['Inquilino existente' => $existe];
                    $this->data['Codigo'] = 400;
                }
            } else {
                if (!$vApellido)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['apellido' => "El formato del campo es incorrecto o está vacio, no se permite ningun caracter que no sea alfabetico."]);
                if (!$vNombre)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['nombre' => "El formato del campo es incorrecto o está vacio, no se permite ningun caracter que no sea alfabetico."]);
                if (!$vDocumento)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['documento' => "El formato del campo es incorrecto o está vacio, solo se admiten entre 7 y 8 digitos, sin ningún otro tipo de caracter que no sea númerico."]);
                if (!$vEmail)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['email' => "El formato del campo es incorrecto o está vacio, respetar formato 'usuario@correo.dominio/s'"]);
                if (!$vActivo)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['activo' => "El formato del campo es incorrecto o está vacio, solo admite valores true o false."]);

                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevoInquilino;
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

    public function editar(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();


            $tablaInquilinos = $connection->prepare('SELECT apellido, nombre, documento, email, activo FROM inquilinos WHERE id = :idURL');
            $tablaInquilinos->execute([':idURL' => $id]);
            $existeInquilino = $tablaInquilinos->fetchColumn();

            if ($existeInquilino) {
                $cambios = $request->getParsedBody();

                $vApellido = isset($cambios['apellido']) && $this->validador($this->patronAlfabetico, $cambios['apellido']);
                $vNombre = isset($cambios['nombre']) && $this->validador($this->patronAlfabetico, $cambios['nombre']);
                $vEmail = isset($cambios['email']) && $this->validador($this->patronEmail, $cambios['email']);
                $vDocumento = isset($cambios['documento']) && $this->validador($this->patronDocumento, $cambios['documento']);
                $vActivo = isset($cambios['activo']) && is_bool($cambios['activo']);

                if ($vApellido && $vNombre && $vEmail && $vDocumento && $vActivo) {
                    $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE documento = :nuevoDocumento AND id <> :idURL');
                    $tablaInquilinos->execute([
                        ':nuevoDocumento' => $cambios['documento'],
                        ':idURL' => $id,
                    ]);
                    $inquilinoRepetido = $tablaInquilinos->fetch(PDO::FETCH_ASSOC);

                    if (!$inquilinoRepetido) {
                        $tablaInquilinos = $connection->prepare('UPDATE inquilinos SET id = :idURL, apellido = :nuevoApellido, nombre = :nuevoNombre, documento = :nuevoDocumento, email = :nuevoEmail, activo = :nuevoActivo WHERE id = :idURL');
                        $tablaInquilinos->execute([
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
                        $this->data['Codigo'] = 200;

                    } else {
                        $this->data['Status'] = 'Fail';
                        $this->data['Mensaje'] = 'Ya existe otro Inquilino con el numero de documento ingresado.';
                        $this->data['Data'] = ['Inquilino existente' => $inquilinoRepetido];
                        $this->data['Codigo'] = 400;
                    }
                } else {
                    if (!$vApellido)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['apellido' => "El formato del campo es incorrecto o está vacio, no se permite ningun caracter que no sea alfabetico."]);
                    if (!$vNombre)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['nombre' => "El formato del campo es incorrecto o está vacio, no se permite ningun caracter que no sea alfabetico."]);
                    if (!$vDocumento)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['documento' => "El formato del campo es incorrecto o está vacio, solo se admiten entre 7 y 8 digitos, sin ningún otro tipo de caracter que no sea númerico."]);
                    if (!$vEmail)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['email' => "El formato del campo es incorrecto o está vacio, respetar formato 'usuario@correo.dominio/s'"]);
                    if (!$vActivo)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['activo' => "El formato del campo es incorrecto o está vacio, solo admite valores true o false."]);

                    $this->data['Status'] = 'Fail';
                    $this->data['Data'] = $cambios;
                    $this->data['Codigo'] = 400;
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

            // No se puede eliminar el inquilino si ya hay una reserva a su nombre
            $reservasInquilino = $connection->prepare('SELECT * FROM reservas WHERE inquilino_id = :idURL');
            $reservasInquilino->execute([':idURL' => $id]);
            $existeReserva = $reservasInquilino->fetch(PDO::FETCH_ASSOC);

            if (!$existeReserva) {
                $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
                $tablaInquilinos->execute([':idURL' => $id]);
                $existeInquilino = $tablaInquilinos->fetchColumn();

                if ($existeInquilino) {
                    $tablaInquilinos = $connection->prepare('DELETE FROM inquilinos WHERE id = :idURL');
                    $tablaInquilinos->execute([':idURL' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Inquilino eliminado correctamente de la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'Error: El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 404;
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'El inquilino no se puede eliminar porque ya se encuentra asociado a una reserva.';
                $this->data['Data'] = $existeReserva;
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
            $query = $connection->query('SELECT * FROM inquilinos');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Inquilinos recibidos correctamente.';
            $this->data['Data'] = $datos;
            $this->data['Codigo'] = 200;

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();

        }
        $response->getBody()->write(json_encode($this->data));
        return $response->withStatus($this->data['Codigo']);

    }

    public function verInquilino(Request $request, Response $response, $args)
    {
        try {

            $id = $args['id'];
            $connection = $this->getConnection();
            $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
            $tablaInquilinos->execute([':idURL' => $id]);
            $existeInquilino = $tablaInquilinos->fetch(PDO::FETCH_ASSOC);

            if ($existeInquilino) {
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Inquilino recibido correctamente.';
                $this->data['Data'] = $existeInquilino;
                $this->data['Codigo'] = 200;
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: No existe un inquilino con el ID ingresado.';
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

    public function historial(Request $request, Response $response, $args)
    {
        try {

            $id = $args['idInquilino'];
            $connection = $this->getConnection();
            // Busco inquilino
            $tablaInquilinos = $connection->prepare('SELECT * FROM inquilinos WHERE id = :idURL');
            $tablaInquilinos->execute([':idURL' => $id]);
            $existeInquilino = $tablaInquilinos->fetchColumn();
            if ($existeInquilino) {
                // Busco reservas
                $reservasInquilino = $connection->prepare('SELECT * FROM reservas WHERE inquilino_id = :idURL');
                $reservasInquilino->execute([':idURL' => $id]);
                $existenReservas = $reservasInquilino->fetchAll(PDO::FETCH_ASSOC);

                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Historial del inquilino recibido correctamente.';
                $this->data['Data'] = $existenReservas;
                $this->data['Codigo'] = 200;

            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Error: No existe un inquilino con el ID ingresado.';
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
}