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

namespace app\system\validate;

use think\Validate;

/**
 * 插件验证器
 * @package app\system\validate
 */
class Plugins extends Validate
{
    //定义验证规则
    protected $rule = [
		'name|插件名'			=> 'require|alphaDash|unique:system_plugins',
		'title|插件标题'			=> 'require',
		'identifier|插件标识'		=> 'require|unique:system_plugins',
    ];
}
