<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Reserva extends Endpoint
{
    // Una reserva solo se puede realizar si el inquilino está activo y la propiedad está disponible.
    public function crear(Request $request, Response $response)
    {
        try {

            $connection = $this->getConnection();
            $nuevaReserva = $request->getParsedBody();

            $fechaActual = date('Y-m-d');

            $datoPropiedadID = isset($nuevaReserva['propiedad_id']) && is_int($nuevaReserva['propiedad_id']) && $nuevaReserva['propiedad_id'] > 0;
            $datoInquilinoID = isset($nuevaReserva['inquilino_id']) && is_int($nuevaReserva['inquilino_id']) && $nuevaReserva['inquilino_id'] > 0;
            $datoCantidad_noches = isset($nuevaReserva['cantidad_noches']) && is_int($nuevaReserva['cantidad_noches']) && $nuevaReserva['cantidad_noches'] > 0;
            $datoValor_total = isset($nuevaReserva['valor_total']) && is_int($nuevaReserva['valor_total']) && $nuevaReserva['valor_total'] > 0;

            // Busca si existe el id de la tabla propiedades
            $existePropiedad = false;
            if ($datoPropiedadID) {
                $tablaPropiedad = $connection->prepare('SELECT fecha_inicio_disponibilidad, disponible, valor_noche FROM propiedades WHERE id = :propiedadID');
                $tablaPropiedad->execute([':propiedadID' => $nuevaReserva['propiedad_id']]);
                $existePropiedad = $tablaPropiedad->fetch(PDO::FETCH_ASSOC);
            }

            $datoFecha_desde = false;
            if ($existePropiedad)
                $datoFecha_desde = isset($nuevaReserva['fecha_desde']) && $nuevaReserva['fecha_desde'] > $fechaActual && $nuevaReserva['fecha_desde'] >= $existePropiedad['fecha_inicio_disponibilidad'];

            // Busca si existe el id de la tabla inquilinos
            $existeInquilino = false;
            if ($datoInquilinoID) {
                $tablaInquilino = $connection->prepare('SELECT activo FROM inquilinos WHERE id = :inquilinoID');
                $tablaInquilino->execute([':inquilinoID' => $nuevaReserva['inquilino_id']]);
                $existeInquilino = $tablaInquilino->fetch(PDO::FETCH_ASSOC);
            }

            // Corroborar fecha disponible y compararla con fecha desde y fecha actual
            if (
                ($existePropiedad && $existeInquilino) && ($existePropiedad['disponible'] == 1 && $existeInquilino['activo'] == 1)
                && ($datoFecha_desde && $datoCantidad_noches && $datoValor_total)
            ) {
                $tableReserva = $connection->prepare('INSERT INTO reservas(propiedad_id, inquilino_id, fecha_desde, cantidad_noches, valor_total) VALUES(:propiedad_id, :inquilino_id, :fecha_desde, :cantidad_noches, :valor_total) ');
                $tableReserva->execute([
                    ':propiedad_id' => $nuevaReserva['propiedad_id'],
                    ':inquilino_id' => $nuevaReserva['inquilino_id'],
                    ':fecha_desde' => $nuevaReserva['fecha_desde'],
                    ':cantidad_noches' => $nuevaReserva['cantidad_noches'],
                    ':valor_total' => $nuevaReserva['valor_total'],
                ]);

                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Reserva creada correctamente.';
                $this->data['Data'] = $nuevaReserva;
                $this->data['Codigo'] = 200;
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevaReserva;
                $this->data['Codigo'] = 400;

                if (!$existePropiedad && $datoPropiedadID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['propiedad_id' => "No existe propiedad con el ID ingresado."]);
                else if (!$datoPropiedadID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['propiedad_id' => "No se introdujo el campo o no se introdujo un valor valido (entero > 0)."]);
                if (!$existeInquilino && $datoInquilinoID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['inquilino_id' => "No existe inquilino con el ID ingresado."]);
                else if (!$datoInquilinoID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['inquilino_id' => "No se introdujo el campo o no se introdujo un valor valido (entero > 0)."]);

                if (!$datoFecha_desde)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_desde' => 'No se introdujo el campo, no es del tipo fecha (YYYY-mm-dd) y/o no es una fecha mayor a la actual y a la disponible en la propiedad.']);
                if (!$datoCantidad_noches)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_noches' => 'No se introdujo el campo o no se introdujo un valor valido (entero > 0).']);
                if (!$datoValor_total)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['valor_total' => 'No se introdujo el campo o no se introdujo un valor valido (entero > 0).']);

                // NO HAY DISPONIBILIDAD O NO ESTA ACTIVO
                if ($existePropiedad['disponible'] == 0)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['Propiedad["disponible"]' => 'La propiedad buscada no se encuentra disponible.']);
                if ($existeInquilino['activo'] == 0)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['Inquilino["activo"]' => 'El inquilino asociado para la reserva no se encuentra activo.']);

            }
            return $this->HTTPCodeError($response);
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
            $editarReserva = $request->getParsedBody();

            $fechaActual = date('Y-m-d');

            $datoPropiedadID = isset($editarReserva['propiedad_id']) && is_int($editarReserva['propiedad_id']) && $editarReserva['propiedad_id'] > 0;
            $datoInquilinoID = isset($editarReserva['inquilino_id']) && is_int($editarReserva['inquilino_id']) && $editarReserva['inquilino_id'] > 0;
            $datoCantidad_noches = isset($editarReserva['cantidad_noches']) && is_int($editarReserva['cantidad_noches']) && $editarReserva['cantidad_noches'] > 0;
            $datoValor_total = isset($editarReserva['valor_total']) && is_int($editarReserva['valor_total']) && $editarReserva['valor_total'] > 0;

            $existePropiedad = false;
            if ($datoPropiedadID) {
                $tablaPropiedad = $connection->prepare('SELECT fecha_inicio_disponibilidad, disponible FROM propiedades WHERE id = :propiedadID');
                $tablaPropiedad->execute([':propiedadID' => $editarReserva['propiedad_id']]);
                $existePropiedad = $tablaPropiedad->fetch(PDO::FETCH_ASSOC);
            }

            $datoFecha_desde = false;
            if ($existePropiedad)
                $datoFecha_desde = isset($editarReserva['fecha_desde']) && $editarReserva['fecha_desde'] > $fechaActual && $editarReserva['fecha_desde'] >= $existePropiedad['fecha_inicio_disponibilidad'];

            $existeInquilino = false;
            if ($datoInquilinoID) {
                $tablaInquilino = $connection->prepare('SELECT activo FROM inquilinos WHERE id = :inquilinoID');
                $tablaInquilino->execute([':inquilinoID' => $editarReserva['inquilino_id']]);
                $existeInquilino = $tablaInquilino->fetchColumn();
            }

            // la disponibilidad tiene que estar antes que la reserva y la reserva tiene que ser mayor a la fecha actual. Asi sabemos que esta disponible y a tiempo para reservar. A su vez, debe ser valido el nuevo inquilino ID y propiedad ID en caso de cambiarlos
            if (
                (($existeInquilino && $existePropiedad) && ($existeInquilino == 1 && $existePropiedad['disponible'] == 1))
                && ($datoFecha_desde && $datoCantidad_noches && $datoValor_total)
            ) {
                $tableReserva = $connection->prepare('UPDATE reservas SET id = :idURL, propiedad_id = :propiedad_id, inquilino_id = :inquilino_id, fecha_desde = :fecha_desde, cantidad_noches = :cantidad_noches, valor_total = :valor_total WHERE id = :idURL');
                $tableReserva->execute([
                    ':idURL' => $id,
                    ':propiedad_id' => $editarReserva['propiedad_id'],
                    ':inquilino_id' => $editarReserva['inquilino_id'],
                    ':fecha_desde' => $editarReserva['fecha_desde'],
                    ':cantidad_noches' => $editarReserva['cantidad_noches'],
                    ':valor_total' => $editarReserva['valor_total'],
                ]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Reserva editada correctamente.';
                $this->data['Data'] = $editarReserva;
                $this->data['Codigo'] = 200;
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $editarReserva;
                $this->data['Codigo'] = 400;

                if (!$existePropiedad && $datoPropiedadID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['propiedad_id' => "No existe propiedad con el ID ingresado."]);
                else if (!$datoPropiedadID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['propiedad_id' => "No se introdujo el campo o no se introdujo un valor valido (entero > 0)."]);
                if (!$existeInquilino && $datoInquilinoID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['inquilino_id' => "No existe inquilino con el ID ingresado."]);
                else if (!$datoInquilinoID)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['inquilino_id' => "No se introdujo el campo o no se introdujo un valor valido (entero > 0)."]);

                if ((isset($editarReserva['fecha_desde']) && $existePropiedad) && ($editarReserva['fecha_desde'] < $existePropiedad['fecha_inicio_disponibilidad']))
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_desde' => 'La propiedad no se encuentra disponible en la fecha en la que se desea realizar la reserva. Revise la disponibilidad de la propiedad e ingrese una nueva fecha de reserva.']);
                else if (!$datoFecha_desde)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_desde' => 'No se introdujo el campo o no es del tipo fecha (YYYY-mm-dd) y/o no es una fecha mayor a la actual.']);
                if (!$datoCantidad_noches)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_noches' => 'No se introdujo el campo o no se introdujo un valor valido (entero > 0).']);
                if (!$datoValor_total)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['valor_total' => 'No se introdujo el campo o no se introdujo un valor valido (entero > 0).']);

                // NO HAY DISPONIBILIDAD O NO ESTA ACTIVO
                if ($existePropiedad['disponible'] == 0)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['Propiedad["disponible"]' => 'La propiedad buscada no se encuentra disponible.']);
                if ($existeInquilino['activo'] == 0)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['Inquilino["activo"]' => 'El inquilino asociado para la reserva no se encuentra activo.']);

            }
            return $this->HTTPCodeError($response);
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

            $tablaReserva = $connection->prepare('SELECT * FROM reservas WHERE id = :idURL');
            $tablaReserva->execute([':idURL' => $id]);
            $existeReserva = $tablaReserva->fetch(PDO::FETCH_ASSOC);

            $fechaActual = date('Y-m-d');

            if ($existeReserva && $existeReserva > $fechaActual) {
                $tablaReserva = $connection->prepare('DELETE FROM reservas WHERE id = :idURL');
                $tablaReserva->execute([':idURL' => $id]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Reserva eliminada correctamente de la base de datos.';
                $this->data['Data'] = ['Reserva eliminada' => $existeReserva];
                $this->data['Codigo'] = 200;
            } else {
                $this->data['Status'] = 'Fail';
                if (!$existeReserva) {
                    $this->data['Mensaje'] = 'Error: El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 404;
                } else {
                    $this->data['Mensaje'] = 'Error: La reserva a eliminar ya ha comenzado.';
                    $this->data['Data'] = ['Reserva' => $existeReserva];
                    $this->data['Codigo'] = 400;
                }
            }
            return $this->HTTPCodeError($response);
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
            $query = $connection->query('SELECT * FROM reservas');
            $datos = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Reservas recibidos correctamente.';
            $this->data['Data'] = $datos;
            $this->data['Codigo'] = 200;

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();

        }
        $response->getBody()->write(json_encode($this->data));
        return $response;

    }
}