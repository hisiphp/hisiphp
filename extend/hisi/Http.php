<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------

namespace hisi;

use Env;
use hisi\Dir;

class Http {
    protected static $userAgent = [
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36 OPR/26.0.1656.60',
        'Opera/8.0 (Windows NT 5.1; U; en)',
        'Mozilla/5.0 (Windows NT 5.1; U; en; rv:1.8.1) Gecko/20061208 Firefox/2.0.0 Opera 9.50',
        'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 9.50',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0',
        'Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11',
        'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER',
        'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; LBBROWSER)',
        'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E; LBBROWSER)',
        'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; QQBrowser/7.0.3698.400)',
        'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)',
        'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.84 Safari/535.11 SE 2.X MetaSr 1.0',
        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Maxthon/4.4.3.4000 Chrome/30.0.1599.101 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 UBrowser/4.0.3214.0 Safari/537.36',
    ];
    /**
     * GET请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function get($url, $params = '', $header = [], $timeout = 30, $options = [])
    {
        return self::send($url, $params, 'GET', $header, $timeout, $options);
    }

    /**
     * POST请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function post($url, $params = '', $header = [], $timeout = 30, $options = [])
    {
        return self::send($url, $params, 'POST', $header, $timeout, $options);
    }

    /**
     * DELETE请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function delete($url, $params = '', $header = [], $timeout = 30, $options = [])
    {
        return self::send($url, $params, 'DELETE', $header, $timeout, $options);
    }

    /**
     * PUT请求
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function put($url, $params = '', $header = [], $timeout = 30, $options = [])
    {
        return self::send($url, $params, 'PUT', $header, $timeout, $options);
    }

    /**
     * 下载远程文件
     * @param string $url 请求的地址
     * @param string $savePath 本地保存完整路径
     * @param mixed $params 传递的参数
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认3600秒
     * @return bool|string
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function down($url, $savePath, $params = '', $header = [], $timeout = 3600)
    {
        if (!is_dir(dirname($savePath))) {
            Dir::create(dirname($savePath));
        }
        
        $ch = curl_init();
        $fp = fopen($savePath, 'wb');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header ? : ['Expect:']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 64000);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $res        = curl_exec($ch);
        $curlInfo   = curl_getinfo($ch);

        if (curl_errno($ch) || $curlInfo['http_code'] != 200) {
            curl_error($ch);
            @unlink($savePath);
            return false;
        } else {
            curl_close($ch);
        }

        fclose($fp);

        return $savePath;
    }

    /**
     * CURL发送Request请求,支持GET、POST、PUT、DELETE
     * @param string $url 请求的地址
     * @param mixed $params 传递的参数
     * @param string $method 请求的方法
     * @param array $header 传递的头部参数
     * @param int $timeout 超时设置，默认30秒
     * @param mixed $options CURL的参数
     * @return array|string
     * @author 橘子俊 <364666827@qq.com>
     */
    private static function send($url, $params = '', $method = 'GET', $header = [], $timeout = 30, $options = [])
    {
        $cookieFile = Env::get('runtime_path') . 'temp/' . md5(config('authkey')) . '.txt';
        $userAgent = self::$userAgent[array_rand(self::$userAgent, 1)];
        $ch = curl_init();
        $opt                            = [];
        $opt[CURLOPT_COOKIEJAR]         = $cookieFile;
        $opt[CURLOPT_COOKIEFILE]        = $cookieFile;
        $opt[CURLOPT_USERAGENT]         = $userAgent;
        $opt[CURLOPT_CONNECTTIMEOUT]    = $timeout;
        $opt[CURLOPT_TIMEOUT]           = $timeout;
        $opt[CURLOPT_RETURNTRANSFER]    = true;
        $opt[CURLOPT_HTTPHEADER]        = $header ? : ['Expect:'];
        $opt[CURLOPT_FOLLOWLOCATION]    = true;

        if (substr($url, 0, 8) == 'https://') {
            $opt[CURLOPT_SSL_VERIFYPEER] = false;
            $opt[CURLOPT_SSL_VERIFYHOST] = 2;
        }

        if (is_array($params)) {
            $params = http_build_query($params);
        }

        switch (strtoupper($method)) {
            case 'GET':
                $extStr             = (strpos($url, '?') !== false) ? '&' : '?';
                $opt[CURLOPT_URL]   = $url . (($params) ? $extStr . $params : '');
                break;

            case 'POST':
                $opt[CURLOPT_POST]          = true;
                $opt[CURLOPT_POSTFIELDS]    = $params;
                $opt[CURLOPT_URL]           = $url;
                break;

            case 'PUT':
                $opt[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opt[CURLOPT_POSTFIELDS]    = $params;
                $opt[CURLOPT_URL]           = $url;
                break;

            case 'DELETE':
                $opt[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opt[CURLOPT_POSTFIELDS]    = $params;
                $opt[CURLOPT_URL]           = $url;
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
