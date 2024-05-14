<?php

namespace Endpoints;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Propiedad extends Endpoint
{
    public function crear(Request $request, Response $response)
    {
        try {
            $connection = $this->getConnection();
            $nuevaPropiedad = $request->getParsedBody();

            $fechaActual = date('Y-m-d');

            // VALIDACIONES DE FORMATOS
            $vDomicilio = isset($nuevaPropiedad['domicilio']) && is_string($nuevaPropiedad['domicilio']) && !empty(trim($nuevaPropiedad['domicilio']));
            $vCantidad_huespedes = isset($nuevaPropiedad['cantidad_huespedes']) && (is_int($nuevaPropiedad['cantidad_huespedes']) && $nuevaPropiedad['cantidad_huespedes'] > 0);
            $vFecha_inicio_disponibilidad = isset($nuevaPropiedad['fecha_inicio_disponibilidad']) && $nuevaPropiedad['fecha_inicio_disponibilidad'] > $fechaActual;
            $vCantidad_dias = isset($nuevaPropiedad['cantidad_dias']) && (is_int($nuevaPropiedad['cantidad_dias']) && $nuevaPropiedad['cantidad_dias'] > 0);
            $vValor_noche = isset($nuevaPropiedad['valor_noche']) && (is_int($nuevaPropiedad['valor_noche']) && $nuevaPropiedad['valor_noche'] > 0);
            $vDisponible = isset($nuevaPropiedad['disponible']) && is_bool($nuevaPropiedad['disponible']);
            $vLocalidadID = isset($nuevaPropiedad['localidad_id']) && is_int($nuevaPropiedad['localidad_id']) && $nuevaPropiedad['localidad_id'] > 0;
            $vTipoPropiedadID = isset($nuevaPropiedad['tipo_propiedad_id']) && is_int($nuevaPropiedad['tipo_propiedad_id']) && $nuevaPropiedad['tipo_propiedad_id'] > 0;

            // Validaciones opcionales
            $vCantidad_habitaciones = isset($nuevaPropiedad['cantidad_habitaciones']) && (is_int($nuevaPropiedad['cantidad_habitaciones']) && $nuevaPropiedad['cantidad_habitaciones'] > 0);
            $vCantidad_banios = isset($nuevaPropiedad['cantidad_banios']) && (is_int($nuevaPropiedad['cantidad_banios']) && $nuevaPropiedad['cantidad_banios'] > 0);
            $vCochera = isset($nuevaPropiedad['cochera']) && is_bool($nuevaPropiedad['cochera']);

            // Busca si existe el id de la tabla localidades
            $existeLocalidad = false;
            if ($vLocalidadID) {
                $tablaLocalidad = $connection->prepare('SELECT * FROM localidades WHERE id = :localidadID');
                $tablaLocalidad->execute([':localidadID' => $nuevaPropiedad['localidad_id']]);
                $existeLocalidad = $tablaLocalidad->fetchColumn();
            }

            // Busca si existe el id de la tabla tipo_propiedades
            $existeTipoPropiedad = false;
            if ($vTipoPropiedadID) {
                $tablaTipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :tipoPropiedadID');
                $tablaTipoPropiedad->execute([':tipoPropiedadID' => $nuevaPropiedad['tipo_propiedad_id']]);
                $existeTipoPropiedad = $tablaTipoPropiedad->fetchColumn();
            }

            // Obtencion de datos de campos no obligatorios
            $cantidadHabitaciones = $nuevaPropiedad['cantidad_habitaciones'] ?? null;
            $cantidadBanios = $nuevaPropiedad['cantidad_banios'] ?? null;
            $cochera = $nuevaPropiedad['cochera'] ?? null;
            $imagen = $nuevaPropiedad['imagen'] ?? null;
            $tipo_imagen = $nuevaPropiedad['tipo_imagen'] ?? null;


            //Que es el campo de imagen? Como se llena? Bajar imagen e introducirla en base64-image.de para obtener el codigo
            if ($existeLocalidad && $existeTipoPropiedad && $vDomicilio && $vCantidad_huespedes && $vFecha_inicio_disponibilidad && $vCantidad_dias && $vDisponible && $vValor_noche) {
                // Adaptación a valor shortInt que admite la BD MySQL
                if ($nuevaPropiedad['disponible'] === false)
                    $nuevaPropiedad['disponible'] = 0;
                if ($vCochera && $nuevaPropiedad['cochera'] === false)
                    $nuevaPropiedad['cochera'] = 0;

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
                $this->data['Codigo'] = 200;
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Data'] = $nuevaPropiedad;
                $this->data['Codigo'] = 400;

                // ID INEXISTENTE EN OTRAS TABLAS
                if (!$existeLocalidad)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['localidad_id' => "No existe el ID de localidad ingresado. Verifique la existencia del campo y su valor "]);
                if (!$existeTipoPropiedad)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['tipo_propiedad_id' => "No existe el ID del tipo de propiedad ingresado. Verifique la existencia del campo y su valor"]);

                // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                if (!$vDomicilio)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['domicilio' => "Formato incorrecto del campo. Debe ser un string/cadena no vacia"]);
                if (!$vCantidad_huespedes)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_huespedes' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                if (!$vFecha_inicio_disponibilidad)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_inicio_disponibilidad' => "Formato incorrecto del campo. Respetar formato de tipo YYYY-mm-dd (posterior a la fecha actual)"]);
                if (!$vCantidad_dias)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_dias' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                if (!$vDisponible)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['disponible' => "Formato incorrecto del campo. Respetar formato de tipo boolean (true/false)"]);
                if (!$vValor_noche)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['valor_noche' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);

                // (opcionales)
                if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_habitaciones' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                if ($cantidadBanios && !$vCantidad_banios)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_banios' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                if ($cochera && !$vCochera)
                    $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cochera' => "Formato incorrecto del campo. Respetar formato de tipo boolean (true/false)"]);

                // Falta imagen y tipo de imagen
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


            $buscarPropiedad = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL');
            $buscarPropiedad->execute([':idURL' => $id]);
            $propiedad = $buscarPropiedad->fetch(PDO::FETCH_ASSOC);

            if ($propiedad) {
                $editarPropiedad = $request->getParsedBody();

                $fechaActual = date('Y-m-d');

                // VALIDACIONES DE FORMATOS
                $vDomicilio = isset($editarPropiedad['domicilio']) && is_string($editarPropiedad['domicilio']) && !empty(trim($editarPropiedad['domicilio']));
                $vCantidad_huespedes = isset($editarPropiedad['cantidad_huespedes']) && (is_int($editarPropiedad['cantidad_huespedes']) && $editarPropiedad['cantidad_huespedes'] > 0);
                $vFecha_inicio_disponibilidad = isset($editarPropiedad['fecha_inicio_disponibilidad']) && $editarPropiedad['fecha_inicio_disponibilidad'] > $fechaActual;
                $vCantidad_dias = isset($editarPropiedad['cantidad_dias']) && (is_int($editarPropiedad['cantidad_dias']) && $editarPropiedad['cantidad_dias'] > 0);
                $vValor_noche = isset($editarPropiedad['valor_noche']) && (is_int($editarPropiedad['valor_noche']) && $editarPropiedad['valor_noche'] > 0);
                $vDisponible = isset($editarPropiedad['disponible']) && is_bool($editarPropiedad['disponible']);
                $vLocalidadID = isset($editarPropiedad['localidad_id']) && is_int($editarPropiedad['localidad_id']) && $editarPropiedad['localidad_id'] > 0;
                $vTipoPropiedadID = isset($editarPropiedad['tipo_propiedad_id']) && is_int($editarPropiedad['tipo_propiedad_id']) && $editarPropiedad['tipo_propiedad_id'] > 0;

                // Validaciones opcionales
                $vCantidad_habitaciones = isset($editarPropiedad['cantidad_habitaciones']) && (is_int($editarPropiedad['cantidad_habitaciones']) && $editarPropiedad['cantidad_habitaciones'] > 0);
                $vCantidad_banios = isset($editarPropiedad['cantidad_banios']) && (is_int($editarPropiedad['cantidad_banios']) && $editarPropiedad['cantidad_banios'] > 0);
                $vCochera = isset($editarPropiedad['cochera']) && is_bool($editarPropiedad['cochera']);

                // Busca si existe el id de la tabla localidades
                $existeLocalidad = false;
                if ($vLocalidadID) {
                    $tablaLocalidad = $connection->prepare('SELECT * FROM localidades WHERE id = :localidadID');
                    $tablaLocalidad->execute([':localidadID' => $editarPropiedad['localidad_id']]);
                    $existeLocalidad = $tablaLocalidad->fetchColumn();
                }

                // Busca si existe el id de la tabla tipo_propiedades
                $existeTipoPropiedad = false;
                if ($vTipoPropiedadID) {
                    $tablaTipoPropiedad = $connection->prepare('SELECT * FROM tipo_propiedades WHERE id = :tipoPropiedadID');
                    $tablaTipoPropiedad->execute([':tipoPropiedadID' => $editarPropiedad['tipo_propiedad_id']]);
                    $existeTipoPropiedad = $tablaTipoPropiedad->fetchColumn();
                }

                // Obtencion de datos de campos no obligatorios
                $cantidadHabitaciones = $editarPropiedad['cantidad_habitaciones'] ?? null;
                $cantidadBanios = $editarPropiedad['cantidad_banios'] ?? null;
                $cochera = $editarPropiedad['cochera'] ?? null;
                $imagen = $editarPropiedad['imagen'] ?? null;
                $tipo_imagen = $editarPropiedad['tipo_imagen'] ?? null;

                if ($existeLocalidad && $existeTipoPropiedad && $vDomicilio && $vCantidad_huespedes && $vFecha_inicio_disponibilidad && $vCantidad_dias && $vDisponible && $vValor_noche) {
                    // Adaptación a valor shortInt que admite la BD MySQL
                    if ($editarPropiedad['disponible'] === false)
                        $editarPropiedad['disponible'] = 0;
                    if ($vCochera && $editarPropiedad['cochera'] === false)
                        $editarPropiedad['cochera'] = 0;

                    $tablaPropiedades = $connection->prepare('UPDATE propiedades SET id = :idURL, domicilio = :nuevoDomicilio, localidad_id = :nuevaLocalidad_id, cantidad_habitaciones = :nuevaCantidad_habitaciones, cantidad_banios = :nuevaCantidad_banios, cochera = :nuevaCochera, cantidad_huespedes = :nuevaCantidad_huespedes, fecha_inicio_disponibilidad = :nuevaFecha_inicio_disponibilidad, cantidad_dias = :nuevaCantidad_dias, disponible = :nuevoDisponible, valor_noche = :nuevoValor_noche, tipo_propiedad_id = :nuevoTipo_propiedad_id, imagen = :nuevaImagen, tipo_imagen = :nuevoTipo_imagen WHERE id = :idURL');
                    $tablaPropiedades->execute([
                        'idURL' => $id,
                        ':nuevoDomicilio' => $editarPropiedad['domicilio'],
                        ':nuevaLocalidad_id' => $editarPropiedad['localidad_id'],
                        ':nuevaCantidad_habitaciones' => $cantidadHabitaciones,
                        ':nuevaCantidad_banios' => $cantidadBanios,
                        ':nuevaCochera' => $cochera,
                        ':nuevaCantidad_huespedes' => $editarPropiedad['cantidad_huespedes'],
                        ':nuevaFecha_inicio_disponibilidad' => $editarPropiedad['fecha_inicio_disponibilidad'],
                        ':nuevaCantidad_dias' => $editarPropiedad['cantidad_dias'],
                        ':nuevoDisponible' => $editarPropiedad['disponible'],
                        ':nuevoValor_noche' => $editarPropiedad['valor_noche'],
                        ':nuevoTipo_propiedad_id' => $editarPropiedad['tipo_propiedad_id'],
                        ':nuevaImagen' => $imagen,
                        ':nuevoTipo_imagen' => $tipo_imagen,
                    ]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Propiedad editada correctamente';
                    $this->data['Data'] = [
                        'Nueva propiedad' => $editarPropiedad,
                        'Anterior propiedad' => $propiedad
                    ];
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Data'] = $editarPropiedad;
                    $this->data['Codigo'] = 400;

                    // ID INEXISTENTE EN OTRAS TABLAS
                    if (!$existeLocalidad)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['localidad_id' => "No existe el ID de localidad ingresado. Verifique la existencia del campo y su valor "]);
                    if (!$existeTipoPropiedad)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['tipo_propiedad_id' => "No existe el ID del tipo de propiedad ingresado. Verifique la existencia del campo y su valor"]);

                    // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                    if (!$vDomicilio)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['domicilio' => "Formato incorrecto del campo. Debe ser un string/cadena no vacia"]);
                    if (!$vCantidad_huespedes)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_huespedes' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                    if (!$vFecha_inicio_disponibilidad)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_inicio_disponibilidad' => "Formato incorrecto del campo. Respetar formato de tipo YYYY-mm-dd (posterior a la fecha actual)"]);
                    if (!$vCantidad_dias)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_dias' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                    if (!$vDisponible)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['disponible' => "Formato incorrecto del campo. Respetar formato de tipo boolean (true/false)"]);
                    if (!$vValor_noche)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['valor_noche' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);

                    // (opcionales)
                    if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_habitaciones' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                    if ($cantidadBanios && !$vCantidad_banios)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_banios' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                    if ($cochera && !$vCochera)
                        $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cochera' => "Formato incorrecto del campo. Respetar formato de tipo boolean (true/false)"]);
                }

            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'Propiedad a editar no encontrada. Revisar ID.';
                $this->data['Codigo'] = 404;
            }

            return $this->HTTPCodeError($response);

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getLine();

        }

        $response->getBody()->write(json_encode($this->data));
        return $response;

    }

    // SI HAY UNA RESERVA HECHA CON EL INQUILINO/PROPIEDAD ASOCIADA, NO SE PUEDE BORRAR
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
                $buscarPropiedad = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL');
                $buscarPropiedad->execute([':idURL' => $id]);
                $propiedad = $buscarPropiedad->fetch(PDO::FETCH_ASSOC);
                if ($propiedad) {
                    $this->data['Data'] = $propiedad;
                    $inquilinos = $connection->prepare('DELETE FROM propiedades WHERE id = :idURL');
                    $inquilinos->execute([':idURL' => $id]);
                    $this->data['Status'] = 'Success';
                    $this->data['Mensaje'] = 'Propiedad eliminada correctamente de la base de datos.';
                    $this->data['Codigo'] = 200;
                } else {
                    $this->data['Status'] = 'Fail';
                    $this->data['Mensaje'] = 'El ID ingresado no se encuentra en la base de datos.';
                    $this->data['Data'] = $id;
                    $this->data['Codigo'] = 404;
                }
            } else {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = 'La propiedad no se puede eliminar porque ya se encuentra asociada a una reserva.';
                $this->data['Data'] = ['Reserva' => $existeReserva];
                $this->data['Codigo'] = 400;
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
            $filtro = $request->getQueryParams();
            $connection = $this->getConnection();

            // Arma la orden o busqueda en base a los filtros recibidos
            $sql = "SELECT * FROM propiedades WHERE 1=1";
            $patronNumeroFiltro = "/^[1-9]{1,}$/";

            // Verificar filtros introducidos y su formato
            $vDisponible = true;
            $vLocalidad = true;
            $vFecha = true;
            $vCantidadHuespedes = true;
            foreach ($filtro as $clave => $valor) {
                switch ($clave) {
                    case 'disponible':
                        if ($filtro['disponible'] != "" && ($filtro['disponible'] === "1" || $filtro['disponible'] === "0" || $filtro['disponible'] === "true" || $filtro['disponible'] === "false")) {
                            if ($filtro['disponible'] == false || $filtro['disponible'] == "false")
                                $filtro['disponible'] = 0;
                            else if ($filtro['disponible'] == true)
                                $filtro['disponible'] = 1;
                            $sql .= " AND disponible=" . $filtro['disponible'];
                        } else {
                            $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['disponible' => "Formato incorrecto del campo. Respetar formato de tipo boolean (true/false) o (1/0)"]);
                            $vDisponible = false;
                        }
                        break;

                    case 'localidad_id':
                        if (preg_match($patronNumeroFiltro, $filtro['localidad_id']))
                            $sql .= " AND localidad_id=" . $filtro['localidad_id'];
                        else {
                            $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['localidad_id' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                            $vLocalidad = false;
                        }
                        break;

                    case 'fecha_inicio_disponibilidad':
                        if (strtotime($filtro['fecha_inicio_disponibilidad']))
                            $sql .= " AND fecha_inicio_disponibilidad>=" . $filtro['fecha_inicio_disponibilidad'];
                        else {
                            $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['fecha_inicio_disponibilidad' => "Formato incorrecto del campo. Respetar formato de tipo fecha (YYYY-mm-dd)"]);
                            $vFecha = false;
                        }
                        break;

                    case 'cantidad_huespedes':
                        if (preg_match($patronNumeroFiltro, $filtro['cantidad_huespedes']))
                            $sql .= " AND cantidad_huespedes=" . $filtro['cantidad_huespedes'];
                        else {
                            $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['cantidad_huespedes' => "Formato incorrecto del campo. Respetar formato de tipo entero > 0"]);
                            $vCantidadHuespedes = false;
                        }
                        break;
                }
            }

            $datos = $connection->prepare($sql);
            $datos->execute();
            $datosFiltrados = $datos->fetchAll(PDO::FETCH_ASSOC);

            if (!$vDisponible || !$vLocalidad || !$vFecha || !$vCantidadHuespedes) {
                $this->data['Status'] = 'Fail';
                $this->data['Mensaje'] = array_merge($this->data['Mensaje'], ['Error' => 'Valor de filtrado no valido.']);
                $this->data['Data'] = null;
                $this->data['Codigo'] = 400;
            } else {
                if ($filtro && !$datosFiltrados)
                    $this->data['Mensaje'] = 'No se encontraron propiedades con el filtro ingresado.';
                else {
                    if ($datosFiltrados != null)
                        $this->data['Mensaje'] = 'Propiedades recibidas correctamente.';
                    else
                        $this->data['Mensaje'] = 'No se han encontrado propiedades con el filtro buscado.';

                    $this->data['Data'] = $datosFiltrados;
                }
                $this->data['Status'] = 'Success';
                $this->data['Codigo'] = 200;
            }

            return $this->HTTPCodeError($response);

        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getLine();
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