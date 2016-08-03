<?php
namespace model;

use \lib\Common;
use \lib\ConfigLoader;
use \lib\FileOperator;
use \lib\HZip;
use \model\Mcms_comment;
use \model\Mcms_key;
use \model\Mcms_lang;
use \model\Mcms_value;

class Mcms_import
{

    /**
     * [import description]
     * @param  [type] $baseDir [description]
     * @param  [type] $system  [description]
     * @param  [type] $method  [description]
     * @return [type]          [description]
     */
    public static function import($baseDir, $system, $method)
    {
        $cms_config = ConfigLoader::load('cms_config');
        $row        = Mcms_system::get($system);
        if (in_array($method, $cms_config['import_ext'])) {
            return self::$method($baseDir, $system);
        } else {
            Common::echoMsg(false, "上传格式不正确！");
        }
    }
    /**
     * [PHP description]
     * @param [type] $file_path [description]
     * @param [type] $system    [description]
     */
    public static function PHP($file_path, $system)
    {
        $res = HZip::unZip($file_path); // 解压
        @unlink($file_path); // 删除上传文件
        if ($res['status'] === false) {
            Common::echoMsg(false, array('common' => $res['message']));
        }
        $tmp_dir        = $res['message'];
        $tmp_basic_path = $tmp_dir . '/lang/';
        $cms_lang       = Mcms_lang::getKeyPair();
        $count          = 0;
        foreach ($cms_lang as $lang_key => $lang_name) {
            $tmp_path = $tmp_basic_path . $lang_key . '/text.php';
            if (!is_file($tmp_path)) {
                continue;
            }
            unset($T);
            require $tmp_path;
            if (!isset($T)) {
                continue;
            }
            foreach ($T as $key_code => $content) {
                list($key_id, $is_new) = Mcms_key::insert($system, $key_code);
                $is_new && $count++;
                Mcms_value::insert($key_id, $lang_key, $content);
            }
        }
        //删除临时文件
        FileOperator::deleteDir($tmp_dir);
        return $count;
    }

    /**
     * [Android description]
     * @param [type] $file_path [description]
     * @param [type] $system    [description]
     */
    public static function Android($file_path, $system)
    {
        $res = HZip::unZip($file_path); // 解压
        @unlink($file_path); // 删除上传文件
        if ($res['status'] === false) {
            Common::echoMsg(false, array('common' => $res['message']));
        }
        $tmp_dir        = $res['message'];
        $tmp_basic_path = $tmp_dir . '/lang/';
        $cms_lang       = Mcms_lang::getKeyPair();
        $count          = 0;
        foreach ($cms_lang as $lang_key => $lang_name) {
            $tmp_path = $tmp_basic_path . $lang_key . '.xml';
            if (!is_file($tmp_path)) {
                continue;
            }
            $doc = new \DOMDocument();
            $doc->load($tmp_path);
            $resources = $doc->getElementsByTagName("resources");

            $nodelist = $resources->item(0)->childNodes;
            Mcms_comment::delete($system);
            $comment    = '';
            $sort       = 0;
            $comment_id = '';
            foreach ($nodelist as $key => $value) {
                if ($value->nodeType == '1') {
                    if ($value->attributes) {
                        $key_code = $value->attributes->getNamedItem('name')->value;
                        $content  = $value->nodeValue;
                        // <xliff:g id="minlength1">%@</xliff:g> => {1}
                        $content               = preg_replace('/<xliff:g id=\"minlength1\">%(\d+)\$s<\/xliff:g>/', '{$1}', $content);
                        $content               = preg_replace('/%(\d+)\$s/', '{$1}', $content);
                        list($key_id, $is_new) = Mcms_key::insert($system, $key_code, $comment_id);
                        $is_new && $count++;
                        Mcms_value::insert($key_id, $lang_key, $content);
                    }
                } elseif ($value->nodeType == '8') {
                    //备注
                    $comment = $value->nodeValue;
                    $sort++;
                    $comment_id = Mcms_comment::insert($system, $comment, $sort);
                }
            }
        }
        //删除临时文件
        FileOperator::deleteDir($tmp_dir);
        return $count;
    }

    /**
     * [Excel description]
     * @param [type] $file_path [description]
     * @param [type] $system    [description]
     */
    public static function Excel($file_path, $system)
    {
        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        $objPHPExcel = \PHPExcel_IOFactory::load($file_path);

        $cms_lang = Mcms_lang::getKeyPair();
        $count    = 0;

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $lang_key = $worksheet->getTitle();
            if (array_key_exists($lang_key, $cms_lang)) {
                foreach ($worksheet->getRowIterator() as $key => $row) {
                    if ($key == 1) {
                        continue;
                    }
                    $key_code              = (string) $worksheet->getCell("A" . $key);
                    $content               = (string) $worksheet->getCell("B" . $key);
                    list($key_id, $is_new) = Mcms_key::insert($system, $key_code);
                    $is_new && $count++;
                    Mcms_value::insert($key_id, $lang_key, $content);
                }
            }
        }

        //删除临时文件
        @unlink($file_path); // 删除上传文件
        return $count;
    }
}
