<?php

namespace app\components;

/**
 * Class ModelResponse
 * @package app\components
 */
class ModelResponse
{
    const TYPE_ERROR = 0;
    const TYPE_RESPONSE = 1;
    const TYPE_LINK = 2;
    const TYPE_OK = 3;
    const TYPE_NOT_AUTH = 4;
    const TYPE_FORBIDDEN = 5;

    private $type;
    private $response;

    public function __construct($type, $response = null)
    {
        $this->type = $type;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getType()
    {
        return $this->type;
    }

}