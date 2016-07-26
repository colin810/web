<?php
namespace model;

use \lib\DBHelper;
use \lib\Common;

class Menneagram
{
    protected $id;
    protected $enneagram_id;
    protected $enneagram_name;
    protected $enneagram_desc;
    protected $status;
    protected $create_clerk;
    protected $create_time;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * 獲取 enneagram 數組
     * @param  integer $status [description]
     * @return [type]          [description]
     */
    public static function getAll($status = 1)
    {
        $db    = DBHelper::getInstance();
        $where = array();
        if (!empty($status)) {
            $where['AND']['status'] = $status;
        }
        $result = $db->select('enneagram', '*', $where);
        return Common::rebuildArray('enneagram_id', $result);
    }

    /**
     * [get description]
     * @param  [type] $enneagram_id [description]
     * @return [type]               [description]
     */
    public static function get($enneagram_id)
    {
        $db = DBHelper::getInstance();
        return $db->get('enneagram', '*', array('enneagram_id' => $enneagram_id));
    }
}
