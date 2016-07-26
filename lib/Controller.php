<?php
namespace lib;

use \lib\ViewRender;

class Controller
{
    protected $render;
    public function __construct()
    {
        $this->render = new ViewRender();
    }

    /**
     * [redirect description]
     * @param  [type] $view   [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function redirect($view, $params)
    {
        $url = $this->render->createUrl($view, $params);
        header("Location: {$url}");
        exit;
    }
}
