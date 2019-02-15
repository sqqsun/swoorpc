<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/9
 * Time: 1:04 AM
 */

return [
    'server' => [
        'host' => env('SWOORPC_SERVER_HOST', '0.0.0.0'), //监听地址
        'port' => env('SWOORPC_SERVER_PORT', 9501), //监听端口
        'mode' => SWOOLE_PROCESS, //运行的模式
        'sock_type' => SWOOLE_SOCK_TCP,//指定Socket的类型

        'options' => [ //参见Swoole官方文档
            'worker_num' => 4,    //worker process num
            'max_request' => 256, //设置worker进程的最大任务数
            'buffer_output_size' => 1024 * 1024 * 2, //发送输出缓存区内存尺寸
            'package_max_length' => 1024 * 1024 * 2,  //协议最大长度
            'socket_buffer_size' => 1024 * 1024 * 2,
            'task_worker_num' => 100, //task进程的数量
            'task_max_request' => 100, //task进程的最大任务数
        ]
    ],

    'client' => [
        'sock_type' => SWOOLE_TCP | SWOOLE_KEEP,
        'options' => [ //参见Swoole官方文档
            'package_max_length' => 1024 * 1024 * 2,  //协议最大长度
        ],

    ],

    'hclients' => [//辅助生成客户端列表
//        'serviceDemo' => 'tcp://10.10.0.0:9501',
    ]
];