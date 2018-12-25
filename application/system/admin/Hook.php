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

namespace app\system\admin;

use app\system\model\SystemHook as HookModel;
use app\system\model\SystemHookPlugins as HookPluginsModel;

/**
 * 钩子控制器
 * @package app\system\admin
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

            $where      = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $keyword    = $this->request->param('keyword');

            if ($keyword) {
                $where[] = ['name', 'like', "%{$keyword}%"];
            }

            $data['data']   = HookModel::where($where)->page($page)->limit($limit)->select();
            $data['count']  = HookModel::where($where)->count('id');
            $data['code']   = 0;
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

            return $this->success('保存成功');
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

            return $this->success('保存成功');

        }

        $row = HookModel::where('id', $id)->field('id,name,intro,system')->find()->toArray();

        if ($row['system'] == 1) {
            return $this->error('禁止编辑系统钩子');
        }

        // 关联的插件
        $hookPlugins = HookPluginsModel::where('hook', $row['name'])->order('sort')->column('id,plugins,status,sort');

        $this->assign('formData', $row);
        $this->assign('hook_plugins', $hookPlugins);
        return $this->fetch('form');
    }

    /**
     * 删除钩子
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {
        $id         = $this->request->param('id/a');
        $map        = [];
        $map['id']  = $id;
        $rows       = HookModel::where($map)->field('id,system')->select();

        foreach ($rows as $v) {
            // 排除系统钩子
            if ($v['system'] == 1) {
                return $this->error('禁止删除系统钩子');
            }
        }

        $map        = [];
        $map['id']  = $id;
        $res = HookModel::where($map)->delete();

        if ($res === false) {
            return $this->error('操作失败');
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
        $id         = $this->request->param('id/a');
        $val        = $this->request->param('val/d');
        $map        = [];
        $map['id']  = $id;
        $rows       = HookModel::where($map)->field('id,system')->select();

        foreach ($rows as $v) {

            // 排除系统钩子
            if ($v['system'] == 1) {
                return $this->error('禁止操作系统钩子');
            }

        }

        $res = HookModel::where($map)->setField('status', $val);;
        if ($res === false) {
            return $this->error('操作失败');
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
        $id         = $this->request->param('id/a');
        $val        = $this->request->param('val/d');
        $map        = [];
        $map['id']  = $id;
        $res = HookPluginsModel::where($map)->setField('status', $val);
        
        if ($res === false) {
            return $this->error('操作失败');
        }

        return $this->success('操作成功');
    }
}
