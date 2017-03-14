<?php

use Phalcon\Loader;
use Phalcon\Cli\Router;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Cli\Dispatcher as MvcDispatcher;
use Doctrine\Common\Cache\RedisCache;
use EasyWeChat\Foundation\Application as WeMP;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Cache\Backend\Redis as BackendRedis;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/apps/Common/config/config.php";
});

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $router = new Router();

    $router->setDefaultModule('Task');
    $router->setDefaultTask('Sherman\Task\Tasks');
    return $router;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    return new $class($dbConfig);
});

// 设置模型缓存服务
$di->set('modelsCache', function () {
    // 默认缓存时间为一天
    $frontCache = new FrontData(["lifetime" => 86400]);
    // Create the Cache setting redis connection options
    $cache = new BackendRedis($frontCache, [
        "host" => getenv('REDIS_HOST'),
        "port" => getenv('REDIS_PORT'),
        'auth' => getenv('REDIS_AUTH'),
        'persistent' => false,
        'index' => 2,
    ]);

    return $cache;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
/*$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});*/

/**
 * Starts the session the first time some component requests the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

$di->setShared('dispatcher', function() {
    $dispatcher = new Phalcon\Cli\Dispatcher();
    $dispatcher->setDefaultNamespace('Sherman\Task\Tasks');
    $dispatcher->setNamespaceName('Sherman\Task\Tasks');
    return $dispatcher;
});

/**
 * Registering easywechat
 */
$di->setShared('easywechat', function () use ($di) {
    $cacheDriver = new RedisCache();

    // 获取 redis 实例
    $shermanRedisClass = \ShermanRedis::getInstance();
    $shermanRedis = $shermanRedisClass->getRedisInstance();
    $cacheDriver->setRedis($shermanRedis);
    $wcConfig = $di->get('config')->wechat->toArray();
    $wcConfig['cache'] = $cacheDriver;
    return new WeMP($wcConfig);
});

/**
 * Registering hashids
 */
$di->setShared('hashids', function () {
    $hashidsConfig = $this->getConfig()->hashids->toArray();
    return new \Hashids\Hashids($hashidsConfig['salt'], $hashidsConfig['length'], $hashidsConfig['alphabet']);
});

$loader = new Loader();
$loader->registerNamespaces([
    'Sherman\Lib\Validators' => APP_PATH . '/lib/validators/',
    'Sherman\Common\Controllers' => APP_PATH . '/apps/Common/controllers/',
    'Sherman\Common\Models' => APP_PATH . '/apps/Common/models/',
]);

$loader->registerFiles([
    APP_PATH . '/apps/Common/utils/functions.php',
    APP_PATH . '/apps/Common/utils/redis.php',
    APP_PATH . '/lib/vendor/autoload.php',
]);

$loader->registerDirs([
    APP_PATH.'/lib/Sms/AliSms',
    APP_PATH.'/lib/Sms/ClSms',
]);
$loader->register();
