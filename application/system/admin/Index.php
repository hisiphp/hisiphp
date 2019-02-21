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

namespace app\system\admin;

use Env;
use hisi\Dir;

/**
 * 后台默认首页控制器
 * @package app\system\admin
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
        $path   = Env::get('runtime_path');
        $cache  = $this->request->param('cache/d', 0);
        $log    = $this->request->param('log/d', 0);
        $temp   = $this->request->param('temp/d', 0);

        if ($cache == 1) {
            Dir::delDir($path.'cache');
        }

        if ($temp == 1) {
            Dir::delDir($path.'temp');
        }

        if ($log == 1) {
            Dir::delDir($path.'log');
        }

        return $this->success('任务执行成功');
    }
}
