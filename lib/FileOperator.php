<?php

namespace lib;

class FileOperator
{
    /**
     * [makeDir description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public static function makeDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * [deleteDir description]
     * @return [type] [description]
     */
    public static function deleteDir($path)
    {
        sleep(2);
        self::deleteChildDir($path);
        rmdir($path);
    }

    /**
     * [deleteChildDir description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public static function deleteChildDir($path)
    {
        $op = dir($path);
        while (false != ($item = $op->read())) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (is_dir($op->path . '/' . $item)) {
                self::deleteChildDir($op->path . '/' . $item);
                rmdir($op->path . '/' . $item);
            } else {
                unlink($op->path . '/' . $item);
            }
        }
        $op->close();
    }
}
