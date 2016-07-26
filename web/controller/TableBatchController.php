<?php
namespace web\controller;

use \lib\Controller;
use \lib\CurlExt;
use \lib\FileOperator;
use \lib\HttpDownload;
use \lib\HZip;

class TableBatchController extends Controller
{
    public function indexAction()
    {
        $read_file_path  = WEB_ROOT . '/resource/tmp/123.xls';
        $write_file_path = WEB_ROOT . '/resource/tmp/123.txt';
        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        $objPHPExcel = \PHPExcel_IOFactory::load($read_file_path);

        echo '<meta charset="utf-8" />';
        $count = $objPHPExcel->getSheetCount();
        for ($index = 0; $index < $count; $index++) {
            $activeSheet = $objPHPExcel->setActiveSheetIndex($index);

            //获取TABLE_COMMEND
            //$table_commend = $activeSheet->getTitle();

            //获取DB_NAME
            $tmp     = $activeSheet->getCell('A1');
            $tmp     = rtrim($tmp, ';');
            $tmp_arr = explode('：', $tmp);
            $db_name = trim($tmp_arr[1]);

            //获取TABLE_NAME
            $tmp        = $activeSheet->getCell('A2');
            $tmp_arr    = explode('：', $tmp);
            $tmp        = trim($tmp_arr[1]);
            $tmp_arr    = explode(';', $tmp);
            $table_name = trim($tmp_arr[0]);

            //获取字段信息
            $i      = 0;
            $fields = array();
            foreach ($activeSheet->getRowIterator() as $row) {
                if ($i++ < 4) {
                    continue;
                }
                $field_name = trim($activeSheet->getCell('B' . $i)->getValue());
                if (empty($field_name)) {
                    break;
                }
                $field_type = trim($activeSheet->getCell('C' . $i)->getValue());
                array_push($fields, array('field_name' => $field_name, 'field_type' => $field_type));
            }

            $createSql = function ($db_name, $table_name, $fields) {
                $sql       = "use {$db_name};\r\n";
                $fieldsSql = function ($value, $key) use (&$sql) {
                    $sql .= "  `{$value['field_name']}` {$value['field_type']},\r\n";
                };
                $sql .= "CREATE TABLE `{$table_name}` (\r\n" .
                    "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\r\n";
                array_walk($fields, $fieldsSql);
                $sql .= "  PRIMARY KEY (`id`)\r\n" .
                    ") ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;\r\n\r\n";
                return $sql;
            };

            $sql = $createSql($db_name, $table_name, $fields);

            file_put_contents($write_file_path, $sql, FILE_APPEND);
        }
        echo 'OK';
    }

    public function downloadAction()
    {
        $domain = 'http://local.tabledoc.com';
        //$domain      = 'http://192.168.10.245/tabledocsAdmin';
        $url         = $domain . '/index.php';
        $data        = array();
        $ignore_list = array('information_schema', 'mysql', 'performance_schema'); //不查询的数据库
        $opts        = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => 'tableDocAdmin:lzK6AZrllwOPA',
            //CURLOPT_USERPWD        => 'tableDocAdmin:9ks76Wf2p5',
            CURLOPT_HTTP_VERSION   => '1.0',
        );
        $result = CurlExt::send($url, $data, '', false, $opts);
        /*** 获取DB_NAME ***/
        $db_html = preg_replace("/[\n\r]|&nbsp\;/", '', $result['result']);

        //$db_matches[1] DB_NAME, $db_matches[2] DB_COMMENT
        preg_match_all("/.*?<a href='db\.php\?dbname=(\w+)'>\w+<\/a><\/td>.*?<td align=\"left\">(.*?)<\/td>.*?/", $db_html, $db_matches);
        $db_count = sizeof($db_matches[1]) / 2;
        /*** 获取TABLE_NAME ***/
        //初始化curl_multi
        $mh_table      = curl_multi_init();
        $ignore_count  = 0;
        $curr_db_index = 0;
        for ($i = 0; $i < $db_count; $i++) {
            //因为没有权限访问这些数据库，会因此导致整个网站forbidden，故忽略这些数据库
            if (in_array($db_matches[1][$i], $ignore_list)) {
                $ignore_count++;
                continue;
            }
            $res[$curr_db_index]['db_name']    = $db_matches[1][$i];
            $res[$curr_db_index]['db_comment'] = $db_matches[2][$i];
            $url                               = $domain . '/db.php?dbname=' . $db_matches[1][$i];
            //初始各个 curl
            $table_conn[$curr_db_index] = curl_init($url);
            curl_setopt_array($table_conn[$curr_db_index], $opts);
            curl_multi_add_handle($mh_table, $table_conn[$curr_db_index]);
            $curr_db_index++;
        }

