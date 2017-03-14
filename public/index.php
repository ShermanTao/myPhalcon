<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;

define('APP_PATH', realpath('..'));

if ($_SERVER["HTTP_HOST"] == 'phalcon.app:8000') {
    define('ENV_TEST', true);//测试环境
} else {
    define('ENV_TEST', false);
}

if (ENV_TEST) {
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    ini_set('display_errors','off');
    error_reporting(E_ERROR);
}

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the right services to provide a full stack framework
     */
    $di = new FactoryDefault();

    /**
     * Include env config环境变量
     */
    require __DIR__ . '/../config/env.php';
    Dotenv::load(__DIR__. '/../config');

    /**
     * Include services
     */
    require __DIR__ . '/../config/services.php';

    /**
     * Handle the request注入应用容器
     */
    $application = new Application($di);

    /**
     * Include modules加载业务模块
     */
    require __DIR__ . '/../config/modules.php';

    /**
     * Include routes加载路由
     */
    require __DIR__ . '/../config/routes.php';

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
