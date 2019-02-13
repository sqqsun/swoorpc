<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/9
 * Time: 1:04 AM
 */

return [
    'server' => [
        'host' => env('SWOORPC_SERVER_HOST', '0.0.0.0'),
        'port' => env('SWOORPC_SERVER_PORT', 9501),
//        'mode'=>SWOOLE_PROCESS,
//        'sock_type'=>SWOOLE_SOCK_TCP,

        'options' => [ //参见Swoole官方文档
            'worker_num' => 4,    //worker process num
            'max_request' => 50,
//            'reactor_num' => 2, //reactor thread num
//            'backlog' => 128,   //listen backlog
//            'dispatch_mode' => 1,
        ]
    ],

    'client' => [


        'uris' => [
            'client1' => 'tcp://10.10.0.0:9501',
        ]
    ],

];