        $db_count = $db_count - $ignore_count;
        //等待全部完成(TABLE_NAME)
        do {
            $mrc = curl_multi_exec($mh_table, $active);
        } while ($active);
        //逐一取回数据
        for ($curr_db_index = 0; $curr_db_index < $db_count; $curr_db_index++) {
            $tmp = curl_multi_getcontent($table_conn[$curr_db_index]);
            curl_multi_remove_handle($mh_table, $table_conn[$curr_db_index]);

            $table_html = preg_replace("/[\n\r]|&nbsp\;/", '', $tmp);
            //$table_matches[1] TABLE_NAME, $table_matches[2] TABLE_COMMENT
            preg_match_all("/.*?<td align=\"left\"><IMG SRC=\"images\/table\.gif\" WIDTH=\"14\" HEIGHT=\"16\" BORDER=\"0\" align=\"absmiddle\"> <a href='table\.php\?dbname=\w+&tabname=(\w+)'>\w+<\/a><\/td>    <td align=\"left\">(.*?)<\/td>.*?/", $table_html, $table_matches);

            $table_count  = sizeof($table_matches[1]) / 2;
            $ignore_count = 0;
            $table_index  = 0;
            /*** 获取FIELDS_INFO ***/
            $fileds_curl = curl_multi_init();
            for ($curr_table_index = 0; $curr_table_index < $table_count; $curr_table_index++) {
                // gss_开头的表为权限表，不需要下载
                if (strpos($table_matches[1][$curr_table_index], 'gss_') !== false) {
                    $ignore_count++;
                    continue;
                }

                $table_name                                                   = $res[$curr_db_index]['tables'][$table_index]['table_name']                                                   = $table_matches[1][$curr_table_index];
                $res[$curr_db_index]['tables'][$table_index]['table_comment'] = $table_matches[2][$curr_table_index];

                //获取FIELDS_INFO
                $url                       = $domain . '/table.php?dbname=' . $res[$curr_db_index]['db_name'] . '&tabname=' . $table_name;
                $fields_conn[$table_index] = curl_init($url);
                curl_setopt_array($fields_conn[$table_index], $opts);
                curl_multi_add_handle($fileds_curl, $fields_conn[$table_index]);

                //等待全部完成
                do {
                    $mrc = curl_multi_exec($fileds_curl, $active);
                } while ($active);

                $table_index++;
            }
            $table_count = $table_count - $ignore_count;
            for ($curr_table_index = 0; $curr_table_index < $table_count; $curr_table_index++) {
                $tmp = curl_multi_getcontent($fields_conn[$curr_table_index]);
                curl_multi_remove_handle($fileds_curl, $fields_conn[$curr_table_index]);

                $fields_html = preg_replace("/[\n\r]|&nbsp\;/", '', $tmp);
                //$fields_matches[1] FIELDS_NAME, $fields_matches[2] FIELD_TYPE, $fields_matches[3] FIELD_COMMENT, $fields_matches[4] FIELD_REMARK
                preg_match_all("/.*?<tr>    <td align=\"center\">.*?<\/td>    <td align=\"left\"><IMG SRC=\"images\/minus\.gif\" WIDTH=\"9\" HEIGHT=\"9\" BORDER=\"0\"  align=\"absmiddle\"> (.+?)<\/td>    <td align=\"left\" title=\".*?\">(.*?)<\/td>    <td align=\"left\">(.*?)<\/td>    <td align=\"left\"><TABLE width='98\%' align=\"center\" border=\"0\"><TR><TD class='ot'>(.*?)<\/TD><\/TR><\/TABLE><\/td>  <\/tr>.*?/", $fields_html, $fields_matches);
                foreach ($fields_matches[1] as $key => $value) {
                    $res[$curr_db_index]['tables'][$curr_table_index]['fields'][$key]['field_name']    = $fields_matches[1][$key];
                    $res[$curr_db_index]['tables'][$curr_table_index]['fields'][$key]['field_type']    = $fields_matches[2][$key];
                    $res[$curr_db_index]['tables'][$curr_table_index]['fields'][$key]['field_comment'] = $fields_matches[3][$key];
                    $res[$curr_db_index]['tables'][$curr_table_index]['fields'][$key]['field_remark']  = $fields_matches[4][$key];
                }
            }
            curl_multi_close($fileds_curl);
        }
        curl_multi_close($mh_table);

