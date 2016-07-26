<?php
namespace admin\controller;

use \lib\ConfigLoader;
use \lib\Controller;
use \model\Madmin_role;

class adminsysController extends Controller
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
            $this->redirect('welcome/login');
        }
    }

    /**
     * [mainAction description]
     * @return [type] [description]
     */
    public function mainAction()
    {
        $admin_acl_config = ConfigLoader::load('admin_acl_config');
        $allow_acl        = array();
        if ($_SESSION['admin']['role_ids']) {
            $tmp_acl = Madmin_role::getAcl($_SESSION['admin']['role_ids']);
            array_walk($tmp_acl, function ($v, $k) use (&$allow_acl) {
                $split = explode(',', $v);
            });
        }

        if ($_SESSION['admin']['allow_acl']) {
            $tmp_acl = explode(',', $_SESSION['admin']['allow_acl']);
            array_walk($tmp_acl, function ($v, $k) use (&$allow_acl) {
                if (!in_array($v, $allow_acl)) {
                    array_push($allow_acl, $v);
                }
            });
        }

        $_SESSION['admin']['allow_acl_array'] = $allow_acl;

        $acl = array();
        foreach ($admin_acl_config as $key => $value) {
            if (in_array($key, $allow_acl)) {
                $acl[$key] = $value;
            }
        }

        $this->render->display('main', array('admin_acl_config' => $acl));
    }
}
