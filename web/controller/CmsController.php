<?php
namespace web\controller;

use \lib\Common;
use \lib\ConfigLoader;
use \lib\Controller;
use \lib\CurlExt;
use \lib\DBHelper;
use \lib\FileOperator;
use \lib\FileUpload;
use \lib\HttpDownload;
use \lib\HZip;
use \lib\Register;
use \model\Mcms;
use \model\Mcms_comment;
use \model\Mcms_key;
use \model\Mcms_log;
use \model\Mcms_value;

class CmsController extends Controller
{
    protected static $loginID;
    protected static $username;

    public function __construct()
    {
        parent::__construct();
        if (isset($_SESSION['yespoID'])) {
            $this->loginID  = $_SESSION['yespoID'];
            $this->username = $_SESSION['username'];
        } elseif (Register::get('action') != 'index') {
            $this->redirect('index');
        }
    }

    /**
     * [登录]
     * @return [type] [description]
     */
    public function indexAction()
    {
        if ($_POST) {
            $params = array('yespoID', 'yespoPassword');
            $params = Common::getReqestParams($params, 'cms');
            $this->login($params['yespoID'], $params['yespoPassword']);
        }
        $this->render->display('index');
    }

    /**
     * [登录方法]
     * @param  [type] $yespoID       [description]
     * @param  [type] $yespoPassword [description]
     * @return [type]                [description]
     */
    public function login($yespoID, $yespoPassword)
    {
        $cms_config = ConfigLoader::load('cms_config');
        if (!array_key_exists($yespoID, $cms_config['account'])) {
            Common::echoMsg(false, array('common' => 'You are not authorized to login.'));
        }

        $url          = 'http://192.168.32.123/testlogin.php';
        $result       = CurlExt::send($url);
        $redirect_url = $result['info']['redirect_url'];
        $params       = Common::getParamsFromUrl($redirect_url);
        $url          = $params['domain'] . '/login.php';
        $post         = array('username' => $yespoID, 'password' => $yespoPassword, 'code' => $params['code'], 'computer_id' => $params['computer_id']);
        $result       = CurlExt::send($url, $post);
        if ($result['error']) {
            Common::echoMsg(false, array('common' => $result['error']));
        }
        $result = json_decode($result['result'], true);
        if (isset($result['error_code']) && !empty($result['error_code'])) {
            Common::echoMsg(false, array('common' => $result['error_msg']));
        }

        $_SESSION['yespoID']  = $this->loginID  = strtolower($yespoID);
        $_SESSION['username'] = $this->username = $cms_config['account'][$this->loginID];
        Common::echoMsg(true);
    }

    /**
     * 列表頁
     * @return [type] [description]
     */
    public function listAction()
    {
        $params = array(
            'search_system',
            'search_lang',
            'search_condition_type',
            'search_value',
        );

        $params = Common::getReqestParams($params, 'cms');

        //获取搜索条件
        $cms_config          = ConfigLoader::load('cms_config');
        $default_search_lang = array_keys($cms_config['lang']);
        $pop_count           = sizeof($cms_config['lang']) - 3;
        for ($i = 0; $i < $pop_count; $i++) {
            array_pop($default_search_lang);
        }

        $cookie         = $_COOKIE;
        $getSearchValue = function ($field_name, $default_value, $setCookie = true) use ($params, $cookie) {
            if (isset($params[$field_name])) {
                $value = $params[$field_name];
            } elseif (isset($cookie[$field_name])) {
                $value = json_decode($cookie[$field_name]);
            } else {
                $value = $default_value;
            }
            if ($setCookie) {
                $expired = time() + 3600 * 24 * 7;
                setcookie($field_name, json_encode($value), $expired, '/');
            }
            return $value;
        };

        $search_system         = $getSearchValue('search_system', 'api'); //默认系统
        $search_lang           = $getSearchValue('search_lang', $default_search_lang); //默认选中语言
        $search_condition_type = $getSearchValue('search_condition_type', 1); //设置默认搜索条件
        $search_value          = $getSearchValue('search_value', '', false); //默认搜索值
        list($list, $page)     = Mcms::getPageList($search_system, $search_lang, $search_value, $search_condition_type);

        $search_arr = array(
            'search_system'         => $search_system,
            'search_lang'           => $search_lang,
            'search_condition_type' => $search_condition_type,
            'search_value'          => $search_value,
        );
        $extraData = array(
            'cms_config' => $cms_config,
            'search_arr' => $search_arr,
            'list'       => $list,
            'page'       => $page,
        );
        $this->render->display('list', $extraData);
    }

