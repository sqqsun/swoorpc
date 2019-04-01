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
        $result = null;
        try {
            $result = $this->_handle($mothed, $params, $options);
        } catch (\Exception $ex) {  //异常重试
            if ($recount > 0) {
                $this->_connect(true);
                return $this->_send($mothed, $params, $options, $recount - 1);
            }
        }

        if ((null == $result || false === $result || $result == '') && $recount > 0) { //重试
            $this->_connect(true);
            return $this->_send($mothed, $params, $options, $recount - 1);
        }


        if (null == $result || false === $result || $result == '') {
            $errMessage = 'RPC 请求异常:' . $mothed . json_encode($params);
            \Log::error($errMessage);
            \Log::error([$result]);
            throw new RpcException($errMessage);
        }


        $rpcOutput = Swoorpc::swoorpc_unserialize(substr($result, self::$options['package_body_offset']));
        if ($rpcOutput->getCode() != 0) {
            throw new RpcException($rpcOutput->getMessage(), $rpcOutput->getCode());
        }
        return $rpcOutput->getMessage();
    }


    private function _connect($reconnect = false)
    {

        if ($reconnect) {
            if (isset($this->_client) && null != $this->_client) {
                try {
                    $this->_client->close(true);
                } catch (\Exception $ex) {
                    \Log::error($ex);
                }
//                unset($this->_client);
//                $this->_client = null;
            }
        }

        $this->_client = new swoole_client($this->_sock_type);
        $this->_client->set(array_merge($this->_options, self::$options));

        for ($i = 0; $i < 10; $i++) {
            $ret = $this->_client->connect($this->_host, $this->_port, -1);
            if ($ret === false and ($this->_client->errCode == 114 or $this->_client->errCode == 115)) {
                //强制关闭，重连
                try {
                    $this->_client->close(true);
                } catch (\Exception $ex) {
                    \Log::error($ex);
                }
                continue;
            } else {
                break;
            }
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


    private function _handle($mothed, $params, $options = null)
    {
        $input = new RpcInput($mothed, $params, $options);
        $inputStr = Swoorpc::swoorpc_serialize($input);
        $requestStr = pack(self::$options['package_length_type'], strlen($inputStr)) . $inputStr;
        if ($this->_client->send($requestStr) === false) {
            return false;
        }
        $result = $this->_client->recv();
        return $result;
    }
}