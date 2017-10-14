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
/**
 * 插件类
 * @package app\common\controller
 */
abstract class Plugins
{
    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * 获取错误信息
     * @return string
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 必须实现安装方法
     * @return mixed
     */
    abstract public function install();

    /**
     * 必须实现卸载方法
     * @return mixed
     */
    abstract public function uninstall();
}
