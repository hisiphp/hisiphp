<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\common\util;

class Http {
    /**
     * 用CURL模拟获取网页内容,兼容旧版
     * @param string $url       所要获取内容的网址
     * @param array  $params      所要提交的数据
     * @param array $header     请求头信息
     * @param string $charset   编码
     * @param string $proxy     代理设置
     * @param integer $expire   时间限制
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function getRequest($url, $params = [], $header = [], $charset = 'UTF-8', $proxy = null, $expire = 30) 
    {
        return self::send($url, $params, 'GET', $header, $expire);
    }

    /**
     * 用CURL模拟提交数据,兼容旧版
     * @param string $url       post所要提交的网址
     * @param array  $params    所要提交的数据
     * @param array $header     请求头信息
     * @param string $charset   编码
     * @param string $proxy     代理设置
     * @param integer $expire   所用的时间限制
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function postRequest($url, $params = [], $header = [], $charset= 'UTF-8', $proxy = null, $expire = 30)
    {
        return self::send($url, $params, 'POST', $header, $expire);
    }

    /**
     * GET请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $expire 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function get($url, $params = '', $header = [], $expire = 30, $options = [])
    {
        return self::send($url, $params, 'GET', $header, $expire, $options);
    }

    /**
     * POST请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $expire 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function post($url, $params = '', $header = [], $expire = 30, $options = [])
    {
        return self::send($url, $params, 'POST', $header, $expire, $options);
    }

    /**
     * DELETE请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $expire 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function delete($url, $params = '', $header = [], $expire = 30, $options = [])
    {
        return self::send($url, $params, 'DELETE', $header, $expire, $options);
    }

    /**
     * PUT请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $expire 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function put($url, $params = '', $header = [], $expire = 30, $options = [])
    {
        return self::send($url, $params, 'PUT', $header, $expire, $options);
    }

    /**
     * CURL发送Request请求,支持GET、POST、PUT、DELETE
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param string $method 请求的方法
     * @param array $header 传递的头部参数
     * @param int $expire 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    private static function send($url, $params = '', $method = 'GET', $header = [], $expire = 30, $options = [])
    {
        $cookieFile = RUNTIME_PATH . 'temp/' . md5(config('authkey')) . '.txt';
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36';
        $ch = curl_init();
        $opt                            = [];
        $opt[CURLOPT_COOKIEJAR]         = $cookieFile;
        $opt[CURLOPT_COOKIEFILE]        = $cookieFile;
        $opt[CURLOPT_USERAGENT]         = $userAgent;
        $opt[CURLOPT_CONNECTTIMEOUT]    = $expire;
        $opt[CURLOPT_TIMEOUT]           = $expire;
        $opt[CURLOPT_HTTPHEADER]        = $header ? : ['Expect:'];
        $opt[CURLOPT_FOLLOWLOCATION]    = true;
        $opt[CURLOPT_RETURNTRANSFER]    = true;

        if (substr($url, 0, 8) == 'https://') {
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
            $opt[CURLOPT_SSL_VERIFYHOST] = 2;
        }
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        switch (strtoupper($method)) {
            case 'GET':
                $extStr   = (strpos($url, '?') !== false) ? '&' : '?';
                $url      = $url . (($params) ? $extStr . $params : '');
                $opt[CURLOPT_URL] = $url . '?' . $params;
                break;

            case 'POST':
                $opt[CURLOPT_POST] = true;
                $opt[CURLOPT_POSTFIELDS] = $params;
                $opt[CURLOPT_URL] = $url;
                break;

            case 'PUT':
                $opt[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opt[CURLOPT_POSTFIELDS] = $params;
                $opt[CURLOPT_URL] = $url;
                break;

            case 'DELETE':
                $opt[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opt[CURLOPT_POSTFIELDS] = $params;
                $opt[CURLOPT_URL] = $url;
                break;

            default:
                return ['error' => 0, 'msg' => '请求的方法不存在', 'info' => []];
                break;
        }
        curl_setopt_array($ch, (array) $opt + $options);
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        if ($result == false || !empty($error)) {
            $errno  = curl_errno($ch);
            $info   = curl_getinfo($ch);
            curl_close($ch);
            return [
                'errno' => $errno,
                'msg'   => $error,
                'info'  => $info,
            ];
        }
        curl_close($ch);
        return $result;
    }
}
