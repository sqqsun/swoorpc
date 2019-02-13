<?php
/**
 * Created by PhpStorm.
 * User: sunqiang
 * Date: 2019/2/11
 * Time: 3:32 PM
 */

namespace Swoorpc\DTO;


class RpcOutput
{

    private $code = 0;
    private $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }


    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }
}