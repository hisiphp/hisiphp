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

use app\system\model\SystemLog as LogModel;

/**
 * 日志管理控制器
 * @package app\system\admin
 */
class Log extends Admin
{
    protected $hisiTable = 'SystemLog';

    /**
     * 日志首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {

            $where  = $data = [];
            $page   = $this->request->param('page/d', 1);
            $limit  = $this->request->param('limit/d', 15);
            $uid    = $this->request->param('uid/d');
            
            if ($uid) {
                $where['uid'] = $uid;
            }

            $data['data']   = LogModel::with('user')->where($where)->page($page)->order('mtime desc')->limit($limit)->select();
            $data['count']  = LogModel::where($where)->count('id');
            $data['code']   = 0;

            return json($data);

        }

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
