<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/15
 * Time: 10:31 AM
 */

namespace Swoorpc\Client;

use Swoorpc\DTO\RpcInput;
use Swoorpc\DTO\RpcOutput;
use Swoorpc\Exception\RpcException;
use Swoorpc\Swoorpc;
use swoole_client;

class TcpAsyncClient
{
    private static $options = [
        'open_length_check' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,     //第N个字节是包长度的值
        'package_body_offset' => 4,       //第几个字节开始计算长度
    ];

    private $_client;
    private $_sock_type;
    private $_host;
    private $_port;
    private $_options;

    private $_methodCache = [];

    private $_isConnected = false;
    private $_connecting = false;

    public function __construct($config, $host, $port)
    {
        $this->_sock_type = SWOOLE_TCP;
        $this->_options = $config['options'];
        $this->_host = $host;
        $this->_port = $port;
        $this->_connect();
    }

    public function _send($mothed, $params, $recount = 5)
    {

        if ($this->_isConnected) {
            return $this->_handle($mothed, $params);
        } else {
//            if ( $recount > 0) {
//                sleep(1);
//                if (!$this->_connecting) {
//                    $this->_connect();
//                }
//                return $this->_send($mothed, $params, $recount - 1);
//            }
        }
    }

    private function _connect()
    {
        unset($this->_client);
        $this->_client = new swoole_client($this->_sock_type, SWOOLE_SOCK_ASYNC);
        $this->_client->set(array_merge($this->_options, self::$options));
        $this->_client->on('connect', [$this, '_onConnect']);
        $this->_client->on('receive', [$this, '_onReceive']);
        $this->_client->on('close', [$this, '_onClose']);
        $this->_client->on('error', [$this, '_onError']);
        $isconnection = $this->_client->connect($this->_host, $this->_port, 3);
        $this->_connecting = true;
        return $isconnection;
    }

    public function _onReceive($cli, $data)
    {
        //\Log::info($data);


    }

    public function _onConnect($cli)
    {
        $this->_isConnected = true;
        $this->_connecting = false;
    }

    public function _onClose($cli)
    {
        $this->_isConnected = false;
        $this->_connecting = false;
    }

    public function _onError($cli)
    {
        $this->_isConnected = false;
        $this->_connecting = false;
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

    private function _handle($mothed, $params)
    {
        $input = new RpcInput($mothed, $params);
        $inputStr = Swoorpc::swoorpc_serialize($input);
        $requestStr = pack(self::$options['package_length_type'], strlen($inputStr)) . $inputStr;
        return $this->_client->send($requestStr);
    }
}