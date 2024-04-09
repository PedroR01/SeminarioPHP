<?php

use Slim\Factory\AppFactory;

use endpoints\Localidades;

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

// '/' hace referencia al localHost.
// ACÁ VAN LOS ENDPOINTS -- En este caso, que es local, nosotros definimos el endpoint. Si fuera en un server, habria que ver bien la URL.
// Si yo quisiera podria poner '/pepe' y mientras ingrese en localHost:80/pepe se van a mostrar todos los datos
Localidades::crear($app);
crear($app);
editar($app);
eliminar($app);

$app->run();
