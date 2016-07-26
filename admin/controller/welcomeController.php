<?php
namespace admin\controller;

use \lib\Common;
use \lib\ConfigLoader;
use \lib\Controller;
use \model\Madmin_user;

class welcomeController extends Controller
{
    /**
     * [loginAction description]
     * @return [type] [description]
     */
    public function loginAction()
    {
        if ($_POST) {
            $params = array('username', 'password');
            $params = Common::getReqestParams($params, 'admin');

            $admin = Madmin_user::login($params['username'], $params['password']);
            if ($admin === false) {
                Common::echoMsg(false, array('common' => '账号或密码错误！'));
            } else {
                $this->setLoginInfo($admin);
                Common::echoMsg(true);
            }
        }
        $this->render->display('login');
    }

    /**
     * [setLoginInfo description]
     * @param [type] $admin [description]
     */
    private function setLoginInfo($admin)
    {
        $_SESSION['admin']['user_id']   = $admin['user_id'];
        $_SESSION['admin']['username']  = $admin['username'];
        $_SESSION['admin']['role_ids']  = $admin['role_ids'];
        $_SESSION['admin']['allow_acl'] = $admin['allow_acl'];
    }
}
