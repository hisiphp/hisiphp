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

use app\system\model\SystemUser as UserModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;
use hisi\Tree;

/**
 * 后台用户、角色控制器
 * @package app\system\admin
 */
class User extends Admin
{
    public $tabData = [];
    protected $hisiTable = 'SystemUser';
    protected $hisiModel = 'SystemUser';
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $tabData['menu'] = [
            [
                'title' => '管理角色',
                'url' => 'system/user/role',
            ],
            [
                'title' => '管理员',
                'url' => 'system/user/index',
            ],
        ];
        $this->tabData = $tabData;
    }

    /**
     * 用户管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($q = '')
    {
        if ($this->request->isAjax()) {
            $where      = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $keyword    = $this->request->param('keyword/s');
            $where[]    = ['id', 'neq', 1];
            if ($keyword) {
                $where[] = ['username', 'like', "%{$keyword}%"];
            }

            $data['data'] = UserModel::with('hasRoles')->where($where)->page($page)->limit($limit)->select();
            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }

        $assign = [];
        $assign['hisiTabData'] = $this->tabData;
        $assign['hisiTabType'] = 3;

        return $this->assign($assign)->fetch();
    }

    /**
     * 布局切换
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function iframe()
    {
        $val = UserModel::where('id', ADMIN_ID)->value('iframe');
        if ($val == 1) {
            $val = 0;
        } else {
            $val = 1;
        }
        if (!UserModel::where('id', ADMIN_ID)->setField('iframe', $val)) {
            return $this->error('切换失败');
        }
        cookie('hisi_iframe', $val);
        return $this->success('请稍等，页面切换中...', url('system/index/index'));
    }

    /**
     * 主题设置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setTheme()
    {
        $theme = $this->request->param('theme/d', 0);
        if (UserModel::setTheme($theme, true) === false) {
            return $this->error('设置失败');
        }
        return $this->success('设置成功');
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
            $data['password'] = md5($data['password']);
            $data['password_confirm'] = md5($data['password_confirm']);

            // 验证
            $result = $this->validate($data, 'SystemUser');
            if($result !== true) {
                return $this->error($result);
            }
            
            unset($data['id'], $data['password_confirm']);

            $data['last_login_ip'] = '';
            $data['auth'] = '';
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            if (!UserModel::create($data)) {
                return $this->error('添加失败');
            }

            return $this->success('添加成功');
        }
        
        $this->assign('menus', []);
        $this->assign('roles', RoleModel::where('id', '>', 1)->order('id asc')->column('id,name'));

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
        if ($id == 1 || ADMIN_ID == $id) {
            return $this->error('禁止修改当前登录用户');
        }
        
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            if ($data['password']) {
                $data['password'] = md5($data['password']);
                $data['password_confirm'] = md5($data['password_confirm']);
            }
            
            // 验证
            $result = $this->validate($data, 'SystemUser.update');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::with('hasIndexs')->where('id', '=', $id)->field('id,username,nick,email,mobile,status')->find()->toArray();

        $row['role_id'] = array_column($row['has_indexs'], 'role_id');

        $this->assign('roles', RoleModel::where('id', '>', 1)->order('id asc')->column('id,name'));
        $this->assign('formData', $row);
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
            // 防止伪造篡改
            unset($data['role_id'], $data['status']);

            if ($data['password']) {
                $data['password'] = md5($data['password']);
                $data['password_confirm'] = md5($data['password_confirm']);
            }

            // 验证
            $result = $this->validate($data, 'SystemUser.info');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $row = UserModel::where('id', ADMIN_ID)->field('username,nick,email,mobile')->find()->toArray();
        $this->assign('formData', $row);
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
        parent::del();
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
        if ($this->request->isAjax()) {
            $data = [];
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);

            $data['data'] = RoleModel::where('id', '<>', 1)->select();
            $data['count'] = RoleModel::where('id', '<>', 1)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }

        $this->assign('hisiTabData', $this->tabData);
        $this->assign('hisiTabType', 3);
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
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!RoleModel::create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '添加角色'],
            ['title' => '设置权限'],
        ];

        $this->assign('menus', MenuModel::getAuthTree());
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 2);
        
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
            
            // 验证
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                return $this->error($result);
            }
            if (!RoleModel::update($data)) {
                return $this->error('修改失败');
            }

            // 更新权限缓存
            session('role_auth_'.$data['id'], $data['auth']);

            return $this->success('修改成功');
        }
        $tabData = [];
        $tabData['menu'] = [
            ['title' => '修改角色'],
            ['title' => '设置权限'],
        ];
        $row = RoleModel::where('id', $id)->field('id,name,intro,auth,status')->find()->toArray();

        $this->assign('formData', $row);
        $this->assign('menus', MenuModel::getAuthTree($row['auth']));
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 2);
        
        return $this->fetch('roleform');
    }

    /**
     * 角色状态设置
     */
    public function statusRole()
    {
        $this->hisiTable = 'SystemRole';
        parent::status();
    }

    /**
     * 删除角色
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delRole()
    {
        $this->hisiModel = 'SystemRole';
        parent::del();
    }
}
