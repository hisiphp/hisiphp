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

namespace app\system\home;

use app\common\controller\Common;
use app\system\model\SystemPlugins as PluginsModel;

class Plugins extends Common
{
    public function _empty()
    {
        /**
         * 支持以下两种URL模式
         * URL模式1 [/plugins/插件名/控制器/[方法]?参数1=参数值&参数2=参数值]
         * URL模式2 [/plugins.php?_p=插件名&_c=控制器&_a=方法&参数1=参数值&参数2=参数值] 推荐
         */
        $params = $this->request->param('');

        if (!isset($params['_p'])) {
            return $this->error('缺少参数[_p]');
        }

        $plugin = $params['_p'];
        if (isset($params['_c']) && !empty($params['_c'])) {
            $controller = ucfirst($params['_c']);
        } else {
            $controller = 'Index';
        }
        
        if (isset($params['_a']) && !empty($params['_a'])) {
            $action = $params['_a'];
        } else {
            $action = 'Index';
        }

        $_GET['_p'] = $plugin;
        $_GET['_c'] = $controller;
        $_GET['_a'] = $action;
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
        
        return plugins_run($plugin.'/'.$controller.'/'.$action, $params, 'home');
    }
}
