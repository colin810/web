<?php
namespace web\controller;

use \lib\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $this->render->display('index');
    }
}