        //导出excel
        $this->exportExcel($res);
    }

    /**
     * 导出excel
     * @param  [type] $res = array('' => array('db_name' => '', 'db_comment' => '', 'tables' => array('table_name' => '', 'table_comment' => '', 'fields' => array('key' => '', 'field_name' => '', 'field_type' => '', 'field_comment' => '', 'field_remark' => ''))))
     * @return [type]      [description]
     */
    public function exportExcel($res)
    {
        $row_height  = 28;
        $font_size   = 12;
        $font_family = '宋体';
        $time        = time();
        $save_path   = RESOURCE_PATH . '/tmp/' . session_id() . '/' . $time;
        if (!FileOperator::makeDir($save_path)) {
            exit('CAN NOT CREATE THE DIRECTORY.');
        }

        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        foreach ($res as $db_index => $db_arr) {
            $objPHPExcel = new \PHPExcel();
            foreach ($db_arr['tables'] as $table_index => $table_arr) {
                //设置活动sheet
                if (!empty($table_index)) {
                    $newSheet = $objPHPExcel->createSheet();
                    $newSheet->setTitle(substr($table_arr['table_name'], 0, 30));
                    $objPHPExcel->setActiveSheetIndex($table_index);
                } else {
                    $objPHPExcel->getActiveSheet()->setTitle(substr($table_arr['table_name'], 0, 30));
                }
                $sheet = $objPHPExcel->getActiveSheet();

                //设置表格内容
                $sheet->setCellValue('A1', '数据库：' . $db_arr['db_name'] . ';' . $db_arr['db_comment']);
                $sheet->setCellValue('A2', '数据表：' . $table_arr['table_name'] . ';' . $table_arr['table_comment']);
                $sheet->setCellValue('A3', '序号');
                $sheet->setCellValue('B3', '字段名称');
                $sheet->setCellValue('C3', '数据类型');
                $sheet->setCellValue('D3', '栏位标准名称');
                $sheet->setCellValue('E3', '栏位说明');
                $sheet->setCellValue('F3', '备注说明');
                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(50);
                $sheet->getColumnDimension('E')->setWidth(50);
                $sheet->getColumnDimension('F')->setWidth(50);

                //合并单元格
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

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

                //设置粗体
                $sheet->getStyle("A3:F3")->getFont()->setBold(true);

                //输出字段信息
                $curr_index = 4;
                foreach ($table_arr['fields'] as $field_index => $field_arr) {
                    $sheet->setCellValue('A' . $curr_index, $field_index + 1);
                    $sheet->setCellValue('B' . $curr_index, $field_arr['field_name']);
                    $sheet->setCellValue('C' . $curr_index, $field_arr['field_type']);
                    $row_count1 = preg_match_all("/<br.*?\/>/", $field_arr['field_comment'], $tmp_matches);
                    $row_count2 = preg_match_all("/<br.*?\/>/", $field_arr['field_remark'], $tmp_matches);
                    $row_count  = max($row_count1, $row_count2) + 1;
                    $sheet->setCellValue('D' . $curr_index, preg_replace("/<br.*?\/>/", "\n", $field_arr['field_comment']));
                    $sheet->setCellValue('F' . $curr_index, preg_replace("/<br.*?\/>/", "\n", $field_arr['field_remark']));
                    $sheet->getStyle("D{$curr_index}:F{$curr_index}")->getAlignment()->setWrapText(true);
                    $sheet->getRowDimension($curr_index)->setRowHeight($row_height * $row_count);
                    $curr_index++;
                }
                $curr_index = $curr_index - 1;
                $sheet->getStyle("D3:D{$curr_index}")->getFont()->setBold(true)->getColor()->setRGB('0066CC');
                $sheet->getStyle("A1:F{$curr_index}")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            }

            //生成excel文件
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $save_file = $save_path . '/' . $db_arr['db_name'] . '.xls';
            $objWriter->save($save_file);
            $objPHPExcel->disconnectWorksheets();
        }

        //压缩文件
        $zip_file = dirname($save_path) . '/' . $time . '.zip';
        HZip::zipDir($save_path, $zip_file);
        //下载
        $downloader = new HttpDownload;
        $downloader->set_byfile($zip_file); //Download from a file
        $downloader->download(); //Download File
        //删除
        FileOperator::deleteDir($save_path);
        unlink($zip_file);
    }

}
