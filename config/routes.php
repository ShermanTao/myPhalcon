<?php

$router = $di->get("router");

$router->setUriSource($router::URI_SOURCE_SERVER_REQUEST_URI);

foreach ($application->getModules() as $key => $module) {

    $namespace = str_replace('Module', 'Controllers', $module["className"]);

    $router->add('/'.$key.'/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'Index',
        'action' => 'index',
        'params' => 1
    ])->setName($key);

    $router->add('/'.$key.'/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ]);

    $router->add('/'.$key.'/:controller/:action/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3,
    ]);
}

$router->add('/', []);

$router->notFound(
    array(
        'module'    =>  'Common',
        'namespace' =>  'Sherman\Common\Controllers',
        'controller' => 'Index',
        'action' => 'notFound',
    )
);

$di->set("router", $router);
