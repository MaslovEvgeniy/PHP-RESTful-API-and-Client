<?php

namespace app\components;

/**
 * Class Helper contains static helper methods
 * @package app\components
 */
class Helper
{
    /**
     * Sanitizing external inputs
     * @param null $data data to sanitize
     * @return null|string sanitized data
     */
    public static function safeInput($data = null)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public static function generateSwaggerDoc()
    {
        $swagger = \Swagger\scan("/");
        header('Content-Type: application/json');
        echo $swagger;
    }
}