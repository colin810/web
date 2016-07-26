<?php

namespace lib;

class AutoLoader
{
    /**
     * [autoLoad description]
     * @return [type] [description]
     */
    static function autoLoad()
    {
        spl_autoload_register(function ($class) {
            $str_file = WEB_ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if (is_file($str_file)) {
                require_once ($str_file);
            }
        });
    }
}
