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
namespace app\common\validate;

use think\Validate;

/**
 * 用户验证器
 * @package app\admin\validate
 */
class AdminMember extends Validate
{
    //定义验证规则
    protected $rule = [
        'username|用户名' => 'checkUsername:thinkphp|unique:admin_member',
        'email|邮箱'     => 'checkEmail:thinkphp|email|unique:admin_member',
        'password|密码'  => 'requireWith|length:6,20',
        'mobile|手机号'   => 'checkMobile:thinkphp|unique:admin_member',
        'nick|昵称'   => 'unique:admin_member',
    ];

    //定义验证提示
    protected $message = [
        'password.require' => '密码不能为空',
        'password.length'  => '密码长度6-20位',
        'password.token'   => '请刷新后操作',
    ];

    //定义验证场景
    protected $scene = [
        //无token验证登录
        'login'  =>  [
            'username' => 'requireWith:username|checkUsername:thinkphp', 
            'email' => 'requireWith:email|email|checkEmail:thinkphp', 
            'mobile' => 'requireWith:mobile|checkMobile:thinkphp', 
            'password' => 'require|length:6,20',
        ],
        // token验证登陆
        'login_token'  =>  [
            'username' => 'requireWith:username|checkUsername:thinkphp', 
            'email' => 'requireWith:email|email|checkEmail:thinkphp', 
            'mobile' => 'requireWith:mobile|checkMobile:thinkphp', 
            'password' => 'require|length:6,20|token',
        ],
    ];
    
    /**
     * 检查邮箱
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    protected function checkEmail($value, $rule, $data)
    {
        if (empty($data['username']) && empty($data['email']) && empty($data['mobile'])) {
            return '用户名、手机、邮箱至少选填一项！';
        }
        return true;
    }

    /**
     * 检查用户名
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    protected function checkUsername($value, $rule, $data)
    {
        if (empty($data['username']) && empty($data['email']) && empty($data['mobile'])) {
            return '用户名、手机、邮箱至少选填一项！';
        }
        
        if (!is_username($value)) {
            return '用户名必须以中文或字母开头[支持中文,字母,数字,下划线]';
        }

        return true;
    }

    /**
     * 检查手机号
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    protected function checkMobile($value, $rule, $data)
    {
        if (empty($data['username']) && empty($data['email']) && empty($data['mobile'])) {
            return '用户名、手机、邮箱至少选填一项！';
        }
        
        if (!is_mobile($value)) {
            return '手机号格式错误！';
        }

        return true;
    }
}
