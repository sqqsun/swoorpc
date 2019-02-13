<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/8
 * Time: 8:26 PM
 */

namespace Swoorpc;


class Swoorpc
{


    public static function createServer($config)
    {
        $serv = new Server\TcpServer($config);
        return $serv;
    }


    public static function createClient($uri)
    {
        $client = null;

        $uri = parse_url($uri);
        $scheme = $uri['scheme'];
        $host = $uri['host'];
        $port = $uri['port'];

        switch ($scheme) {
            case 'tcp':
                $client = new Client\TcpClient(SWOOLE_TCP, $host, $port);
                break;
            default:
                $client = new Client\TcpClient(SWOOLE_TCP, $host, $port);
                break;
        }

        return $client;
    }


    public static function swoorpc_serialize($obj)
    {
        //压缩
        $str = serialize($obj);
        return $str;
    }

    public static function swoorpc_unserialize($str)
    {
        $obj = unserialize($str);
        return $obj;
    }
}