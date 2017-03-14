<?php
/**
 * Created by PhpStorm.
 * User: sherman
 * Date: 2016/9/6
 * Time: 20:26
 */
namespace Sherman\Task\Tasks;

class MainTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        echo "\nThis is the default task and the default action \n";
        taskLog(['\nThis is the default task and the default action \n']);
    }

}