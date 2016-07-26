<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Mcms_comment
{
	protected $id;
    protected $comment_id;
    protected $comment_name;
    protected $system;
    protected $sort;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $system       [description]
     * @param  [type] $comment_name [description]
     * @param  [type] $sort         [description]
     * @return [type]               [description]
     */
    public static function insert($system, $comment_name, $sort)
    {
        $db         = DBHelper::getInstance();
        $comment_id = Common::guid();
        $data       = array(
            'comment_id'   => $comment_id,
            'comment_name' => $comment_name,
            'system'       => $system,
            'sort'         => $sort,
            'modify_clerk' => $_SESSION['username'],
            'modify_time'  => TIMESTAMP,
        );
        if ($db->insert('cms_comment', $data) > 0) {
            return $comment_id;
        } else {
            return false;
        }
    }

    /**
     * [delete description]
     * @param  [type] $system [description]
     * @return [type]         [description]
     */
    public static function delete($system)
    {
        $db    = DBHelper::getInstance();
        $where = array('system' => $system);
        return $db->delete('cms_comment', $where);
    }
}
