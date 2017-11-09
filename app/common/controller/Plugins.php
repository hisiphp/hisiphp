<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\View;
use think\Exception;
/**
 * 插件类
 * @package app\common\controller
 */
abstract class Plugins
{
    /**
     * @var null 视图实例对象
     */
    protected $view = null;

    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * @var string 插件路径
     */
    public $plugins_path = '';

    /**
     * @var string 插件信息
     */
    public $plugins_info = '';

    /**
     * @var string 插件名
     */
    public $plugns_name = '';

    /**
     * 插件构造方法
     */
    public function __construct()
    {
        // 获取插件名
        $class = get_class($this);
        $this->plugns_name = substr($class, strrpos($class, '\\') + 1);

        $this->view = new View();
        $this->plugins_path = ROOT_PATH.'plugins/'.$this->plugns_name.'/';

        if (is_file($this->plugins_path.'info.php')) {
            $this->plugins_info = include $this->plugins_path.'info.php';
        }
    }

    /**
     * 模板变量赋值
     * @param string $name 模板变量
     * @param string $value 变量的值
     * @author 橘子俊 <364666827@qq.com>
     * @return $this
     */
    final protected function assign($name = '', $value='')
    {
        $this->view->assign($name, $value);
        return $this;
    }

    /**
     * 显示方法,仅限钩子方法使用
     * @param string $template 模板名
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if ($template != '') {
            if (!is_file($template)) {
                $template = $this->plugins_path. 'view/widget/'. $template . '.' . config('template.view_suffix');
                if (!is_file($template)) {
                    throw new Exception('模板不存在：'.$template);
                }
            }
            echo $this->view->fetch($template, $vars, $replace, $config);
        }
    }

    /**
     * 获取错误信息
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 必须实现安装方法
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    abstract public function install();

    /**
     * 必须实现卸载方法
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    abstract public function uninstall();
}
