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
namespace app\common\validate;

use think\Validate;

/**
 * 等级验证器
 * @package app\common\validate
 */
class AdminMemberLevel extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|等级名称' => 'require|unique:member_level',
        'status|状态设置'  => 'require|in:0,1',
    ];

    //定义验证提示
    protected $message = [
        'name.require' => '请填写等级名称',
        'name.unique' => '等级名称已存在',
        'status.require'    => '请设置等级状态',
    ];
}
