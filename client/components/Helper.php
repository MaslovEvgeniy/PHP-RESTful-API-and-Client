<?php
/**
 * Created by PhpStorm.
 * User: maslo
 * Date: 22-Jun-17
 * Time: 16:53
 */

namespace app\components;

use SimpleXMLElement;

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
}