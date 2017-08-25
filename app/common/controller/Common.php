<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\View;
use think\Controller;

/**
 * 项目公共控制器
 * @package app\common\controller
 */
class Common extends Controller
{
    protected function _initialize()
    {
    }
    /**
     * 渲染插件模板
     * @param string $template 模板名称
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    final protected function pfetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $plugin = $_GET['_p'];
        $controller = $_GET['_c'];
        $action = $_GET['_a'];
        $template = $template ? $template : $controller.'/'.$action;
        if(defined('ENTRANCE') && ENTRANCE == 'admin') {
            $template = 'admin/'.$template;
        }
        $template_path = strtolower("plugins/{$plugin}/view/{$template}.".config('template.view_suffix'));
        return parent::fetch($template_path, $vars, $replace, $config);
    }
}