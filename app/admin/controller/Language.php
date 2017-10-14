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
use app\common\model\AdminLanguage as LanguageModel;
/**
 * 语言包管理控制器
 * @package app\admin\controller
 */

class Language extends Admin
{
    /**
     * 语言包管理首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        $data_list = LanguageModel::order('sort asc')->column('id,code,name,icon,sort,status');
        $this->assign('data_list', $data_list);
        return $this->fetch();
    }

    /**
     * 添加语言包
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $model = new LanguageModel();
            if (!$model->storage()) {
                return $this->error($model->getError());
            }
            return $this->success('保存成功。');
        }

        return $this->fetch('form');
    }

    /**
     * 修改语言包
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit()
    {
        $id = get_num();
        if ($this->request->isPost()) {
            $model = new LanguageModel();
            if (!$model->storage()) {
                return $this->error($model->getError());
            }
            return $this->success('保存成功。');
        }
        $data_info = LanguageModel::get($id);
        $this->assign('data_info', $data_info);
        return $this->fetch('form');
    }

    /**
     * 删除语言包
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {
        $id = get_num();
        $model = new LanguageModel(); 
        if ($model->del($id) === false) {
            return $this->error('删除失败！');
        }
        return $this->success('删除成功');
    }
}
