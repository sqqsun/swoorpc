<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/11
 * Time: 5:57 PM
 */

namespace Swoorpc\Client;


class Proxy
{
    private $client;
    private $prefix;

    public function __construct($client, $prefix)
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    public function __call($name, $arguments)
    {
        return $this->client->_send($this->prefix . '_' . $name, $arguments);
    }
}