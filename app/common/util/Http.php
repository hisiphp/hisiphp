<?php
namespace app\common\util;

class Http {
    /**
     * 用CURL模拟获取网页页面内容
     *
     * @param string $url     所要获取内容的网址
     * @param array  $data        所要提交的数据
     * @param string $proxy   代理设置
     * @param integer $expire 时间限制
     * @return string
     */
    public static function getRequest($url, $data = array(), $charset= 'UTF-8', $proxy = null, $expire = 30) {

        //参数分析
        if (!$url) {
            return false;
        }
        if (!is_array($data)) {
            $data = (array)$data;
        }

        //分析是否开启SSL加密
        $ssl = substr($url, 0, 8) == 'https://' ? true : false;

        //读取网址内容
        $ch = curl_init();

        //设置代理
        if (!is_null($proxy)) {
            curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        }

        //分析网址中的参数
        $paramUrl = http_build_query($data, '', '&');
        $extStr   = (strpos($url, '?') !== false) ? '&' : '?';
        $url      = $url . (($paramUrl) ? $extStr . $paramUrl : '');

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($ssl) {
            // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        
        $this_header = ["content-type: application/x-www-form-urlencoded;charset=".$charset];
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);

        //设置浏览器
        curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * 用CURL模拟提交数据
     *
     * @param string $url        post所要提交的网址
     * @param array  $data        所要提交的数据
     * @param string $proxy        代理设置
     * @param integer $expire    所用的时间限制
     * @return string
     */
    public static function postRequest($url, $data = null, $charset= 'UTF-8', $type = 'array',$proxy = null, $expire = 30) {

        //参数分析
        if (!$url) {
            return false;
        }

        //cookie file
        $cookieFile = CACHE_PATH . 'temp/' . md5(config('authkey')) . '.txt';

        //分析是否开启SSL加密
        $ssl         = substr($url, 0, 8) == 'https://' ? true : false;

        //读取网址内容
        $ch = curl_init();

        //设置代理
        if (!is_null($proxy)) {
            curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $this_header = ["content-type: application/x-www-form-urlencoded;charset=".$charset];
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
        //cookie设置
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);

        //设置浏览器
        curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POST, true);
        //Post提交的数据包
        if($type == 'array'){
            curl_setopt($ch,  CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($ch,  CURLOPT_POSTFIELDS, $data);
        }

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}