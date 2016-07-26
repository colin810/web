<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Madmin_role
{
    protected $id;
    protected $role_id;
    protected $role_name;
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
        $data['role_id']      = Common::guid();
        $data['role_name']    = $data['role_name'];
        $data['allow_acl']    = implode(',', $data['allow_acl']);
        $data['status']       = $data['status'];
        $data['create_clerk'] = $_SESSION['admin']['username'];
        $data['create_time']  = TIMESTAMP;
        $data['modify_clerk'] = $_SESSION['admin']['username'];
        $data['modify_time']  = TIMESTAMP;
        if ($db->insert('admin_role', $data)) {
            return $data['role_id'];
        } else {
            return false;
        }
    }

    /**
     * [getAcl description]
     * @param  [type] $role_ids [description]
     * @return [type]           [description]
     */
    public static function getAcl($role_ids)
    {
        $role_ids = explode(',', $role_ids);
        $db       = DBHelper::getInstance();
        $where    = array(
            'AND' => array(
                'role_id' => $role_ids,
                'status'  => 1,
            ),
        );
        $rs = $db->select('admin_role', 'allow_acl', $where);
        if (empty($rs)) {
            return false;
        } else {
            return $rs;
        }
    }

}
