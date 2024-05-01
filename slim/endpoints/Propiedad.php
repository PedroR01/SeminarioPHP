<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Propiedad extends Endpoint
{
    // Usar array_filter($eFormato); RESOLVER
    private $eFormato = [ // Por ahora de solo los requeridos
        'Domicilio' => "",
        'localidad_id' => "",
        'cantidad_habitaciones' => "",
        'cantidad_banios' => "",
        'cochera' => "",
        'cantidad_huespedes' => "",
        'fecha_inicio_disponibilidad' => "",
        'cantidad_días' => "",
        'disponible' => "",
        'valor_noche' => "",
        'tipo_propiedad_id' => "",
        'imagen' => "",
        'tipo_imagen' => ""
    ];

    // ARREGLAR VALIDACION DE LAS FECHAS Y LIMPIAR CODIGO ACA Y EN EDITAR RESOLVER
    public function crear(Request $request, Response $response) // Puede haber propiedades repetidas - Creo que hice verificaciones de mas
    {
        try {
            $connection = $this->getConnection();
            $nuevaPropiedad = $request->getParsedBody();

            $fechaActual = 'd-m-Y';

            // VALIDACIONES DE FORMATOS
            $vDomicilio = isset($nuevaPropiedad['domicilio']) && $this->validador($this->patronDomicilio, $nuevaPropiedad['domicilio']);
            $vCantidad_huespedes = isset($nuevaPropiedad['cantidad_huespedes']) && (is_int($nuevaPropiedad['cantidad_huespedes']) && 0 < $nuevaPropiedad['cantidad_huespedes']);
            $vFecha_inicio_disponibilidad = isset($nuevaPropiedad['fecha_inicio_disponibilidad']) && $nuevaPropiedad['fecha_inicio_disponibilidad'] > $fechaActual;
            $vCantidad_dias = isset($nuevaPropiedad['cantidad_dias']) && (is_int($nuevaPropiedad['cantidad_dias']) && 0 < $nuevaPropiedad['cantidad_dias']);
            $vValor_noche = isset($nuevaPropiedad['valor_noche']) && (is_int($nuevaPropiedad['valor_noche']) && 0 < $nuevaPropiedad['valor_noche']);
            $vDisponible = isset($nuevaPropiedad['disponible']) && ($nuevaPropiedad['disponible'] == true || $nuevaPropiedad['disponible'] = false);

            // Validaciones opcionales
            $vCantidad_habitaciones = isset($nuevaPropiedad['cantidad_habitaciones']) && (is_int($nuevaPropiedad['cantidad_habitaciones']) && 0 < $nuevaPropiedad['cantidad_habitaciones']);
            $vCantidad_banios = isset($nuevaPropiedad['cantidad_banios']) && (is_int($nuevaPropiedad['cantidad_banios']) && 0 < $nuevaPropiedad['cantidad_banios']);
            $vCochera = ($nuevaPropiedad['cochera'] == true || $nuevaPropiedad['cochera'] == false);

            // Busca si existe el id de la tabla localidades
            $tablaLocalidad = $connection->prepare('SELECT * FROM localidades WHERE id = :localidadID');
            $tablaLocalidad->execute([':localidadID' => $nuevaPropiedad['localidad_id']]);
            $existeLocalidad = $tablaLocalidad->fetchColumn();

            // Busca si existe el id de la tabla tipo_propiedades
            $tablaTipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :tipoPropiedadID');
            $tablaTipoPropiedad->execute([':tipoPropiedadID' => $nuevaPropiedad['tipo_propiedad_id']]);
            $existeTipoPropiedad = $tablaTipoPropiedad->fetchColumn();

            // Obtencion de datos de campos no obligatorios
            $cantidadHabitaciones = $nuevaPropiedad['cantidad_habitaciones'] ?? null;
            $cantidadBanios = $nuevaPropiedad['cantidad_banios'] ?? null;
            $cochera = $nuevaPropiedad['cochera'] ?? null;
            $imagen = $nuevaPropiedad['imagen'] ?? null;
            $tipo_imagen = $nuevaPropiedad['tipo_imagen'] ?? null;


            //Que es el campo de imagen? Como se llena? Bajar imagen e introducirla en base64-image.de para obtener el codigo
            if ($existeLocalidad && $existeTipoPropiedad && $vDomicilio && $vCantidad_huespedes && $vFecha_inicio_disponibilidad && $vCantidad_dias && $vDisponible && $vValor_noche) {
                $tablaPropiedades = $connection->prepare('INSERT INTO propiedades (domicilio, localidad_id, cantidad_habitaciones, cantidad_banios, cochera, cantidad_huespedes, fecha_inicio_disponibilidad, cantidad_dias, disponible, valor_noche, tipo_propiedad_id, imagen, tipo_imagen) VALUES (:domicilio, :localidad_id, :cantidad_habitaciones, :cantidad_banios, :cochera, :cantidad_huespedes, :fecha_inicio_disponibilidad, :cantidad_dias, :disponible, :valor_noche, :tipo_propiedad_id, :imagen, :tipo_imagen)');
                $tablaPropiedades->execute([
                    ':domicilio' => $nuevaPropiedad['domicilio'],
                    ':localidad_id' => $nuevaPropiedad['localidad_id'],
                    ':cantidad_habitaciones' => $cantidadHabitaciones,
                    ':cantidad_banios' => $cantidadBanios,
                    ':cochera' => $cochera,
                    ':cantidad_huespedes' => $nuevaPropiedad['cantidad_huespedes'],
                    ':fecha_inicio_disponibilidad' => $nuevaPropiedad['fecha_inicio_disponibilidad'],
                    ':cantidad_dias' => $nuevaPropiedad['cantidad_dias'],
                    ':disponible' => $nuevaPropiedad['disponible'],
                    ':valor_noche' => $nuevaPropiedad['valor_noche'],
                    ':tipo_propiedad_id' => $nuevaPropiedad['tipo_propiedad_id'],
                    ':imagen' => $imagen,
                    ':tipo_imagen' => $tipo_imagen,
                ]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Propiedad agregada correctamente';
                $this->data['Data'] = $nuevaPropiedad;
                $this->data['Codigo'] = '200';
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevaPropiedad;
                $this->data['Codigo'] = '400';

                // ID INEXISTENTE EN OTRAS TABLAS
                if (!$existeLocalidad)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID de localidad ingresado.';
                if (!$existeTipoPropiedad)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID del tipo de propiedad ingresado.';

                // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                if (!$vDomicilio)
                    $this->data['Mensaje'] .= ' DOMICILIO: Formato incorrecto de tipo domicilio. Respetar formato de tipo Calle "numeroDeCalle" "numeroDeDomicilio"';
                if (!$vCantidad_huespedes)
                    $this->data['Mensaje'] .= ' CANTIDAD HUESPEDES: Formato incorrecto de tipo cantidad_huespedes. Respetar formato de tipo entero > 0';
                if (!$vFecha_inicio_disponibilidad)
                    $this->data['Mensaje'] .= 'FECHA INICIO DISPONIBILIDAD: Formato incorrecto de tipo fecha. Respetar formato de tipo YYYY-mm-dd (posterior a la fecha actual)';
                if (!$vCantidad_dias)
                    $this->data['Mensaje'] .= ' CANTIDAD DIAS: Formato incorrecto de tipo cantidad_dias. Respetar formato de tipo entero > 0';
                if (!$vDisponible)
                    $this->data['Mensaje'] .= 'DISPONIBLE: Formato incorrecto de tipo disponible. Respetar formato de tipo boolean (true/false)';
                if (!$vValor_noche)
                    $this->data['Mensaje'] .= ' VALOR NOCHE: Formato incorrecto de tipo valor_noche. Respetar formato de tipo entero > 0';

                // (opcionales)
                if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                    $this->data['Mensaje'] .= ' CANTIDAD HABITACIONES: Formato incorrecto de tipo cantidad_habitaciones. Respetar formato de tipo entero > 0';
                if ($cantidadBanios && !$vCantidad_banios)
                    $this->data['Mensaje'] .= ' CANTIDAD BANIOS: Formato incorrecto de tipo cantidad_banios. Respetar formato de tipo entero > 0';
                if ($cochera && !$vCochera)
                    $this->data['Mensaje'] .= ' COCHERA: Formato incorrecto de tipo cochera. Respetar formato de tipo boolean (true/false)';

                // Falta imagen y tipo de imagen
            }
        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();

        }

        $response->getBody()->write(json_encode($this->data));
        return $response;

    }
    public function editar(Request $request, Response $response, $args) // Mucho codigo repetido del metodo crear
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $nuevaPropiedad = $request->getParsedBody();

            // No se puede editar la propiedad si ya hay una reserva vinculada.
            $reserva = $connection->prepare('SELECT * FROM reservas WHERE propiedad_id = :idURL');
            $reserva->execute([':idURL' => $id]);
            $existeReserva = $reserva->fetch(PDO::FETCH_ASSOC);
            $fechaActual = date('Y-m-d'); // Al no pasarle un segundo parametro toma la fecha actual 

            if (!$existeReserva) {
                // VALIDACIONES DE FORMATOS
                $vDomicilio = isset($nuevaPropiedad['domicilio']) && $this->validador($this->patronDomicilio, $nuevaPropiedad['domicilio']);
                $vCantidad_huespedes = isset($nuevaPropiedad['cantidad_huespedes']) && (is_int($nuevaPropiedad['cantidad_huespedes']) && 0 < $nuevaPropiedad['cantidad_huespedes']);
                $vFecha_inicio_disponibilidad = isset($nuevaPropiedad['fecha_inicio_disponibilidad']) && $nuevaPropiedad['fecha_inicio_disponibilidad'] > $fechaActual;
                $vCantidad_dias = isset($nuevaPropiedad['cantidad_dias']) && (is_int($nuevaPropiedad['cantidad_dias']) && 0 < $nuevaPropiedad['cantidad_dias']);
                $vValor_noche = isset($nuevaPropiedad['valor_noche']) && (is_int($nuevaPropiedad['valor_noche']) && 0 < $nuevaPropiedad['valor_noche']);
                $vDisponible = isset($nuevaPropiedad['disponible']) && ($nuevaPropiedad['disponible'] == true || $nuevaPropiedad['disponible'] = false);

                // Validaciones opcionales
                $vCantidad_habitaciones = isset($nuevaPropiedad['cantidad_habitaciones']) && (is_int($nuevaPropiedad['cantidad_habitaciones']) && 0 < $nuevaPropiedad['cantidad_habitaciones']);
                $vCantidad_banios = isset($nuevaPropiedad['cantidad_banios']) && (is_int($nuevaPropiedad['cantidad_banios']) && 0 < $nuevaPropiedad['cantidad_banios']);
                $vCochera = ($nuevaPropiedad['cochera'] == true || $nuevaPropiedad['cochera'] == false);
                // tipo imagen con formatos (png, gif, etc)

                // Busca si existe el id de la tabla localidades
                $tablaLocalidad = $connection->prepare('SELECT * FROM localidades WHERE id = :localidadID');
                $tablaLocalidad->execute([':localidadID' => $nuevaPropiedad['localidad_id']]);
                $existeLocalidad = $tablaLocalidad->fetchColumn();

                // Busca si existe el id de la tabla tipo_propiedades
                $tablaTipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :tipoPropiedadID');
                $tablaTipoPropiedad->execute([':tipoPropiedadID' => $nuevaPropiedad['tipo_propiedad_id']]);
                $existeTipoPropiedad = $tablaTipoPropiedad->fetchColumn();

                // Obtencion de datos de campos no obligatorios
                $cantidadHabitaciones = $nuevaPropiedad['cantidad_habitaciones'] ?? null;
                $cantidadBanios = $nuevaPropiedad['cantidad_banios'] ?? null;
                $cochera = $nuevaPropiedad['cochera'] ?? null;
                $imagen = $nuevaPropiedad['imagen'] ?? null;
                $tipo_imagen = $nuevaPropiedad['tipo_imagen'] ?? null;
                if ($existeLocalidad && $existeTipoPropiedad && $vDomicilio && $vCantidad_huespedes && $vFecha_inicio_disponibilidad && $vCantidad_dias && $vDisponible && $vValor_noche) {
                    $tablaPropiedades = $connection->prepare('UPDATE propiedades SET id = :idURL, domicilio = :nuevoDomicilio, localidad_id = :nuevaLocalidad_id, cantidad_habitaciones = :nuevaCantidad_habitaciones, cantidad_banios = :nuevaCantidad_banios, cochera = :nuevaCochera, cantidad_huespedes = :nuevaCantidad_huespedes, fecha_inicio_disponibilidad = :nuevaFecha_inicio_disponibilidad, cantidad_dias = :nuevaCantidad_dias, disponible = :nuevoDisponible, valor_noche = :nuevoValor_noche, tipo_propiedad_id = :nuevoTipo_propiedad_id, imagen = :nuevaImagen, tipo_imagen = :nuevoTipo_imagen WHERE id = :idURL');
                    $tablaPropiedades->execute([
                        'idURL' => $id,
                        ':nuevoDomicilio' => $nuevaPropiedad['domicilio'],
                        ':nuevaLocalidad_id' => $nuevaPropiedad['localidad_id'],
                        ':nuevaCantidad_habitaciones' => $cantidadHabitaciones,
                        ':nuevaCantidad_banios' => $cantidadBanios,
                        ':nuevaCochera' => $cochera,
                        ':nuevaCantidad_huespedes' => $nuevaPropiedad['cantidad_huespedes'],
                        ':nuevaFecha_inicio_disponibilidad' => $nuevaPropiedad['fecha_inicio_disponibilidad'],
                        ':nuevaCantidad_dias' => $nuevaPropiedad['cantidad_dias'],
                        ':nuevoDisponible' => $nuevaPropiedad['disponible'],
                        ':nuevoValor_noche' => $nuevaPropiedad['valor_noche'],
                        ':nuevoTipo_propiedad_id' => $nuevaPropiedad['tipo_propiedad_id'],
                        ':nuevaImagen' => $imagen,
                        ':nuevoTipo_imagen' => $tipo_imagen,
                    ]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Propiedad editada correctamente';
                    $this->data['Data'] = $nuevaPropiedad;
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Data'] = $nuevaPropiedad;
                    $this->data['Codigo'] = '400';

                    // ID INEXISTENTE EN OTRAS TABLAS
                    if (!$existeLocalidad)
                        $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID de localidad ingresado.';
                    if (!$existeTipoPropiedad)
                        $this->data['Mensaje'] = $this->data['Mensaje'] . 'No existe el ID del tipo de propiedad ingresado.';

                    // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                    if (!$vDomicilio)
                        $this->data['Mensaje'] .= ' DOMICILIO: Formato incorrecto de tipo domicilio. Respetar formato de tipo Calle "numeroDeCalle" "numeroDeDomicilio"';
                    if (!$vCantidad_huespedes)
                        $this->data['Mensaje'] .= ' CANTIDAD HUESPEDES: Formato incorrecto de tipo cantidad_huespedes. Respetar formato de tipo entero > 0';
                    if (!$vFecha_inicio_disponibilidad)
                        $this->data['Mensaje'] .= 'FECHA INICIO DISPONIBILIDAD: Formato incorrecto de tipo fecha. Respetar formato de tipo YYYY-mm-dd (posterior a la fecha actual)';
                    if (!$vCantidad_dias)
                        $this->data['Mensaje'] .= ' CANTIDAD DIAS: Formato incorrecto de tipo cantidad_dias. Respetar formato de tipo entero > 0';
                    if (!$vDisponible)
                        $this->data['Mensaje'] .= 'DISPONIBLE: Formato incorrecto de tipo disponible. Respetar formato de tipo boolean (true/false)';
                    if (!$vValor_noche)
                        $this->data['Mensaje'] .= ' VALOR NOCHE: Formato incorrecto de tipo valor_noche. Respetar formato de tipo entero > 0';

                    // (opcionales)
                    if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                        $this->data['Mensaje'] .= ' CANTIDAD HABITACIONES: Formato incorrecto de tipo cantidad_habitaciones. Respetar formato de tipo entero > 0';
                    if ($cantidadBanios && !$vCantidad_banios)
                        $this->data['Mensaje'] .= ' CANTIDAD BANIOS: Formato incorrecto de tipo cantidad_banios. Respetar formato de tipo entero > 0';
                    if ($cochera && !$vCochera)
                        $this->data['Mensaje'] .= ' COCHERA: Formato incorrecto de tipo cochera. Respetar formato de tipo boolean (true/false)';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La propiedad no se puede editar porque ya se encuentra asociada a una reserva.';
                $this->data['Data'] = $existeReserva;
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

    // SI HAY UNA RESERVA HECHA CON EL INQUILINO/PROPIEDAD ASOCIADA, NO SE PUEDE BORRAR RESOLVER
    public function eliminar(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            // No se puede eliminar la propiedad si ya hay una reserva vinculada.
            $reserva = $connection->prepare('SELECT * FROM reservas WHERE propiedad_id = :idURL');
            $reserva->execute([':idURL' => $id]);
            $existeReserva = $reserva->fetch(PDO::FETCH_ASSOC);

            if (!$existeReserva) {
                $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL');
                $propiedad->execute([':idURL' => $id]);
                $existeID = $propiedad->fetch(PDO::FETCH_ASSOC);
                if ($existeID) {
                    $this->data['Data'] = $existeID;
                    $inquilinos = $connection->prepare('DELETE FROM propiedades WHERE id = :idURL');
                    $inquilinos->execute([':idURL' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Propiedad eliminada correctamente de la base de datos.';
                    $this->data['Codigo'] = '200';
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = '404';
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La propiedad no se puede eliminar porque ya se encuentra asociada a una reserva.';
                $this->data['Data'] = $existeReserva;
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
            $filtro = $request->getQueryParams();
            $connection = $this->getConnection();

            // Initialize default values for parameters
            $disponible = isset($filtro['disponible']) ? $filtro['disponible'] : null;
            $localidad = isset($filtro['localidad_id']) ? $filtro['localidad_id'] : null;
            $fecha = isset($filtro['fecha_inicio_disponibilidad']) ? $filtro['fecha_inicio_disponibilidad'] : null;
            $cantHuespedes = isset($filtro['cantidad_huespedes']) ? $filtro['cantidad_huespedes'] : null;

            // Arma la orden o busqueda en base a los filtros recibidos
            $orden = "SELECT * FROM propiedades WHERE 1=1";
            if ($disponible !== null) {
                $orden .= " AND disponible = :disponible";
            }
            if ($localidad !== null) {
                $orden .= " AND localidad_id = :localidad";
            }
            if ($fecha !== null) {
                $orden .= " AND fecha_inicio_disponibilidad = :fecha";
            }
            if ($cantHuespedes !== null) {
                $orden .= " AND cantidad_huespedes = :cantHuespedes";
            }

            $datos = $connection->prepare($orden);

            if ($disponible !== null) {
                if ($disponible == true)
                    $disponible = 1;
                else if ($disponible == false)
                    $disponible = 0;
                $datos->bindParam(':disponible', $disponible);
            }
            if ($localidad !== null) {
                $datos->bindParam(':localidad', $localidad);
            }
            if ($fecha !== null) {
                $datos->bindParam(':fecha', $fecha);
            }
            if ($cantHuespedes !== null) {
                $datos->bindParam(':cantHuespedes', $cantHuespedes);
            }

            // Ejecuta el filtrado
            $datos->execute();
            $datosFiltrados = $datos->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Propiedades recibidas correctamente.';
            $this->data['Data'] = $datosFiltrados;
            $this->data['Codigo'] = '200';
        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }

        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function verPropiedad(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL'); // prepare te deja pasar parametros o placeholders ":idURL" x ej.
            $propiedad->execute([':idURL' => $id]);
            $datos = $propiedad->fetch(PDO::FETCH_ASSOC); // Con este parametro devuelve solo las columnas con sus respectivos nombres, sino se incluye también un "duplicado" de cada columna. Ej "domicilio": "pepe 61" [0]: "pepe 61"

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Propiedad recibida correctamente.';
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