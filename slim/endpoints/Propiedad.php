<?php

namespace Endpoints;

use Exception;
use PDO;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Propiedad extends Endpoint
{
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

    private function validarCantidades($body, $campo, $min)
    {
        return (is_int($body["$campo"]) && $body["$campo"] > $min);
    }

    public function crear(Request $request, Response $response) // Puede haber propiedades repetidas - Creo que hice verificaciones de mas
    {
        try {

            $connection = $this->getConnection();

            $nuevaPropiedad = $request->getParsedBody();

            // Primero deberia validar las expresiones regulares de los campos ingresados en el request
            // Verificar los campos requeridos -- ¿Como verifico/inicializo en null aquellos campos no requeridos que no estan en el request?
            $datoDomicilio = isset($nuevaPropiedad['domicilio']);
            $datoCantidad_huespedes = isset($nuevaPropiedad['cantidad_huespedes']);
            $datoFecha_inicio_disponibilidad = isset($nuevaPropiedad['fecha_inicio_disponibilidad']);
            $datoCantidad_dias = isset($nuevaPropiedad['cantidad_dias']);
            $datoDisponible = isset($nuevaPropiedad['disponible']);
            $datoValor_noche = isset($nuevaPropiedad['valor_noche']);

            // Define el formato esperado para la fecha (día-mes-año)
            $formatoFecha = 'd-m-Y';
            $fechaActual = new DateTime;

            // VALIDACIONES DE FORMATOS
            // Intenta crear un objeto DateTime a partir de la cadena para validar el formato introducido por el usuario
            $vFecha = DateTime::createFromFormat($formatoFecha, $nuevaPropiedad['fecha_inicio_disponibilidad']); // Esto devuelve un valor o un boolean?
            $vDomicilio = $this->validador($this->patronDomicilio, $nuevaPropiedad['domicilio']);
            //$vCantidad_huespedes = (is_int($nuevaPropiedad['cantidad_huespedes']) && 0 < $nuevaPropiedad['cantidad_huespedes']);
            $vCantidad_huespedes = $this->validarCantidades($nuevaPropiedad, 'cantidad_huespedes', 0);
            $vCantidad_dias = $this->validarCantidades($nuevaPropiedad, 'cantidad_dias', 0);
            //$vCantidad_dias = (is_int($nuevaPropiedad['cantidad_dias']) && 0 < $nuevaPropiedad['cantidad_dias']);
            $vDisponible = is_bool($nuevaPropiedad['disponible']); // LOS CAMPOS BOOLEANOS ESTAN EN LA BD COMO TINYINT... PUEDO PONER VARIOS VALORES NUMERICOS... Deberia restringirlo a 0 o 1 para saber si es true o false? Ademas true me lo acepta cuando lo envio por Request pero false no... DUDA 
            $vValor_noche = $this->validarCantidades($nuevaPropiedad, 'valor_noche', 0);
            //$vValor_noche = (is_int($nuevaPropiedad['valor_noche']) && 0 < $nuevaPropiedad['valor_noche']);

            // Validaciones opcionales
            $vCantidad_habitaciones = $this->validarCantidades($nuevaPropiedad, 'cantidad_habitaciones', 0);
            $vCantidad_banios = $this->validarCantidades($nuevaPropiedad, 'cantidad_banios', 0);
            $vCochera = is_bool($nuevaPropiedad['cochera']);
            // Nose como validar imagen y tipo_imagen. DUDA

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
            if ($existeLocalidad && $existeTipoPropiedad && $datoDomicilio && $datoCantidad_huespedes && $datoFecha_inicio_disponibilidad && $datoCantidad_dias && $datoDisponible && $datoValor_noche) {
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

                // NO SE HAN INTRODUCIDO VALORES EN LOS CAMPOS OBLIGATORIOS
                if (!$datoDomicilio)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "domicilio" (obligatorio).';
                if (!$datoCantidad_huespedes)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "cantidad_huespedes" (obligatorio).';
                if (!$datoFecha_inicio_disponibilidad)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "fecha_inicio_disponibilidad" (obligatorio).';
                if (!$datoCantidad_dias)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "cantidad_dias" (obligatorio).';
                if (!$datoDisponible)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "disponible" (obligatorio).';
                if (!$datoValor_noche)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "valor_noche" (obligatorio).';

                // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                if (!$vDomicilio)
                    $eFormato['domicilio'] = 'Formato incorrecto de tipo domicilio. Respetar formato de tipo Calle "numeroDeCalle" "numeroDeDomicilio"';
                if (!$vCantidad_huespedes)
                    $eFormato['cantidad_huespedes'] = 'Formato incorrecto de tipo cantidad_huespedes. Respetar formato de tipo entero > 0';
                if (!$vFecha && ($vFecha < $fechaActual))
                    $eFormato['fecha_inicio_disponibilidad'] = 'Formato incorrecto de tipo fecha. Respetar formato de tipo dd-mm-aaaa (posterior a la fecha actual)';
                if (!$vCantidad_dias)
                    $eFormato['cantidad_dias'] = 'Formato incorrecto de tipo cantidad_dias. Respetar formato de tipo entero > 0';
                if (!$vDisponible)
                    $eFormato['disponible'] = 'Formato incorrecto de tipo disponible. Respetar formato de tipo boolean (true/false)';
                if (!$vValor_noche)
                    $eFormato['valor_noche'] = 'Formato incorrecto de tipo valor_noche. Respetar formato de tipo entero > 0';

                // (opcionales)
                if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                    $eFormato['cantidad_habitaciones'] = 'Formato incorrecto de tipo cantidad_habitaciones. Respetar formato de tipo entero > 0';
                if ($cantidadBanios && !$vCantidad_banios)
                    $eFormato['cantidad_banios'] = 'Formato incorrecto de tipo cantidad_banios. Respetar formato de tipo entero > 0';
                if ($cochera && !$vCochera)
                    $eFormato['cochera'] = 'Formato incorrecto de tipo cochera. Respetar formato de tipo boolean (true/false)';
                // Falta imagen y tipo_imagen

                $this->data['Mensaje'] = $this->data['Mensaje'] . 'Errores de formato: ' . $eFormato;
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

            // Primero deberia validar las expresiones regulares de los campos ingresados en el request
            // Verificar los campos requeridos -- ¿Como verifico/inicializo en null aquellos campos no requeridos que no estan en el request?
            $datoDomicilio = isset($nuevaPropiedad['domicilio']);
            $datoCantidad_huespedes = isset($nuevaPropiedad['cantidad_huespedes']);
            $datoFecha_inicio_disponibilidad = isset($nuevaPropiedad['fecha_inicio_disponibilidad']);
            $datoCantidad_dias = isset($nuevaPropiedad['cantidad_dias']);
            $datoDisponible = isset($nuevaPropiedad['disponible']);
            $datoValor_noche = isset($nuevaPropiedad['valor_noche']);

            // Define el formato esperado para la fecha (día-mes-año)
            $formatoFecha = 'd-m-Y';
            $fechaActual = new DateTime;

            // VALIDACIONES DE FORMATOS
            // Intenta crear un objeto DateTime a partir de la cadena para validar el formato introducido por el usuario
            $vFecha = DateTime::createFromFormat($formatoFecha, $nuevaPropiedad['fecha_inicio_disponibilidad']); // Esto devuelve un valor o un boolean?
            $vDomicilio = $this->validador($this->patronDomicilio, $nuevaPropiedad['domicilio']);
            //$vCantidad_huespedes = (is_int($nuevaPropiedad['cantidad_huespedes']) && 0 < $nuevaPropiedad['cantidad_huespedes']);
            $vCantidad_huespedes = $this->validarCantidades($nuevaPropiedad, 'cantidad_huespedes', 0);
            $vCantidad_dias = $this->validarCantidades($nuevaPropiedad, 'cantidad_dias', 0);
            //$vCantidad_dias = (is_int($nuevaPropiedad['cantidad_dias']) && 0 < $nuevaPropiedad['cantidad_dias']);
            $vDisponible = is_bool($nuevaPropiedad['disponible']);
            $vValor_noche = $this->validarCantidades($nuevaPropiedad, 'valor_noche', 0);
            //$vValor_noche = (is_int($nuevaPropiedad['valor_noche']) && 0 < $nuevaPropiedad['valor_noche']);

            // Validaciones opcionales
            $vCantidad_habitaciones = $this->validarCantidades($nuevaPropiedad, 'cantidad_habitaciones', 0);
            $vCantidad_banios = $this->validarCantidades($nuevaPropiedad, 'cantidad_banios', 0);
            $vCochera = is_bool($nuevaPropiedad['cochera']);
            // Nose como validar imagen y tipo_imagen. DUDA

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


            // Como puede haber propiedades repetidas no hace falta que chequee ningun campo "único". DUDA
            if ($existeLocalidad && $existeTipoPropiedad && $datoDomicilio && $datoCantidad_huespedes && $datoFecha_inicio_disponibilidad && $datoCantidad_dias && $datoDisponible && $datoValor_noche) {
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

                // NO SE HAN INTRODUCIDO VALORES EN LOS CAMPOS OBLIGATORIOS
                if (!$datoDomicilio)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "domicilio" (obligatorio).';
                if (!$datoCantidad_huespedes)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "cantidad_huespedes" (obligatorio).';
                if (!$datoFecha_inicio_disponibilidad)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "fecha_inicio_disponibilidad" (obligatorio).';
                if (!$datoCantidad_dias)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "cantidad_dias" (obligatorio).';
                if (!$datoDisponible)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "disponible" (obligatorio).';
                if (!$datoValor_noche)
                    $this->data['Mensaje'] = $this->data['Mensaje'] . 'No se ha introducido ningun valor en el campo "valor_noche" (obligatorio).';

                // NO SE HA RESPETADO EL FORMATO DE ALGUN CAMPO (obligatorios)
                if (!$vDomicilio)
                    $eFormato['domicilio'] = 'Formato incorrecto de tipo domicilio. Respetar formato de tipo Calle "numeroDeCalle" "numeroDeDomicilio"';
                if (!$vCantidad_huespedes)
                    $eFormato['cantidad_huespedes'] = 'Formato incorrecto de tipo cantidad_huespedes. Respetar formato de tipo entero > 0';
                if (!$vFecha && ($vFecha < $fechaActual)) // NO FUNCIONA. Puedo poner una fecha menor a la actual. DUDA
                    $eFormato['fecha_inicio_disponibilidad'] = 'Formato incorrecto de tipo fecha. Respetar formato de tipo dd-mm-aaaa (posterior a la fecha actual)';
                if (!$vCantidad_dias)
                    $eFormato['cantidad_dias'] = 'Formato incorrecto de tipo cantidad_dias. Respetar formato de tipo entero > 0';
                if (!$vDisponible)
                    $eFormato['disponible'] = 'Formato incorrecto de tipo disponible. Respetar formato de tipo boolean (true/false)';
                if (!$vValor_noche)
                    $eFormato['valor_noche'] = 'Formato incorrecto de tipo valor_noche. Respetar formato de tipo entero > 0';

                // (opcionales)
                if ($cantidadHabitaciones && !$vCantidad_habitaciones)
                    $eFormato['cantidad_habitaciones'] = 'Formato incorrecto de tipo cantidad_habitaciones. Respetar formato de tipo entero > 0';
                if ($cantidadBanios && !$vCantidad_banios)
                    $eFormato['cantidad_banios'] = 'Formato incorrecto de tipo cantidad_banios. Respetar formato de tipo entero > 0';
                if ($cochera && !$vCochera)
                    $eFormato['cochera'] = 'Formato incorrecto de tipo cochera. Respetar formato de tipo boolean (true/false)';
                // Falta imagen y tipo_imagen DUDA

                $this->data['Mensaje'] = $this->data['Mensaje'] . 'Errores de formato: ' . $eFormato;
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

            $inquilinos = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL');
            $inquilinos->execute([':idURL' => $id]);
            $existeID = $inquilinos->fetchColumn();

            if ($existeID) {
                $inquilinos = $connection->prepare('DELETE FROM propiedades WHERE id = :idURL');
                $inquilinos->execute([':idURL' => $id]);
                $this->data['Status'] = 'Success';
                $this->data['Mensaje'] = 'Propiedad eliminada correctamente de la base de datos.';
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

    public function listar(Request $request, Response $response, $args) // Se deben mostrar todos los datos o solo los del filtro? Lo resolvi bien?? DUDA
    {
        try {
            $connection = $this->getConnection();
            // Disponibles
            $query = $connection->query('SELECT * FROM propiedades WHERE disponible = 1 
            ORDER BY localidad_id ASC, fecha_inicio_disponibilidad ASC, cantidad_huespedes DESC');
            $datosDisponibles = $query->fetchAll(PDO::FETCH_ASSOC);
            // No disponibles
            $query = $connection->query('SELECT * FROM propiedades WHERE disponible = 0 
            ORDER BY localidad_id ASC, fecha_inicio_disponibilidad ASC, cantidad_huespedes DESC');
            $datosNoDisponibles = $query->fetchAll(PDO::FETCH_ASSOC);

            $this->data['Status'] = 'Success';
            $this->data['Mensaje'] = 'Propiedades recibidas correctamente.';
            $this->data['Data'] = array_merge($datosDisponibles, $datosNoDisponibles);
            $this->data['Codigo'] = '200';
        } catch (Exception $e) {
            $this->data['Status'] = 'Throw Exception';
            $this->data['Mensaje'] = $e->getMessage();
            $this->data['Codigo'] = $e->getCode();
        }

        $response->getBody()->write(json_encode($this->data));
        return $response;
    }

    public function verPropiedad(Request $request, Response $response, $args) // Que diferencia principal tengo entre prepare y query para este caso y el de listar? DUDA
    {
        try {
            $id = $args['id'];
            $connection = $this->getConnection();

            $propiedad = $connection->prepare('SELECT * FROM propiedades WHERE id = :idURL');
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