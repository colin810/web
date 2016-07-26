<?php
namespace lib;

use \lib\Register;

class ConfigLoader
{
    /**
     * [load description]
     * @param  [type] $name [description]
     * @param  string $ext  [description]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public static function load($name, $ext = '.php', $path = '')
    {

        if (!Register::get($name)) {
            if (empty($path)) {
                $path = CONFIG_PATH;
            }
            $file = $path . '/' . $name . $ext;
            if (!is_file($file)) {
                return false;
            }

            switch ($ext) {
                case '.ini':
                    $value = parse_ini_file($file, true);
                    break;
                case '.php':
                    $value = require $file;
                    break;
                default:
                    $value = require $file;
                    break;
            }
            Register::set($name, $value);
        }
        return Register::get($name);
    }
}
