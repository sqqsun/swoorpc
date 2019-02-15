<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/15
 * Time: 1:43 PM
 */

namespace Swoorpc\Client;


class ProxyAsync
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
        switch ($name) {
            case 'task':
                $options = ['task' => true];
                break;
            case 'timerAfter':
                list($times) = array_slice($arguments, -1);
                if (!is_array($times)) {
                    $times = [$times];
                }
                $arguments = array_slice($arguments, 0, count($arguments) - 1);
                $options['timerAfter'] = $times;
                break;

            default:
                $options = [];
                break;
        }
        return $this->client->_send($this->name, $arguments, $options);
    }
}