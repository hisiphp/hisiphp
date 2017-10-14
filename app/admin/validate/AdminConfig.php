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
namespace app\admin\validate;

use think\Validate;

/**
 * 配置验证器
 * @package app\admin\validate
 */
class AdminConfig extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|配置标识' => 'require|unique:admin_config',
        'title|配置标题' => 'require',
        'type|配置类型'    => 'require',
    ];

    //定义验证提示
    // protected $message = [
    //     'name.require' => '请选择所属模块',
    //     'title.require'    => '请选择所属菜单',
    //     'type.require'    => '菜单链接已存在',
    // ];
}
