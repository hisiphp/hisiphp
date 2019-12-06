<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------

namespace app\common\controller;
use View;
use think\Controller;
use think\facade\Env;

/**
 * 控制器基类
 * @package app\common\controller
 */
class Base extends Controller
{
    protected function initialize() {

    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string    $template 模板文件名或者内容
     * @param array     $vars     模板输出变量
     * @param array     $replace 替换内容
     * @param array     $config     模板参数
     * @param bool      $renderContent     是否渲染内容
     * @return string
     * @throws Exception
     * @author 橘子俊 <364666827@qq.com>
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        if (defined('IS_PLUGINS')) {
            return self::pluginsFetch($template , $vars , $replace , $config , $renderContent);
        }
        return parent::fetch($template , $vars , $replace , $config , $renderContent);
    }
    
    /**
     * 渲染插件模板
     * @param string    $template 模板文件名或者内容
     * @param array     $vars     模板输出变量
     * @param array     $replace 替换内容
     * @param array     $config     模板参数
     * @param bool      $renderContent     是否渲染内容
     * @return string
     * @throws Exception
     * @author 橘子俊 <364666827@qq.com>
     */
    final protected function pluginsFetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        if (stripos($template, config('template.view_suffix')) === false) {
            $plugin     = $_GET['_p'];
            $controller = $_GET['_c'];
            $action     = $_GET['_a'];
            if (!$template) {
                $template = $controller.'/'.parse_name($action);
            } elseif (strpos($template, '/') == false) {
                $template = $controller.'/'.$template;
            }
            
            if(defined('ENTRANCE') && ENTRANCE == 'admin') {
                $template = 'admin/'.$template;
            } else {
                $template = 'home/'.$template;
            }
            
            $template = Env::get('root_path').strtolower("plugins/{$plugin}/view/{$template}.".config('template.view_suffix'));
        }
        
        return parent::fetch($template, $vars, $replace, $config, $renderContent);
    }
    
    /**
     * 自动获取模型层、逻辑层、验证器层、服务层、数据库实例（不支持跨模块）
     */
    public function __get($name)
    {
        $class = $name;
        if (substr($name, 0, 7) == 'plugins') {
            $name = ltrim($name, 'plugins');
            $layer = get_layer(strtolower($name));
            $use = 'plugins\\'.$this->request->param('_p').'\\'.$layer.'\\'.ltrim($name, ucfirst($layer));
        } else {
            $layer  = get_layer($name);
            $name = ltrim($name, $layer); 
            $use = 'app\\'.$this->request->module().'\\'.$layer.'\\'.$name;
            if ($layer == 'db') {
               return $this->$class = Db::name($name);
            }
        }
        
        return $this->$class = (new $use);
    }
}
