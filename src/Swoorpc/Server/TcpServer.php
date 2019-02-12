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

    private $calls = [];
    private $serv;

    public function __construct($config)
    {
        $this->serv = new swoole_server($config['host'], $config['port']);
        if ($config['options']) {
            $this->serv->set($config['options']);
        }
        $this->serv->on('connect', [$this, 'onConnect']);
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('close', [$this, 'onClose']);
    }

    public function onConnect($serv, $fd)
    {
        echo "onConnect:$fd" . PHP_EOL;
    }

    public function onReceive($serv, $fd, $reactor_id, $data)
    {

        try {
            $input = Swoorpc::swoorpc_unserialize($data);
            $result = call_user_func_array($this->calls[$input->getMothed()], $input->getParams());
            $output = new RpcOutput(0, $result);
            $serv->send($fd, Swoorpc::swoorpc_serialize($output));
        } catch (\Exception $ex) {
            $output = new RpcOutput($ex->getCode(), $ex->getMessage());
            $serv->send($fd, Swoorpc::swoorpc_serialize($output));
        }
    }

    public function onClose($serv, $fd)
    {
        echo "onClose:$fd" . PHP_EOL;
    }

    public function addMethod($class, $method, $alias)
    {
        $this->calls[$alias] = [$class, $method];
    }

    public function start()
    {
        $this->serv->start();
    }
}