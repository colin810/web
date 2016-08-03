<?php
namespace web\controller;

use \lib\Common;
use \lib\ConfigLoader;
use \lib\Controller;
use \lib\CurlExt;
use \lib\FileUpload;
use \lib\Register;
use \model\Mcms;
use \model\Mcms_download;
use \model\Mcms_import;
use \model\Mcms_key;
use \model\Mcms_lang;
use \model\Mcms_log;
use \model\Mcms_system;
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

        $params     = Common::getReqestParams($params, 'cms');
        $cms_config = ConfigLoader::load('cms_config');

        //获取搜索条件
        $default_search_lang = array();
        $cms_lang            = Mcms_lang::getKeyPair();
        $cms_system          = Mcms_system::getKeyPair();

        //默认显示前三个语言
        $i = 0;
        foreach ($cms_lang as $key => $value) {
            if ($i < 3) {
                array_push($default_search_lang, $key);
            } else {
                break;
            }
            $i++;
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
            'cms_lang'   => $cms_lang,
            'cms_system' => $cms_system,
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
        $content = isset($_POST['edit_content']) ? $_POST['edit_content'] : '';
        $remark  = isset($_POST['edit_remark']) ? $_POST['edit_remark'] : '';

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
        $cms_lang   = Mcms_lang::getKeyPair();
        $cms_system = Mcms_system::getKeyPair();
        $this->render->display('batch', array('cms_config' => $cms_config, 'cms_lang' => $cms_lang, 'cms_system' => $cms_system));
    }
    /**
     * [导入语言包]
     * @return [type] [description]
     */
    public function importAction()
    {
        if ($_POST) {
            $params = array('import_system', 'import_ext');
            $params = Common::getReqestParams($params, 'cms');
            $system = $params['import_system'];
            $method = $params['import_ext'];

            //上传ZIP
            $uploader    = new FileUpload();
            $import_path = WEB_ROOT . '/resource/tmp/import/';
            $uploader->set('allowtype', array('zip', 'xls', 'xlsx'));
            $uploader->set('path', $import_path);
            if (!$uploader->upload('lang_zip')) {
                Common::echoMsg(false, array('lang_zip' => $uploader->getErrorMsg()));
            }
            $file_path = $import_path . $uploader->getFileName();
            $count     = Mcms_import::import($file_path, $system, $method);
            Common::echoMsg(true, "数据导入成功,新增键值 {$count} 个！");
        }
    }

    /**
     * 下載語言包
     * @return [type] [description]
     */
    public function downloadAction()
    {
        //生成语言包文件
        $params  = array('download_system', 'download_ext');
        $params  = Common::getReqestParams($params, 'cms');
        $system  = $params['download_system'];
        $method  = $params['download_ext'];
        $baseDir = WEB_ROOT . '/resource/tmp/download/' . time() . '/lang/';
        Mcms_download::download($baseDir, $system, $method);
    }

    /**
     * 增加key值
     */
    public function addAction()
    {
        $cms_system = Mcms_system::getKeyPair();
        $cms_lang   = Mcms_lang::getKeyPair();
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
            foreach ($cms_lang as $key => $value) {
                if ($content[$key]) {
                    Mcms_value::insert($key_id, $key, $content[$key]);
                }
            }
            Common::echoMsg(true, '新增 key code 成功!');
        }
        $this->render->display('add', array('cms_system' => $cms_system, 'cms_lang' => $cms_lang));
    }

    /**
     * 新增系統
     */
    public function addSystemAction()
    {
        if ($_POST) {
            $params      = array('edit_system_key', 'edit_system_name');
            $params      = Common::getReqestParams($params, 'cms');
            $system_key  = $params['edit_system_key'];
            $system_name = $params['edit_system_name'];
            $isExists    = Mcms_system::get($system_key);
            if (!empty($isExists)) {
                Common::echoMsg(false, array('edit_system_key' => '系统代码 : ' . $system_key . ' 已存在！'));
            }
            $last_id = Mcms_system::insert($system_key, $system_name);
            if ($last_id > 0) {
                Common::echoMsg(true, '新增系统成功!');
            } else {
                Common::echoMsg(true, '新增系统失败!');
            }
        }
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
        $cms_lang   = Mcms_lang::getKeyPair();
        $cms_system = Mcms_system::getKeyPair();
        $id         = trim($_GET['id']);
        $model      = Mcms_log::get($id);
        $this->render->display('logdt', array('model' => $model, 'cms_config' => $cms_config, 'cms_lang' => $cms_lang, 'cms_system' => $cms_system));
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
