<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/13
 * Time: 1:34 PM
 */
namespace Swoorpc;

class HClients
{

    private static $_instance;
    private static $clients = array();

    private function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    function __get($name)
    {
        $client = isset(self::$clients[$name]) ? self::$clients[$name] : null;
        if (!$client) {
            $config = config('swoorpc.client.uris');
            $uri = $config[$name];
            $client = Swoorpc::createClient($uri);
            self::$clients[$name] = $client;
        }
        return $client;
    }
}