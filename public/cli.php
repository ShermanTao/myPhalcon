<?php

use Phalcon\Di\FactoryDefault\Cli as CliDi;
use Phalcon\Cli\Console as ConsoleApp;

error_reporting(E_ALL);

define('APP_PATH', realpath(dirname(dirname(__FILE__))));

$di = new CliDi();

/**
 * Include env config环境变量
 */
require __DIR__ . '/../config/env.php';
Dotenv::load(__DIR__. '/../config');

/**
 * Include services
 */
require __DIR__ . '/../config/servicesCli.php';

/**
 * Create a console application
 */
$application = new ConsoleApp($di);

/**
 * Include modules加载业务模块
 */
require __DIR__ . '/../config/modules.php';

/**
 * Include routes加载路由
 */
//require __DIR__ . '/../config/routesCli.php';


/**
 * Process the console arguments
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}
try {
    /**
     * Handle
     */
    $application->handle($arguments);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
