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

namespace app\system\home;

use app\common\controller\Common;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use hisi\Cloud as CloudApi;
use hisi\Dir;
use hisi\PclZip;
use Env;

ignore_user_abort(true);

/**
 * 应用市场推送
 * @package app\system\home
 */
class Push extends Common
{
    protected function initialize() {
        if (!config('sys.cloud_push')) {
            exit('{"code":0,"msg":"您的站点已关闭云端推送功能","data":[]}');
        }

        parent::initialize();
        $this->appPath      = Env::get('app_path');
        $this->updatePath   = ROOT_PATH.'backup/uppack/';
        $this->cloud        = new CloudApi(config('hs_cloud.identifier'), $this->updatePath);
        $this->sign         = $this->request->param('sign');
        $this->token        = $this->request->param('token');
        $this->version      = $this->request->param('version');
        $this->appId        = $this->request->param('app_id');
        $this->appName      = strtolower($this->request->param('app_name'));
        $this->appSecretKey = $this->request->param('secret_key');
        $this->appIdentifier = strtolower($this->request->param('app_identifier'));

        if (empty($this->appId) || 
            empty($this->sign) || 
            empty($this->token) || 
            empty($this->version) || 
            empty($this->appIdentifier) || 
            empty($this->appName) || 
            empty($this->appSecretKey)) {
            http_response_code(202);
            exit('{"code":0,"msg":"参数传递错误","data":[]}');
        }
    }

