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
use app\admin\model\AdminModule as ModuleModel;
use app\admin\model\AdminPlugins as PluginsModel;
use app\common\util\Cloud;
use app\common\util\Dir;
use app\common\util\PclZip;
use think\Db;
set_time_limit(0);
/**
 * 在线升级控制器
 * @package app\admin\controller
 */
class Upgrade extends Admin
{
    public $app_type = 'system';
    public $identifier = 0;
    public $app_version = '';
    protected function _initialize() {
        parent::_initialize();
        $this->update_path = ROOT_PATH.'backup'.DS.'uppack'.DS;
        $this->update_back_path = ROOT_PATH.'backup'.DS.'upback'.DS;
        $this->cloud = new Cloud(config('hs_cloud.identifier'), $this->update_path);
        $this->app_type = input('param.app_type/s', 'system');
        $this->identifier = input('param.identifier', 0);
        $this->cache_upgrade_list = 'upgrade_version_list'.$this->identifier;
        $this->app_key = '';
        $map = [];
        $map['identifier'] = $this->identifier;
        $map['status'] = ['neq', 0];
        switch ($this->app_type) {
            case 'module':
                $module = ModuleModel::where($map)->find();
                $this->app_key = $module->app_keys;
                $this->app_version = $module->version;
                break;

            case 'plugins':
                $plugins = PluginsModel::where($map)->find();
                $this->app_key = $module->app_keys;
                $this->app_version = $plugins->version;
                break;

            case 'theme':
                $app_name = input('param.app_name');
                if ($app_name) {
                    cookie('upgrade_app_name', $app_name);
                }
                $this->app_version = input('param.app_version');
                break;
            
            default:
                $this->app_version = config('hisiphp.version');
                break;
        }
        if (!$this->app_version) {
            return $this->error('未安装的插件或模块禁止更新！');
        }
    }
    
