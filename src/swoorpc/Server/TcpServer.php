<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/11
 * Time: 11:50 AM
 */
namespace Swoorpc\Server;

use swoole_server;
use Swoorpc\DTO\RpcOutput;
use Swoorpc\Swoorpc;

class TcpServer
{

    private static $options = [
        'open_length_check' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,     //第N个字节是包长度的值
        'package_body_offset' => 4,       //第几个字节开始计算长度
    ];

    private $calls = [];
    private $serv;

    public function __construct($config)
    {
        $this->serv = new swoole_server($config['host'], $config['port'], $config['mode'], $config['sock_type']);
        if ($config['options']) {
            $this->serv->set(array_merge($config['options'], self::$options));
        }
        $this->serv->on('connect', [$this, 'onConnect']);
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('close', [$this, 'onClose']);
        $this->serv->on('task', [$this, 'onTask']);
        $this->serv->on('finish', [$this, 'onFinish']);
    }

    public function onConnect($serv, $fd)
    {
        //echo "onConnect:$fd" . PHP_EOL;
    }

    public function onReceive($serv, $fd, $reactor_id, $data)
    {
        $this->handle($serv, $fd, $data);
    }

    public function onClose($serv, $fd)
    {
        //echo "onClose:$fd" . PHP_EOL;
    }

    public function addMethod($class, $method, $alias)
    {
        $this->calls[$alias] = [$class, $method];
    }

    public function start()
    {
        $this->serv->start();
    }

    private function handle($serv, $fd, $data)
    {
        try {
            $input = Swoorpc::swoorpc_unserialize(substr($data, self::$options['package_body_offset']));
            $options = $input->getOptions();

            if ($options && count($options) > 0) {
                if (isset($options['task']) && $options['task']) {
                    $taskId = $serv->task(['mothed' => $input->getMothed(), 'params' => $input->getParams()]);
                    $output = new RpcOutput(0, $taskId);
                    $dataStr = Swoorpc::swoorpc_serialize($output);
                }

            } else {
                //同步阻塞
                $result = call_user_func_array($this->calls[$input->getMothed()], $input->getParams());
                $output = new RpcOutput(0, $result);
                $dataStr = Swoorpc::swoorpc_serialize($output);
            }


        } catch (\Exception $ex) {
            $output = new RpcOutput($ex->getCode(), $ex->getMessage());
            $dataStr = Swoorpc::swoorpc_serialize($output);
        }
        $dataLen = strlen($dataStr);
        $responseData = pack(self::$options['package_length_type'], $dataLen) . $dataStr;

        $serv->send($fd, $responseData);
    }


    public function onTask(swoole_server $serv, $task_id, $from_id, $data)
    {
        return call_user_func_array($this->calls[$data['mothed']], $data['params']);
    }

    public function onFinish(swoole_server $serv, int $task_id, string $data)
    {
        
    }
}