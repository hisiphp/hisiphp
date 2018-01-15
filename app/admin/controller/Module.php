<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AdminModule as ModuleModel;
use app\admin\model\AdminMenu as MenuModel;
use app\common\util\Dir;
use app\common\util\PclZip;
use think\Db;
use think\Xml;
/**
 * 模块管理控制器
 * @package app\admin\controller
 */
class Module extends Admin
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
                'title' => '已启用模块',
                'url' => 'admin/module/index?status=2',
            ],
            [
                'title' => '未启用模块',
                'url' => 'admin/module/index?status=1',
            ],
            [
                'title' => '未安装模块',
                'url' => 'admin/module/index?status=0',
            ],
            [
                'title' => '<strong style="color:#428bca">应用市场</strong>',
                'url' => 'http://store.hisiphp.com/modules',
            ],
            [
                'title' => '导入模块',
                'url' => 'admin/module/import',
            ],
        ];
        if (config('develop.app_debug') == 1) {
            array_push($tab_data['menu'], ['title' => '设计新模块', 'url' => 'admin/module/design',]);
        }
        $this->tab_data = $tab_data;
    }

    /**
     * 模块管理首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($status = 2)
    {
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('?status='.$status);
        $map = [];
        $map['status'] = $status;
        $map['system'] = 0;
        $modules = ModuleModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,default,system,app_id,identifier,config,name,version,status');
        if ($status == 0) {
            // 自动将本地未入库的模块导入数据库
            $all_module = ModuleModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList(APP_PATH);
            $sys_dir = config('hs_system.modules');
            array_push($sys_dir, 'extra');
            if (!is_dir(ROOT_PATH.'static'.DS.'app_icon')) {
                Dir::create(ROOT_PATH.'static'.DS.'app_icon', 0777, true);
            }
            
            foreach ($files as $k => $f) {
                // 排除系统模块和已存在数据库的模块
                if (array_search($f, $sys_dir) !== false || array_key_exists($f, $all_module) || !is_dir(APP_PATH.$f)) {
                    continue;
                }
                if (file_exists(APP_PATH.$f.DS.'info.php')) {
                    $info = include_once APP_PATH.$f.DS.'info.php';
                    $sql = [];
                    $sql['name'] = $info['name'];
                    $sql['identifier'] = $info['identifier'];
                    $sql['theme'] = $info['theme'];
                    $sql['title'] = $info['title'];
                    $sql['intro'] = $info['intro'];
                    $sql['author'] = $info['author'];
                    copy(APP_PATH.$f.DS.$f.'.png', ROOT_PATH.'static'.DS.'app_icon'.DS.$f.'.png');
                    $sql['icon'] = ROOT_DIR.'static/app_icon/'.$f.'.png';
                    $sql['version'] = $info['version'];
                    $sql['url'] = $info['author_url'];
                    $sql['config'] = '';
                    $sql['status'] = 0;
                    $sql['default'] = 0;
                    $sql['system'] = 0;
                    $sql['app_id'] = 0;
                    $db = ModuleModel::create($sql);
                    $sql['id'] = $db->id;
                    $modules = array_merge($modules, [$sql]);
                }
            }
        }

        $this->assign('data_list', array_values($modules));
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    /**
     * 模块设计
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function design()
    {
        if (config('develop.app_debug') == 0) {
            return $this->error('非开发模式禁止使用此功能');
        }
        if ($this->request->isPost()) {
            $model = new ModuleModel();
            if (!$model->design($this->request->post())) {
                return $this->error($model->getError());
            }
            return $this->success('模块已自动生成完毕', url('index?status=0'));
        }
        
        $tab_data = $this->tab_data;
        $tab_data['current'] = 'admin/module/design';
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    /**
     * 修改模块设计
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit()
    {
        $_id = get_num();

        $module_db = ModuleModel::where('id', $_id)->find();
        if (!$module_db) {
            return $this->error('模块不存在');
        }

        if ($module_db['app_id'] > 0) {
            return $this->error('禁止修改此模块');
        }

        if (!is_file(APP_PATH.$module_db['name'].DS.'info.php')) {
            return $this->error('模块配置文件丢失');
        }
        
        if ($this->request->isPost()) {
            $post = input('post.');
            $data = $post;

            unset($data['id']);
            $data['config'] = $data['module_depend'] = $data['plugin_depend'] = $data['hooks'] = $data['tables'] = '[]';
            // 模块配置重组
            $config = [];
            if (isset($post['config'])) {
                foreach ($post['config']['sort'] as $k => $v) {
                    $arr = [];
                    $arr['sort'] = $v;
                    $arr['title'] = $post['config']['title'][$k];
                    $arr['name'] = $post['config']['name'][$k];
                    $arr['type'] = $post['config']['type'][$k];
                    $arr['options'] = parse_attr($post['config']['options'][$k]);
                    $arr['value'] = $post['config']['value'][$k];
                    $arr['tips'] = $post['config']['tips'][$k];
                    $config[$v] = $arr;
                }
                ksort($config);
                $config_str = '';
                foreach ($config as $k => $v) {
                    $v['options'] = array_filter($v['options']);
                    if ($v['options']) {
                        $options = "[\n";
                        foreach ($v['options'] as $kk => $vv) {
                            $options .= "                '{$kk}'=> '{$vv}',\n";
                        }
                        $options .= "            ]";
                    } else {
                        $options = "''";
                    }
                    $config_str .= "        [\n            'sort' => {$k}, \n            'title' => '{$v['title']}', \n            'name' => '{$v['name']}', \n            'type' => '{$v['type']}', \n            'options' => {$options}, \n            'value' => '{$v['value']}', \n            'tips' => '{$v['tips']}',\n        ], \n";
                }
                $data['config'] = "[\n    {$config_str}    ]";
            }

            // 模块依赖重组
            if (isset($post['module_depend'])) {
                $module_depend = '';
                foreach ($post['module_depend']['name'] as $k => $v) {
                    $module_depend .= "        ['{$v}', '{$post['module_depend']['identifier'][$k]}', '{$post['module_depend']['version'][$k]}', '".htmlspecialchars_decode($post['module_depend']['type'][$k])."'],\n";
                }
                $data['module_depend'] = "[\n{$module_depend}    ]";
            }

            // 插件依赖重组
            if (isset($post['plugin_depend'])) {
                $plugin_depend = '';
                foreach ($post['plugin_depend']['name'] as $k => $v) {
                    $plugin_depend .= "        ['{$v}', '{$post['plugin_depend']['identifier'][$k]}', '{$post['plugin_depend']['version'][$k]}', '".htmlspecialchars_decode($post['plugin_depend']['type'][$k])."'],\n";
                }
                $data['plugin_depend'] = "[\n{$plugin_depend}    ]";
            }

            // 预埋钩子重组
            if (isset($post['hooks'])) {
                $hooks = '';
                foreach ($post['hooks']['key'] as $k => $v) {
                    $hooks .= "        '{$v}' => '{$post['hooks']['desc'][$k]}',\n";
                }
                $data['hooks'] = "[\n{$hooks}    ]";
            }

            // 数据表重组
            if (isset($post['tables'])) {
                $tables = '';
                foreach ($post['tables'] as $k => $v) {
                    $tables .= "        '{$v}',\n";
                }
                $data['tables'] = "[\n{$tables}    ]";
            }

            if (!$this->mkInfo($data)) {
                return $this->error('配置文件更新失败');
            }
            
            $sqlmap = [];
            $sqlmap['title'] = $data['title'];
            $sqlmap['identifier'] = $data['identifier'];
            $sqlmap['icon'] = $data['icon'];
            $sqlmap['intro'] = $data['intro'];
            $sqlmap['author'] = $data['author'];
            $sqlmap['url'] = $data['url'];
            $sqlmap['version'] = $data['version'];
            $sqlmap['config'] = '';
            
            // 将配置更新到数据库
            if ($config) {
                // 重组旧配置，以方便后面找值
                $old_config = json_decode($module_db['config'], 1);
                $old_config_arr = [];
                foreach ($old_config as $k => $v) {
                    $old_config_arr[$v['name']] = $v;
                }
                // 将旧配置的值赋值到新配置
                foreach ($config as $k => &$v) {
                    if (isset($old_config_arr[$v['name']])) {
                        $v['value'] = $old_config_arr[$v['name']]['value'];
                    }
                }
                $sqlmap['config'] = json_encode($config, 1);
            }

            $res = ModuleModel::where('id', $_id)->update($sqlmap);
            if (!$res) {
                return $this->error('保存失败');
            }
            return $this->success('保存成功');
        }

        $module_info = include_once APP_PATH.$module_db['name'].DS.'info.php';
        $module_db['db_prefix'] = $module_info['db_prefix'];

        $tab_data['menu'] = [
            [
                'title' => '基本信息',
            ], [
                'title' => '模块配置',
            ], [
                'title' => '依赖模块',
            ], [
                'title' => '依赖插件',
            ], [
                'title' => '数据库',
            ], [
                'title' => '预埋钩子',
            ],
        ];

        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        $this->assign('module_info', $module_info);
        $this->assign('data_info', $module_db);
        return $this->fetch();
    }

    /**
     * 安装模块
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function install($id = 0)
    {
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return $this->error('模块不存在');
        }
        if ($mod['status'] > 0) {
            return $this->error('请勿重复安装此模块');
        }
        $mod_path = APP_PATH.$mod['name'].DS;
        // 模块自定义配置
        if (!file_exists($mod_path.'info.php')) {
            return $this->error('模块配置文件不存在[info.php]');
        }
        $info = include_once $mod_path.'info.php';
        if ($this->request->isPost()) {
            // 过滤系统表
            foreach ($info['tables'] as $t) {
                if (in_array($t, config('hs_system.tables'))) {
                    return $this->error('模块数据表与系统表重复['.$t.']');
                }
            }

            $post = $this->request->post();
            // 导入SQL
            $sql_file = realpath($mod_path.'sql'.DS.'install.sql');
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $sql_list = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
                if ($sql_list) {
                    if ($post['clear'] == 1) {// 清空所有数据
                        foreach ($info['tables'] as $table) {
                            if (Db::query("SHOW TABLES LIKE '".config('database.prefix').$table."'")) {
                                Db::execute('DROP TABLE IF EXISTS `'.config('database.prefix').$table.'`;');
                            }
                        }
                    }
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
                                if ($post['clear'] == 1) {
                                    return $this->error('导入SQL失败，请检查install.sql的语句是否正确或者表是否存在');
                                } else {
                                    return $this->error('导入SQL失败，请尝试选择清除旧数据');
                                }
                            }
                        }
                    }
                }
            }
            // 导入菜单
            if ( file_exists($mod_path.'menu.php') ) {
                $menus = include_once $mod_path.'menu.php';
                // 如果不是数组且不为空就当JSON数据转换
                if (!is_array($menus) && !empty($menus)) {
                    $menus = json_decode($menus, 1);
                }
                if (MenuModel::importMenu($menus, $mod['name']) == false) {
                    // 执行回滚
                    MenuModel::where('module', $mod['name'])->delete();
                    return $this->error('添加菜单失败，请重新安装');
                }
            }
            
            // 导入模块钩子
            if (!empty($info['hooks'])) {
                $hook_mod = model('AdminHook');
                foreach ($info['hooks'] as $k => $v) {
                    $map = [];
                    $map['name'] = $k;
                    $map['intro'] = $v;
                    $map['source'] = 'module.'.$mod['name'];
                    $hook_mod->storage($map);
                }
            }
            cache('hook_plugins', null);
            // 导入模块配置
            if (isset($info['config']) && !empty($info['config'])) {
                $menu = [];
                $menu['pid'] = 10;
                $menu['module'] = $mod['name'];
                $menu['title'] = $mod['title'].'配置';
                $menu['url'] = 'admin/system/index';
                $menu['param'] = 'group='.$mod['name'];
                $menu['system'] = 0;
                $menu['debug'] = 0;
                $menu['sort'] = 100;
                $menu['status'] = 1;
                $menu_mod = new MenuModel;
                $menu_mod->storage($menu);
                ModuleModel::where('id', $id)->setField('config', json_encode($info['config'], 1));
            }
            // 更新模块基础信息
            $sqlmap = [];
            $sqlmap['title'] = $info['title'];
            $sqlmap['identifier'] = $info['identifier'];
            $sqlmap['intro'] = $info['intro'];
            $sqlmap['author'] = $info['author'];
            $sqlmap['url'] = $info['author_url'];
            $sqlmap['version'] = $info['version'];
            $sqlmap['status'] = 2;
            ModuleModel::where('id', $id)->update($sqlmap);
            ModuleModel::moduleRoute(true);
            return $this->success('模块已安装成功', url('index?status=2'));
        }
        // 模块依赖检查
        foreach ($info['module_depend'] as $k => $v) {
            if (!isset($v[3])) {
                $v[3] = '=';
            }
            $v[4] = '✔︎';
            $v[5] = '';
            // 判断模块是否存在
            if (!is_dir(APP_PATH.$v[0])) {
                $v[4] = '<span class="red">✘ 模块不存在</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            if (!file_exists(APP_PATH.$v[0].'/info.php')) {
                $v[4] = '<span class="red">✘ 模块配置文件不存在</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            $dinfo = include APP_PATH.$v[0].'/info.php';
            $v[5] = $dinfo['version'];
            // 判断依赖的模块标识是否一致
            if ($dinfo['identifier'] != $v[1]) {
                $v[4] = '<span class="red">✘ 模块标识不匹配</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            // 版本对比
            if (version_compare($dinfo['version'], $v[2], $v[3]) === false) {
                $v[4] = '<span class="red">✘ 需要的版本必须'.$v[3].$v[2].'</span>';
                $info['module_depend'][$k] = $v;
                continue;
            }
            $info['module_depend'][$k] = $v;
        }
        // 插件依赖检查 TODO
        $info['id'] = $mod['id'];
        $this->assign('tables', $this->checkTable($info['tables']));
        $this->assign('data_info', $info);
        return $this->fetch();
    }

    /**
     * 模块图标上传
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function icon()
    {
        $_id = get_num();

        $module = ModuleModel::where('id', $_id)->find();
        if (!$module) {
            return $this->error('参数传递错误');
        }

        $file = request()->file('file');
        if (!$file->checkExt('png')) {
            return $this->error('只允许上传PNG图标');
        }
        if (!$file->checkSize(102400)) {
            return $this->error('图标大小超过系统限制(100KB)');
        }
        $image_path = ROOT_PATH . 'upload' . DS . 'temp' . DS;
        $file->rule('')->move($image_path, $module['name'] . '.png');
        $image = getimagesize($image_path.$module['name'] . '.png');
        if ($image[0] !== 200 || $image[1] !== 200 ) {
            unlink($image_path.$module['name'] . '.png');
            return $this->error('图标尺寸不符合要求(200px * 200px)');
        }

        // 将图标移动到模块目录下面
        copy($image_path.$module['name'] . '.png', APP_PATH.$module['name'].'.png');
        copy($image_path.$module['name'] . '.png', ROOT_PATH.'static'.DS.'app_icon'.DS.$module['name'].'.png');
        return $this->success('/static/app_icon/'.$module['name'].'.png?v='.time());
    }

    /**
     * 模块配置 作废(原因，不利于权限管理)
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    // public function setting($id = 0)
    // {
    //     $row = ModuleModel::where('id', $id)->field('id,name,config,title')->find()->toArray();
    //     if ($row['config']) {
    //         $row['config'] = json_decode($row['config'], 1);
    //     } else {
    //         return $this->error('此模块无需配置');
    //     }

    //     if ($this->request->isPost()) {
    //         foreach ($row['config'] as &$conf) {
    //             $conf['value'] = input('post.'.$conf['name']);
    //         }
    //         if (ModuleModel::where('id', $id)->setField('config', json_encode($row['config'], 1)) === false) {
    //             return $this->error('配置保存失败');
    //         }
    //         return $this->success('配置保存成功');
    //     }
        
    //     $this->assign('data_info', $row);
    //     return $this->fetch();
    // }

    /**
     * 导入模块
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
            
            $decom_path = '.'.trim($_file, '.zip');
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
            if (!is_dir($decom_path.'/upload/app')) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('导入失败，安装包不完整(-1)');
            }
            // 获取模块名
            $files = Dir::getList($decom_path.'/upload/app/');
            if (!isset($files[0])) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('导入失败，安装包不完整(-2)');
            }
            $app_name = $files[0];
            // 防止重复导入模块
            if (is_dir(APP_PATH.$app_name)) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('模块已存在');
            }
            // 应用目录
            $app_path = $decom_path.'/upload/app/'.$app_name.'/';
            // 获取安装包基本信息
            if (!file_exists($app_path.'info.php')) {
                Dir::delDir($decom_path);
                @unlink($file);
                return $this->error('安装包缺少[info.php]文件');
            }
            $info = include_once $app_path.'info.php';
            // 安装模块路由
            if (file_exists($app_path.$app_name.'.php')) {
                Dir::copyDir($app_path.$app_name.'.php', './route');
            }
            // 复制app目录
            if (!is_dir(ROOT_PATH.'app'.DS.$app_name)) {
                Dir::create(ROOT_PATH.'app'.DS.$app_name, 0777, true);
            }
            Dir::copyDir($app_path, './app/'.$app_name);
            if (!is_dir(ROOT_PATH.'static'.DS.'app_icon'.DS)) {
                Dir::create(ROOT_PATH.'static'.DS.'app_icon'.DS, 0755, true);
            }
            // 复制应用图标=
            if (file_exists($decom_path.'/upload/app/'.$app_name.'/'.$app_name.'.png')) {
                @copy($decom_path.'/upload/app/'.$app_name.'/'.$app_name.'.png', ROOT_PATH.'static'.DS.'app_icon'.DS.$app_name.'.png');
            }
            // 复制static目录
            if (is_dir($decom_path.'/upload/static')) {
                Dir::copyDir($decom_path.'/upload/static', ROOT_PATH.'static');
            }
            // 复制theme目录
            if (is_dir($decom_path.'/upload/theme')) {
                Dir::copyDir($decom_path.'/upload/theme', ROOT_PATH.'theme');
            }
            // 删除临时目录和安装包
            Dir::delDir($decom_path);
            @unlink($file);
            return $this->success('模块导入成功', url('index?status=0'));
        }

        $tab_data = $this->tab_data;
        $tab_data['current'] = 'admin/module/import';
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    /**
     * 卸载模块
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function uninstall($id = 0)
    {
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return $this->error('模块不存在');
        }
        if ($mod['status'] == 0) {
            return $this->error('模块未安装');
        }

        if ($this->request->isPost()) {
            $mod_path = APP_PATH.$mod['name'].DS;
            // 模块自定义配置
            if (!file_exists($mod_path.'info.php')) {
                return $this->error('模块配置文件不存在[info.php]');
            }
            $info = include_once $mod_path.'info.php';

            // 过滤系统表
            foreach ($info['tables'] as $t) {
                if (in_array($t, config('hs_system.tables'))) {
                    return $this->error('模块数据表与系统表重复['.$t.']');
                }
            }

            $post = $this->request->post();
            // 导入SQL
            $sql_file = realpath($mod_path.'sql'.DS.'uninstall.sql');
            if (file_exists($sql_file) && $post['clear'] == 1) {
                $sql = file_get_contents($sql_file);
                $sql_list = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
                if ($sql_list) {
                    $sql_list = array_filter($sql_list);
                    foreach ($sql_list as $v) {
                        // 防止删除整个数据库
                        if (stripos($v, config('database.database')) !== false) {
                            return $this->error('uninstall.sql文件含有数据库名['.config('database.database').']');
                        }
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
            // 删除当前模块菜单
            MenuModel::where('module', $mod['name'])->delete();
            // 删除模块钩子
            model('AdminHook')->where('source', 'module.'.$mod['name'])->delete();
            cache('hook_plugins', null);
            // 更新模块状态为未安装
            ModuleModel::where('id', $id)->update(['status' => 0, 'default' => 0, 'config' => '']);
            ModuleModel::moduleRoute(true);
            $this->success('模块已卸载成功', url('index?status=0'));
        }

        $this->assign('data_info', $mod);
        return $this->fetch();
    }

    /**
     * 删除模块
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del($id = 0)
    {
        $module = ModuleModel::where('id', $id)->find();
        if (!$module) {
            return $this->error('模块不存在');
        }
        if ($module['name'] == 'admin') {
            return $this->error('禁止删除系统模块');
        }
        if ($module['status'] != 0) {
            return $this->error('已安装的模块禁止删除');
        }

        // 删除模块文件
        $path = APP_PATH.$module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            return $this->error('模块删除失败['.$path.']');
        }

        // 删除模块路由
        $path = APP_PATH.$module['name'].'.php';
        if (is_file($path)) {
            @unlink($path);
        }

        // 删除模块模板
        $error = '';
        $path = ROOT_PATH.'theme'.DS.$module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            $error = '模块模板删除失败['.$path.']';
        }

        // 删除模块相关附件
        $path = ROOT_PATH.'static'.DS.$module['name'];
        if (is_dir($path) && Dir::delDir($path) === false) {
            $error .= '<br>模块删除失败['.$path.']';
        }

        // 删除模块记录
        ModuleModel::where('id', $id)->delete();
        // 删除菜单记录
        MenuModel::where('module', $module['name'])->delete();
        // 删除权限记录 TODO
        if ($error) {
            return $this->error($error);
        }

        return $this->success('模块删除成功');
    }

    /**
     * 设置默认模块
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setDefault()
    {
        $id = input('param.id/d');
        $val = input('param.val/d');
        if ($val == 1) {
            $res = ModuleModel::where('id', $id)->find();
            if ($res['system'] == 1) {
                return $this->error('禁止设置系统模块');
            }
            if ($res['status'] != 2) {
                return $this->error('禁止设置未启用或未安装的模块');
            }

            ModuleModel::where('id > 0')->setField('default', 0);
            ModuleModel::where('id', $id)->setField('default', 1);
        } else {
            ModuleModel::where('id', $id)->setField('default', 0);
        }
        return $this->success('操作成功');
    }

    /**
     * 状态设置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function status()
    {
        $val    = input('param.val/d');
        $id     = input('param.id/d');
        $val    = $val+1;// 因为layui开关效果只支持0和1

        if ($id == 1) {
            return $this->error('禁止设置系统模块');
        }
        $res = ModuleModel::where('id', $id)->find();

        if ($res['status'] <= 0) {
            return $this->error('只允许操作已安装模块');
        }

        $res = ModuleModel::where('id', $id)->setField('status', $val);
        if ($res === false) {
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }

    /**
     * 主题管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function theme($id = 0)
    {
        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }
        $path = ROOT_PATH.'theme'.DS.$module['name'].DS;
        if (!is_dir($path)) {
            return $this->error('模块主题不存在');
        }
        $theme = Dir::getList($path);
        $themes = [];
        foreach ($theme as $k => $v) {
            if (!is_file($path.$v.DS.'config.xml')) {
                continue;
            }
            $xml = file_get_contents($path.$v.DS.'config.xml');
            $themes[$k] = xml2array($xml);
            $themes[$k]['name'] = $v;
            $themes[$k]['thumb'] = ROOT_DIR.'theme/'.$module['name'].'/'.$v.'/thumb.png';
            if (!is_file($themes[$k]['thumb'])) {
                $themes[$k]['thumb'] = ROOT_DIR.'static/admin/image/theme.png';
            }
        }
        $this->assign('data_info', $module);
        $this->assign('data_list', $themes);
        return $this->fetch();
    }

    /**
     * 设置默认主题
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setDefaultTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->error('参数传递错误');
        }

        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }

        $res = ModuleModel::where('id', $id)->setField('theme', $theme);
        if (!$res) {
            return $this->error('设置默认主题失败');
        }
        return $this->success('设置成功');
    }

    /**
     * 删除主题
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delTheme($id = 0, $theme = '')
    {
        if (empty($theme)) {
            return $this->error('参数传递错误');
        }

        $module = ModuleModel::where(['id' => $id, 'status' => 2])->find();
        if (!$module) {
            return $this->error('模块不存在或未安装');
        }
        $path = ROOT_PATH.'theme'.DS.$module['name'].DS;
        Dir::delDir($path.$theme);
        return $this->success('删除成功');
    }

    /**
     * 模块打包下载
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function package($id = 0)
    {
        return $this->success('开发中...');
    }

    /**
     * 生成目录
     * @param array $list 目录列表
     * @author 橘子俊 <364666827@qq.com>
     */
    private function mkDir($list)
    {
        foreach ($list as $dir) {
            if (!is_dir(ROOT_PATH . $dir)) {
                Dir::create(ROOT_PATH.$dir);
            }
        }
    }

    /**
     * 添加模型菜单
     * @param array $data 菜单数据
     * @param string $mod 模型名称
     * @param int $pid 父ID
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */    
    private function addMenu($data = [], $mod = '', $pid = 0)
    {
        if (empty($data)) {
            return false;
        }
        foreach ($data as $v) {
            $v['pid'] = $pid;
            $childs = $v['childs'];
            unset($v['childs']);
            $res = model('AdminMenu')->storage($v);
            if (!$res) {
                return false;
            }
            if (!empty($childs)) {
                $this->addMenu($childs, $mod, $res['id']);
            }
        }
        return true;
    }

    /**
     * 检查表是否存在
     * @param array $list 目录列表
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    private function checkTable($tables = [])
    {
        $res = [];
        foreach ($tables as $k => $v) {
            $res[$k]['table'] = config('database.prefix').$v;
            $res[$k]['exist'] = '<span style="color:green">✔︎</span>';
            if (Db::query("SHOW TABLES LIKE '".config('database.prefix').$v."'")) {
                $res[$k]['exist'] = '<strong style="color:red">表名已存在</strong>'; 
            }
        }
        return $res;
    }

    /**
     * 生成模块信息文件
     * @author 橘子俊 <364666827@qq.com>
     */
    private function mkInfo($data = [])
    {
        // 配置内容
        $config = <<<INFO
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
/**
 * 模块基本信息
 */
return [
    // 模块名[必填]
    'name'        => '{$data['name']}',
    // 模块标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：模块名.[应用市场ID].module.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '{$data['icon']}',
    // 模块简介[选填]
    'intro' => '{$data['intro']}',
    // 开发者[必填]
    'author'      => '{$data['author']}',
    // 开发者网址[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => {$data['module_depend']},
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => {$data['plugin_depend']},
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => {$data['tables']},
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => '{$data['db_prefix']}',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => {$data['hooks']},
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => {$data['config']},
];
INFO;
        return file_put_contents(APP_PATH. $data['name'] . DS . 'info.php', $config);
    }
}