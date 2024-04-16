<?php

// ANOTACIONES -- Autoload en el composer para el NAMESPACE. El NAMESPACE es para poder hacer el enrutamiento con una clase. Faltaria añadir una carpeta src para meter la de endpoints y ahi cambiarla a Endpoints

use Slim\Factory\AppFactory;

use Endpoints\Localidades;
use Endpoints\Inquilino;

require __DIR__ . '/vendor/autoload.php'; //Carga las clases a las que hace referencia arriba

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); //Ya que por defecto PSR-7 no admite formatos JSON o XML, el contenido se necesita decodificar. Este metodo se encarga de esta decodificación para poder tratar con ese tipo de formatos.
$app->addRoutingMiddleware(); //Gestionar error cuando queremos acceder a una ruta no definida (error 404: not found x ej) -- Sin esto te puede tirar 200
$app->addErrorMiddleware(true, true, true); //Permite mostrar los errores del codigo

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    // Configuracion 
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE') // Permite acceder a estos metodos desde cualquier lado en React, evita que tire error.
        ->withHeader('Content-Type', 'application/json')
    ;
});

/* ---- ROUTES ---- */

// -- Localidades -- //
$app->get('/localidades/listar', Localidades::class . ':listar');
$app->post('/localidades/crear', Localidades::class . ':crear');
$app->put('/localidades/{id}/editar', Localidades::class . ':editar');
$app->delete('/localidades/{id}/eliminar', Localidades::class . ':eliminar');

// -- Inquilinos -- //
$app->post('/inquilino/crear', Inquilino::class . ':crear');

/* ----        ---- */


$app->run();
