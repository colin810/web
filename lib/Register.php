<?php
namespace lib;

class Register
{
    protected static $objects;

    /**
     * [set description]
     * @param [type] $alias  [description]
     * @param [type] $object [description]
     */
    public static function set($alias, $object)
    {
        self::$objects[$alias] = $object;
    }

    /**
     * [get description]
     * @param  [type] $alias [description]
     * @return [type]        [description]
     */
    public static function get($alias)
    {
        if (isset(self::$objects[$alias])) {
            return self::$objects[$alias];
        }
        return null;
    }

    /**
     * [dispose description]
     * @param  [type] $alias [description]
     * @return [type]        [description]
     */
    public static function dispose($alias)
    {
        unset(self::$objects[$alias]);
    }
}
