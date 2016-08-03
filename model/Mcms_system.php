<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;
use \lib\Register;

class Mcms_system
{
    protected $id;
    protected $system_id;
    protected $system_key;
    protected $system_name;
    protected $down_ext;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $system_key  [description]
     * @param  [type] $system_name [description]
     * @return [type]              [description]
     */
    public static function insert($system_key, $system_name)
    {
        $db                   = DBHelper::getInstance();
        $data                 = array();
        $data['system_id']    = Common::guid();
        $data['system_key']   = $system_key;
        $data['system_name']  = $system_name;
        $data['modify_clerk'] = $_SESSION['username'];
        $data['modify_time']  = TIMESTAMP;
        return $db->insert('cms_system', $data);
    }

    /**
     * [get description]
     * @param  [type] $system_key [description]
     * @return [type]             [description]
     */
    public function get($system_key)
    {
        $db = DBHelper::getInstance();
        return $db->get('cms_system', '*', array('system_key' => $system_key));
    }

    /**
     * [getAll description]
     * @return [type] [description]
     */
    public static function getAll()
    {
        $cms_system = Register::get('cms_system');
        if (empty($cms_system)) {
            $db         = DBHelper::getInstance();
            $cms_system = $db->select('cms_system', '*');
            Register::set('cms_system', $cms_system);
        }
        return $cms_system;
    }

    /**
     * [getKeyPair description]
     * @return [type] [description]
     */
    public static function getKeyPair()
    {
        $result = Register::get('cms_system_key_pair');
        if (empty($result)) {
            $result     = array();
            $cms_system = self::getAll();
            foreach ($cms_system as $key => $row) {
                $result[$row['system_key']] = $row['system_name'];
            }
        }
        return $result;
    }
}