    /**
     * 首页
     * @author 橘子俊 <364666827@qq.com>
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $account = input('post.account/s');
            $password = input('post.password/s');
            $data = [];
            $data['account'] = $account;
            $data['password'] = $password;
            $data['site_name'] = config('base.site_name');
            $data['version'] = config('hisiphp.version');
            $res = $this->cloud->data($data)->api('bind');
            if (isset($res['code']) && $res['code'] == 1) {
                // 缓存站点标识
                $str = "<?php\nreturn ['identifier' => '".$res['data']."'];\n";
                file_put_contents(APP_PATH.'extra'.DS.'hs_cloud.php', $str);
                if (is_file(APP_PATH.'extra'.DS.'hs_cloud.php')) {
                    $cloud = include_once APP_PATH.'extra'.DS.'hs_cloud.php';
                    if (isset($cloud['identifier']) && !empty($cloud['identifier'])) {
                        return $this->success('恭喜您，已成功绑定云平台账号。');
                    }
                }
                return $this->error('extra'.DS.'hs_cloud.php写入失败！');
            }
            return $this->error($res['msg'] ? $res['msg'] : '云平台绑定失败！(-0)');
        }
        $this->assign('api_url', $this->cloud->apiUrl());
        $this->assign('tab_type', 3);
        return $this->fetch();
    }

    /**
     * 升级文件列表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isPost()) {
            $result = $this->getVersion();
            return json($result);
        }
        $this->assign('identifier', $this->identifier);
        $this->assign('app_type', $this->app_type);
        $this->assign('app_version', $this->app_version);
        return $this->fetch();
    }

    /**
     * 下载升级包
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function download($version = '')
    {
        if (!$this->request->isPost()) {
            return $this->error('参数传递错误！');
        }
        if (empty($version)) {
            return $this->error('参数传递错误！');
        }
        if (!is_dir($this->update_path)) {
            Dir::create($this->update_path, 0755, true);
        }
        $lock = $this->update_path.$this->identifier.'upgrade.lock';
        if (!is_file($lock)) {
            file_put_contents($lock, time());
        } else {
            return $this->error('升级任务执行中，请手动删除此文件后重试！<br>文件地址：/backup/uppack/'.$this->identifier.'upgrade.lock');
        }

        $versions = $this->getVersion();
        // 检查当前升级补丁前面是否还有未升级的补丁
        $tobe = [];
        $file = '';
        foreach ($versions['data'] as $k => $v) {
            if (version_compare($k, $version, '>=')) {
                if (version_compare($k, $version, '=')) {
                    $file = $this->cloud->data(['version' => $k, 'app_identifier' => $this->identifier, 'app_key' => $this->app_key])->down($this->app_type.'/get/upgrade');
                }
                break;
            } else {
                $file = $this->cloud->data(['version' => $k, 'app_identifier' => $this->identifier, 'app_key' => $this->app_key])->down($this->app_type.'/get/upgrade');
                if ($file === false) {
                    $this->clearCache($file);
                    return $this->error('前置版本 '.$k.' 升级失败！');
                } else {
                    if (self::_install($file, $k, $this->app_type) === false) {
                        $this->clearCache($file);
                        return $this->error($this->error);
                    }
                }
            }
        }

        if ($file === false || empty($file)) {
            $this->clearCache($file);
            return $this->error('获取升级包失败！');
        }
        return $this->success(basename($file));
    }

    /**
     * 安装方法
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function install($file = '', $version = '')
    {
        if (!$this->request->isPost()) {
            return $this->error('参数传递错误！');
        }
        $file = $this->update_path.$file;
        if (!file_exists($file)) {
            $this->clearCache($file);
            return $this->error($version.' 升级包异常，请重新升级！');
        }

        if (self::_install($file, $version, $this->app_type) === false) {
            $this->clearCache($file);
            return $this->error($this->error);
        }
        $jump_url = '';
        if ($this->app_type == 'theme') {
            $param = input('param.');
            $param['app_version'] = $param['version'];
            $param['app_name'] = cookie('upgrade_app_name');
            unset($param['file'], $param['version']);
            $jump_url = url('lists?'.http_build_query($param));
        }
        return $this->success('升级包安装成功。', $jump_url);
    }

    /**
     * 执行安装
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _install($file = '', $version = '', $app_type = 'system')
    {
        if (empty($file) || empty($version)) {
            $this->error = '参数传递错误！';
            return false;
        }
        switch ($app_type) {
            case 'module':// 模块升级安装
                return self::_moduleInstall($file, $version);
                break;
            case 'plugins':// 插件升级安装
                return self::_pluginsInstall($file, $version);
                break;
            case 'theme':// 主题升级安装
                return self::_themeInstall($file, $version);
                break;
            default:// 系统升级安装
                return self::_systemInstall($file, $version);
                break;
        }
        clearstatcache();
    }

    /**
     * 系统升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _systemInstall($file, $version)
    {
        $_version = cache($this->cache_upgrade_list);
        $_version = $_version['data'];
        $md5file = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->update_path);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($this->update_back_path)) {
            Dir::create($this->update_back_path);
        }
        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[backup/uppack]文件夹权限！';
            return false;
        }
        // 备份需要升级的旧版本
        $up_info = include_once $decom_path.DS.'upgrade.php';
        $back_path = $this->update_back_path.config('hisiphp.version');
        if (!is_dir($back_path)) {
            Dir::create($back_path, 0777, true);
        }
        $layout = '';
        array_push($up_info['update'], '/version.php');
        //备份旧文件
        foreach ($up_info['update'] as $k => $v) {
            $_dir = $back_path.dirname($v).DS;
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777, true);
            }
            if (basename($v) == 'layout.php') {
                $layout = APP_PATH.'admin'.DS.'view'.DS.'layout.php';
            }
            if (is_file('.'.ROOT_DIR.$v)) {
                @copy('.'.ROOT_DIR.$v, $_dir.basename($v));
            }
        }

        // 根据升级补丁删除文件
        if (isset($up_info['delete'])) {
            foreach ($up_info['delete'] as $k => $v) {
                if (is_file('.'.ROOT_DIR.$v)) {
                    @unlink('.'.ROOT_DIR.$v);
                }
            }
        }

        // 更新升级文件
        Dir::copyDir($decom_path.DS.'upload', '.');

        // 同步更新扩展模块下的layout.php TODO
        // 导入SQL
        $sql_file = realpath($decom_path.DS.'database.sql');
        if (is_file($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, 0, ['hisiphp_' => config('database.prefix')]);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败！';
                        return false;
                    }
                }
            }
        }
        $this->clearCache('', $version);
        return true;
    }

    /**
     * 模块升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _moduleInstall($file, $version)
    {
        $module = ModuleModel::where('identifier', $this->identifier)->find();
        $back_path = $this->update_back_path.'module'.DS.$module->name.DS.$module->version;
        $_version = cache($this->cache_upgrade_list);
        $_version = $_version['data'];
        $md5file = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->update_path);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($back_path)) {
            Dir::create($back_path);
        }
        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[backup/uppack]文件夹权限！';
            return false;
        }
        // 获取本次升级信息
        if (!is_file($decom_path.DS.'upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整！';
            return false;
        }
        $up_info = include_once $decom_path.DS.'upgrade.php';
        //备份需要升级的旧版本
        if (isset($up_info['update'])) {
            foreach ($up_info['update'] as $k => $v) {
                $_dir = $back_path.dirname($v).DS;
                if (!is_dir($_dir)) {
                    Dir::create($_dir, 0777, true);
                }
                if (is_file('.'.ROOT_DIR.$v)) {
                    @copy('.'.ROOT_DIR.$v, $_dir.basename($v));
                }
            }
        }
        // 根据升级补丁删除文件
        if (isset($up_info['delete'])) {
            foreach ($up_info['delete'] as $k => $v) {
                // 锁定删除文件范围
                if ( (substr($v, 0, strlen('/app/'.$module->name)) == '/app/'.$module->name ||
                    substr($v, 0, strlen('/theme/'.$module->name)) == '/theme/'.$module->name ||
                    substr($v, 0, strlen('/static/'.$module->name)) == '/static/'.$module->name) && strpos($v, '..') === false) {
                    $v = trim($v, '/');
                    if (is_file('.'.ROOT_DIR.$v)) {
                        @unlink('.'.ROOT_DIR.$v);
                    }
                }
            }
        }
        // 复制app目录
        if (is_dir($decom_path.DS.'upload'.DS.'app'.DS.$module->name)) {
            Dir::copyDir($decom_path.DS.'upload'.DS.'app'.DS.$module->name, '.'.ROOT_DIR.'app'.DS.$module->name);
        }
        // 复制static目录
        if (is_dir($decom_path.DS.'upload'.DS.'static')) {
            Dir::copyDir($decom_path.DS.'upload'.DS.'static'.DS.$module->name, '.'.ROOT_DIR.'static'.DS.$module->name);
        }
        // 复制theme目录
        if (is_dir($decom_path.DS.'upload'.DS.'theme')) {
            Dir::copyDir($decom_path.DS.'upload'.DS.'theme'.DS.$module->name, '.'.ROOT_DIR.'theme'.DS.$module->name);
        }
        // 读取模块info
        if (!is_file(APP_PATH.$module->name.DS.'info.php')) {
            $this->error = $module->name.'模块配置文件[info.php]丢失！';
            return false;
        }
        $module_info = include_once APP_PATH.$module->name.DS.'info.php';
        if (!isset($module_info['db_prefix']) || empty($module_info['db_prefix'])) {
            $module_info['db_prefix'] = 'db_';
        }
        // 导入SQL
        $sql_file = realpath($decom_path.DS.'database.sql');
        if (is_file($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, 0, [$module_info['db_prefix'] => config('database.prefix')]);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败！';
                        return false;
                    }
                }
            }
        }
        // 更新模块版本信息
        ModuleModel::where('identifier', $this->identifier)->setField('version', $version);
        $this->clearCache('', $version);
        return true;
    }

    /**
     * 插件升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _pluginsInstall($file, $version)
    {
        $plugins = PluginsModel::where('identifier', $this->identifier)->find();
        $back_path = $this->update_back_path.'plugins'.DS.$plugins->name.DS.$plugins->version;
        $_version = cache($this->cache_upgrade_list);
        $_version = $_version['data'];
        $md5file = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->update_path);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($back_path)) {
            Dir::create($back_path);
        }
        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[backup/uppack]文件夹权限！';
            return false;
        }
        // 获取本次升级信息
        if (!is_file($decom_path.DS.'upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整！';
            return false;
        }
        $up_info = include_once $decom_path.DS.'upgrade.php';
        //备份需要升级的旧版本
        $plugins_path = '.'.ROOT_DIR.'plugins/'.$plugins->name.'/';
        foreach ($up_info['update'] as $k => $v) {
            $v = trim($v, '/');
            $_dir = $back_path.dirname($v).DS;
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777, true);
            }
            if (is_file($plugins_path.$v)) {
                @copy($plugins_path.$v, $_dir.basename($v));
            }
        }
        // 根据升级补丁删除文件
        if (isset($up_info['delete'])) {
            foreach ($up_info['delete'] as $k => $v) {
                if (strpos($v, '..') === false) {
                    $v = trim($v, '/');
                    if (is_file($plugins_path.$v)) {
                        @unlink($plugins_path.$v);
                    }
                }
            }
        }

        if (!is_dir($decom_path.DS.'upload'.DS.$plugins->name)) {
            $this->error = '升级失败，升级包文件不完整！';
            return false;
        }
        if (!is_dir('.'.ROOT_DIR.'plugins'.DS.$plugins->name)) {
            $this->error = '升级失败，插件目录不存在['.ROOT_DIR.'plugins'.DS.$plugins->name.']！';
            return false;
        }
        // 复制插件目录
        Dir::copyDir($decom_path.DS.'upload'.DS.$plugins->name, '.'.ROOT_DIR.'plugins'.DS.$plugins->name);

        // 读取插件info
        if (!is_file('.'.ROOT_DIR.'plugins'.DS.$plugins->name.DS.'info.php')) {
            $this->error = $plugins->name.'插件配置文件[info.php]丢失！';
            return false;
        }
        $plugins_info = include_once ROOT_PATH.'plugins'.DS.$plugins->name.DS.'info.php';
        if (!isset($plugins_info['db_prefix']) || empty($plugins_info['db_prefix'])) {
            $plugins_info['db_prefix'] = 'db_';
        }
        // 导入SQL
        $sql_file = realpath($decom_path.DS.'database.sql');
        if (is_file($sql_file)) {
            $sql = file_get_contents($sql_file);
            $sql_list = parse_sql($sql, 0, [$plugins_info['db_prefix'] => config('database.prefix')]);
            if ($sql_list) {
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败！';
                        return false;
                    }
                }
            }
        }
        // 更新模块版本信息
        PluginsModel::where('identifier', $this->identifier)->setField('version', $version);
        $this->clearCache('', $version);
        return true;
    }

    /**
     * 主题升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _themeInstall($file, $version)
    {
        $module_name = cookie('upgrade_app_name');
        if (!$module_name) {
            $this->error = '升级失败，请稍后在试！';
            return false;
        }
        if (!strpos($this->identifier, '.')) {
            $this->error = '升级失败，参数传递错误！';
            return false;
        }
        $identifier = explode('.', $this->identifier);
        $app_name = $identifier[0];
        if (!is_file('.'.DS.'theme'.DS.$module_name.DS.$app_name.DS.'config.xml')) {
            $this->error = '升级失败，原版本缺少config.xml文件！';
            return false;
        }
        $xml = file_get_contents('.'.DS.'theme'.DS.$module_name.DS.$app_name.DS.'config.xml');
        $config = xml2array($xml);
        if (!isset($config['identifier'])) {
            $this->error = '升级失败，原版本config.xml配置缺少identifier！';
            return false;
        }
        if ($config['identifier'] != $this->identifier) {
            $this->error = '升级失败，异常请求！';
            return false;
        }
        // 隐藏以下代码可以支持升降级
        // if (version_compare($config['version'], $version, '>=')) {
        //     $this->error = '升级失败，不支持降级！';
        //     return false;
        // }
        $back_path = $this->update_back_path.'theme'.DS.$module_name.DS.$app_name.DS.$this->app_version;
        $_version = cache($this->cache_upgrade_list);
        $_version = $_version['data'];
        $md5file = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->update_path);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($back_path)) {
            Dir::create($back_path);
        }
        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[backup/uppack]文件夹权限！';
            return false;
        }
        // 获取本次升级信息
        if (!is_file($decom_path.DS.'upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整！';
            return false;
        }
        $up_info = include_once $decom_path.DS.'upgrade.php';
        //备份需要升级的旧版本
        foreach ($up_info['update'] as $k => $v) {
            $_dir = $back_path.dirname($v).DS;
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777, true);
            }
            if (is_file('./'.$v)) {
                @copy('./'.$v, $_dir.basename($v));
            }
        }
        // 根据升级补丁删除文件
        if (isset($up_info['delete'])) {
            foreach ($up_info['delete'] as $k => $v) {
                if (substr($v, 0, strlen('/theme/'.$module_name)) != '/theme/'.$module_name || strpos($v, '..') !== false) {
                    $this->error = '升级补丁文件异常';
                    return false;
                }
                if (is_file('./'.$v)) {
                    @unlink('./'.$v);
                }
            }
        }
        // 复制升级文件
        Dir::copyDir($decom_path.DS.'upload'.DS.$app_name, '.'.ROOT_DIR.'theme'.DS.$module_name.DS.$app_name);
        $this->clearCache('', $version);
        return true;
    }

    /**
     * 获取升级版本
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    private function getVersion()
    {
        $cache = cache($this->cache_upgrade_list);
        if (isset($cache['data']) && !empty($cache['data'])) {
            return $cache;
        }
        $result = $this->cloud->data(['version' => $this->app_version, 'app_identifier' => $this->identifier, 'app_key' => $this->app_key])->api($this->app_type.'/get/versions');
        if ($result['code'] == 1) {
            cache($this->cache_upgrade_list, $result, 3600);  
        }
        return $result;
    }

    /**
     * 清理升级包、升级锁、升级版本列表、升级解压文件
     * @param string $file 升级包文件路径
     * @param string $version 当前升级版本号
     * @author 橘子俊 <364666827@qq.com>
     */
    private function clearCache($file = '', $version = '')
    {
        if (is_file($this->update_path.$this->identifier.'upgrade.lock')) {
            unlink($this->update_path.$this->identifier.'upgrade.lock');
        }
        if (is_file($file)) {
            unlink($file);
        }
        // 在升级缓存列表里面清除已升级的版本信息
        if ($version) {
            $version_cache = cache($this->cache_upgrade_list);
            unset($version_cache['data'][$version]);
            cache($this->cache_upgrade_list, $version_cache, 3600);
        }
        // 删除升级解压文件
        if (is_dir($this->update_path)) {
            Dir::delDir($this->update_path);
        }
        // 删除系统缓存
        Dir::delDir(RUNTIME_PATH.'cache');
        Dir::delDir(RUNTIME_PATH.'temp');
    }
}