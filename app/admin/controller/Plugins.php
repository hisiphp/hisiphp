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
use app\common\util\PclZip;
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
            [
                'title' => '导入插件',
                'url' => 'admin/plugins/import',
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
        $plugins = PluginsModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,system,app_id,identifier,name,version,status');
        if ($status == 0) {
            $plugins_path = ROOT_PATH.DS.'plugins'.DS;
            // 自动将本地未入库的插件导入数据库
            $all_plugins = PluginsModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList($plugins_path);
            foreach ($files as $k => $f) {
                // 排除已存在数据库的插件
                if (array_key_exists($f, $all_plugins) || !is_dir($plugins_path.$f)) {
                    continue;
                }
                if (file_exists($plugins_path.$f.DS.'info.php')) {
                    $info = include_once $plugins_path.$f.DS.'info.php';
                    $sql = [];
                    $sql['name'] = $info['name'];
                    $sql['identifier'] = $info['identifier'];
                    $sql['title'] = $info['title'];
                    $sql['intro'] = $info['intro'];
                    $sql['author'] = $info['author'];
                    $sql['icon'] = ROOT_DIR.substr($info['icon'], 1);
                    $sql['version'] = $info['version'];
                    $sql['url'] = $info['author_url'];
                    $sql['config'] = '';
                    $sql['status'] = 0;
                    $sql['system'] = 0;
                    $sql['app_id'] = 0;
                    $db = PluginsModel::create($sql);
                    $sql['id'] = $db->id;
                    $plugins = array_merge($plugins, [$sql]);
                }
            }
        }
        $this->assign('data_list', array_values($plugins));
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
        $pluginsInfo = plugins_info($row['name']);
        if (!$row['config'] && !$pluginsInfo['config']) {
            return $this->error('此插件无需配置！');
        }

        if (!$row['config'] && $pluginsInfo['config']) {
            $config = $pluginsInfo['config'];
        } else {
            $config = json_decode($row['config'], 1);
        }
        
        foreach ($config as &$v) {
            if (isset($v['options']) && $v['options']) {
                $v['options'] = array_filter(parse_attr($v['options']));
            }
            if ($v['type'] == 'checkbox' && isset($v['value']) && $v['value']) {
                if (!is_array($v['value'])) {
                    $v['value'] = explode(',', $v['value']);
                }
            }
        }
        $row['config'] = $config;

        if ($this->request->isPost()) {
            $postData = input('post.');
            foreach ($row['config'] as &$conf) {
                $conf['value'] = isset($postData[$conf['name']]) ? $postData[$conf['name']] : '';
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

        // 更新插件基础信息
        $sqlmap = [];
        $sqlmap['title'] = $info['title'];
        $sqlmap['identifier'] = $info['identifier'];
        $sqlmap['intro'] = $info['intro'];
        $sqlmap['author'] = $info['author'];
        $sqlmap['url'] = $info['author_url'];
        $sqlmap['version'] = $info['version'];
        $sqlmap['status'] = 2;
        PluginsModel::where('id', $id)->update($sqlmap);
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
     * 导入插件
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $_file = input('param.file');
            if (empty($_file)) {
                return $this->error('请上传模块安装包');
            }

            $file = realpath('.'.$_file);
            if (ROOT_DIR != '/') {// 针对子目录处理
                $file = realpath('.'.str_replace(ROOT_DIR, '/', $_file));
            }
            
            if (!file_exists($file)) {
                return $this->error('上传文件无效');
            }
            
            $decom_path = '.'.trim(str_replace(ROOT_DIR, '/', $_file), '.zip');
            if (!is_dir($decom_path)) {
                Dir::create($decom_path, 0777, true);
            }

            // 解压安装包到$decom_path
            $archive = new PclZip();
            $archive->PclZip($file);
            if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('导入失败('.$archive->error_string.')');
            }
            
            // 获取插件名
            $files = Dir::getList($decom_path.'/upload/');

            if (!isset($files[0])) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('导入失败，安装包不完整');
            }

            $app_name = $files[0];
            // 防止重复导入插件
            if (is_dir(ROOT_PATH.'plugins'.DS.$app_name)) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('插件已存在');
            } else {
                Dir::create(ROOT_PATH.'plugins'.DS.$app_name, 0777, true);
            }

            // 复制插件
            Dir::copyDir($decom_path.'/upload/'.$app_name.'/', ROOT_PATH.'plugins'.DS.$app_name);

            // 删除临时目录和安装包
            Dir::delDir($decom_path);
            @unlink($file);

            return $this->success('插件导入成功', url('index?status=0'));
        }

        $tab_data = $this->tab_data;
        $tab_data['current'] = 'admin/plugins/import';
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
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
        $val    = input('param.val/d');
        $id     = input('param.id/d');
        $val    = $val+1;// 因为layui开关效果只支持0和1

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
        return plugins_run($plugin.'/'.$controller.'/'.$action, $params);
    }
}