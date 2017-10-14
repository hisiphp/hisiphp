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
namespace app\admin\controller;

/**
 * 后台开发工具控制器
 * 仅供开发人员使用
 * @package app\admin\controller
 */
class Develop extends Admin
{
    public $tab_data = [];
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();

        $tab_data['menu'] = [
            [
                'title' => '模板预览'
            ],
            [
                'title' => '查看代码'
            ],
        ];

        $this->tab_data = $tab_data;
    }

    /**
     * 列表演示
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function lists()
    {
        $this->assign('tab_data', $this->tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }

    /**
     * 编辑演示
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit()
    {
        $this->assign('tab_data', $this->tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }
}