    /**
     * 显示排版模式
     * @return [type] [description]
     */
    public function ueditorAction()
    {
        $key_id = trim($_GET['ajax_key_id']);
        $lang   = trim($_GET['ajax_lang']);
        $model  = Mcms_value::getKeyAndValue($key_id, $lang);
        $this->render->display('ueditor', array('model' => $model));
    }

    /**
     * 获取内容
     * @return [type] [description]
     */
    public function contentAction()
    {
        $params = array(
            'ajax_key_id',
            'ajax_lang',
        );
        $params = Common::getReqestParams($params, 'cms');
        exit(Mcms_value::getContent($params['ajax_key_id'], $params['ajax_lang']));
    }

    /**
     * 保存数据
     * @return [type] [description]
     */
    public function saveAction()
    {
        $params = array(
            'edit_key_id',
            'edit_lang',
            'edit_content',
            'edit_version',
            'edit_remark',
        );
        $params = Common::getReqestParams($params, 'cms');

        $key_id  = $params['edit_key_id'];
        $lang    = $params['edit_lang'];
        $content = isset($_POST['edit_content']) ? trim($_POST['edit_content']) : '';
        $remark  = isset($_POST['edit_remark']) ? trim($_POST['edit_remark']) : '';

        $key_value   = Mcms_value::get($key_id, $lang);
        $max_version = Mcms_key::getMaxVersion($params['edit_key_id']);
        //确定版本号
        if (isset($params['edit_version'])) {
            $max_version = $version = $key_value['version'] < $max_version ? $max_version : $max_version + 1;
        } else {
            $version = empty($key_value['version']) ? 0 : $key_value['version'];
        }

        //写入数据库
        if (empty($key_value)) {
            Mcms_value::insert($key_id, $lang, $content, $version);
        } else {
            Mcms_value::update($key_value['value_id'], $content, $version, $remark);
        }
        //更新版本号
        Mcms_key::updateVersion($key_id, $max_version);
        if (Common::hasHtml($content)) {
            Common::echoMsg(true);
        } else {
            Common::echoMsg(true);
        }
    }

    /**
     * [getrowAction description]
     * @return [type] [description]
     */
    public function getrowAction()
    {
        $params = array(
            'search_lang',
            'edit_key_id',
        );
        $params = Common::getReqestParams($params, 'cms');
        $lang   = $params['search_lang'];
        $key_id = $params['edit_key_id'];
        $row    = Mcms::getRow($lang, $key_id);
        foreach ($lang as $key => $value) {
            if (Common::hasHtml($row[$value . '_content'])) {
                $row[$value . '_content'] = Common::cutstr($row[$value . '_content_clean'], 30);
            } else {
                $row[$value . '_content'] = empty($row[$value . '_content_clean']) ? '' : $row[$value . '_content_clean'];
            }
        }
        $row['modify_time'] = date('Y-m-d H:i:s', $row['modify_time']);
        Common::echoMsg(true, $row);
    }

    /**
     * [批量功能]
     * @return [type] [description]
     */
    public function batchAction()
    {
        $cms_config = ConfigLoader::load('cms_config');
        $this->render->display('batch', array('cms_config' => $cms_config));
    }
    /**
     * [导入语言包]
     * @return [type] [description]
     */
    public function importAction()
    {
        if ($_POST) {
            $params = array('import_system');
            $params = Common::getReqestParams($params, 'cms');
            $system = $params['import_system'];

            //上传ZIP
            $uploader    = new FileUpload();
            $import_path = WEB_ROOT . '/resource/tmp/import/';
            $uploader->set('allowtype', array('zip', 'xls'));
            $uploader->set('path', $import_path);
            if (!$uploader->upload('lang_zip')) {
                Common::echoMsg(false, array('lang_zip' => $uploader->getErrorMsg()));
            }

            $file_path = $import_path . $uploader->getFileName();
            if ($system == 'app') {
                $count = $this->importAppData($file_path, $system);
            } elseif ($system == 'server') {
                $count = $this->importServerData($file_path, $system);
            } else {
                $count = $this->importWebData($file_path, $system);
            }

            Common::echoMsg(true, "数据导入成功,新增键值 {$count} 个！");
        }
    }

