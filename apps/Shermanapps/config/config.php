<?php

return new \Phalcon\Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => getenv('DB_HOST'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'dbname'   => getenv('DB_DATABASE'),
        'charset'  => 'utf8',
    ],
    'application' => [
        'controllersDir' => __DIR__ . '/../controllers/',
        'modelsDir'      => __DIR__ . '/../models/',
        'baseUri'        => '/'
    ],
    'app_env' => getenv('APP_ENV'),
]);