    /**
     * 模块推送
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function module()
    {
        // 判断模块名称是否唯一
        $module = ModuleModel::where('name', $this->appName)->find();
        if ($module) {
            if ($module->identifier == $this->appIdentifier) {
                return $this->apiReturn('您已安装过此模块，如需重新安装，请进入您的网站后台-模块管理-删除此模块并重新推送安装！');
            }
            return $this->apiReturn('推送失败，您的网站已存在相同模块名['.$this->appName.']，但与当前模块不匹配！');
        }
        if (is_dir($this->appPath.$this->appName)) {
            return $this->apiReturn('推送失败，模块路径[application/'.$this->appName.']已经存在！');
        }
        // 检查模块名是否为系统限制名称
        if (in_array($this->appName, config('hs_system.config')) !== false) {
            return $this->apiReturn('推送失败，模块名与系统冲突！');
        }
        $file = $this->cloud->data(['app_id' => $this->appId, 'app_name' => $this->appName, 'version' => $this->version, 'app_identifier' => $this->appIdentifier, 'sign' => $this->sign, 'token' => $this->token])->down('module/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败，请稍后在试！');
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777, true);
        }

        // 解压安装包到$decomPath
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[/backup/uppack]文件夹权限！');
        }

        // 应用目录
        $appPath = $decomPath.'/upload/application/'.$this->appName.'/';

        // 获取安装包基本信息
        if (!is_file($appPath.'info.php')) {
            return $this->apiReturn('安装包缺少[info.php]文件！');
        }

        // 安装模块路由
        if (is_file($appPath.$this->appName.'.php')) {
            Dir::copyDir($appPath.$this->appName.'.php', './route');
        }

        $info = include_once $appPath.'info.php';
        // 复制app目录
        if (!is_dir($decomPath.'/upload/application')) {
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }

        if (!is_dir(ROOT_PATH.'application/'.$this->appName)) {
            Dir::create(ROOT_PATH.'application/'.$this->appName, 0777, true);
        }
        
        // 复制static目录
        if (is_dir($decomPath.'/upload/static')) {
            Dir::copyDir($decomPath.'/upload/static', '.'.ROOT_DIR.'static');
        }

        // 复制theme目录
        if (is_dir($decomPath.'/upload/theme')) {
            Dir::copyDir($decomPath.'/upload/theme', '.'.ROOT_DIR.'theme');
        }

        // 删除临时目录和安装包
        Dir::delDir($decomPath);
        @unlink($file);

        // 注册模块
        $map                = [];
        $map['name']        = $this->appName;
        $map['identifier']  = $info['identifier'];
        $map['title']       = $info['title'];
        $map['intro']       = $info['intro'];
        $map['icon']        = '/static/'.$this->appName.'/'.$this->appName.'.png';;
        $map['version']     = $info['version'];
        $map['author']      = isset($info['author']) ? $info['author'] : '';
        $map['url']         = isset($info['author_url']) ? $info['author_url'] : '';
        $map['app_id']      = $this->appId;
        $map['app_keys']    = $this->appSecretKey;
        $map['config']      = '';
        $res = ModuleModel::create($map);
        if (!$res) {
            return $this->apiReturn('推送成功，但模块注册失败！');
        }
        clearstatcache();
        return $this->apiReturn('恭喜您！模块推送成功。', 1);
    }

    /**
     * 插件推送
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function plugins()
    {
        // 判断模块名称是否唯一
        $plugins = PluginsModel::where('name', $this->appName)->find();
        if ($plugins) {
            if ($plugins->identifier == $this->appIdentifier) {
                return $this->apiReturn('推送失败，您已安装过此插件，如需重新安装，请进入您的网站后台-插件管理-删除此插件并重新推送安装！');
            }
            return $this->apiReturn('推送失败，您的网站已存在相同插件名['.$this->appName.']，但与当前插件不匹配！');
        }

        if (is_dir(ROOT_PATH.'plugins/'.$this->appName)) {
            return $this->apiReturn('推送失败，插件路径[plugins/'.$this->appName.']已经存在！');
        }

        $file = $this->cloud->data(['app_id' => $this->appId, 'app_name' => $this->appName, 'version' => $this->version, 'app_identifier' => $this->appIdentifier, 'sign' => $this->sign, 'token' => $this->token])->down('plugins/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败，请稍后在试！');
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777, true);
        }

        // 解压安装包到$decomPath
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[/backup/uppack]文件夹权限！');
        }

        if (!is_dir(ROOT_PATH.'plugins/'.$this->appName)) {
            Dir::create(ROOT_PATH.'plugins/'.$this->appName, 0777, true);
        }

        $appPath    = $decomPath.'/upload/plugins/'.$this->appName.'/';
        $staticPath = $decomPath.'/upload/static/'.$appName.'/';
        if (!is_file($appPath.'info.php')) {
            Dir::delDir(ROOT_PATH.'plugins/'.$this->appName);
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }

        $info = include_once $appPath.'info.php';

        // 复制到插件目录
        Dir::copyDir($appPath, ROOT_PATH.'plugins/'.$this->appName);
        // 复制静态资源
        Dir::copyDir($staticPath, ROOT_PATH.'public/static/plugins/'.$this->appName);
        
        // 删除临时目录和安装包
        Dir::delDir($decomPath);
        @unlink($file);

        // 注册插件
        $map                = [];
        $map['name']        = $this->appName;
        $map['identifier']  = $info['identifier'];
        $map['title']       = $info['title'];
        $map['intro']       = $info['intro'];
        $map['icon']        = ROOT_DIR.'static/plugins/'.$this->appName.'/'.$this->appName.'.png';
        $map['version']     = $info['version'];
        $map['author']      = isset($info['author']) ? $info['author'] : '';
        $map['url']         = isset($info['author_url']) ? $info['author_url'] : '';
        $map['app_id']      = $this->appId;
        $map['app_keys']    = $this->appSecretKey;
        $map['config']      = '';
        $res = PluginsModel::create($map);
        if (!$res) {
            return $this->apiReturn('推送成功，但插件注册失败！');
        }
        clearstatcache();
        return $this->apiReturn('恭喜您！插件推送成功。', 1);
    }

    /**
     * 主题推送
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function theme()
    {
        $file = $this->cloud->data(['app_id' => $this->appId, 'app_name' => $this->appName, 'version' => $this->version, 'app_identifier' => $this->appIdentifier, 'sign' => $this->sign, 'token' => $this->token])->down('theme/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败！');
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777, true);
        }

        // 解压安装包到$decomPath
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[/backup/uppack]文件夹权限！');
        }
        $appPath = $decomPath.'/upload/'.$this->appName.'/';

        if (!is_file($appPath.'config.xml')) {
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }
        $xml = file_get_contents($appPath.'config.xml');
        $config = xml2array($xml);

        if (!isset($config['depend']) || empty($config['depend'])) {
            Dir::delDir($decomPath);
            @unlink($file);
            return $this->apiReturn('推送失败，模板配置有误！');
        }

        $depend = explode('.', strtolower($config['depend']));
        $module = $depend[0];

        if (!is_dir(ROOT_PATH.'public/theme/'.$module) || !is_dir($this->appPath.$module)) {
            Dir::delDir($decomPath);
            @unlink($file);
            return $this->apiReturn('推送失败，您的网站未安装相应模块['.$config['depend'].']');
        }

        if (!is_file($this->appPath.$module.'/info.php')) {
            Dir::delDir($decomPath);
            @unlink($file);
            return $this->apiReturn('推送失败，您的网站模块异常['.$config['depend'].']');
        }

        $module_info = include_once $this->appPath.$module.'/info.php';
        if ($module_info['identifier'] != $config['depend']) {
            Dir::delDir($decomPath);
            @unlink($file);
            return $this->apiReturn('推送失败，请先安装依赖模块['.$config['depend'].']');
        }

        // 复制到插件目录
        Dir::copyDir($appPath, '.'.ROOT_DIR.'public/theme/'.$module.'/'.$this->appName);
        // 删除临时目录和安装包
        Dir::delDir($decomPath);
        @unlink($file);

        if (!is_file(ROOT_PATH.'public/theme/'.$module.'/'.$this->appName.'/config.xml')) {
            return $this->apiReturn('未知错误，模板推送失败！');
        }
        clearstatcache();
        return $this->apiReturn('恭喜您！插件推送成功。', 1);
    }

    /**
     * 将返回结果以json格式输出
     * @author 橘子俊 <364666827@qq.com>
     */
    private function apiReturn($msg = '', $code = 0, $data = [])
    {
        $arr = [];
        $arr['code'] = $code;
        $arr['msg'] = $msg;
        $arr['data'] = $data;
        if ($code == 1) {
            $status_code = 200;
        } else {
            $status_code = 202;
        }
        return json($arr, $status_code);
    }
}
