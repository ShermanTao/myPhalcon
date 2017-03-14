<?php

/**
 * Register application modules
 */
$application->registerModules([
    //公共模块
    'Common' => [
        'className' => 'Sherman\Common\Module',
        'path' => __DIR__ . '/../apps/Common/Module.php'
    ],
    //帐号
    'Account' => [
        'className' => 'Sherman\Account\Module',
        'path' => __DIR__ . '/../apps/Account/Module.php'
    ],
    //用户
    'User' => [
        'className' => 'Sherman\User\Module',
        'path' => __DIR__ . '/../apps/User/Module.php'
    ],
    //应用
    'Shermanapps' => [
        'className' => 'Sherman\Shermanapps\Module',
        'path' => __DIR__ . '/../apps/Shermanapps/Module.php'
    ],
    //CLI
    'Task' => [
        'className' => 'Sherman\Task\Module',
        'path' => __DIR__ . '/../apps/Task/Module.php'
    ],
]);
