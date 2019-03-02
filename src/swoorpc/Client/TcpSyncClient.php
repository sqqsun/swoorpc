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

class TcpSyncClient
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


    public function __construct($config, $host, $port)
    {
        $this->_sock_type = $config['sock_type'];
        $this->_options = $config['options'];
        $this->_host = $host;
        $this->_port = $port;
        $this->_connect();
    }

    public function _send($mothed, $params, $options = null, $recount = 5)
    {
        try {
            $result = $this->_handle($mothed, $params, $options);
        } catch (\Exception $ex) {  //异常重试
            if ($recount > 0) {
                sleep(1);
                $this->_connect();
                return $this->_send($mothed, $params, $options, $recount - 1);
            }
        }

        if (!$result && $recount > 0) { //重试
            if ($recount <= 3) {
                sleep(1);
            }
            $this->_connect();
            return $this->_send($mothed, $params, $options, $recount - 1);
        }


        $rpcOutput = Swoorpc::swoorpc_unserialize(substr($result, self::$options['package_body_offset']));
        if ($rpcOutput->getCode() != 0) {
            throw new RpcException($rpcOutput->getMessage(), $rpcOutput->getCode());
        }
        return $rpcOutput->getMessage();
    }


    private function _connect()
    {

        unset($this->_client);
        $this->_client = new swoole_client($this->_sock_type);
        $this->_client->set(array_merge($this->_options, self::$options));
        $isconnection = $this->_client->connect($this->_host, $this->_port, 3);
        return $isconnection;
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


    private function _handle($mothed, $params, $options = null)
    {
        $input = new RpcInput($mothed, $params, $options);
        $inputStr = Swoorpc::swoorpc_serialize($input);
        $requestStr = pack(self::$options['package_length_type'], strlen($inputStr)) . $inputStr;
        $this->_client->send($requestStr);
        $result = $this->_client->recv();
        return $result;
    }
}