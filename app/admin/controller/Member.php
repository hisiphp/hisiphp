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

use app\common\model\AdminMember as MemberModel;
use app\common\model\AdminMemberLevel as LevelModel;
/**
 * 会员管理控制器
 * @package app\admin\controller
 */
class Member extends Admin
{

    /**
     * 会员列表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($q = '')
    {
        $map = [];
        if ($q) {
            if (preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $q)) {// 邮箱
                $map['email'] = $q;
            } elseif (preg_match("/^1\d{10}$/", $q)) {// 手机号
                $map['mobile'] = $q;
            } else {// 用户名、昵称
                $map['username'] = ['like', '%'.$q.'%'];
            }
        }
        
        $data_list = MemberModel::where($map)->paginate(10, false, ['query' => input('get.')]);
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 添加会员
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminMember');
            if($result !== true) {
                return $this->error($result);
            }

            if (!isset($data['password']) || empty($data['password'])) {
                return $this->error('请设置登录密码');
            }

            unset($data['id']);
            if (!MemberModel::create($data)) {
                return $this->error('添加失败！');
            }
            return $this->success('添加成功。');
        }

        $this->assign('level_option', LevelModel::getOption());
        return $this->fetch('form');
    }

    /**
     * 修改会员
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if ($data['mobile'] == 0) {
                unset($data['mobile']);
            }
            // 验证
            $result = $this->validate($data, 'AdminMember.update');
            if($result !== true) {
                return $this->error($result);
            }

            if (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }

            if (!MemberModel::update($data)) {
                return $this->error('修改失败！');
            }
            return $this->success('修改成功。');
        }

        $row = MemberModel::where('id', $id)->field('id,username,level_id,nick,email,mobile,expire_time')->find()->toArray();
        $this->assign('data_info', $row);
        $this->assign('level_option', LevelModel::getOption($row['level_id']));
        return $this->fetch('form');
    }
    
    /**
     * 会员列表弹窗
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function pop() {
        $q = input('param.q/s');
        $callback = input('param.callback/s');
        if (!$callback) {
            echo '<br><br>callback为必传参数！';
            exit;
        }

        $map = [];
        if ($q) {
            if (preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $q)) {// 邮箱
                $map['email'] = $q;
            } elseif (preg_match("/^1\d{10}$/", $q)) {// 手机号
                $map['mobile'] = $q;
            } else {// 用户名、昵称
                $map['username'] = ['like', '%'.$q.'%'];
            }
        }
        
        $data_list = MemberModel::where($map)->paginate(10, true);
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        $this->assign('callback', $callback);
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    // +----------------------------------------------------------------------
    // | 会员等级
    // +----------------------------------------------------------------------

    /**
     * 会员等级列表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function level()
    {
        $data_list = LevelModel::field('id,name,intro,discount,min_exper,max_exper,ctime,default,status')->paginate();
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 添加会员等级
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function addLevel()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminMemberLevel');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!LevelModel::create($data)) {
                return $this->error('添加失败！');
            }
            // 更新缓存
            cache('system_member_level', LevelModel::getAll());
            return $this->success('添加成功。');
        }

        return $this->fetch('levelform');
    }

    /**
     * 修改会员等级
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function editLevel($id = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminMemberLevel');
            if($result !== true) {
                return $this->error($result);
            }
            if (!LevelModel::update($data)) {
                return $this->error('修改失败！');
            }
            // 更新缓存
            cache('system_member_level', LevelModel::getAll());
            return $this->success('修改成功。');
        }
        $row = LevelModel::where('id', $id)->find()->toArray();

        $this->assign('data_info', $row);
        return $this->fetch('levelform');
    }

    /**
     * 删除会员等级
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delLevel()
    {
        $ids = input('param.ids/a');
        $model = new LevelModel;
        if (!$model->del($ids)) {
            return $this->error($model->getError());
        }
        return $this->success('删除成功');
    }

    /**
     * 设置默认等级
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setDefault($id = 0)
    {
        LevelModel::update(['default' => 0], ['id' => ['neq', $id]]);
        if (LevelModel::where('id', $id)->setField('default', 1) === false) {
            return $this->error('设置失败！');
        }

        return $this->success('设置成功。');
    }
}
