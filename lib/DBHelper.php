<?php

namespace lib;

use \lib\ConfigLoader;
use \lib\Register;

class DBHelper
{
    private function __construct()
    {}

    /**
     * [getInstance description]
     * @return [type] [description]
     */
    public static function getInstance()
    {
        $instance = Register::get('db');
        if (empty($instance)) {
            $option   = ConfigLoader::load('db_config');
            $instance = new medoo($option);
            Register::set('db', $instance);
        }
        return $instance;
    }

    /**
     * [queryAll description]
     * @param  [type] $sql    [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public static function queryAll($sql, $params = array())
    {
        $db  = self::getInstance();
        $sth = $db->pdo->prepare($sql);
        if ($params) {
            foreach ($params as $key => $value) {
                $sth->bindParam(":{$key}", $value, \PDO::PARAM_STR);
            }
        }
        $sth->execute();
        return $sth->fetchAll();
    }

    /**
     * [query description]
     * @param  [type] $sql    [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public static function query($sql, $params = array())
    {
        $db  = self::getInstance();
        $sth = $db->pdo->prepare($sql);
        if ($params) {
            foreach ($params as $key => $value) {
                $sth->bindParam(":{$key}", $value, \PDO::PARAM_STR);
            }
        }
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * [select description]
     * @param  [type] $table_name [description]
     * @param  [type] $join       [description]
     * @param  [type] $columns    [description]
     * @param  [type] $where      [description]
     * @return [type]             [description]
     */
    public function select($table_name, $join, $columns, $where)
    {
        $sql = self::constructSql($table_name, $join, $columns, $where);
        return self::queryAll($sql);
    }

    /**
     * [get description]
     * @param  [type] $table_name [description]
     * @param  [type] $join       [description]
     * @param  [type] $columns    [description]
     * @param  [type] $where      [description]
     * @return [type]             [description]
     */
    public function get($table_name, $join, $columns, $where)
    {
        $sql = self::constructSql($table_name, $join, $columns, $where);
        $row = self::query($sql);
        if (is_array($columns)) {
            return $row;
        } else {
            return $row[$columns];
        }
    }
    /**
     * [constructSql description]
     * @param  [type] $table_name [description]
     * @param  [type] $join       [description]
     * @param  [type] $columns    [description]
     * @param  [type] $where      [description]
     * @return [type]             [description]
     */
    protected static function constructSql($table_name, $join, $columns, $where)
    {
        $join_str = $where_str = $groupby_str = $order_str = $limit_str = '';
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        if ($join) {
            foreach ($join as $method => $tables) {
                foreach ($tables as $join_table => $conditions) {
                    $join_str .= $method . ' JOIN ' . $join_table . ' ON ';
                    $condition_str = '';
                    foreach ($conditions as $left => $right) {
                        $condition_str .= 'AND ' . $left . ' = ' . $right . ' ';
                    }
                    $join_str .= trim($condition_str, 'AND ');
                }
            }
        }

        $where_arr = $where['WHERE'];
        $where_str = self::where($where_arr);
        if ($where_str) {
            $where_str = ' WHERE ' . $where_str;
        }

        if (isset($where['GROUPBY'])) {
            $groupby_str = ' GROUP BY ' . implode(',', $where['GROUPBY']);
        }

        if (isset($where['ORDER'])) {
            $order_str = ' ORDER BY ' . implode(',', $where['ORDER']);
        }

        if (isset($where['LIMIT'])) {
            $limit_str = ' LIMIT ' . implode(',', $where['LIMIT']);
        }
        $sql = "SELECT {$columns} FROM {$table_name} " . $join_str . $where_str . $groupby_str . $order_str . $limit_str;
        return $sql;
    }

    /**
     * [where description]
     * @param  [type] &$where   [description]
     * @param  string $operator [description]
     * @return [type]           [description]
     */
    protected static function where(&$where, $operator = 'AND')
    {
        if (!isset($where) || empty($where)) {
            return '';
        }

        if (!is_array($where)) {
            return $where;
        }

        foreach ($where as $key => $value) {
            list($connect_symbol) = explode(' ', $key);
            if (is_array($value) && in_array($connect_symbol, array('AND', 'OR'))) {
                $where[$key] = self::where($value, $key);
                if (empty($where[$key])) {
                    unset($where[$key]);
                }
            }
        }
        list($connect_symbol) = explode(' ', $operator);
        $where_str            = '(' . implode(' ' . $connect_symbol . ' ', $where) . ')';
        return $where_str;
    }
}
