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

use app\admin\model\AdminUser as UserModel;
use app\admin\model\AdminRole as RoleModel;
use app\admin\model\AdminMenu as MenuModel;
use think\Validate;

/**
 * 后台用户、角色控制器
 * @package app\admin\controller
 */
class User extends Admin
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
                'title' => '管理员角色',
                'url' => 'admin/user/role',
            ],
            [
                'title' => '系统管理员',
                'url' => 'admin/user/index',
            ],
        ];
        $this->tab_data = $tab_data;
    }

    /**
     * 用户管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($q = '')
    {
        $sqlmap = [];
        if ($q) {
            $sqlmap['username'] = ['like', '%'.$q.'%'];
        }
        $data_list = UserModel::where($sqlmap)->paginate();
        // 分页
        $pages = $data_list->render();
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('');
        $this->assign('role_list', RoleModel::getAll());
        $this->assign('data_list', $data_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 布局切换
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function iframe()
    {
        $val = input('param.val', 0);
        if ($val != 0 && $val != 1) {
            return $this->error('参数传递错误');
        }
        if (UserModel::where('id', ADMIN_ID)->setField('iframe', $val) === false) {
            return $this->error('切换失败');
        }
        if ($val == 1) {
            cookie('hisi_iframe', 'yes');
        } else {
            cookie('hisi_iframe', null);
        }
        return $this->success('布局切换成功，跳转中...', url('index/index'));
    }

    /**
     * 添加用户
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function addUser()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminUser');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            $data['last_login_ip'] = '';
            $data['auth'] = '';
            if (!UserModel::create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
        }

        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '添加用户'],
        ];
        $this->assign('menu_list', '');
        $this->assign('role_option', RoleModel::getOption());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch('userform');
    }

    /**
     * 修改用户
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function editUser($id = 0)
    {
        if ($id == 1 && ADMIN_ID != $id) {
            return $this->error('禁止修改超级管理员');
        }
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!isset($data['auth'])) {
                $data['auth'] = '';
            }
            
            $row = UserModel::where('id', $id)->field('role_id,auth')->find();
            if ($data['id'] == 1 || ADMIN_ID == $id) {// 禁止更改超管角色，当前登陆用户不可更改自己的角色和自定义权限
                unset($data['role_id'], $data['auth']);
                if (!$row['auth']) {
                    $data['auth'] = '';
                }
            } else if ($row['role_id'] != $data['role_id']) {// 如果分组不同，自定义权限无效
                $data['auth'] = '';
            }

            if (isset($data['role_id']) && RoleModel::where('id', $data['role_id'])->value('auth') == json_encode($data['auth'])) {// 如果自定义权限与角色权限一致，则设置自定义权限为空
                $data['auth'] = '';
            }

            // 验证
            $result = $this->validate($data, 'AdminUser.update');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password'] == '') {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::where('id', $id)->field('id,username,role_id,nick,email,mobile,auth,status')->find()->toArray();
        if (!$row['auth']) {
            $auth = RoleModel::where('id', $row['role_id'])->value('auth');
            $row['auth'] = json_decode($auth);
        } else {
            $row['auth'] = json_decode($row['auth']);
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '修改用户'],
            ['title' => '设置权限'],
        ];

        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('role_option', RoleModel::getOption());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        $this->assign('role_option', RoleModel::getOption($row['role_id']));
        $this->assign('data_info', $row);
        return $this->fetch('userform');
    }

    /**
     * 修改个人信息
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function info()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['id'] = ADMIN_ID;
            // 防止伪造
            unset($data['role_id'], $data['status']);

            if ($data['password'] == '') {
                unset($data['password']);
            }
            // 验证
            $result = $this->validate($data, 'AdminUser.info');
            if($result !== true) {
                return $this->error($result);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::where('id', ADMIN_ID)->field('username,nick,email,mobile')->find()->toArray();
        $this->assign('data_info', $row);
        return $this->fetch();
    }

    /**
     * 删除用户
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delUser()
    {
        $ids   = input('param.ids/a');
        $model = new UserModel();
        if ($model->del($ids)) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError());
    }

    // +----------------------------------------------------------------------
    // | 角色
    // +----------------------------------------------------------------------

    /**
     * 角色管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function role()
    {
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('');
        $data_list = RoleModel::field('id,name,intro,ctime,status')->paginate();
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 添加角色
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function addRole()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminRole');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!RoleModel::create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '添加角色'],
            ['title' => '设置权限'],
        ];
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch('roleform');
    }

    /**
     * 修改角色
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function editRole($id = 0)
    {
        if ($id <= 1) {
            return $this->error('禁止编辑');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 当前登陆用户不可更改自己的分组角色
            if (ADMIN_ROLE == $data['id']) {
                return $this->error('禁止修改当前角色(原因：您不是超级管理员)');
            }

            // 验证
            $result = $this->validate($data, 'AdminRole');
            if($result !== true) {
                return $this->error($result);
            }
            if (!RoleModel::update($data)) {
                return $this->error('修改失败');
            }

            // 更新权限缓存
            cache('role_auth_'.$data['id'], $data['auth']);

            return $this->success('修改成功');
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '修改角色'],
            ['title' => '设置权限'],
        ];
        $row = RoleModel::where('id', $id)->field('id,name,intro,auth,status')->find()->toArray();
        $row['auth'] = json_decode($row['auth']);
        $this->assign('data_info', $row);
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch('roleform');
    }
    /**
     * 删除角色
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delRole()
    {
        $ids   = input('param.ids/a');
        $model = new RoleModel();
        if ($model->del($ids)) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError());
    }
}
