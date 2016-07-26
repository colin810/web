<?php
namespace lib;

use \lib\Register;

class ViewRender
{

    protected $module;
    protected $controller;
    protected $action;
    protected $baseUrl;

    const EXT    = '.php';
    const OUTEXT = '.html';

    public function __construct()
    {
        $this->module     = Register::get('module');
        $this->controller = Register::get('controller');
        $this->action     = Register::get('action');
        $this->baseUrl    = WEB_ROOT . '\\' . $this->module . '\\view\\';
    }

    /**
     * [display description]
     * @param  [type] $view   [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function display($view, $params = array())
    {
        $this->set($params);
        ob_start();
        $this->render($view);
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        exit;
    }

    /**
     * [render description]
     * @param  [type] $view   [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function render($view, $params = array())
    {
        if (strpos($view, '/') === false) {
            $path = $this->baseUrl . strtolower($this->controller) . '\\' . $view . self::EXT;
        } else {
            $path = $this->baseUrl . $view . self::EXT;
        }
        if (!is_file($path)) {
            die('THIS SCRIPT FILE NOT EXISTS');
        } else {
            require $path;
        }
    }

    /**
     * [createUrl description]
     * @param  [type] $view   [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function createUrl($view, $params = array())
    {
        if (strpos($view, '/') === false) {
            $path = '/' . $this->module . '/' . $this->controller . '/' . $view;
        } else {
            $path = '/' . $this->module . '/' . $view;
        }
        $path .= self::OUTEXT;
        if (!empty($params)) {
            $query_string = '';
            foreach ((array) $params as $key => $value) {
                $query_string .= '&' . $key . '=' . $value;
            }
            $path .= '?' . trim($query_string, '&');
        }
        return $path;
    }

    /**
     * [set description]
     * @param array $array [description]
     */
    public function set($array = array())
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
