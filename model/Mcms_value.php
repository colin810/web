<?php
namespace model;

use \lib\Common;
use \lib\DBHelper;

class Mcms_value
{
    protected $id;
    protected $value_id;
    protected $key_id;
    protected $lang;
    protected $content;
    protected $content_clean;
    protected $remark;
    protected $version;
    protected $modify_clerk;
    protected $modify_time;

    /**
     * [insert description]
     * @param  [type] $key_id  [description]
     * @param  [type] $lang    [description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public static function insert($key_id, $lang, $content, $version = 0)
    {
        $content          = str_replace("\'", "'", $content);
        $content          = str_replace('\"', '"', $content);
        $db               = DBHelper::getInstance();
        $data             = array();
        $data['value_id'] = Common::guid();
        $data['key_id']   = $key_id;
        $data['lang']     = $lang;
        // $data['content']       = Common::removewrap($content);
        $data['content']       = $content;
        $data['content_clean'] = Common::removeXSS($content);
        $data['remark']        = '';
        $data['version']       = $version;
        $data['modify_clerk']  = $_SESSION['username'];
        $data['modify_time']   = TIMESTAMP;

        $where = array(
            'AND' => array(
                'key_id' => $key_id,
                'lang'   => $lang,
            ),
        );

        if (!$db->has('cms_value', $where) && $db->insert('cms_value', $data) > 0) {
            return $data['value_id'];
        }
        return false;
    }

    /**
     * [update description]
     * @param  [type] $value_id [description]
     * @param  [type] $content  [description]
     * @param  [type] $version  [description]
     * @param  string $remark   [description]
     * @return [type]           [description]
     */
    public static function update($value_id, $content, $version, $remark = '')
    {
        $content = str_replace("\'", "'", $content);
        $content = str_replace('\"', '"', $content);
        $db      = DBHelper::getInstance();
        $data    = array();
        // $data['content']       = Common::removewrap($content);
        $data['content']       = $content;
        $data['content_clean'] = Common::removeXSS($content);
        $data['remark']        = $remark;
        $data['version']       = $version;
        $data['modify_clerk']  = $_SESSION['username'];
        $data['modify_time']   = TIMESTAMP;
        $where                 = array(
            'AND' => array(
                'value_id' => $value_id,
            ),
        );
        return $db->update('cms_value', $data, $where);
    }

    /**
     * [get description]
     * @param  [type] $key_id [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public static function get($key_id, $lang)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'key_id' => $key_id,
                'lang'   => $lang,
            ),
        );
        return $db->get('cms_value', '*', $where);
    }

    /**
     * [getContent description]
     * @param  [type] $key_id [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public static function getContent($key_id, $lang)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'key_id' => $key_id,
                'lang'   => $lang,
            ),
        );
        return $db->get('cms_value', 'content', $where);
    }

    /**
     * [getValueID description]
     * @param  [type] $key_id [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public static function getValueID($key_id, $lang)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'key_id' => $key_id,
                'lang'   => $lang,
            ),
        );
        return $db->get('cms_value', 'value_id', $where);
    }

    /**
     * [getKeyAndValue description]
     * @param  [type] $key_id [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public function getKeyAndValue($key_id, $lang)
    {
        $db    = DBHelper::getInstance();
        $where = array(
            'AND' => array(
                'cms_value.key_id' => $key_id,
                'cms_value.lang'   => $lang,
            ),
        );
        $columns = array(
            'cms_key.key_code',
            'cms_value.key_id',
            'cms_value.lang',
            'cms_value.content',
            'cms_value.remark',
        );
        $join = array(
            '[>]cms_key' => array(
                'key_id' => 'key_id',
            ),
        );
        return $db->get('cms_value', $join, $columns, $where);
    }
}
