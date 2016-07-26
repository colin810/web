<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Mcms_log
{
    protected $id;
    protected $key_id;
    protected $key_code;
    protected $value_id;
    protected $system;
    protected $lang;
    protected $content;
    protected $content_clean;
    protected $remark;
    protected $version;
    protected $modify_clerk;
    protected $modify_time;
    protected $opt_flag;

    /**
     * [get description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function get($id)
    {
        $db = DBHelper::getInstance();
        return $db->get('cms_log', '*', array('id' => $id));
    }

    /**
     * [getPageList description]
     * @param  [type] $key_id       [description]
     * @param  [type] $lang         [description]
     * @param  string $search_value [description]
     * @return [type]               [description]
     */
    public static function getPageList($key_id, $lang, $search_value = '')
    {
        $db           = DBHelper::getInstance();
        $where        = array();
        $where['AND'] = array(
            'key_id' => $key_id,
            'lang'   => $lang,
        );

        if (!empty($search_value)) {
            $where['AND']['OR'] = array(
                'content[~]'       => $search_value,
                'content_clean[~]' => $search_value,
                'modify_clerk[~]'  => $search_value,
            );
        }

        $where['ORDER'] = 'id DESC';
        $where['LIMIT'] = Common::pageLimit();
        $list           = $db->select('cms_log', '*', $where);
        unset($where['LIMIT']);
        $total_record = $db->count('cms_log', $where);
        $page         = Common::getPage($total_record);
        return array($list, $page);
    }
}
