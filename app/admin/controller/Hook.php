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

use app\admin\model\AdminHook as HookModel;
use app\admin\model\AdminHookPlugins as HookPluginsModel;
use think\Loader;
/**
 * 钩子控制器
 * @package app\admin\controller
 */
class Hook extends Admin
{

    /**
     * 首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $where = $data = [];
            $page = input('param.page/d', 1);
            $limit = input('param.limit/d', 15);
            $keyword = input('param.keyword');
            if ($keyword) {
                $where['name'] = ['like', "%{$keyword}%"];
            }
            $data['data'] = HookModel::where($where)->page($page)->limit($limit)->select();
            $data['count'] = HookModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * 添加钩子
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $mod = new HookModel();
            if (!$mod->storage()) {
                return $this->error($mod->getError());
            }
            return $this->success('保存成功。');
        }
        $this->assign('hook_plugins', '');
        return $this->fetch('form');
    }

    /**
     * 修改钩子
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit($id = 0)
    {
        $row = HookModel::where('id', $id)->field('id,name,intro,system')->find()->toArray();
        if ($this->request->isPost()) {
            if ($row['system'] == 1) {
                return $this->error('禁止编辑系统钩子！');
            }
            $mod = new HookModel();
            if (!$mod->storage()) {
                return $this->error($mod->getError());
            }
            return $this->success('保存成功。');
        }
        // 关联的插件
        $hook_plugins = HookPluginsModel::where('hook', $row['name'])->order('sort')->column('id,plugins,sort,status');
        $this->assign('data_info', $row);
        $this->assign('hook_plugins', $hook_plugins);
        return $this->fetch('form');
    }

    /**
     * 删除钩子
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {
        $id   = input('param.id/a');
        $where = [];
        $where['id'] = ['in', $id];
        $rows = HookModel::where($where)->column('id,system');
        foreach ($rows as $k => $v) {
            if ($v != 1) {
                HookModel::where('id', $k)->delete();
            }
        }
        return $this->success('操作成功');
    }

    /**
     * 状态设置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function status()
    {
        $id = input('param.id/a');
        $val = input('param.val/d');
        $where = [];
        $where['id'] = ['in', $id];
        $rows = HookModel::where($where)->column('id,system');
        foreach ($rows as $k => $v) {
            if ($v != 1) {
                HookModel::where('id', $k)->setField('status', $val);
            }
        }
        return $this->success('操作成功');
    }

    /**
     * 钩子插件状态
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function hookPluginsStatus()
    {
        $id = input('param.id');
        $val = input('param.val/d');
        if (HookPluginsModel::where('id', $id)->setField('status', $val) === false) {
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }
}
