<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/11
 * Time: 4:39 PM
 */

namespace Swoorpc\Client;

use Swoorpc\DTO\RpcInput;
use Swoorpc\DTO\RpcOutput;
use Swoorpc\Exception\RpcException;
use Swoorpc\Swoorpc;
use swoole_client;

class TcpClient
{
    private $_client;
    private $_sock_type;
    private $_host;
    private $_port;

    private $_methodCache = [];


    public function __construct($sock_type, $host, $port)
    {
        $this->_sock_type = $sock_type;
        $this->_host = $host;
        $this->_port = $port;
        $this->_client = new swoole_client($this->_sock_type);
        $this->_connect($this->_host, $this->_port);
    }

    public function _send($mothed, $params)
    {
        $input = new RpcInput($mothed, $params);
        $inputStr = Swoorpc::swoorpc_serialize($input);
        $this->_client->send($inputStr);
        $result = $this->_client->recv();
        $rpcOutput = Swoorpc::swoorpc_unserialize($result);
        if ($rpcOutput->getCode() != 0) {
            throw new RpcException($rpcOutput->getCode(), $rpcOutput->getMessage());
        }
        return $rpcOutput->getMessage();
    }

    public function __destruct()
    {
        $this->_client->close();
    }

    private function _connect($host, $port)
    {
        if (!$this->_client->connect($host, $port, -1)) {
            throw new RpcException("连接失败:{$host}:{$port}");
        }
    }


    public function __get($name)
    {

        if (isset($this->_methodCache[$name])) {
            return $this->_methodCache[$name];
        }
        $method = new Proxy($this, $name);
        $this->_methodCache[$name] = $method;
        return $method;

        // TODO: Implement __get() method.
    }

    public function __call($name, $arguments)
    {
        return $this->_send($name, $arguments);
    }
}