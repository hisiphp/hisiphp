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
use app\admin\model\AdminLog as LogModel;
/**
 * 日志管理控制器
 * @package app\admin\controller
 */
class Log extends Admin
{
    /**
     * 日志首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        $uid = input('param.uid/d');
        $map = [];
        if ($uid) {
            $map['uid'] = $uid;
        }
        $data_list = LogModel::where($map)->paginate();
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        return $this->fetch();
    }
    /**
     * 清空日志
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function clear()
    {
        if (!LogModel::where('id > 0')->delete()) {
            return $this->error('日志清空失败');
        }
        return $this->success('日志清空成功');
    }
}
