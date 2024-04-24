<?php

namespace Endpoints;

use Exception;
use DateTime;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Reserva extends Endpoint
{
    private $eFormato = [ // Por ahora de solo los requeridos
        'propiedad_id' => "",
        'inquilino_id' => "",
        'fecha_desde' => "",
        'cantidad_noches' => "",
        'valor_total' => ""
    ];

    /*
       El valor total es la multiplicación del valor de una propiedad por una noche por la cantidad de noches de la reserva. 
       Una reserva solo se puede realizar si el inquilino está activo y la propiedad está disponible.
       Una reserva solo se puede editar o eliminar si no comenzó (fecha_desde es menor a la fecha actual)
       */
    public function crear(Request $request, Response $response) // se recibe el campo valor y se carga el valor_total luego de hacer el calculo? DUDA
    {
        try {

            $connection = $this->getConnection();
            $nuevaReserva = $request->getParsedBody();

            // Busca si existe el id de la tabla propiedades
            $tablaPropiedad = $connection->prepare('SELECT disponible, valor_noche FROM propiedades WHERE id = :propiedadID');
            $tablaPropiedad->execute([':propiedadID' => $nuevaReserva['propiedad_id']]);
            $existePropiedad = $tablaPropiedad->fetch(PDO::FETCH_ASSOC);

            // Busca si existe el id de la tabla inquilinos
            $tablaInquilino = $connection->prepare('SELECT activo FROM inquilinos WHERE id = :inquilinoID');
            $tablaInquilino->execute([':inquilinoID' => $nuevaReserva['inquilino_id']]);
            $existeInquilino = $tablaInquilino->fetchColumn();

            $fechaActual = new DateTime;

            // Corroborar fecha disponible y compararla con fecha desde y fecha actual
            if (($existePropiedad && $existeInquilino) && ($existePropiedad['disponible'] == 1 && $existeInquilino == 1)) {
                $valorTotal = $existePropiedad['valor_noche'] * $nuevaReserva['cantidad_noches']; // Aca se guarda el precio total por todas las noches reservadas
                $tableReserva = $connection->prepare('INSERT INTO reservas(propiedad_id, inquilino_id, fecha_desde, cantidad_noches, valor_total) VALUES(:propiedad_id, :inquilino_id, :fecha_desde, :cantidad_noches, :valor_total) ');
                $tableReserva->execute([
                    ':propiedad_id' => $nuevaReserva['propiedad_id'],
                    ':inquilino_id' => $nuevaReserva['inquilino_id'],
                    ':fecha_desde' => $nuevaReserva['fecha_desde'],
                    ':cantidad_noches' => $nuevaReserva['cantidad_noches'],
                    ':valor_total' => $valorTotal,
                ]);

                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Reserva creada correctamente.';
                $this->data['Data'] = $nuevaReserva;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevaReserva;
                $this->data['Codigo'] = '400';

                // ID INEXISTENTE EN OTRAS TABLAS
                if (!$existePropiedad)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID de Propiedad ingresado.';
                if (!$existeInquilino)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID del Inquilino ingresado.';

                // NO HAY DISPONIBILIDAD O NO ESTA ACTIVO
                if ($existePropiedad['Disponible'])
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'La propiedad buscada no se encuentra disponible.';
                if ($existeInquilino)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'El inquilino asociado para la reserva no se encuentra activo.';

                // Errores de formato
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
        $connection = $this->getConnection();
        $editarReserva = $request->getParsedBody();

        // Busca si existe el id de la tabla propiedades
        $tablaPropiedad = $connection->prepare('SELECT fecha_inicio_disponibilidad, valor_noche FROM propiedades WHERE id = :propiedadID');
        $tablaPropiedad->execute([':propiedadID' => $editarReserva['propiedad_id']]);
        $existePropiedad = $tablaPropiedad->fetchColumn();

        // Busca si existe el id de la tabla inquilinos
        $tablaInquilino = $connection->prepare('SELECT * FROM inquilinos WHERE id = :inquilinoID');
        $tablaInquilino->execute([':inquilinoID' => $editarReserva['inquilino_id']]);
        $existeInquilino = $tablaInquilino->fetchColumn();

        $fechaActual = new DateTime;

        if (($existePropiedad && $existeInquilino) && ($existePropiedad['fecha_inicio_disponibilidad'] < $editarReserva['fecha_desde'] && $editarReserva['fecha_desde'] > $fechaActual)) {
        }
    }

    public function eliminar(Request $request, Response $response, $args)
    {

    }

    public function listar(Request $request, Response $response, $args)
    {

    }
}