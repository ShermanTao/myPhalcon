<?php

namespace Sherman\User;


use Phalcon\Loader;
use Phalcon\Logger;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {

        $loader = new Loader();

        $loader->registerNamespaces([
            'Sherman\User\Controllers' => __DIR__ . '/controllers/',
            'Sherman\User\Models' => __DIR__ . '/models/',
            'Sherman\Shermanapps\Models' => APP_PATH . '/apps/Shermanapps/models/',
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Read configuration
         */
        $config = include APP_PATH . "/apps/User/config/config.php";

        /**
         * Setting up the view component
         */
        $di['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');

            return $view;
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['db'] = function () use ($config) {
            $eventsManager = new EventsManager();

            if ($config['app_env'] == 'develop') {
                $logger = new FileLogger(APP_PATH."/logs/sql.log");

                // Listen all the database events
                $eventsManager->attach('db', function ($event, $connection) use ($logger) {
                    if ($event->getType() == 'beforeQuery') {
                        $logger->log($connection->getSQLStatement(), Logger::INFO);
                    }
                });
            }

            $config = $config->database->toArray();

            $dbAdapter = '\Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
            unset($config['adapter']);

            $connection = new $dbAdapter($config);

            // Assign the eventsManager to the db adapter instance
            $connection->setEventsManager($eventsManager);

            return $connection;
        };
    }

}
