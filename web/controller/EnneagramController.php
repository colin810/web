<?php

namespace web\controller;

use \lib\Common;
use \lib\Controller;
use \model\Menneagram;
use \model\Menneagram_group;
use \model\Menneagram_row;
use \model\Menneagram_title;

class EnneagramController extends Controller
{
    public function indexAction()
    {
        if ($_POST) {
            $params     = array('username', 'telephone');
            $params     = Common::getReqestParams($params, 'enneagram', true);
            $session_id = Menneagram_group::insert($params['username'], $params['telephone']);

            if ($session_id === false) {
                Common::echoMsg(false, array('common' => '数据保存失败，请联系管理员！'));
            }

            //保存測試數據
            $data = array();
            foreach ($params['enneagram'] as $enneagram_id => $row) {
                foreach ($row as $title_id => $opt_val) {
                    $data[] = array(
                        'session_id'   => $session_id,
                        'enneagram_id' => $enneagram_id,
                        'title_id'     => $title_id,
                        'opt_val'      => $opt_val,
                    );
                }
            }

            $id = Menneagram_row::insert($data);
            if (empty($id)) {
                Common::echoMsg(false, array('common' => '数据保存失败，请联系管理员！'));
            }
            //返回測試結果
            $resultDesc = Menneagram_row::result($session_id);
            Common::echoMsg(true, array('common' => $resultDesc['enneagram_desc']));
        }

        //隨機排序
        $title_list = Menneagram_title::getAll();
        $list       = array();

        foreach ($title_list as $key => $row) {
            $start = rand(0, sizeof($title_list) - 1);
            $tmp   = array('enneagram_id' => $title_list[$start]['enneagram_id'], 'title_id' => $title_list[$start]['title_id'], 'title_name' => $title_list[$start]['title_name']);
            array_push($list, $tmp);
            array_splice($title_list, $start, 1);
        }
        $this->render->display('index', array('list' => $list));
    }

    public function listAction()
    {
        $search              = isset($_GET['search']) ? trim($_GET['search']) : '';
        list($result, $page) = Menneagram_group::getPageList($search);
        $enneagram           = Menneagram::getAll();
        $this->render->display('list', array('result' => $result, 'page' => $page, 'enneagram' => $enneagram));
    }

    public function detailAction()
    {
        if ($_POST) {
            $params    = array('session_id');
            $params    = Common::getReqestParams($params, 'enneagram', true);
            $enneagram = Menneagram::getAll();
            $list      = Menneagram_row::getDetailList($params['session_id']);
            $html      = '';
            foreach ($list as $key => $value) {
                $html .= $enneagram[$value['enneagram_id']]['enneagram_name'] . ' ' . $value['rank'] . '<br/>';
            }
            exit($html);
        }
    }
}
