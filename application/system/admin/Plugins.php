<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------

namespace app\system\admin;

use app\system\model\SystemPlugins as PluginsModel;
use app\system\model\SystemHookPlugins as HookPluginsModel;
use app\system\model\SystemMenu as MenuModel;
use think\Db;
use hisi\Dir;
use hisi\PclZip;
use think\facade\Env;
use think\facade\Log;

/**
 * 插件管理控制器
 * @package app\system\admin
 */
class Plugins extends Admin
{
    public $tabData = [];
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $tabData['menu'] = [
            [
                'title' => '已启用',
                'url' => 'system/plugins/index?status=2',
            ],
            [
                'title' => '已停用',
                'url' => 'system/plugins/index?status=1',
            ],
            [
                'title' => '待安装',
                'url' => 'system/plugins/index?status=0',
            ],
            [
                'title' => '导入插件',
                'url' => 'system/plugins/import',
            ],
        ];

        if (config('sys.app_debug') == 1) {
            array_push($tabData['menu'], ['title' => '生成插件', 'url' => 'system/plugins/design',]);
        }
        
        $this->tabData = $tabData;
    }

    /**
     * 插件管理首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        $status = $this->request->param('status/d', 2);
        $tabData = $this->tabData;
        $tabData['current'] = url('?status='.$status);
        $map            = [];
        $map['status']  = $status;
        $map['system']  = 0;
        $plugins = PluginsModel::where($map)->order('sort,id')->column('id,title,author,intro,icon,system,app_keys,identifier,name,version,config,status');
        if ($status == 0) {
            $pluginsPath = Env::get('root_path').'plugins/';
            // 自动将本地未入库的插件导入数据库
            $allPlugins = PluginsModel::order('sort,id')->column('id,name', 'name');
            $files = Dir::getList($pluginsPath);
            foreach ($files as $k => $f) {
                // 排除已存在数据库的插件
                if (array_key_exists($f, $allPlugins) || !is_dir($pluginsPath.$f)) {
                    continue;
                }
                if (file_exists($pluginsPath.$f.'/info.php')) {
                    $info = include_once $pluginsPath.$f.'/info.php';
                    $sql                = [];
                    $sql['name']        = $info['name'];
                    $sql['identifier']  = $info['identifier'];
                    $sql['title']       = $info['title'];
                    $sql['intro']       = $info['intro'];
                    $sql['author']      = $info['author'];
                    $sql['icon']        = ROOT_DIR.substr($info['icon'], 1);
                    $sql['version']     = $info['version'];
                    $sql['url']         = $info['author_url'];
                    $sql['config']      = '';
                    $sql['status']      = 0;
                    $sql['system']      = 0;
                    $sql['app_keys']    = '';
                    $db = PluginsModel::create($sql);
                    $sql['id'] = $db->id;
                    $plugins = array_merge($plugins, [$sql]);
                }
            }
        }

        $this->assign('emptyTips', '<tr><td colspan="5" align="center" height="100">未发现相关模块，快去<a href="'.url('store/index').'?type=2"> <strong style="color:#428bca">应用市场</strong> </a>看看吧！</td></tr>');
        $this->assign('data_list', array_values($plugins));
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch();
    }

    /**
     * 插件设计
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function design()
    {
        if (config('sys.app_debug') == 0) {
            return $this->error('非开发模式禁止使用此功能');
        }

        if ($this->request->isPost()) {
            $model = new PluginsModel();
            $data = $this->request->post();
            $result = $this->validate($data, 'app\system\validate\SystemPlugins');
            if ($result !== true) {
                return $this->error($result);
            }

            if (!$model->design($data)) {
                return $this->error($model->getError());
            }
            return $this->success('插件已生成完毕', url('index?status=0'));
        }

        $tabData = [];
        $tabData['menu'] = [
            ['title' => '插件设计'],
            ['title' => '插件配置'],
            // ['title' => '插件菜单'],
        ];

        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 2);
        return $this->fetch();
    }

    /**
     * 插件配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function setting($id = 0)
    {
        $where = [];
        if (is_numeric($id)) {
            $where[] = ['id', '=', $id];
        } else {
            $where[] = ['name', '=', $id];
        }

        $row = PluginsModel::where($where)->field('id,name,config,title')->find()->toArray();
        $pluginsInfo = plugins_info($row['name']);
        if (!$row['config'] && !$pluginsInfo['config']) {
            return $this->error('此插件无需配置');
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
            $postData = $this->request->post();
            foreach ($row['config'] as &$conf) {
                $conf['value'] = isset($postData[$conf['name']]) ? $postData[$conf['name']] : '';
            }
            if (PluginsModel::where('id', $id)->setField('config', json_encode($row['config'], 1)) === false) {
                return $this->error('配置保存失败');
            }
            PluginsModel::getConfig('', true);
            return $this->success('配置保存成功');
        }
        $this->assign('formData', $row);
        return $this->fetch();
    }

    /**
     * 安装插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function install()
    {
        $id = get_num();
        $result = $this->execInstall($id);
        if ($result !== true) {
            return $this->error($result);
        }
        return $this->success('插件已安装成功', url('index?status=2'));
    }

    /**
     * 执行插件安装
     * @date   2018-11-01
     * @access public
     * @author 橘子俊 364666827@qq.com
     * @param  int $id  模块ID
     * @return bool|string  
     */
    public function execInstall($id)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return '插件不存在';
        }

        if ($plug['status'] > 0) {
            return '请勿重复安装此插件';
        }

        $plugPath = Env::get('root_path').'plugins/'.$plug['name'].'/';
        if (!file_exists($plugPath.'info.php')) {
            return '插件文件[info.php]丢失';
        }

        $info       = include_once $plugPath.'info.php';
        $plugClass  = get_plugins_class($plug['name']);
        if (!class_exists($plugClass)) {
            return '插件不存在';
        }

        $plugObj = new $plugClass;
        
        if(!$plugObj->install()) {
            return '插件安装前的方法执行失败（原因：'. $plugObj->getError().'）';
        }

        // 将插件钩子注入到钩子索引表
        if (isset($plugObj->hooks) && !empty($plugObj->hooks)) {
            if (!HookPluginsModel::storage($plugObj->hooks, $plug['name'])) {
                return '安装插件钩子时出现错误，请重新安装';
            }
            cache('hook_plugins', null);
        }

        // 导入SQL
        $sqlFile = realpath($plugPath.'sql/install.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('hs_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'install.sql文件含有系统表['.$t.']';
                        }
                    }
                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入菜单
        if ( file_exists($plugPath.'menu.php') ) {
            $menus = include_once $plugPath.'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, 1);
            }
            if (MenuModel::importMenu($menus, 'plugins.'.$plug['name'], 'plugins') == false) {
                // 执行回滚
                MenuModel::where('module', 'plugins.'.$plug['name'])->delete();
                return '插件菜单失败(原因：可能是param参数异常)，请重新安装！';
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
        
        if(!$plugObj->installAfter()) {
            return '插件安装前的方法执行失败（原因：'. $plugObj->getError().'）';
        }

        return true;
    }

    /**
     * 卸载插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function uninstall($id = 0)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return $this->error('插件不存在');
        }
        if ($plug['status'] == 0) {
            return $this->error('插件未安装');
        }

        $plugPath = Env::get('root_path').'plugins/'.$plug['name'].'/';
        
        // 插件基本信息
        if (!file_exists($plugPath.'info.php')) {
            return $this->error('插件文件[info.php]丢失');
        }
        $info = include_once $plugPath.'info.php';

        $plugClass = get_plugins_class($plug['name']);
        if (!class_exists($plugClass)) {
            return $this->error('插件不存在');
        }

        $plugObj = new $plugClass;

        if(!$plugObj->uninstall()) {
            return $this->error('插件卸载前的方法执行失败（原因：'. $plugObj->getError().'）');
        }

        if (!HookPluginsModel::del($plug['name'])) {
            return $this->error('插件相关钩子删除失败');
        }
        cache('hook_plugins', null);

        // 导入SQL
        $sqlFile = realpath($plugPath.'sql/uninstall.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 防止删除整个数据库
                    if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                        return $this->error('uninstall.sql文件疑似含有删除数据库的SQL');
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
                        return $e->getMessage();
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
        
        if(!$plugObj->uninstallAfter()) {
            return $this->error('插件卸载后的方法执行失败（原因：'. $plugObj->getError().'）');
        }

        return $this->success('插件已卸载成功', url('index?status=0'));
    }

    /**
     * 导入插件
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            if (empty($file)) {
                return $this->error('请上传模块安装包');
            }

            if (!$file->checkExt('zip')) {
                return $this->error('请上传 ZIP 格式的安装包');
            }

            $basePath = './upload/temp/file/';
            $file = $file->rule('md5')->move($basePath);
            $file = $basePath.$file->getSaveName();

            if (ROOT_DIR != '/') {// 针对子目录处理
                $file = realpath(str_replace(ROOT_DIR, '/', $file));
            }
            
            if (!file_exists($file)) {
                return $this->error('上传文件无效');
            }
            
            $decomPath = '.'.trim($file, '.zip');
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }
            
            // 解压安装包到$decomPath
            $archive = new PclZip();
            $archive->PclZip($file);
            if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败('.$archive->error_string.')');
            }

            // 获取插件名
            $files = Dir::getList($decomPath.'/upload/plugins/');

            if (!isset($files[0])) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('导入失败，安装包不完整');
            }

            $appName = $files[0];

            // 防止重复导入插件
            if (is_dir(Env::get('root_path').'plugins/'.$appName)) {
                Dir::delDir($decomPath);
                @unlink($file);
                return $this->error('插件已存在');
            } else {
                Dir::create(Env::get('root_path').'plugins/'.$appName, 0777);
            }

            // 复制插件
            Dir::copyDir($decomPath.'/upload/plugins/'.$appName.'/', Env::get('root_path').'plugins/'.$appName);

            // 文件安全检查
            $safeCheck = Dir::safeCheck(Env::get('root_path').'plugins/'.$appName);
            if ($safeCheck) {
                foreach($safeCheck as $v) {
                    Log::warning('文件 '. $v['file'].' 含有危险函数：'.str_replace('(', '', implode(',', $v['function'])));
                }
            }

            // 复制静态资源
            Dir::copyDir($decomPath.'/upload/public/static/'.$appName, './static/plugins/'.$appName);

            // 删除临时目录和安装包
            Dir::delDir($decomPath);
            @unlink($file);

            $this->success($safeCheck ? '插件导入成功，部分文件可能存在安全风险，请查看系统日志' : '插件导入成功', url('index?status=0'));
        }

        $tabData = $this->tabData;
        $tabData['current'] = 'system/plugins/import';
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch();
    }

    /**
     * 状态设置
     * @author 橘子俊 <364666827@qq.com>
     */
    public function status()
    {
        $val    = $this->request->param('val/d');
        $id     = $this->request->param('id/d');
        $val    = $val+1;// 因为layui开关效果只支持0和1

        $res = PluginsModel::where('id', $id)->find();

        if ($res['status'] <= 0) {
            return $this->error('只允许操作已安装插件');
        }

        $res = PluginsModel::where('id', $id)->setField('status', $val);
        if ($res === false) {
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }

    /**
     * 删除插件
     * @author 橘子俊 <364666827@qq.com>
     */
    public function del($id = 0)
    {
        $plug = PluginsModel::where('id', $id)->find();
        if (!$plug) {
            return $this->error('插件不存在');
        }
        if ($plug['status'] != 0) {
            return $this->error('请先卸载插件['.$plug['name'].']！');
        }
        if (Dir::delDir(ROOT_PATH.'plugins/'.$plug['name']) === false) {
            return $this->error('插件目录失败(原因：可能没有权限)！');
        }
        // 删除插件静态资源目录
        Dir::delDir('.'.ROOT_DIR.'static/plugins/'.$plug['name']);

        if (!PluginsModel::where('id', $id)->delete()) {
            return $this->error('插件数据删除失败');
        }

        return $this->success('插件删除成功');
    }

    /**
     * 执行内部插件
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function run() {
        $plugin     = $_GET['_p'] = $this->request->param('_p');
        $controller = $_GET['_c'] = ucfirst($this->request->param('_c', 'Index'));
        $action     = $_GET['_a'] = $this->request->param('_a', 'index');
        $params     = $this->request->except(['_p', '_c', '_a'], 'param');

        if (empty($plugin)) {
            return $this->error('插件名必须传入[_p]');
        }
            
        if (!PluginsModel::where(['name' => $plugin, 'status' => 2])->find() ) {
            return $this->error("插件可能不存在或者未安装");
        }

        if (!plugins_action_exist($plugin.'/'.$controller.'/'.$action)) {
            return $this->error("找不到插件方法：{$plugin}/{$controller}/{$action}");
        }
        return plugins_run($plugin.'/'.$controller.'/'.$action, $params);
    }
}
