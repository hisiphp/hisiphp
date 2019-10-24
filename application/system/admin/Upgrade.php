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

use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use hisi\Cloud;
use hisi\Dir;
use hisi\PclZip;
use think\Db;
use Env;

/**
 * 在线升级控制器
 * @package app\system\admin
 */
class Upgrade extends Admin
{
    public $appType = 'system';
    public $identifier = 0;
    public $appVersion = '';
    protected function initialize() {
        parent::initialize();

        $this->rootPath         = Env::get('root_path');
        $this->appPath          = Env::get('app_path');
        $this->updatePath       = $this->rootPath.'backup/uppack/';
        $this->updateBackPath   = $this->rootPath.'backup/upback/';
        $this->cloud            = new Cloud(config('hs_cloud.identifier'), $this->updatePath);
        $this->appType          = $this->request->param('app_type/s', 'system');
        $this->identifier       = $this->request->param('identifier/s', 'system');
        $this->cacheUpgradeList = 'upgrade_version_list'.$this->identifier;
        $this->appKey           = '';

        $map = [];
        $map[] = ['identifier', '=', $this->identifier];
        $map[] = ['status', '<>', 0];

        switch ($this->appType) {
            case 'module':
                $this->appInfo      = ModuleModel::where($map)->find();
                $this->appKey       = $this->appInfo->app_keys;
                $this->appVersion   = $this->appInfo->version;
                break;

            case 'plugins':
                $this->appInfo      = PluginsModel::where($map)->find();
                $this->appKey       = $this->appInfo->app_keys;
                $this->appVersion   = $this->appInfo->version;
                break;

            case 'theme':
                $appName = $this->request->param('app_name');
                if ($appName) {
                    cookie('upgrade_app_name', $appName);
                }
                $this->appVersion = $this->request->param('app_version');
                break;
            
            default:
                $this->appVersion = config('hisiphp.version');
                break;
        }

        if (!$this->appVersion) {
            return $this->error('未安装的插件或模块禁止更新');
        }
    }
    
