<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
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
        return $this->fetch();
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
