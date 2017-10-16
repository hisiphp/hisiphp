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

use app\common\controller\Common;
use app\admin\model\AdminMenu as MenuModel;
use app\admin\model\AdminRole as RoleModel;
use app\admin\model\AdminUser as UserModel;
use app\admin\model\AdminLog as LogModel;
use think\Db;
/**
 * 后台公共控制器
 * @package app\admin\controller
 */
class Admin extends Common
{
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();
        $model = new UserModel();
        // 判断登陆
        $login = $model->isLogin();
        if (!$login['uid']) {
            return $this->error('请登陆之后在操作！', ROOT_DIR.config('sys.admin_path'));
        }
        define('ADMIN_ID', $login['uid']);
        define('ADMIN_ROLE', $login['role_id']);
        
        $c_menu = MenuModel::getInfo();
        if (!$c_menu) {
            return $this->error('节点不存在或者已禁用！');
        }

        // 检查权限
        if (!RoleModel::checkAuth($c_menu['id'])) {
            $url = '';
            // 如果没有后台首页的登录权限，直接退出，避免出现死循环跳转
            if ($c_menu['url'] == 'admin/index/index') {
                $url = ROOT_DIR.config('sys.admin_path');
                model('AdminUser')->logout();
            }
            return $this->error('['.$c_menu['title'].'] 访问权限不足', $url);
        }

        // 系统日志记录
        $log = [];
        $log['uid'] = ADMIN_ID;
        $log['title'] = $c_menu['title'];
        $log['url'] = $c_menu['url'];
        $log['param'] = json_encode(input('param.'));
        $log['remark'] = '浏览数据';
        if ($this->request->isPost()) {
            $log['remark'] = '保存数据';
        }
        $log_result = LogModel::where($log)->find();
        $log['ip'] = $this->request->ip();
        if (!$log_result) {
            LogModel::create($log);
        } else {
            $log['id'] = $log_result->id;
            $log['count'] = $log_result->count+1;
            LogModel::update($log);
        }

        // 如果不是ajax请求，则读取菜单
        if (!$this->request->isAjax()) {
            // 获取当前访问的节点信息
            $this->assign('_admin_menu_current', $c_menu);
            $_bread_crumbs = MenuModel::getBrandCrumbs($c_menu['id']);
            $this->assign('_bread_crumbs', $_bread_crumbs);
            // 获取当前访问的节点的顶级节点
            $this->assign('_admin_menu_parents', current($_bread_crumbs));
            // 获取导航菜单
            $this->assign('_admin_menu', MenuModel::getMainMenu());
            // 分组切换类型 0单个分组[有链接]，1分组切换[有链接]，2分组切换[无链接]，3无需分组切换，具体请看后台layout.php
            $this->assign('tab_type', 0);
            // tab切换数据
            // $tab_data = [
            //     ['title' => '后台首页', 'url' => 'admin/index/index'],
            // ];
            // current 可不传
            // $this->assign('tab_data', ['menu' => $tab_data, 'current' => 'admin/index/index']);
            $this->assign('tab_data', '');
            // 列表页默认数据输出变量
            $this->assign('data_list', '');
            $this->assign('pages', '');
            // 编辑页默认数据输出变量
            $this->assign('data_info', '');
            $this->assign('admin_user', $login);
            $this->assign('languages', model('AdminLanguage')->lists());
        }
    }

    /**
     * 获取当前方法URL
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    protected function getActUrl() {
        $model      = request()->module();
        $controller = request()->controller();
        $action     = request()->action();
        return $model.'/'.$controller.'/'.$action;
    }

    /**
     * 通用状态设置
     * 禁用、启用都是调用这个内部方法
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function status() {
        $val   = input('param.val');
        $ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
        $table = input('param.table');
        $field = input('param.field', 'status');
        if (empty($ids)) {
            return $this->error('参数传递错误[1]！');
        }
        if (empty($table)) {
            return $this->error('参数传递错误[2]！');
        }
        // 以下表操作需排除值为1的数据
        if ($table == 'admin_menu' || $table == 'admin_user' || $table == 'admin_role' || $table == 'admin_module') {
            if (in_array('1', $ids) || ($table == 'admin_menu' && in_array('2', $ids))) {
                return $this->error('系统限制操作');
            }
        }
         // 获取主键
        $pk = Db::name($table)->getPk();
        $map = [];
        $map[$pk] = ['in', $ids];

        $res = Db::name($table)->where($map)->setField($field, $val);
        if ($res === false) {
            return $this->error('状态设置失败');
        }
        return $this->success('状态设置成功');
    }

    /**
     * 通用删除
     * 单纯的记录删除
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del() {
        $ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
        $table = input('param.table');
        if (empty($ids)) {
            return $this->error('无权删除(原因：可能您选择的是系统菜单)');
        }
        // 禁止以下表通过此方法操作
        if ($table == 'admin_user' || $table == 'admin_role') {
            return $this->error('非法操作');
        }

        // 以下表操作需排除值为1的数据
        if ($table == 'admin_menu' || $table == 'admin_module') {
            if ((is_array($ids) && in_array('1', $ids))) {
                return $this->error('禁止操作');
            }
        }
            
        // 获取主键
        $pk = Db::name($table)->getPk();
        $map = [];
        $map[$pk] = ['in', $ids];

        $res = Db::name($table)->where($map)->delete();
        if ($res === false) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    /**
     * 通用排序
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function sort() {
        $ids   = input('param.ids/d') ? input('param.ids/d') : input('param.id/d');
        $table = input('param.table');
        $field = input('param.field/s', 'sort');
        $val   = input('param.val/d');
        // 获取主键
        $pk = Db::name($table)->getPk();
        $map = [];
        $map[$pk] = ['in', $ids];
        $res = Db::name($table)->where($map)->setField($field, $val);
        if ($res === false) {
            return $this->error('排序设置失败');
        }
        return $this->success('排序设置成功');
    }
}
