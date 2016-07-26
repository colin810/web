<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Mcms_key
{
    protected $id;
    protected $key_id;
    protected $comment_id;
    protected $system;
    protected $key_code;
    protected $max_version;
    protected $sort;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $system   [description]
     * @param  [type] $key_code [description]
     * @return [type]           [description]
     */
    public static function insert($system, $key_code, $comment_id = '')
    {
        $db     = DBHelper::getInstance();
        $key_id = self::getKeyID($system, $key_code);

        if (empty($key_id)) {
            $key_id              = Common::guid();
            $tmp                 = array();
            $tmp['key_id']       = $key_id;
            $tmp['comment_id']   = $comment_id;
            $tmp['system']       = $system;
            $tmp['key_code']     = $key_code;
            $tmp['max_version']  = 1;
            $tmp['modify_clerk'] = $_SESSION['username'];
            $tmp['modify_time']  = TIMESTAMP;
            $db->insert('cms_key', $tmp);
            $is_new = true;
        } else {
            $tmp = array('comment_id' => $comment_id);
            $db->update('cms_key', $tmp, array('key_id' => $key_id));
            $is_new = false;
        }
        return array($key_id, $is_new);
    }

    /**
     * [updateVersion description]
     * @param  [type] $key_id      [description]
     * @param  [type] $max_version [description]
     * @return [type]              [description]
     */
    public static function updateVersion($key_id, $max_version)
    {
        $db                  = DBHelper::getInstance();
        $tmp                 = array();
        $tmp['max_version']  = $max_version;
        $tmp['modify_clerk'] = $_SESSION['username'];
        $tmp['modify_time']  = TIMESTAMP;
        return $db->update('cms_key', $tmp, array('key_id' => $key_id));
    }

    /**
     * [getKeyID description]
     * @param  [type] $system   [description]
     * @param  [type] $key_code [description]
     * @return [type]           [description]
     */
    public static function getKeyID($system, $key_code)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'system'   => $system,
                'key_code' => $key_code,
            ),
        );
        return $db->get('cms_key', 'key_id', $where);
    }

    /**
     * [getVersion description]
     * @param  [type] $key_id [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public static function getMaxVersion($key_id)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'key_id' => $key_id,
            ),
        );
        return $db->get('cms_key', 'max_version', $where);
    }
}
