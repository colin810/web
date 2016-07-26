<?php
namespace web\controller;

use \lib\Common;
use \lib\Controller;

class SvnController extends Controller
{
    public function indexAction()
    {
        if (isset($_POST['content'])) {
            $operates = array('Added', 'Deleted', 'Modified');
            $content  = $_POST['content'];
            $lines    = explode(Chr(10), $content);
            $files    = array();
            foreach ($lines as $key => $line) {
                $tmp = explode(' : ', $line);
                if (sizeof($tmp) < 2) {
                    continue;
                }
                $operate = trim($tmp[0]);
                $file    = trim($tmp[1]);
                $file    = strpos($file, "/web") === 0 ? substr($file, 4) : $file; // ltrim($file, "/web");
                if (in_array($operate, $operates)) {
                    if ($operate == 'Deleted') {
                        unset($files[$file]);
                        continue;
                    }
                    $files[$file] = $file;
                }
            }
            sort($files);
            Common::echoMsg(true, $files);
        }
        $this->render->display('index');
    }
}
