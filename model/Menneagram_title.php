<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Menneagram_title
{
    protected $id;
    protected $title_id;
    protected $enneagram_id;
    protected $title_name;
    protected $status;
    protected $create_clerk;
    protected $create_time;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [getAll description]
     * @return [type] [description]
     */
    public static function getAll()
    {
        $where = array(
            'AND' => array(
                'status' => 1,
            ),
        );
        $db = DBHelper::getInstance();
        return $db->select('enneagram_title', '*', $where);
    }

    /**
     * [insert description]
     * @param  [type] $enneagram_id [description]
     * @param  [type] $title_name   [description]
     * @return [type]               [description]
     */
    public static function insert($enneagram_id, $title_name)
    {
        $data = array(
            'title_id'     => Common::guid(),
            'enneagram_id' => $enneagram_id,
            'title_name'   => $title_name,
            'status'       => 1,
            'create_clerk' => $_SESSION['username'],
            'create_time'  => TIMESTAMP,
            'modify_clerk' => $_SESSION['username'],
            'modify_time'  => TIMESTAMP,
        );

        $db = DBHelper::getInstance();
        return $db->insert('enneagram_title', $data);
    }

    /**
     * [update description]
     * @param  [type] $enneagram_id [description]
     * @param  [type] $data         [description]
     * @return [type]               [description]
     */
    public static function update($enneagram_id, $data)
    {
        $data['modify_clerk'] = $_SESSION['username'];
        $data['modify_time']  = TIMESTAMP;
        $db = DBHelper::getInstance();
        return $db->update('enneagram_title', $data, array('enneagram_id' => $enneagram_id));
    }

    /**
     * [delete description]
     * @param  [type] $enneagram_id [description]
     * @return [type]               [description]
     */
    public static function delete($enneagram_id)
    {
        $data = array(
            'status'       => 2,
            'modify_clerk' => $_SESSION['username'],
            'modify_time'  => TIMESTAMP,
        );

        $db = DBHelper::getInstance();
        return $db->update('enneagram_title', $data, array('enneagram_id' => $enneagram_id));
    }
}
