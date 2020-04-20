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

use app\common\controller\Common;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemUser as UserModel;
use app\system\model\SystemLog as LogModel;
use app\system\model\SystemLanguage as LangModel;
use app\system\model\SystemUserRole as UserRoleModel;
use think\Db;
use hisi\Dir;
use think\facade\Env;
use think\facade\Log;

/**
 * 后台公共控制器
 * @package app\system\admin
 */
class Admin extends Common
{
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    protected $hisiModel = '';
    // [通用添加、修改专用] 表名(不含表前缀) 
    protected $hisiTable = '';
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名
    protected $hisiValidate = false;
    //[通用添加专用] 添加数据验证场景名
    protected $hisiAddScene = false;
    //[通用更新专用] 更新数据验证场景名
    protected $hisiEditScene = false;
    // 数据权限设置，可选值：own 个人，org 组织，false 不启用
    protected $dataRight = false;
    // 数据权限字段名
    protected $dataRightField = 'admin_id';

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        
        $model = new UserModel();
        // 判断登陆
        $login = $model->isLogin();
        if (!isset($login['uid']) || !$login['uid']) {
            return $this->error('请登陆之后在操作', '/'.config('sys.admin_path'));
        }
        
        if (!defined('ADMIN_ID')) {
            
            define('ADMIN_ID', $login['uid']);
            define('ADMIN_ROLE', $login['role_id']);

            $curMenu = MenuModel::getInfo();
            if ($curMenu) {
                if (!RoleModel::checkAuth($curMenu['id']) && 
                    $curMenu['url'] != 'system/index/index') {
                    return $this->error('['.$curMenu['title'].'] 访问权限不足');
                } 
            } else if (config('sys.admin_whitelist_verify')) {
                return $this->error('节点不存在或者已禁用！');
            } else {
                $curMenu = ['title' => '', 'url' => '', 'id' => 0];
            }

            $this->_systemLog($curMenu['title']);

            // 如果不是ajax请求，则读取菜单
            if (!$this->request->isAjax()) {
                $breadCrumbs = [];
                $menuParents = ['pid' => 1];

                if ($curMenu['id']) {
                    $breadCrumbs = MenuModel::getBreadCrumbs($curMenu['id']);
                    $menuParents = current($breadCrumbs);
                } else {
                    $breadCrumbs = MenuModel::getBreadCrumbs($curMenu['id']);
                }
                
                $this->assign('hisiBreadcrumb', $breadCrumbs);
                // 获取当前访问的菜单信息
                $this->assign('hisiCurMenu', $curMenu);
                // 获取当前菜单的顶级节点
                $this->assign('hisiCurParents', $menuParents);
                // 获取导航菜单
                $this->assign('hisiMenus', MenuModel::getMainMenu());
                // 分组切换类型 0无需分组切换，1单个分组，2分组切换[无链接]，3分组切换[有链接]，具体请看后台layout.html
                $this->assign('hisiTabType', 0);
                // tab切换数据
                // $hisiTabData = [
                //     ['title' => '后台首页', 'url' => 'system/index/index'],
                // ];
                // current 可不传
                // $this->assign('hisiTabData', ['menu' => $hisiTabData, 'current' => 'system/index/index']);
                $this->assign('hisiTabData', '');
                // 表单数据默认变量名
                $this->assign('formData', '');
                $this->assign('login', $login);
                $this->assign('languages', (new LangModel)->lists());
                $this->assign('hisiHead', '');
                $this->view->engine->layout('system@layout');
            }
        }
    }

    /**
     * 系统日志记录
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    private function _systemLog($title)
    {
        // 系统日志记录
        $log            = [];
        $log['uid']     = ADMIN_ID;
        $log['title']   = $title ? $title : '未加入系统菜单';
        $log['url']     = $this->request->url();
        $log['remark']  = '浏览数据';

        if ($this->request->isPost()) {
            $log['remark'] = '保存数据';
        }

        $result = LogModel::where($log)->find();

        $log['param']   = json_encode($this->request->param());
        $log['ip']      = $this->request->ip();

        if (!$result) {
            LogModel::create($log);
        } else {
            $log['id'] = $result->id;
            $log['count'] = $result->count+1;
            LogModel::update($log);
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
     * [通用方法]添加页面展示和保存
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isPost()) {
            
            $postData = $this->request->post();

            if ($this->hisiValidate) {// 数据验证

                if (strpos($this->hisiValidate, '\\') === false ) {

                    if (defined('IS_PLUGINS')) {
                        $this->hisiValidate = 'plugins\\'.$this->request->param('_p').'\\validate\\'.$this->hisiValidate;
                    } else {
                        $this->hisiValidate = 'app\\'.$this->request->module().'\\validate\\'.$this->hisiValidate;
                    }
                    
                }

                if ($this->hisiAddScene) {
                    $this->hisiValidate = $this->hisiValidate.'.'.$this->hisiAddScene;
                }

                $result = $this->validate($postData, $this->hisiValidate);
                if ($result !== true) {
                    return $this->error($result);
                }
                
            }

            if ($this->hisiModel) {// 通过Model添加

                $model = $this->model();

                if (!$model->save($postData)) {
                    return $this->error($model->getError());
                }

            } else if ($this->hisiTable) {// 通过Db添加

                if (!Db::name($this->hisiTable)->insert($postData)) {
                    return $this->error('保存失败');
                }

            } else {

                return $this->error('当前控制器缺少属性（hisiModel、hisiTable至少定义一个）');

            }

            return $this->success('保存成功', '');
        }

        $template = $this->request->param('template', 'form');

        return $this->fetch($template);
    }

    /**
     * [通用方法]编辑页面展示和保存
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit()
    {
        if ($this->request->isPost()) {// 数据验证
            
            $postData = $this->request->post();

            if ($this->hisiValidate) {

                if (strpos($this->hisiValidate, '\\') === false ) {

                    if (defined('IS_PLUGINS')) {
                        $this->hisiValidate = 'plugins\\'.$this->request->param('_p').'\\validate\\'.$this->hisiValidate;
                    } else {
                        $this->hisiValidate = 'app\\'.$this->request->module().'\\validate\\'.$this->hisiValidate;
                    }

                }

                if ($this->hisiEditScene) {
                    $this->hisiValidate = $this->hisiValidate.'.'.$this->hisiEditScene;
                }

                $result = $this->validate($postData, $this->hisiValidate);
                if ($result !== true) {
                    return $this->error($result);
                }

            }
        }

        $where = [];
        if ($this->hisiModel) {// 通过Model更新

            $model = $this->model();

            $pk = $model->getPk();
            $id = $this->request->param($pk);

            $where[]= [$pk, '=', $id];
            $where  = $this->getRightWhere($where);
            
            if ($this->request->isPost()) {

                if ($model->save($postData, $where) === false) {
                    return $this->error($model->getError());
                }

                return $this->success('保存成功', '');
            }

            $formData = $model->where($where)->find();

        } else if ($this->hisiTable) {// 通过Db更新

            $db = Db::name($this->hisiTable);
            $pk = $db->getPk();
            $id = $this->request->param($pk);

            $where[]= [$pk, '=', $id];
            $where  = $this->getRightWhere($where);

            if ($this->request->isPost()) {

                if (!$db->where($where)->update($postData)) {
                    return $this->error('保存失败');
                }

                return $this->success('保存成功', '');
            }

            $formData = $db->where($where)->find();

        } else {

            return $this->error('当前控制器缺少属性（hisiModel、hisiTable至少定义一个）');

        }

        if (!$formData) {
            return $this->error('数据不存在或没有权限');
        }

        $this->assign('formData', $formData);

        $template = $this->request->param('template', 'form');

        return $this->fetch($template);
    }

    /**
     * [通用方法]状态设置
     * 禁用、启用都是调用这个内部方法
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function status()
    {
        $val        = $this->request->param('val/d');
        $id         = $this->request->param('id/a');
        $field      = $this->request->param('field/s', 'status');
        
        if (empty($id)) {
            return $this->error('缺少id参数');
        }
        
        if ($this->hisiModel) {

            $obj = $this->model();

        } else if ($this->hisiTable) {

            $obj = db($this->hisiTable);

        } else {

            return $this->error('当前控制器缺少属性（hisiModel、hisiTable至少定义一个）');

        }
        
        $pk     = $obj->getPk();

        $where  = [];
        $where[]= [$pk, 'in', $id];
        $where  = $this->getRightWhere($where);

        $result = $obj->where($where)->setField($field, $val);
        if ($result === false) {
            return $this->error('状态设置失败');
        }

        return $this->success('状态设置成功', '');
    }

    /**
     * [通用方法]删除单条记录
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {

        $id = $this->request->param('id/a');
        if (empty($id)) {
            return $this->error('缺少id参数');
        }
        
        if ($this->hisiModel) {
            $model = $this->model();
            $pk = $model->getPk();
            $where[] = [$pk, 'in', $id];
            $where = $this->getRightWhere($where);
            if (method_exists($model, 'withTrashed')) {
                $rows = $model->withTrashed()->where($where)->select();
                foreach($rows as $v) {
                    if ($v->trashed()) {
                        $result = $v->delete(true);
                    } else {
                        $result = $v->delete();
                    }

                    if (!$result) {
                        return $this->error($v->getError());
                    }
                }
            } else {
                $row = $model->where($where)->delete();
            }
        } else if ($this->hisiTable) {
            $db = db($this->hisiTable);
            $pk = $db->getPk();

            $where  = [];
            $where[]= [$pk, 'in', $id];
            $where  = $this->getRightWhere($where);

            $db->where($where)->delete();
        } else {

            return $this->error('当前控制器缺少属性（hisiModel、hisiTable至少定义一个）');

        }

        return $this->success('删除成功', '');
    }

    /**
     * [通用方法]排序
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function sort()
    {
        $id         = $this->request->param('id/a');
        $field      = $this->request->param('field/s', 'sort');
        $val        = $this->request->param('val/d');

        if (empty($id)) {
            return $this->error('缺少id参数');
        }

        if ($this->hisiModel) {

            $obj = $this->model();

        } else if ($this->hisiTable) {

            $obj = db($this->hisiTable);

        } else {

            return $this->error('当前控制器缺少属性（hisiModel、hisiTable至少定义一个）');

        }
        
        $pk     = $obj->getPk();
        $result = $obj->where([$pk => $id])->setField($field, $val);

        if ($result === false) {
            return $this->error('排序设置失败');
        }

        return $this->success('排序设置成功', '');
    }

    /**
     * [通用方法]上传附件
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function upload()
    {
        $model = new \app\common\model\SystemAnnex;
        
        return json($model::fileUpload());
    }

    /** 
     * 实例化模型类($hisiModel)
    */
    protected function model()
    {

        if (!$this->hisiModel) {
            $this->error('hisiModel属性未定义');
        }

        if (defined('IS_PLUGINS')) {
            if (strpos($this->hisiModel, '\\') === false ) {
                $this->hisiModel = 'plugins\\'.$this->request->param('_p').'\\model\\'.$this->hisiModel;
            }

            return (new $this->hisiModel);
        } else {
            if (strpos($this->hisiModel, '/') === false ) {
                $this->hisiModel = $this->request->module().'/'.$this->hisiModel;
            }

            return model($this->hisiModel);
        }
    }

    /** 
     * 实例化数据库类
    */
    protected function db($name = '')
    {
        $name = $name ?: $this->hisiTable;
        if (!$name) {
            $this->error('hisiTable属性未定义');
        }

        return Db::name($name);
    }

    /**
     * 获取同组织下的所有管理员ID
     * @return array
     */
    protected function getAdminIds()
    {
        
        if (ADMIN_ID == 1 || !$this->dataRight) {
            return [];
        }

        $ids = [ADMIN_ID];

        if ($this->dataRight == 'org') {// 组织
            $ids = UserRoleModel::getOrgUserId(ADMIN_ROLE);
        }

        return $ids;
    }

    /**
     * 获取数据权限 where
     * @param array $where
     * @return array
     */
    protected function getRightWhere($where = [])
    {
        $ids = $this->getAdminIds();
        
        if ($ids) {
            $ids[] = 0;    
            $where[] = [$this->dataRightField, 'in', $ids];
        }

        return $where;
    }

    /**
     * 输出layui的json数据
     *
     * @param array $data
     * @param integer $count
     * @return void
     */
    protected function layuiJson($data, $count = 0)
    {
        return json(['data' => $data, 'count' => $count, 'code' => 0]);
    }
}
