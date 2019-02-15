<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/15
 * Time: 1:43 PM
 */

namespace Swoorpc\Client;


class TaskProxy
{
    private $client;
    private $name;


    public function __construct($client, $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    public function __call($name, $arguments)
    {
        if ($name == 'task') {
            $options = ['task' => true];
            return $this->client->_send($this->name, $arguments, $options);
        }
    }
}