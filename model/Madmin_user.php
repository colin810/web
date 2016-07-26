<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Madmin_user
{
    protected $id;
    protected $user_id;
    protected $username;
    protected $password;
    protected $realname;
    protected $mobile;
    protected $role_ids;
    protected $allow_acl;
    protected $status;
    protected $create_clerk;
    protected $create_time;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function insert($data)
    {
        $db                   = DBHelper::getInstance();
        $data['user_id']      = Common::guid();
        $data['password']     = md5($data['password']);
        $data['create_clerk'] = $_SESSION['admin']['username'];
        $data['create_time']  = TIMESTAMP;
        $data['modify_clerk'] = $_SESSION['admin']['username'];
        $data['modify_time']  = TIMESTAMP;
        if ($db->insert('admin_user', $data)) {
            return $data['user_id'];
        } else {
            return false;
        }
    }

    /**
     * [login description]
     * @param  [type] $username [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public static function login($username, $password)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'username' => $username,
                'status'   => 1,
            ),
        );
        $admin = $db->get('admin_user', '*', $where);

        if ($admin && $admin['password'] == md5($password)) {
        	return $admin;
        } else {
        	return false;
        }
    }

}
