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

use hisi\Http;
use Env;

class Cloud {

    // 错误信息
    private $error = '应用市场出现未知错误';

    // 请求的数据
    private $data = [];

    // 接口
    private $api = '';

    // 站点标识
    private $identifier = '';

    // 升级锁
    public $lock = '';

    // 升级目录路径
    public $path = './';

    // 请求类型
    public $type = 'post';

    //服务器地址
    const API_URL = 'https://cloud.hisiphp.com/thinkphp51/';
    
    /**
     * 架构函数
     * @param string $path  目录路径
     * @author 橘子俊 <364666827@qq.com>
     */
    public function __construct($identifier = '', $path = './') {
        $this->identifier = $identifier;
        $this->path = $path;
        $this->lock = './upload/cloud.lock';
    }

    /**
     * 获取服务器地址
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public function apiUrl()
    {
        return self::API_URL;
    }

    /**
     * 获取错误信息
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 需要发送的数据
     * @param  array $data 数据
     * @author 橘子俊 <364666827@qq.com>
     * @return obj
     */
    public function data($data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * api 请求接口
     * @param  string $api 接口
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    public function api($api = '')
    {
        $this->api = self::API_URL.$api;
        return $this->run($this->data);
    }

    /**
     * type 请求类型
     * @param  string $type 请求类型(get,post)
     * @author 橘子俊 <364666827@qq.com>
     * @return obj
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 文件下载
     * @param  string $api 接口
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    public function down($api)
    {
        $this->api  = self::API_URL.$api;
        $saveFile   = $this->path.time().'.zip';
        $request    = $this->run(true);

        // 执行下载
        $result = Http::down($request['url'], $saveFile, $request['params']);

        if (is_file($this->lock)) {
            @unlink($this->lock);
        }

        return $result;
    }

    /**
     * 执行接口
     * @return array
     * @author 橘子俊 <364666827@qq.com>
     */
    private function run($down = false)
    {
        $params['format']       = 'json';
        $params['timestamp']    = time();
        $params['domain']       = get_domain().ROOT_DIR;
        $params['ip']           = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : get_client_ip();
        $params['identifier']   = $this->identifier;
        $params['hisi_version'] = config('hisiphp.version');
        $params                 = array_merge($params, $this->data);
        $params                 = array_filter($params);

        if (is_file($this->lock)) {
            @unlink($this->lock);
        }

        @file_put_contents($this->lock, $params['timestamp']);

        if ($down === true) {
            $result             = [];
            $result['url']      = $this->api;
            $result['params']   = http_build_query($params);
            return $result;
        }

        $type   = $this->type;
        $result = Http::$type($this->api, $params);

        return self::_response($result);
    }

    /**
     * 以数组格式返回
     * @return array
     * @author 橘子俊 <364666827@qq.com>
     */
    private function _response($result = [])
    {

        if (is_file($this->lock)) {
            @unlink($this->lock);
        }
        
        if (!$result || isset($result['errno'])) {
            if (isset($result['msg'])) {
                return ['code' => 0, 'msg' => $result['msg']];
            }
            return ['code' => 0, 'msg' => '请求的接口网络异常，请稍后在试'];
        } else {
            return json_decode($result, 1);
        }
    }
}
