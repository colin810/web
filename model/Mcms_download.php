<?php
namespace model;

use \lib\Common;
use \lib\ConfigLoader;
use \lib\DBHelper;
use \lib\FileOperator;
use \lib\HttpDownload;
use \lib\HZip;
use \model\Mcms;
use \model\Mcms_lang;
use \model\Mcms_system;

class Mcms_download
{
    /**
     * [download description]
     * @param  [type] $baseDir [description]
     * @param  [type] $system  [description]
     * @param  [type] $method  [description]
     * @return [type]          [description]
     */
    public static function download($baseDir, $system, $method)
    {
        $cms_config = ConfigLoader::load('cms_config');
        $row        = Mcms_system::get($system);
        if (in_array($method, $cms_config['download_ext'])) {
            self::$method($baseDir, $system);
        } else {
            Common::echoMsg(false, "下载格式不正确！");
        }
    }

    /**
     * [Excel description]
     * @param [type] $baseDir [description]
     * @param [type] $system  [description]
     */
    public static function Excel($baseDir, $system)
    {
        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $db          = DBHelper::getInstance();
        $cms_lang    = Mcms_lang::getKeyPair();
        $cms_system  = Mcms_system::getKeyPair();
        $filename    = $baseDir . '/tmp.xls';
        $i           = 0;
        $row_height  = 20;
        $font_size   = 12;
        $font_family = '宋体';
        foreach ($cms_lang as $key => $value) {
            $rs = Mcms::getDownloadData($system, $key);
            if (empty($i)) {
                $objPHPExcel->getActiveSheet()->setTitle($key);
            } else {
                $newSheet = $objPHPExcel->createSheet();
                $newSheet->setTitle($key);
                $objPHPExcel->setActiveSheetIndex($i);
            }
            $sheet = $objPHPExcel->getActiveSheet();

            //设置字体
            $sheet->getDefaultRowDimension()->setRowHeight($row_height);
            $font = $sheet->getDefaultStyle()->getFont();
            $font->setName($font_family);
            $font->setSize($font_size);

            //设置对齐方式
            $align = $sheet->getDefaultStyle()->getAlignment();
            $align->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $align->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $align->setIndent(1);

            $sheet->setCellValue('A1', 'Key Code');
            $sheet->setCellValue('B1', '详细描述');
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(50);
            foreach ($rs as $k => $v) {
                $sheet->setCellValue('A' . ($k + 2), $v['key_code']);
                $sheet->setCellValue('B' . ($k + 2), $v['content']);
            }
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $cms_system[$system] . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        FileOperator::deleteDir($baseDir);
    }

    /**
     * [PHP description]
     * @param [type] $baseDir [description]
     * @param [type] $system  [description]
     */
    public static function PHP($baseDir, $system)
    {
        $db       = DBHelper::getInstance();
        $cms_lang = Mcms_lang::getKeyPair();
        foreach ($cms_lang as $key => $value) {
            $path = $baseDir . $key;
            if (!is_dir($path)) {
                $res = mkdir($path, 0777, true);
                if (!$res) {
                    Common::echoMsg(false, "Directory Create Failed！");
                }
            }
            $rs       = Mcms::getDownloadData($system, $key);
            $filename = $path . '/text.php';
            $file     = fopen($filename, 'wb');
            fwrite($file, '<?php Global $T;$T = array (' . Chr(10));
            foreach ($rs as $k => $v) {
                $content = $v['content'];
                $content = str_replace("'", "\'", $content);
                $content = str_replace("&#39;", "\'", $content);
                fwrite($file, '    \'' . $v['key_code'] . '\' => \'' . $content . '\',' . Chr(10));
            }
            fwrite($file, ');');
            fclose($file);
        }
        $inFile  = rtrim($baseDir, '/');
        $outFile = rtrim($baseDir, '/lang/') . '/lang.zip';
        HZip::zipDir($inFile, $outFile);
        $object = new HttpDownload();
        $object->set_byfile($outFile);
        $object->download();
        FileOperator::deleteDir($baseDir);
    }

    /**
     * [Objectc description]
     * @param [type] $baseDir [description]
     * @param [type] $system  [description]
     */
    public static function Android($baseDir, $system)
    {
        $db       = DBHelper::getInstance();
        $cms_lang = Mcms_lang::getKeyPair();
        $path     = $baseDir;
        if (!is_dir($path)) {
            $res = mkdir($path, 0777, true);
            if (!$res) {
                Common::echoMsg(false, "Directory Create Failed！");
            }
        }
        foreach ($cms_lang as $key => $value) {
            $rs       = Mcms::getDownloadData($system, $key);
            $filename = $path . $key . '.xml';
            $file     = fopen($filename, 'wb');
            fwrite($file, '<?xml version="1.0" encoding="utf-8"?>' . Chr(10));
            fwrite($file, '<resources xmlns:xliff="urn:oasis:names:tc:xliff:document:1.2">' . Chr(10));
            foreach ($rs as $k => $v) {
                $name       = $v['key_code'];
                $content    = $v['content'];
                $comment_id = $v['comment_id'];
                $comment    = $v['comment_name'];
                if (empty($last_comment_id)) {
                    $last_comment_id = $comment_id;
                }
                if ($last_comment_id != $comment_id && !empty($comment)) {
                    fwrite($file, '  <!-- ' . $comment . ' -->' . Chr(10));
                    $last_comment_id = $comment_id;
                }
                // {1} => <xliff:g id="minlength1">%1$s</xliff:g>
                $content = preg_replace('/\{(\d+)\}/', '<xliff:g id="minlength1">%$1\$s</xliff:g>', $content);
                $content = str_replace("'", "\'", $content);
                $content = str_replace('"', '\"', $content);
                if (Common::hasHtml($content)) {
                    fwrite($file, '  <string name="' . $name . '"><![CDATA[' . $content . ']]></string>' . Chr(10));
                } else {
                    fwrite($file, '  <string name="' . $name . '">' . $content . '</string>' . Chr(10));
                }
            }
            fwrite($file, '</resources>');
            fclose($file);
        }
        $inFile  = rtrim($baseDir, '/');
        $outFile = rtrim($baseDir, '/lang/') . '/lang.zip';
        HZip::zipDir($inFile, $outFile);
        $object = new HttpDownload();
        $object->set_byfile($outFile);
        $object->download();
        FileOperator::deleteDir($baseDir);
    }

    /**
     * [Objectc description]
     * @param [type] $baseDir [description]
     * @param [type] $system  [description]
     */
    public static function iOS($baseDir, $system)
    {
        $db       = DBHelper::getInstance();
        $cms_lang = Mcms_lang::getKeyPair();
        $path     = $baseDir;
        if (!is_dir($path)) {
            $res = mkdir($path, 0777, true);
            if (!$res) {
                Common::echoMsg(false, "Directory Create Failed！");
            }
        }
        foreach ($cms_lang as $key => $value) {
            $rs       = Mcms::getDownloadData($system, $key);
            $filename = $path . $key . '.strings';
            $file     = fopen($filename, 'wb');
            foreach ($rs as $k => $v) {
                $name       = $v['key_code'];
                $content    = $v['content'];
                $comment_id = $v['comment_id'];
                $comment    = $v['comment_name'];
                if (empty($last_comment_id)) {
                    $last_comment_id = $comment_id;
                }
                if ($last_comment_id != $comment_id && !empty($comment)) {
                    fwrite($file, '// ' . $comment . Chr(10));
                    $last_comment_id = $comment_id;
                }
                $content = str_replace("'", "\'", $content);
                $content = str_replace('"', '\"', $content);
                // {1} => %@
                $content = preg_replace('/\{(\d+)\}/', '%@', $content);
                fwrite($file, '"' . $name . '" = "' . $content . '";' . Chr(10));
            }
            fclose($file);
        }
        $inFile  = rtrim($baseDir, '/');
        $outFile = rtrim($baseDir, '/lang/') . '/lang.zip';
        HZip::zipDir($inFile, $outFile);
        $object = new HttpDownload();
        $object->set_byfile($outFile);
        $object->download();
        FileOperator::deleteDir($baseDir);
    }
}
