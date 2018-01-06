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
namespace app\index\controller;

use app\admin\model\AdminPlugins as PluginsModel;

class Plugins extends Home
{
    public function _empty()
    {
        /**
         * 支持以下两种URL模式
         * URL模式1 [/plugins/插件名/控制器/[方法]?参数1=参数值&参数2=参数值]
         * URL模式2 [/plugins.php?_p=插件名&_c=控制器&_a=方法&参数1=参数值&参数2=参数值] 推荐
         */
        $path = $this->request->path();
        $path = explode('/', $path);
        if (isset($path[1]) && !empty($path[1])) {
            $plugin = $_GET['_p'] = $path[1];
        } else {
            return $this->error('参数传递错误！');
        }
        if (isset($path[2]) && !empty($path[2])) {
            $controller = $_GET['_c'] = $path[2];
        } else {
            $controller = $_GET['_c'] = input('param._c', 'Index');
        }
        $controller = ucfirst($controller);
        
        if (isset($path[3]) && !empty($path[3])) {
            $action = $_GET['_a'] = $path[3];
        } else {
            $action = $_GET['_a'] = input('param._a', 'index');
        }
        
        $params = $this->request->except(['_p', '_c', '_a'], 'param');

        if (empty($plugin)) {
            return $this->error('插件参数传递错误！');
        }            
        if (!PluginsModel::where(['name' => $plugin, 'status' => 2])->find() ) {
            return $this->error("插件可能不存在或者未安装！");
        }
        if (!plugins_action_exist($plugin.'/'.$controller.'/'.$action, 'home')) {
            return $this->error("插件方法不存在[".$plugin.'/'.$controller.'/'.$action."]！");
        }
        return plugins_action($plugin.'/'.$controller.'/'.$action, $params, 'home');
    }
}
