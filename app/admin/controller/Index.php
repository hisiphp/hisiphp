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
namespace app\admin\controller;

use app\common\util\Dir;
/**
 * 后台默认首页控制器
 * @package app\admin\controller
 */

class Index extends Admin
{
    /**
     * 首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        if (cookie('hisi_iframe')) {
            $this->view->engine->layout(false);
            return $this->fetch('iframe');
        } else {
            return $this->fetch();
        }
    }

    /**
     * 欢迎首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function welcome()
    {
        return $this->fetch('index');
    }

    /**
     * 清理缓存
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function clear()
    {
        if (Dir::delDir(RUNTIME_PATH) === false) {
            return $this->error('缓存清理失败！');
        }
        return $this->success('缓存清理成功！');
    }
}
