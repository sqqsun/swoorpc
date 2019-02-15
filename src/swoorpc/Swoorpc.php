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


    public static function createClient($config, $uri, $is_sync = false)
    {
        $client = null;

        $uri = parse_url($uri);
        $scheme = $uri['scheme'];
        $host = $uri['host'];
        $port = $uri['port'];

        switch ($scheme) {
            case 'tcp':
                if ($is_sync) {
                    $client = new Client\TcpAsyncClient($config, $host, $port);
                } else {
                    $client = new Client\TcpSyncClient($config, $host, $port);
                }

                break;
            default:
                //$client = new Client\TcpSyncClient($config, $host, $port);
                break;
        }

        return $client;
    }


    public static function swoorpc_serialize($obj)
    {
        $str = gzcompress(serialize($obj));
        return $str;
    }

    public static function swoorpc_unserialize($str)
    {
        $obj = unserialize(gzuncompress($str));
        return $obj;
    }
}