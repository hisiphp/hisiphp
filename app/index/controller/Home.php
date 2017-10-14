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
/**
 * 系统默认控制器
// +----------------------------------------------------------------------
// | 警告：请勿在index模块下开发任何东西，系统升级会自动覆盖此模块
// +----------------------------------------------------------------------
 * @package app\index\controller
 */
namespace app\index\controller;
use app\common\controller\Common;
class Home extends Common
{
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 将返回结果以json格式输出
     * @author 橘子俊 <364666827@qq.com>
     */
    public function apiReturn($msg = '', $code = 0, $data = [])
    {
        $arr = [];
        $arr['code'] = $code;
        $arr['msg'] = $msg;
        $arr['data'] = $data;
        if ($code == 1) {
            $status_code = 200;
        } else {
            $status_code = 202;
        }
        return json($arr, $status_code);
        exit;
    }
}
