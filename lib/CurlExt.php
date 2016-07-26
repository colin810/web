<?php

namespace lib;

class CurlExt
{

    /**
     * [send description]
     * @param  [type]  $url    [description]
     * @param  array   $post   [description]
     * @param  boolean $https  [description]
     * @param  string  $cookie [description]
     * @return [type]          [description]
     */
    public static function send($url, $post = array(), $cookie = '', $https = false, $extOpts = array(), $timeout = 3, $connect_timeout = 3)
    {
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST           => 0,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connect_timeout,
        );

        if (!empty($extOpts)) {
            foreach ($extOpts as $key => $value) {
                $options[$key] = $value;
            }
        }

        if ($https) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = true;
            $options[CURLOPT_CAINFO]         = CERT_PATH;
        }

        if (isset($post)) {
            $options[CURLOPT_POST]       = 1;
            $options[CURLOPT_POSTFIELDS] = $post;
        }

        if ($cookie == 'w') {
            $options[CURLOPT_COOKIEJAR] = COOKIE_JAR;
        } elseif ($cookie == 'r') {
            $options[CURLOPT_COOKIEFILE] = COOKIE_JAR;
        }

        return self::execCurl($options);
    }

    /**
     * [exec_curl description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    private static function execCurl($options)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $error  = '';
        if ($result == false) {
            $error = curl_error($ch);
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        return array('result' => $result, 'info' => $info, 'error' => $error);
    }
}
