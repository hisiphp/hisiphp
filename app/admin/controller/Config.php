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
use app\admin\model\AdminConfig as ConfigModel;
/**
 * 配置管理控制器
 * @package app\admin\controller
 */

class Config extends Admin
{
    public function index($group = 'base')
    {
        $tab_data = [];
        foreach (config('sys.config_group') as $key => $value) {
            $arr = [];
            $arr['title'] = $value;
            $arr['url'] = '?group='.$key;
            $tab_data['menu'][] = $arr;
        }
        $tab_data['current'] = url('?group='.$group);

        $map = [];
        $map['group'] = $group;
        $data_list = ConfigModel::where($map)->order('sort,id')->paginate();
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    /**
     * 添加配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            switch ($data['type']) {
                case 'switch':
                case 'radio':
                case 'checkbox':
                case 'select':
                    if (!$data['options']) {
                        return $this->error('请填写配置选项！');
                    }
                    break;
                default:
                    break;
            }
            // 验证
            $result = $this->validate($data, 'AdminConfig');
            if($result !== true) {
                return $this->error($result);
            }
            if (!ConfigModel::create($data)) {
                return $this->error('添加失败！');
            }
            // 更新配置缓存
            ConfigModel::getConfig('', true);
            return $this->success('添加成功。');
        }
        return $this->fetch('form');
    }

    /**
     * 修改配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit($id = 0)
    {
        $row = ConfigModel::where('id', $id)->field('id,group,title,name,value,type,options,tips,status,system')->find();
        if ($row['system'] == 1) {
            return $this->error('禁止编辑此配置！');
        }
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminConfig');
            if($result !== true) {
                return $this->error($result);
            }
            if (!ConfigModel::update($data)) {
                return $this->error('保存失败！');
            }
            // 更新配置缓存
            ConfigModel::getConfig('', true);
            return $this->success('保存成功。');
        }
        $row['tips'] = htmlspecialchars_decode($row['tips']);
        $row['value'] = htmlspecialchars_decode($row['value']);
        $this->assign('data_info', $row);
        return $this->fetch('form');
    }

    /**
     * 删除配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {
        $id = input('param.ids/a');
        $model = new ConfigModel();
        if ($model->del($id)) {
            return $this->success('删除成功。');
        }
        // 更新配置缓存
        ConfigModel::getConfig('', true);
        return $this->error($model->getError());
    }
}
