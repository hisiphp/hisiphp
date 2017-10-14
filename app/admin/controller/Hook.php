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
    public function index($q = '')
    {
        $map = [];
        if ($q) {
            $map['name'] = ['like', '%'.$q.'%'];
        }
        
        $data_list = HookModel::where($map)->paginate();
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
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
        if ($this->request->isPost()) {
            $mod = new HookModel();
            if (!$mod->storage()) {
                return $this->error($mod->getError());
            }
            return $this->success('保存成功。');
        }
        $row = HookModel::where('id', $id)->field('id,name,intro,system')->find()->toArray();
        if ($row['system'] == 1) {
            return $this->error('禁止编辑系统钩子！');
        }
        // 关联的插件
        $hook_plugins = HookPluginsModel::where('hook', $row['name'])->order('sort')->column('id,plugins,sort');
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
        $ids   = input('param.ids/a');
        $map = [];
        $map['id'] = ['in', $ids];
        $rows = HookModel::where($map)->field('id,system')->select();
        $ids = [];
        foreach ($rows as $v) {
            // 排除系统钩子
            if ($v['system'] == 1) {
                return $this->error('禁止删除系统钩子！');
            }
        }

        $map = [];
        $map['id'] = ['in', $ids];
        $res = HookModel::where($map)->delete();
        if ($res === false) {
            return $this->error('操作失败！');
        }
        return $this->success('操作成功！');
    }

    /**
     * 状态设置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function status()
    {
        $id     = input('param.ids/d');
        $val    = input('param.val/d');
        $map = [];
        $map['id'] = $id;
        $system = HookModel::where('id', $id)->value('system');
        // 排除系统钩子
        if ($system == 1) {
            return $this->error('禁止操作系统钩子！');
        }
        $res = HookModel::where('id', $id)->setField('status', $val);;
        if ($res === false) {
            return $this->error('操作失败！');
        }
        return $this->success('操作成功！');
    }
}
