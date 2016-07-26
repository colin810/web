<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Menneagram_group
{
    protected $id;
    protected $session_id;
    protected $mobile;
    protected $result;
    protected $create_clerk;
    protected $create_time;

    /**
     * [insert description]
     * @param  [type] $username [description]
     * @param  [type] $mobile   [description]
     * @return [type]           [description]
     */
    public static function insert($username, $mobile)
    {
        $session_id = Common::guid();
        $data       = array(
            'session_id'   => $session_id,
            'mobile'       => $mobile,
            'create_clerk' => $username,
            'create_time'  => TIMESTAMP,
        );
        $db = DBHelper::getInstance();
        if ($db->insert('enneagram_group', $data)) {
            return $session_id;
        } else {
            return false;
        }
    }

    /**
     * [getPageList description]
     * @param  string $search [description]
     * @return [type]         [description]
     */
    public static function getPageList($search = '')
    {
        $db    = DBHelper::getInstance();
        $where = array();

        $search && $where['OR'] = array(
            'create_clerk[~]' => $search,
            'mobile[~]'       => $search,
        );
        $where['LIMIT'] = Common::pageLimit();

        $list = $db->select('enneagram_group', '*', $where);
        unset($where['LIMIT']);
        $total = $db->count('enneagram_group', $where);
        $page  = Common::getPage($total);
        return array($list, $page);
    }
}