    /**
     * 首页
     * @author 橘子俊 <364666827@qq.com>
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $account = $this->request->post('account/s');
            $password = $this->request->post('password/s');

            $data               = [];
            $data['account']    = $account;
            $data['password']   = $password;
            $data['site_name']  = config('base.site_name');
            $data['version']    = config('hisiphp.version');
            
            $res = $this->cloud->data($data)->api('bind');

            if (isset($res['code']) && $res['code'] == 1) {
                // 缓存站点标识
                $file = $this->rootPath.'config/hs_cloud.php';
                $str = "<?php\n// 请妥善保管此文件，谨防泄漏\nreturn ['identifier' => '".$res['data']."'];\n";
                if (file_exists($file)) {
                    unlink($file);
                }

                file_put_contents($file, $str);

                if (!file_exists($file)) {
                    return $this->error('config/hs_cloud.php写入失败');
                }

                return $this->success('恭喜您，已成功绑定云平台账号');
            }

            return $this->error($res['msg'] ? $res['msg'] : '云平台绑定失败！(-0)');
        }
        $this->assign('api_url', $this->cloud->apiUrl());
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
            if (!config('hs_cloud.identifier')) {
                return $this->error('请绑定云平台');
            }
            
            $result = $this->getVersion();
            return json($result);
        }

        $this->assign('identifier', $this->identifier);
        $this->assign('app_type', $this->appType);
        $this->assign('app_version', $this->appVersion);
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
            return $this->error('参数传递错误');
        }

        if (empty($version)) {
            return $this->error('参数传递错误');
        }

        if (!is_dir($this->updatePath)) {
            Dir::create($this->updatePath, 0755);
        }

        $lock = $this->updatePath.$this->identifier.'upgrade.lock';
        if (!is_file($lock)) {
            file_put_contents($lock, time());
        } else {
            return $this->error('升级任务执行中，请手动删除此文件后重试！<br>文件地址：/uppack/'.$this->identifier.'upgrade.lock');
        }

        $versions = $this->getVersion();
        // 检查当前升级补丁前面是否还有未升级的补丁
        $tobe = [];
        $file = '';
        foreach ($versions['data'] as $k => $v) {
            if (version_compare($k, $version, '>=')) {
                if (version_compare($k, $version, '=')) {
                    $file = $this->cloud->data(['version' => $k, 'app_identifier' => $this->identifier, 'app_key' => $this->appKey])->down($this->appType.'/get/upgrade');
                }
                break;
            } else {
                $file = $this->cloud->data(['version' => $k, 'app_identifier' => $this->identifier, 'app_key' => $this->appKey])->down($this->appType.'/get/upgrade');
                if ($file === false) {
                    $this->clearCache($file);
                    return $this->error('前置版本 '.$k.' 升级失败');
                } else {
                    if (self::_install($file, $k, $this->appType) === false) {
                        $this->clearCache($file);
                        return $this->error($this->error);
                    }
                }
            }
        }

        if ($file === false || empty($file)) {
            $this->clearCache($file);
            return $this->error('获取升级包失败');
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
            return $this->error('参数传递错误');
        }
        $file = $this->updatePath.$file;
        if (!file_exists($file)) {
            $this->clearCache($file);
            return $this->error($version.' 升级包异常，请重新升级');
        }

        if (self::_install($file, $version, $this->appType) === false) {
            $this->clearCache($file);
            return $this->error($this->error);
        }
        $jumpUrl = '';
        if ($this->appType == 'theme') {
            $param                  = $this->request->param('');
            $param['app_version']   = $param['version'];
            $param['app_name']      = cookie('upgrade_app_name');
            unset($param['file'], $param['version']);
            $jumpUrl = url('lists?'.http_build_query($param));
        }
        return $this->success('升级包安装成功', $jumpUrl);
    }

    /**
     * 执行安装
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _install($file = '', $version = '', $app_type = 'system')
    {
        if (empty($file) || empty($version)) {
            $this->error = '参数传递错误';
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
        $_version = cache($this->cacheUpgradeList);
        $_version = $_version['data'];
        $md5file = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->updatePath);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($this->updateBackPath)) {
            Dir::create($this->updateBackPath);
        }
        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }
        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/backup/uppack]文件夹权限';
            return false;
        }
        // 备份需要升级的旧版本
        $upInfo = include_once $decomPath.'/upgrade.php';
        $backPath = $this->updateBackPath.config('hisiphp.version').'/';
        if (!is_dir($backPath)) {
            Dir::create($backPath, 0777);
        }

        array_push($upInfo['update'], '/version.php');
        //备份旧文件
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $_dir = $backPath.dirname($v).'/';
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777);
            }

            if ($v == '/composer.json') {
                $newComposer = json_decode(file_get_contents($decomPath.'/upload/composer.json'), 1);
                $oldComposer = json_decode(file_get_contents($this->rootPath.'composer.json'), 1);
                foreach($newComposer['require'] as $kk => $vv) {
                    $oldComposer['require'][$kk] = $vv;
                }
                @file_put_contents($decomPath.'/upload/composer.json', json_encode($oldComposer, 1));
            }

            if (is_file($this->rootPath.$v)) {
                @copy($this->rootPath.$v, $_dir.basename($v));
            }
        }

        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                $v = trim($v, '/');
                if (is_file($this->rootPath.$v)) {
                    @unlink($this->rootPath.$v);
                }
            }
        }
        // 更新升级文件
        Dir::copyDir($decomPath.'/upload', $this->rootPath);

        // 导入SQL
        $sqlFile = realpath($decomPath.'/database.sql');
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, ['hisiphp_' => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败';
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
        $backPath   = $this->updateBackPath.'module/'.$this->appInfo['name'].'/'.$this->appInfo['version'];
        $_version   = cache($this->cacheUpgradeList);
        $_version   = $_version['data'];
        $md5file    = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->updatePath);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($backPath)) {
            Dir::create($backPath);
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }

        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/backup/uppack]文件夹权限';
            return false;
        }
        // 获取本次升级信息
        if (!is_file($decomPath.'/upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整';
            return false;
        }

        $upInfo = include_once $decomPath.'/upgrade.php';
        //备份需要升级的旧版本
        if (isset($upInfo['update'])) {
            foreach ($upInfo['update'] as $k => $v) {
                $v  = trim($v, '/');
                $dir = $backPath.dirname($v).'/';
                if (!is_dir($dir)) {
                    Dir::create($dir, 0777);
                }
                if (is_file($this->rootPath.$v)) {
                    @copy($this->rootPath.$v, $dir.basename($v));
                }
            }
        }

        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                $v = trim($v, '/');
                // 锁定删除文件范围
                if ( ( substr($v, 0, strlen('application/'.$this->appInfo['name'])) == 'application/'.$this->appInfo['name'] ||
                    substr($v, 0, strlen('public/theme/'.$this->appInfo['name'])) == 'public/theme/'.$this->appInfo['name'] ||
                    substr($v, 0, strlen('public/static/'.$this->appInfo['name'])) == 'public/static/'.$this->appInfo['name'] ) && strpos($v, '..') === false) {
                    if (is_file($this->rootPath.$v)) {
                        @unlink($this->rootPath.$v);
                    }
                }
            }
        }

        //根据升级文件清单升级
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $dir = $this->rootPath.dirname($v).'/';
            if (!is_dir($dir)) {
                Dir::create($dir, 0777);
            }

            if (is_file($decomPath.'/upload/'.$v)) {
                @copy($decomPath.'/upload/'.$v, $dir.basename($v));
            }
        }

        // 读取模块info
        if (!is_file($this->appPath.$this->appInfo['name'].'/info.php')) {
            $this->error = $this->appInfo['name'].'模块配置文件[info.php]丢失';
            return false;
        }

        $moduleInfo = include_once $this->appPath.$this->appInfo['name'].'/info.php';
        if (!isset($moduleInfo['db_prefix']) || empty($moduleInfo['db_prefix'])) {
            $moduleInfo['db_prefix'] = 'db_';
        }

        // 整合模块配置
        $oldConfig = $newConfig = '';
        if (!empty($this->appInfo['config'])) {
            $oldConfig = json_decode($this->appInfo['config'], 1);
            sort($oldConfig);
            $oldColumn = array_column($oldConfig, 'name');
        }

        if (!empty($newConfig = $moduleInfo['config'])) {
            if (!empty($oldConfig)) {
                foreach ($newConfig as $k => &$v) {
                    $schKey = array_search($v['name'], $oldColumn);
                    if ($schKey !== false) {
                        $v['value'] = $oldConfig[$schKey]['value'];
                    }
                }
            }
            $newConfig = json_encode($newConfig, 1);
        }

        // 导入SQL
        $sqlFile = realpath($decomPath.'/database.sql');
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$moduleInfo['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败';
                        return false;
                    }
                }
            }
        }

        // 更新模块信息
        $this->appInfo->version = $version;
        $this->appInfo->config = $newConfig;
        $this->appInfo->save();
        $this->clearCache('', $version);
        ModuleModel::getConfig('', true);

        return true;
    }

    /**
     * 插件升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _pluginsInstall($file, $version)
    {
        $backPath   = $this->updateBackPath.'plugins/'.$this->appInfo['name'].'/'.$this->appInfo['version'];
        $_version   = cache($this->cacheUpgradeList);
        $_version   = $_version['data'];
        $md5file    = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->updatePath);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($backPath)) {
            Dir::create($backPath);
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }

        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/backup/uppack]文件夹权限';
            return false;
        }

        // 获取本次升级信息
        if (!is_file($decomPath.'/upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整';
            return false;
        }

        if (!is_dir($decomPath.'/upload/plugins/'.$this->appInfo['name'])) {
            $this->error = '升级失败，升级包文件不完整';
            return false;
        }

        if (!is_dir($this->rootPath.'plugins/'.$this->appInfo['name'])) {
            $this->error = '升级失败，插件目录不存在[/plugins/'.$this->appInfo['name'].']';
            return false;
        }

        // 读取插件info
        if (!is_file($this->rootPath.'plugins/'.$this->appInfo['name'].'/info.php')) {
            $this->error = $this->appInfo['name'].'插件配置文件[info.php]丢失';
            return false;
        }

        $upInfo = include_once $decomPath.'/upgrade.php';
        //备份需要升级的旧版本
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $_dir = $backPath.dirname($v).'/';
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777);
            }
            if (is_file($this->rootPath.$v)) {
                @copy($this->rootPath.$v, $_dir.basename($v));
            }
        }

        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                $v = trim($v, '/');
                if ( ( substr($v, 0, strlen('plugins/'.$this->appInfo['name'])) == 'plugins/'.$this->appInfo['name'] ||
                    substr($v, 0, strlen('public/static/plugins/'.$this->appInfo['name'])) == 'public/static/plugins/'.$this->appInfo['name'] ) && strpos($v, '..') === false) {
                    if (is_file($this->rootPath.$v)) {
                        @unlink($this->rootPath.$v);
                    }
                }
            }
        }

        //根据升级文件清单升级
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $dir = $this->rootPath.dirname($v).'/';
            if (!is_dir($dir)) {
                Dir::create($dir, 0777);
            }

            if (is_file($decomPath.'/upload/'.$v)) {
                @copy($decomPath.'/upload/'.$v, $dir.basename($v));
            }
        }

        $pluginsInfo = include_once $this->rootPath.'plugins/'.$this->appInfo['name'].'/info.php';
        if (!isset($pluginsInfo['db_prefix']) || empty($pluginsInfo['db_prefix'])) {
            $pluginsInfo['db_prefix'] = 'db_';
        }

        // 整合插件配置
        $oldConfig = $newConfig = '';
        if (!empty($this->appInfo['config'])) {
            $oldConfig = json_decode($this->appInfo['config'], 1);
            sort($oldConfig);
            $oldColumn = array_column($oldConfig, 'name');
        }

        if (!empty($newConfig = $pluginsInfo['config'])) {
            if (!empty($oldConfig)) {
                foreach ($newConfig as $k => &$v) {
                    $schKey = array_search($v['name'], $oldColumn);
                    if ($schKey !== false) {
                        $v['value'] = $oldConfig[$schKey]['value'];
                    }
                }
            }
            $newConfig = json_encode($newConfig, 1);
        }

        // 导入SQL
        $sqlFile = realpath($decomPath.'/database.sql');
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$pluginsInfo['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败';
                        return false;
                    }
                }
            }
        }

        // 更新插件信息
        $this->appInfo->version = $version;
        $this->appInfo->config = $newConfig;
        $this->appInfo->save();
        $this->clearCache('', $version);
        PluginsModel::getConfig('', true);

        return true;
    }

    /**
     * 主题升级
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    private function _themeInstall($file, $version)
    {
        $moduleName = cookie('upgrade_app_name');
        if (!$moduleName) {
            $this->error = '升级失败，请稍后在试';
            return false;
        }

        if (!strpos($this->identifier, '.')) {
            $this->error = '升级失败，参数传递错误';
            return false;
        }

        $identifier = explode('.', $this->identifier);
        $appName    = $identifier[0];

        if (!is_file('./theme/'.$moduleName.'/'.$appName.'/config.xml')) {
            $this->error = '升级失败，原版本缺少config.xml文件';
            return false;
        }

        $xml    = file_get_contents('./theme/'.$moduleName.'/'.$appName.'/config.xml');
        $config = xml2array($xml);

        if (!isset($config['identifier'])) {
            $this->error = '升级失败，原版本config.xml配置缺少identifier';
            return false;
        }

        if ($config['identifier'] != $this->identifier) {
            $this->error = '升级失败，异常请求';
            return false;
        }

        // 隐藏以下代码可以支持升降级
        // if (version_compare($config['version'], $version, '>=')) {
        //     $this->error = '升级失败，不支持降级！';
        //     return false;
        // }
        $backPath   = $this->updateBackPath.'theme/'.$moduleName.'/'.$appName.'/'.$this->appVersion;
        $_version   = cache($this->cacheUpgradeList);
        $_version   = $_version['data'];
        $md5file    = md5_file($file);
        // if($md5file != $_version[$version]['md5']) {
        //     Dir::delDir($this->updatePath);
        //     $this->error = '文件不完整，请重新升级！';
        //     return false;
        // }
        if (!is_dir($backPath)) {
            Dir::create($backPath);
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }

        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/../backup/uppack]文件夹权限';
            return false;
        }

        // 获取本次升级信息
        if (!is_file($decomPath.'/upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整';
            return false;
        }

        $upInfo = include_once $decomPath.'/upgrade.php';
        //备份需要升级的旧版本
        foreach ($upInfo['update'] as $k => $v) {
            $_dir = $backPath.dirname($v).'/';
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777);
            }
            if (is_file('./'.$v)) {
                @copy('./'.$v, $_dir.basename($v));
            }
        }

        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                if (substr($v, 0, strlen('/theme/'.$moduleName)) != '/theme/'.$moduleName || strpos($v, '..') !== false) {
                    $this->error = '升级补丁文件异常';
                    return false;
                }
                if (is_file('./'.$v)) {
                    @unlink('./'.$v);
                }
            }
        }

        // 复制升级文件
        Dir::copyDir($decomPath.'/upload/'.$appName, '.'.ROOT_DIR.'theme/'.$moduleName.'/'.$appName);
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
        $cache = cache($this->cacheUpgradeList);
        if (isset($cache['data']) && !empty($cache['data'])) {
            return $cache;
        }

        $result = $this->cloud->data(['version' => $this->appVersion, 'app_identifier' => $this->identifier, 'app_key' => $this->appKey])->api($this->appType.'/get/versions');
        if ($result['code'] == 1) {
            cache($this->cacheUpgradeList, $result, 3600);  
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
        if (is_file($this->updatePath.$this->identifier.'upgrade.lock')) {
            unlink($this->updatePath.$this->identifier.'upgrade.lock');
        }

        if (is_file($file)) {
            unlink($file);
        }

        // 在升级缓存列表里面清除已升级的版本信息
        if ($version) {
            $versionCache = cache($this->cacheUpgradeList);
            unset($versionCache['data'][$version]);
            cache($this->cacheUpgradeList, $versionCache, 3600);
        }

        // 删除升级解压文件
        if (is_dir($this->updatePath)) {
            Dir::delDir($this->updatePath);
        }

        // 删除系统缓存
        Dir::delDir(Env::get('runtime_path').'cache');
        Dir::delDir(Env::get('runtime_path').'temp');
    }
}
