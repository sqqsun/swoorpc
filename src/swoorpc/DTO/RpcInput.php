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
    private $options;

    public function __construct($mothed, $params, $options = null)
    {
        $this->mothed = $mothed;
        $this->params = $params;
        $this->options = $options;
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

    public function getOptions()
    {
        return $this->options;
    }

}