<?php
namespace model;

use \lib\DBHelper;
use \model\Menneagram;

class Menneagram_row
{
    protected $id;
    protected $session_id;
    protected $enneagram_id;
    protected $title_id;
    protected $opt_val;

    /**
     * [insert description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function insert($data)
    {
        $db = DBHelper::getInstance();
        return $db->insert('enneagram_row', $data);
    }

    /**
     * [result description]
     * @param  [type] $session_id [description]
     * @return [type]             [description]
     */
    public static function result($session_id)
    {
        $db           = DBHelper::getInstance();
        $records      = self::getDetailList($session_id);
        $enneagram_id = $records[0]['enneagram_id'];
        $db->update('enneagram_group', array('result' => $enneagram_id), array('session_id' => $session_id));
        return Menneagram::get($enneagram_id);
    }

    /**
     * [getDetailList description]
     * @param  [type] $session_id [description]
     * @return [type]             [description]
     */
    public static function getDetailList($session_id)
    {
        $db = DBHelper::getInstance();
        return $db->query("SELECT enneagram_id, sum(opt_val) AS rank FROM enneagram_row WHERE session_id = '{$session_id}' GROUP BY enneagram_id ORDER BY rank DESC,id ASC")->fetchAll();
    }
}
