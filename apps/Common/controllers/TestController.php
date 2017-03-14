<?php

/**
 * User: sherman
 * Date: 2016/9/2
 * Time: 14:00
 */
namespace Sherman\Common\Controllers;

//use Phalcon\Cache\Backend\Redis as Redis;
//use Phalcon\Cache\Frontend\Data as FrontData;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_RichText;

class TestController extends ControllerBase
{
    public function redisAction()
    {
        $obj = new \Redis();
        $rs = $obj->connect("192.168.56.103");
        $obj->select(5);
        $obj->set("test", '1111');
        var_dump($obj->get("test"));
//        return '111';
//        // Cache data for 2 days
//        $frontCache = new FrontData([
//            'lifetime' => 172800
//        ]);
//
//        // Create the Cache setting redis connection options
//        $cache = new Redis($frontCache, [
//            'host' => '192.168.56.103',
//            'port' => 6379,
//            'auth' => '123456',
//            'persistent' => false,
//            'index' => 0,
//         ]);
//
//         // Cache arbitrary data
//         $cache->save('my-data', [1, 2, 3, 4, 5]);
//
//         // Get data
//         $data = $cache->get('my-data');
    }

    public function testEncryptionAction()
    {
        $hashids = new \Hashids\Hashids('SHERMAN',6, 'asIBFOiuGphWbJTCRgZPAfQLMnY4lvEd5z61NkKcStjDXm327ryHx0ewqU8o9V');

        $id = $hashids->encode(1337);
        $numbers = $hashids->decode($id);
        var_dump($id);
        var_dump($numbers);

        $f = range("a","z");
        $s = range("A","Z");
        $n = range("0","9");
        $arr = array_merge($f, $s, $n);
        shuffle($arr);
        var_dump(implode('',$arr));

        $id = encode(23);
        $numbers = decode("rezPjO");
        var_dump($id);
        var_dump($numbers);
    }

}
