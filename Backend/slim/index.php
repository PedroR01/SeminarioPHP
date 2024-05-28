<?php

use Slim\Factory\AppFactory;

use Endpoints\Localidades;
use Endpoints\Tipo_Propiedad;
use Endpoints\Inquilino;
use Endpoints\Propiedad;
use Endpoints\Reserva;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    // Configuracion 
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
        ->withHeader('Content-Type', 'application/json')
    ;
});

/* ---- ROUTES ---- */

// -- Localidades -- //
$app->get('/localidades', Localidades::class . ':listar');
$app->post('/localidades', Localidades::class . ':crear');
$app->put('/localidades/{id}', Localidades::class . ':editar');
$app->delete('/localidades/{id}', Localidades::class . ':eliminar');

// -- Tipo_Propiedades -- //
$app->get('/tipos_propiedad', Tipo_Propiedad::class . ':listar');
$app->post('/tipos_propiedad', Tipo_Propiedad::class . ':crear');
$app->put('/tipos_propiedad/{id}', Tipo_Propiedad::class . ':editar');
$app->delete('/tipos_propiedad/{id}', Tipo_Propiedad::class . ':eliminar');

// -- Inquilinos -- //
$app->get('/inquilinos', Inquilino::class . ':listar');
$app->get('/inquilinos/{id}', Inquilino::class . ':verInquilino');
$app->get('/inquilinos/{idInquilino}/reservas', Inquilino::class . ':historial');
$app->post('/inquilinos', Inquilino::class . ':crear');
$app->put('/inquilinos/{id}', Inquilino::class . ':editar');
$app->delete('/inquilinos/{id}', Inquilino::class . ':eliminar');

// -- Propiedades -- //
$app->get('/propiedades', Propiedad::class . ':listar');
$app->get('/propiedades/{id}', Propiedad::class . ':verPropiedad');
$app->post('/propiedades', Propiedad::class . ':crear');
$app->put('/propiedades/{id}', Propiedad::class . ':editar');
$app->delete('/propiedades/{id}', Propiedad::class . ':eliminar');

// -- Reservas -- //
$app->get('/reservas', Reserva::class . ':listar');
$app->post('/reservas', Reserva::class . ':crear');
$app->put('/reservas/{id}', Reserva::class . ':editar');
$app->delete('/reservas/{id}', Reserva::class . ':eliminar');


/* ----        ---- */


$app->run();
