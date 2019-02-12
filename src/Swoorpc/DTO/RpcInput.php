<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/11
 * Time: 3:27 PM
 */

namespace Swoorpc\DTO;


class RpcInput
{
    private $mothed;
    private $params;

    public function __construct($mothed, $params)
    {
        $this->mothed = $mothed;
        $this->params = $params;
    }

    public function getMothed()
    {
        return $this->mothed;
    }

    public function getParams()
    {
        if (null == $this->params) {
            $this->params = [];
        }
        return $this->params;
    }
}