    /**
     * [importWebData description]
     * @param  [type] $file_path [description]
     * @param  [type] $system    [description]
     * @return [type]            [description]
     */
    private function importWebData($file_path, $system)
    {
        $res = HZip::unZip($file_path); // 解压
        @unlink($file_path); // 删除上传文件
        if ($res['status'] === false) {
            Common::echoMsg(false, array('common' => $res['message']));
        }
        $tmp_dir        = $res['message'];
        $tmp_basic_path = $tmp_dir . '/lang/';
        $cms_config     = ConfigLoader::load('cms_config');
        $count          = 0;
        foreach ($cms_config['lang'] as $lang_key => $lang_name) {
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
     * [importAppData description]
     * @param  [type] $file_path [description]
     * @param  [type] $system    [description]
     * @return [type]            [description]
     */
    private function importAppData($file_path, $system)
    {
        $res = HZip::unZip($file_path); // 解压
        @unlink($file_path); // 删除上传文件
        if ($res['status'] === false) {
            Common::echoMsg(false, array('common' => $res['message']));
        }
        $tmp_dir        = $res['message'];
        $tmp_basic_path = $tmp_dir . '/lang/';
        $cms_config     = ConfigLoader::load('cms_config');
        $count          = 0;
        foreach ($cms_config['lang'] as $lang_key => $lang_name) {
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
     * [importServerData description]
     * @param  [type] $file_path [description]
     * @param  [type] $system    [description]
     * @return [type]            [description]
     */
    private function importServerData($file_path, $system)
    {
        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        $objPHPExcel = \PHPExcel_IOFactory::load($file_path);

        $cms_config = ConfigLoader::load('cms_config');
        $count      = 0;

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $lang_key = $worksheet->getTitle();
            if (array_key_exists($lang_key, $cms_config['lang'])) {
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

    /**
     * 下載語言包
     * @return [type] [description]
     */
    public function downloadAction()
    {
        //生成语言包文件
        $params  = array('download_system');
        $params  = Common::getReqestParams($params, 'cms');
        $system  = $params['download_system'];
        $baseDir = WEB_ROOT . '/resource/tmp/download/' . time() . '/lang/';

        if ($system == "server") {
            $this->downloadServer($baseDir, $system);
        } elseif ($system == 'app_android' || $system == 'app_ios') {
            $this->downloadApp($baseDir, $system);
        } else {
            $this->downloadWeb($baseDir, $system);
        }
    }

    /**
     * [downloadServer description]
     * @param  [type] $baseDir [description]
     * @param  [type] $system  [description]
     * @return [type]          [description]
     */
    private function downloadServer($baseDir, $system)
    {
        require_once WEB_ROOT . '/ext/PHPExcel/PHPExcel.php';
        $objPHPExcel = new \PHPExcel();
        $db          = DBHelper::getInstance();
        $cms_config  = ConfigLoader::load('cms_config');
        $filename    = $baseDir . '/server.xls';
        $i           = 0;
        $row_height  = 20;
        $font_size   = 12;
        $font_family = '宋体';
        foreach ($cms_config['lang'] as $key => $value) {
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

            $sheet->setCellValue('A1', '错误代码');
            $sheet->setCellValue('B1', '错误描述');
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
        header('Content-Disposition: attachment;filename="server.xls"');
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
    }

    /**
     * [downloadWeb description]
     * @param  [type] $baseDir [description]
     * @return [type]          [description]
     */
    private function downloadWeb($baseDir, $system)
    {
        $db         = DBHelper::getInstance();
        $cms_config = ConfigLoader::load('cms_config');
        foreach ($cms_config['lang'] as $key => $value) {
            $path = $baseDir . $key;
            if (!is_dir($path)) {
                $res = mkdir($path, 0777, true);
                if (!$res) {
                    Common::echoMsg(false, "Directory Create Failed！");
                }
            }
            $rs = Mcms::getDownloadData($system, $key);

            $filename = $path . '/text.php';
            $file     = fopen($filename, 'wb');
            fwrite($file, '<?php Global $T;$T = array (' . Chr(10));
            foreach ($rs as $k => $v) {
                $content = $v['content'];
                $content = str_replace("'", "\'", $content);
                $content = str_replace("&#39;", "\'", $content);
                // $content = str_replace('"', '\"', $content);
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
    }

    /**
     * [downloadWeb description]
     * @param  [type] $baseDir [description]
     * @return [type]          [description]
     */
    private function downloadApp($baseDir, $system)
    {
        $db         = DBHelper::getInstance();
        $cms_config = ConfigLoader::load('cms_config');

        list($system, $client) = explode('_', $system);

        $path = $baseDir;
        if (!is_dir($path)) {
            $res = mkdir($path, 0777, true);
            if (!$res) {
                Common::echoMsg(false, "Directory Create Failed！");
            }
        }
        if ($client == 'android') {
            foreach ($cms_config['lang'] as $key => $value) {
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
        } elseif ($client == 'ios') {
            foreach ($cms_config['lang'] as $key => $value) {
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
        }

        $inFile  = rtrim($baseDir, '/');
        $outFile = rtrim($baseDir, '/lang/') . '/lang.zip';
        HZip::zipDir($inFile, $outFile);
        $object = new HttpDownload();
        $object->set_byfile($outFile);
        $object->download();
    }

    /**
     * 增加key值
     */
    public function addAction()
    {
        $cms_config = ConfigLoader::load('cms_config');
        if ($_POST) {
            $params   = array('edit_system', 'edit_key');
            $params   = Common::getReqestParams($params, 'cms');
            $system   = $params['edit_system'];
            $key_code = $params['edit_key'];
            $content  = $params['edit_content'];

            if (Mcms_key::getKeyID($system, $key_code)) {
                Common::echoMsg(false, array('edit_key' => 'key code : ' . $key_code . ' 已存在！'));
            }
            list($key_id) = Mcms_key::insert($system, $key_code);
            foreach ($cms_config['lang'] as $key => $value) {
                if ($content[$key]) {
                    Mcms_value::insert($key_id, $key, $content[$key]);
                }
            }
            Common::echoMsg(true, '新增 key code 成功!');

        }
        $this->render->display('add', array('cms_config' => $cms_config));
    }

    /**
     * log記錄列表
     * @return [type] [description]
     */
    public function loglistAction()
    {
        $params = array('ajax_key_id', 'ajax_lang', 'search_value');
        $params = Common::getReqestParams($params, 'cms');

        $key_id            = $params['ajax_key_id'];
        $lang              = $params['ajax_lang'];
        $search_value      = isset($params['search_value']) ? $params['search_value'] : '';
        list($list, $page) = Mcms_log::getPageList($key_id, $lang, $search_value);
        $cms_config        = ConfigLoader::load('cms_config');
        $params            = array(
            'key_id'       => $key_id,
            'lang'         => $lang,
            'search_value' => $search_value,
        );
        $extraData = array(
            'list'     => $list,
            'page'     => $page,
            'params'   => $params,
            'opt_flag' => $cms_config['opt_flag'],
        );
        $this->render->display('loglist', $extraData);
    }

    /**
     * [logdtAction description]
     * @return [type] [description]
     */
    public function logdtAction()
    {
        $cms_config = ConfigLoader::load('cms_config');
        $id         = trim($_GET['id']);
        $model      = Mcms_log::get($id);
        $this->render->display('logdt', array('model' => $model, 'cms_config' => $cms_config));
    }

    /**
     * 登出
     * @return [type] [description]
     */
    public function logoutAction()
    {
        unset($_SESSION['yespoID']);
        unset($_SESSION['username']);
        $this->redirect('index');
    }

    /**
     * [backupAction description]
     * @return [type] [description]
     */
    public function backupAction()
    {
        $db_config = ConfigLoader::load('db_config');
        system("@" . WEB_ROOT . "\\bak\\backup.bat");
        Common::echoMsg(true, "备份成功！");
    }
}
