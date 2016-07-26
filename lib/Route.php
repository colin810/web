<?php
namespace lib;

use \lib\Register;

class Route
{
    protected $module;
    protected $controller;
    protected $action;

    const DEFAULT_MODULE     = 'web';
    const DEFAULT_CONTROLLER = 'default';
    const DEFAULT_ACTION     = 'index';
    const CONTROLLER_SUFFIX  = 'Controller';
    const ACTION_SUFFIX      = 'Action';
    const HTML_SUFFIX        = '.html';

    /**
     * [run description]
     * @return [type] [description]
     */
    public function run()
    {
        $uri        = $_SERVER['REQUEST_URI'];
        list($path) = explode('?', $uri);
        $path       = trim($path, '/');
        $path       = str_replace(self::HTML_SUFFIX, '', $path);
        $length     = 0;
        if ($path) {
            $arr    = explode('/', $path);
            $length = count($arr);
        }
        switch ($length) {
            case '3':
                $this->module     = $arr[0];
                $this->controller = $arr[1];
                $this->action     = $arr[2];
                break;

            case '2':
                $this->module     = self::DEFAULT_MODULE;
                $this->controller = $arr[0];
                $this->action     = $arr[1];
                break;

            case '1':
                $this->module     = self::DEFAULT_MODULE;
                $this->controller = self::DEFAULT_CONTROLLER;
                $this->action     = $arr[0];
                break;

            default:
                $this->module     = self::DEFAULT_MODULE;
                $this->controller = self::DEFAULT_CONTROLLER;
                $this->action     = self::DEFAULT_ACTION;
                break;
        }

        Register::set('module', $this->module);
        Register::set('controller', $this->controller);
        Register::set('action', $this->action);

        $str_class  = '\\' . $this->module . '\\controller\\' . ucwords($this->controller) . self::CONTROLLER_SUFFIX;
        $str_action = $this->action . self::ACTION_SUFFIX;

        if (class_exists($str_class)) {
            $class = new $str_class();
        } else {
            die("Class '" . $this->controller . "' doesn't exists!");
        }

        if (method_exists($class, $str_action)) {
            $class->$str_action();
        } else {
            die("Action '" . $this->action . "' doesn't exists!");
        }
    }
}
