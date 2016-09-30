<?php
namespace lib;

use \lib\CFormValidator;

class Common
{

    /**
     * [getParamsFromUrl description]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public static function getParamsFromUrl($url)
    {
        $arr = parse_url($url);
        parse_str($arr['query'], $params);
        $params['domain'] = $arr['scheme'] . '://' . $arr['host'];
        $params['scheme'] = strtolower($arr['scheme']);
        return $params;
    }

    /**
     * [pageLimit description]
     * @param  string $return_type [description]
     * @return [type]              [description]
     */
    public static function pageLimit($return_type = 'array')
    {
        $cur_page  = isset($_REQUEST['cur_page']) ? $_REQUEST['cur_page'] : 1;
        $page_size = isset($_REQUEST['page_size']) ? $_REQUEST['page_size'] : 20;
        $limit     = array(($cur_page - 1) * $page_size, $page_size);
        if ($return_type == 'array') {
            return $limit;
        } else {
            return implode(',', $limit);
        }
    }

    /**
     * [getPage description]
     * @param  [type] $total [description]
     * @return [type]        [description]
     */
    public static function getPage($total)
    {
        $cur_page          = isset($_REQUEST['cur_page']) ? $_REQUEST['cur_page'] : 1;
        $page_size         = isset($_REQUEST['page_size']) ? $_REQUEST['page_size'] : 20;
        $json['cur_page']  = (string) $cur_page;
        $json['page_size'] = (string) $page_size;
        $json['page_num']  = (string) (ceil($total / $page_size));
        $json['total']     = (string) $total;
        return $json;
    }

    /**
     * [cutstr description]
     * @param  [type]  $input  [description]
     * @param  integer $length [description]
     * @param  string  $dot    [description]
     * @return [type]          [description]
     */
    public static function cutstr($input, $length = 20, $dot = ' <b>【More】</b> ')
    {
        mb_internal_encoding("UTF-8");
        $origin_length = mb_strlen($input);
        if ($origin_length > $length) {
            $input = mb_substr($input, 0, $length) . $dot;
        }
        return $input;
    }

    /**
     * [hasHtml description]
     * @param  [type]  $input [description]
     * @return boolean        [description]
     */
    public function hasHtml($input)
    {
        mb_internal_encoding("UTF-8");
        $origin_length = mb_strlen($input);
        $target_length = mb_strlen(self::removeXSS($input));

        if ($origin_length > $target_length) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [removeXSS description]
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public static function removeXSS($input)
    {
        $input = self::removewrap($input);
        $input = str_replace("\'", "'", $input);
        // $input = str_replace("\r", "", $input); //过滤换行
        // $input = str_replace("\n", "", $input); //过滤换行
        // $input = str_replace("\t", "", $input); //过滤换行
        // $input = str_replace("\r\n", "", $input); //过滤换行
        // $input = preg_replace("/\s+/", " ", $input); //过滤多余回车
        $input = preg_replace("/<[ ]+/si", "<", $input); //过滤<__("<"号后面带空格)
        $input = preg_replace("/<\!--.*?-->/si", "", $input); //过滤html注释
        $input = preg_replace("/<(\!.*?)>/si", "", $input); //过滤DOCTYPE
        $input = preg_replace("/<(\/?html.*?)>/si", "", $input); //过滤html标签
        $input = preg_replace("/<(\/?head.*?)>/si", "", $input); //过滤head标签
        $input = preg_replace("/<(\/?meta.*?)>/si", "", $input); //过滤meta标签
        $input = preg_replace("/<(\/?body.*?)>/si", "", $input); //过滤body标签
        $input = preg_replace("/<(\/?link.*?)>/si", "", $input); //过滤link标签
        $input = preg_replace("/<(\/?form.*?)>/si", "", $input); //过滤form标签
        $input = preg_replace("/cookie/si", "COOKIE", $input); //过滤COOKIE标签
        $input = preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si", "", $input); //过滤applet标签
        $input = preg_replace("/<(\/?applet.*?)>/si", "", $input); //过滤applet标签
        $input = preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si", "", $input); //过滤style标签
        $input = preg_replace("/<(\/?style.*?)>/si", "", $input); //过滤style标签
        $input = preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si", "", $input); //过滤title标签
        $input = preg_replace("/<(\/?title.*?)>/si", "", $input); //过滤title标签
        $input = preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si", "", $input); //过滤object标签
        $input = preg_replace("/<(\/?objec.*?)>/si", "", $input); //过滤object标签
        $input = preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si", "", $input); //过滤noframes标签
        $input = preg_replace("/<(\/?noframes.*?)>/si", "", $input); //过滤noframes标签
        $input = preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si", "", $input); //过滤frame标签
        $input = preg_replace("/<(\/?i?frame.*?)>/si", "", $input); //过滤frame标签
        $input = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $input); //过滤script标签
        $input = preg_replace("/<(\/?script.*?)>/si", "", $input); //过滤script标签
        $input = preg_replace("/javascript/si", "Javascript", $input); //过滤script标签
        $input = preg_replace("/vbscript/si", "Vbscript", $input); //过滤script标签
        $input = preg_replace("/on([a-z]+)\s*=/si", "On\\1=", $input); //过滤script标签
        $input = preg_replace("/&#/si", "&＃", $input); //过滤script标签，如javAsCript:alert();
        //使用正则替换
        $pat   = "/<(\/?)(script|i?frame|style|html|body|li|i|map|title|img|link|span|u|font|table|tr|b|marquee|td|strong|div|a|meta|\?|\%)([^>]*?)>/isU";
        $input = preg_replace($pat, "", $input);
        $input = strip_tags($input);
        // $input = htmlspecialchars(stripslashes(trim($input)));
        return $input;
    }

