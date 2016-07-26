<?php

namespace model;

use \lib\Common;
use \lib\DBHelper;

class Mcms
{
    /**
     * [getPageList description]
     * @param  [type] $search_system         [description]
     * @param  [type] $search_lang           [description]
     * @param  [type] $search_value          [description]
     * @param  [type] $search_condition_type [description]
     * @return [type]                        [description]
     */
    public static function getPageList($search_system, $search_lang, $search_value, $search_condition_type)
    {
        $table_alias    = array();
        $join           = array();
        $condition      = array();
        $condition_or_1 = array();
        $columns        = array(
            'cms_key.key_code',
            'cms_key.max_version',
            'cms_key.key_id',
            'cms_key.modify_time',
        );
        $join['LEFT'] = array();
        foreach ($search_lang as $key => $value) {
            array_push(
                $columns,
                "`{$value}`.`content` AS `{$value}_content`",
                "`{$value}`.`content_clean` as `{$value}_content_clean`",
                "`{$value}`.`version` as `{$value}_version`"
            );

            $join['LEFT']["`cms_value` AS `{$value}`"] = array(
                "`cms_key`.`key_id`" => "`{$value}`.`key_id`",
                "`{$value}`.`lang`"  => "'{$value}'",
            );
            $search_value && array_push(
                $condition_or_1,
                "`{$value}`.`content_clean` LIKE '%{$search_value}%'",
                "`{$value}`.`content` LIKE '%{$search_value}%'"
            );
            array_push($table_alias, $value);
        }

        $condition_or_2 = array();
        switch ($search_condition_type) {
            case '1': //全部
                break;
            case '2': //未完成
                foreach ($table_alias as $key => $value) {
                    array_push(
                        $condition_or_2,
                        "`cms_key`.`max_version` > `{$value}`.`version`",
                        "`{$value}`.`version` IS NULL"
                    );
                }
                break;
            case '3': //空值
                foreach ($table_alias as $key => $value) {
                    array_push(
                        $condition_or_2,
                        "`{$value}`.`content` = ''",
                        "`{$value}`.`content` IS NULL"
                    );
                }
                break;
            default:
                break;
        }

        $search_value && array_push(
            $condition_or_1,
            "`cms_key`.`key_code` LIKE '%{$search_value}%'"
        );
        $where = array(
            'WHERE' => array(
                'AND' => array(
                    "`cms_key`.`system` = '{$search_system}'",
                    'OR #1' => $condition_or_1,
                    'OR #2' => $condition_or_2,
                ),
            ),
            'ORDER' => array('`cms_key`.`id` ASC'),
            'LIMIT' => Common::pageLimit(),
        );
        $list = DBHelper::select('cms_key', $join, $columns, $where);

        //获取分页数据
        unset($where['LIMIT']);
        $total_record = DBHelper::get('cms_key', '', 'count(id)', $where);
        $page         = Common::getPage($total_record);

        return array($list, $page);
    }

    /**
     * [getDownloadData description]
     * @param  [type] $system [description]
     * @param  [type] $lang   [description]
     * @return [type]         [description]
     */
    public static function getDownloadData($system, $lang)
    {
        $join = array(
            'LEFT' => array(
                'cms_value'   => array(
                    'cms_key.key_id' => 'cms_value.key_id',
                    'cms_value.lang' => "'{$lang}'",
                ),
                'cms_comment' => array(
                    'cms_key.comment_id' => 'cms_comment.comment_id',
                ),
            ),
        );
        $columns = array(
            'cms_key.key_code',
            'cms_value.content',
            'cms_comment.comment_id',
            'cms_comment.comment_name',
        );
        $where = array(
            'WHERE' => array(
                "cms_key.system = '{$system}'",
            ),
            'ORDER' => array(
                'IF(ISNULL(cms_key.comment_id), 1, 2)',
                'cms_comment.sort',
                'cms_key.key_code',
            ),
        );
        return DBHelper::select('cms_key', $join, $columns, $where);
    }

    /**
     * [getRow description]
     * @param  [type] $lang     [description]
     * @param  [type] $key_code [description]
     * @return [type]           [description]
     */
    public static function getRow($lang, $key_id)
    {
        $table_alias = array();
        $join        = array();
        $columns     = array(
            'cms_key.key_code',
            'cms_key.max_version',
            'cms_key.key_id',
            'cms_key.modify_time',
        );
        $join['LEFT'] = array();
        foreach ($lang as $key => $value) {
            array_push(
                $columns,
                "`{$value}`.`content` AS `{$value}_content`",
                "`{$value}`.`content_clean` as `{$value}_content_clean`",
                "`{$value}`.`version` as `{$value}_version`"
            );

            $join['LEFT']["`cms_value` AS `{$value}`"] = array(
                "`cms_key`.`key_id`" => "`{$value}`.`key_id`",
                "`{$value}`.`lang`"  => "'{$value}'",
            );
            array_push($table_alias, $value);
        }

        $where = array(
            'WHERE' => array(
                'AND' => array(
                    "`cms_key`.`key_id` = '{$key_id}'",
                ),
            ),
        );
        $row = DBHelper::get('cms_key', $join, $columns, $where);
        return $row;
    }
}
