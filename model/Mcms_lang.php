<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;
use \lib\Register;

class Mcms_lang
{
    protected $id;
    protected $lang_id;
    protected $lang_key;
    protected $lang_name;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $lang_key  [description]
     * @param  [type] $lang_name [description]
     * @return [type]            [description]
     */
    public static function insert($lang_key, $lang_name)
    {
        $db                   = DBHelper::getInstance();
        $data                 = array();
        $data['lang_id']      = Common::guid();
        $data['lang_key']     = $lang_key;
        $data['lang_name']    = $lang_name;
        $data['modify_clerk'] = $_SESSION['username'];
        $data['modify_time']  = TIMESTAMP;
        return $db->insert('cms_lang', $data);
    }

    /**
     * [getAll description]
     * @return [type] [description]
     */
    public static function getAll()
    {
        $cms_lang = Register::get('cms_lang');
        if (empty($cms_lang)) {
            $db       = DBHelper::getInstance();
            $cms_lang = $db->select('cms_lang', '*');
            Register::set('cms_lang', $cms_lang);
        }
        return $cms_lang;
    }

    /**
     * [getKeyPair description]
     * @return [type] [description]
     */
    public static function getKeyPair()
    {
        $result = Register::get('cms_lang_key_pair');
        if (empty($result)) {
            $result     = array();
            $cms_system = self::getAll();
            foreach ($cms_system as $key => $row) {
                $result[$row['lang_key']] = $row['lang_name'];
            }
        }
        return $result;
    }
}