    /**
     * [removewrap description]
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public static function removewrap($input)
    {
        $input = str_replace(chr(10), '', $input);
        $input = str_replace(chr(13), '', $input);
        $input = str_replace("\r", "", $input); //过滤换行
        $input = str_replace("\n", "", $input); //过滤换行
        $input = str_replace("\t", "", $input); //过滤换行
        $input = str_replace("\r\n", "", $input); //过滤换行
        $input = preg_replace("/\s+/", " ", $input); //过滤多余回车
        return $input;
    }

    /**
     * [hasEnter description]
     * @param  [type]  $input [description]
     * @return boolean        [description]
     */
    public static function hasEnter($input)
    {
        if (strpos($input, chr(10)) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * [getReqestParams description]
     * @param  array   $params      [description]
     * @param  string  $patten      [description]
     * @param  [type]  $source_data [description]
     * @param  boolean $removeXSS   [description]
     * @return [type]               [description]
     */
    public static function getReqestParams($params = array(), $patten = 'default', $source_data = null, $removeXSS = true)
    {
        if (!isset($source_data)) {
            $source_data = $_REQUEST;
        }

        $result = array();
        $removeXSS && self::loopRemoveXSS($source_data, $result);
        $valid = CFormValidator::validate($params, $patten);
        if ($valid === true) {
            return $removeXSS ? $result : $source_data;
        } else {
            self::echoMsg(false, $valid);
        }
    }

    /**
     * [loopRemoveXSS description]
     * @param  [type] $input   [description]
     * @param  [type] &$result [description]
     * @return [type]          [description]
     */
    public static function loopRemoveXSS($input, &$result)
    {
        foreach ($input as $key => $value) {
            if (!is_array($value)) {
                $result[$key] = self::removeXSS($value);
            } else {
                $result[$key] = self::loopRemoveXSS($value, $result[$key]);
            }
        }
        return $result;
    }

    /**
     * [guid description]
     * @return [type] [description]
     */
    public static function guid()
    {
        $uid = md5(uniqid(rand(), true));
        return strtoupper(hash('ripemd128', $uid));
    }

    /**
     * [rebuildArray description]
     * @param  [type] $key   [description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public static function rebuildArray($key, $array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ((array) $array as $k => $v) {
            if (!isset($v[$key])) {
                return false;
            }
            $result[$v[$key]] = $v;
        }
        return $result;
    }

    /**
     * [echoMsg description]
     * @param  boolean $status  [description]
     * @param  array   $message [description]
     * @return [type]           [description]
     */
    public static function echoMsg($status = true, $message = array())
    {
        $json = array();
        if ($status === true) {
            $json['status'] = 'success';
        } else {
            $json['status'] = 'error';
        }
        $json['message'] = $message;
        exit(json_encode($json));
    }
}
