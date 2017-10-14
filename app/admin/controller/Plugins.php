<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\model\AdminPlugins as PluginsModel;
use app\admin\model\AdminHookPlugins as HookPluginsModel;
use app\admin\model\AdminMenu as MenuModel;
use think\Db;
use app\common\util\Dir;
/**
 * 插件管理控制器
 * @package app\admin\controller
 */
class Plugins extends Admin
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
                'title' => '已启用插件',
                'url' => 'admin/plugins/index?status=2',
            ],
            [
                'title' => '未启用插件',
                'url' => 'admin/plugins/index?status=1',
            ],
            [
                'title' => '未安装插件',
                'url' => 'admin/plugins/index?status=0',
            ],
            [
                'title' => '<strong style="color:#428bca">应用市场</strong>',
                'url' => 'http://store.hisiphp.com/addons',
            ],
        ];
        if (config('develop.app_debug') == 1) {
            array_push($tab_data['menu'], ['title' => '设计新插件', 'url' => 'admin/plugins/design',]);
        }
        $this->tab_data = $tab_data;
    }

    /**
     * 插件管理首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($status = 2)
    {
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('?status='.$status);
        $map = [];
        $map['status'] = $status;
        $data_list = PluginsModel::where($map)->order('sort,id')->paginate();
        $pages = $data_list->render();

        $this->assign('data_list', $data_list);
        $this->assign('pages', $pages);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    /**
     * 插件设计
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function design()
    {
        if (config('develop.app_debug') == 0) {
            return $this->error('非开发模式禁止使用此功能！');
        }
        if ($this->request->isPost()) {
            $model = new PluginsModel();
            if (!$model->design($this->request->post())) {
                return $this->error($model->getError());
            }
            return $this->success('插件已生成完毕。', url('index?status=0'));
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '插件设计'],
            ['title' => '插件配置'],
            // ['title' => '插件菜单'],
        ];

        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }

    /**
     * 插件配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setting($id = 0)
    {
        $row = PluginsModel::where('id', $id)->field('id,name,config,title')->find()->toArray();
        if ($row['config']) {
            $row['config'] = json_decode($row['config'], 1);
        } else {
            return $this->error('此插件无需配置！');
        }

        if ($this->request->isPost()) {
            foreach ($row['config'] as &$conf) {
                $conf['value'] = input('post.'.$conf['name']);
            }
            if (PluginsModel::where('id', $id)->setField('config', json_encode($row['config'], 1)) === false) {
                return $this->error('配置保存失败！');
            }
            PluginsModel::getConfig('', true);
            return $this->success('配置保存成功。');
        }
        
        $this->assign('data_info', $row);
        return $this->fetch();
    }

    /**
     * 安装插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function install($id = 0)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return $this->error('插件不存在！');
        }
        if ($plug['status'] > 0) {
            return $this->error('请勿重复安装此插件！');
        }
        $plug_class = get_plugins_class($plug['name']);
        if (!class_exists($plug_class)) {
            return $this->error('插件不存在！');
        }

        $plug_path = ROOT_PATH.'plugins/'.$plug['name'].'/';
        // 插件基本信息
        if (!file_exists($plug_path.'info.php')) {
            return $this->error('插件文件[info.php]丢失！');
        }
        $info = include_once $plug_path.'info.php';

        $plug_obj = new $plug_class;
        // 安装前先执行插件内部安装程序
        if(!$plug_obj->install()) {
            return $this->error('插件预安装失败!原因：'. $plug_obj->getError());
        }

        // 将插件钩子注入到钩子索引表
        if (isset($plug_obj->hooks) && !empty($plug_obj->hooks)) {
            if (!HookPluginsModel::storage($plug_obj->hooks, $plug['name'])) {
                return $this->error('安装插件钩子时出现错误，请重新安装');
            }
            cache('hook_plugins', null);
        }
        // 导入SQL
        $sql_file = realpath($plug_path.'sql/install.sql');
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('hs_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return $this->error('install.sql文件含有系统表['.$t.']');
                        }
                    }
                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $this->error('导入SQL失败，请检查install.sql的语句是否正确或者表是否存在');
                        }
                    }
                }
            }
        }

        // 导入菜单
        if ( file_exists($plug_path.'menu.php') ) {
            $menus = include_once $plug_path.'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::importMenu($menus, 'plugins.'.$plug['name'], 'plugins') == false) {
                // 执行回滚
                MenuModel::where('module', 'plugins.'.$plug['name'])->delete();
                return $this->error('插件菜单失败(原因：可能是param参数异常)，请重新安装！');
            }
        }
        // 导入配置信息
        if (isset($info['config']) && !empty($info['config'])) {
            PluginsModel::where('id', $id)->setField('config', json_encode($info['config'], 1));
        }
        // 更新插件状态为已安装并启用
        PluginsModel::where('id', $id)->setField('status', 2);
        PluginsModel::getConfig('', true);
        return $this->success('插件已安装成功。', url('index?status=2'));
    }

    /**
     * 卸载插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function uninstall($id = 0)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return $this->error('插件不存在！');
        }
        if ($plug['status'] == 0) {
            return $this->error('插件未安装！');
        }

        $plug_path = ROOT_PATH.'plugins/'.$plug['name'].'/';
        
        // 插件基本信息
        if (!file_exists($plug_path.'info.php')) {
            return $this->error('插件文件[info.php]丢失！');
        }
        $info = include_once $plug_path.'info.php';

        $plug_class = get_plugins_class($plug['name']);
        if (!class_exists($plug_class)) {
            return $this->error('插件不存在！');
        }

        $plug_obj = new $plug_class;
        // 卸载前先执行插件内部卸载程序
        if(!$plug_obj->uninstall()) {
            return $this->error('插件预卸载失败!原因：'. $plug_obj->getError());
        }

        if (!HookPluginsModel::del($plug['name'])) {
            return $this->error('插件相关钩子删除失败！');
        }
        cache('hook_plugins', null);

        // 导入SQL
        $sql_file = realpath($plug_path.'sql/uninstall.sql');
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('hs_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return $this->error('uninstall.sql文件含有系统表['.$t.']');
                        }
                    }
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        return $this->error('导入SQL失败，请检查uninstall.sql的语句是否正确');
                    }
                }
            }
        }
        // 删除插件菜单
        MenuModel::where('module', 'plugins.'.$plug['name'])->delete();
        // 更新插件状态为未安装
        PluginsModel::where('id', $id)->setField('status', 0);
        PluginsModel::where('id', $id)->setField('config', '');
        PluginsModel::getConfig('', true);
        return $this->success('插件已卸载成功。', url('index?status=0'));
    }

    /**
     * 插件打包下载
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function package($id = 0)
    {
        return $this->success('开发中...');
    }

    /**
     * 状态设置
     * @author 橘子俊 <364666827@qq.com>
     */
    public function status()
    {
        $val   = input('param.val/d');
        $id    = input('param.id/d');

        $res = PluginsModel::where('id', $id)->find();

        if ($res['status'] <= 0) {
            return $this->error('只允许操作已安装插件！');
        }

        $res = PluginsModel::where('id', $id)->setField('status', $val);
        if ($res === false) {
            return $this->error('操作失败！');
        }
        return $this->success('操作成功！');
    }

    /**
     * 删除插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function del($id = 0)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return $this->error('插件不存在！');
        }
        if ($plug['status'] != 0) {
            return $this->error('请先卸载插件['.$plug['name'].']！');
        }
        if (Dir::delDir(ROOT_PATH.'plugins/'.$plug['name']) === false) {
            return $this->error('插件目录失败(原因：可能没有权限)！');
        }

        if (!PluginsModel::where('id', $id)->delete()) {
            return $this->error('插件数据删除失败！');
        }

        return $this->success('插件删除成功。');
    }

    /**
     * 执行内部插件
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function run() {
        $plugin     = $_GET['_p'] = input('param._p');
        $controller = $_GET['_c'] = ucfirst(input('param._c', 'Index'));
        $action     = $_GET['_a'] = input('param._a', 'index');
        $params     = $this->request->except(['_p', '_c', '_a'], 'param');

        if (empty($plugin)) {
            return $this->error('插件名必须传入[_p]！');
        }
            
        if (!PluginsModel::where(['name' => $plugin, 'status' => 2])->find() ) {
            return $this->error("插件可能不存在或者未安装！");
        }

        if (!plugins_action_exist($plugin.'/'.$controller.'/'.$action)) {
            return $this->error("找不到插件方法：{$plugin}/{$controller}/{$action}");
        }
        return plugins_action($plugin.'/'.$controller.'/'.$action, $params);
    }
}
