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
 * 应用市场推送
 * @package app\index\controller
 */
namespace app\index\controller;
use app\admin\model\AdminModule as ModuleModel;
use app\admin\model\AdminPlugins as PluginsModel;
use app\common\util\Cloud as CloudApi;
use app\common\util\Dir;
use app\common\util\PclZip;

set_time_limit(0);
ignore_user_abort(true);

class Push extends Home
{
    protected function _initialize() {
        if (!config('sys.cloud_push')) {
            echo '{"code":0,"msg":"您的站点已关闭云端推送功能","data":[]}';
            exit;
        }
        parent::_initialize();
        $this->update_path = ROOT_PATH.'backup'.DS.'uppack'.DS;
        $this->cloud = new CloudApi(config('hs_cloud.identifier'), $this->update_path);
        $this->sign = input('param.sign');
        $this->token = input('param.token');
        $this->version = input('param.version');
        $this->app_id = input('param.app_id');
        $this->app_name = strtolower(input('param.app_name'));
        $this->app_secret_key = input('param.secret_key');
        $this->app_identifier = strtolower(input('param.app_identifier'));
        if (empty($this->app_id) || empty($this->sign) || empty($this->token) || empty($this->version) || empty($this->app_identifier) || empty($this->app_name) || empty($this->app_secret_key)) {
            http_response_code(202);
            echo '{"code":0,"msg":"参数传递错误","data":[]}';
            exit;
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
        $module = ModuleModel::where('name', $this->app_name)->find();
        if ($module) {
            if ($module->identifier == $this->app_identifier) {
                return $this->apiReturn('您已安装过此模块，如需重新安装，请进入您的网站后台-模块管理-删除此模块并重新推送安装！');
            }
            return $this->apiReturn('推送失败，您的网站已存在相同模块名['.$this->app_name.']，但与当前模块不匹配！');
        }
        if (is_dir(APP_PATH.$this->app_name)) {
            return $this->apiReturn('推送失败，模块路径[app/'.$this->app_name.']已经存在！');
        }
        // 检查模块名是否为系统限制名称
        if (in_array($this->app_name, config('hs_system.config')) !== false) {
            return $this->apiReturn('推送失败，模块名与系统冲突！');
        }
        $file = $this->cloud->data(['app_id' => $this->app_id, 'app_name' => $this->app_name, 'version' => $this->version, 'app_identifier' => $this->app_identifier, 'sign' => $this->sign, 'token' => $this->token])->down('module/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败，请稍后在试！');
        }

        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }

        // 解压安装包到$decom_path
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[backup/uppack]文件夹权限！');
        }
        // 应用目录
        $app_path = $decom_path.DS.'upload'.DS.'app'.DS.$this->app_name.DS;
        // 获取安装包基本信息
        if (!is_file($app_path.'info.php')) {
            return $this->apiReturn('安装包缺少[info.php]文件！');
        }
        // 安装模块路由
        if (is_file($app_path.$this->app_name.'.php')) {
            Dir::copyDir($app_path.$this->app_name.'.php', './route');
        }
        $info = include_once $app_path.'info.php';
        // 复制app目录
        if (!is_dir($decom_path.DS.'upload'.DS.'app')) {
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }
        if (!is_dir(ROOT_PATH.'app'.DS.$this->app_name)) {
            Dir::create(ROOT_PATH.'app'.DS.$this->app_name, 0777, true);
        }
        Dir::copyDir($app_path, './app'.DS.$this->app_name);
        if (!is_dir(ROOT_PATH.'static'.DS.'app_icon'.DS)) {
            Dir::create(ROOT_PATH.'static'.DS.'app_icon'.DS, 0755, true);
        }
        // 复制应用图标
        $icon = ROOT_DIR.'static/admin/image/app.png';
        if (is_file($decom_path.DS.'upload'.DS.'app'.DS.$this->app_name.DS.$this->app_name.'.png')) {
            copy($decom_path.DS.'upload'.DS.'app'.DS.$this->app_name.DS.$this->app_name.'.png', ROOT_PATH.'static'.DS.'app_icon'.DS.$this->app_name.'.png');
            $icon = ROOT_DIR.'static/app_icon/'.$this->app_name.'.png';
        }
        // 复制static目录
        if (is_dir($decom_path.DS.'upload'.DS.'static')) {
            Dir::copyDir($decom_path.DS.'upload'.DS.'static', '.'.ROOT_DIR.'static');
        }
        // 复制theme目录
        if (is_dir($decom_path.DS.'upload'.DS.'theme')) {
            Dir::copyDir($decom_path.DS.'upload'.DS.'theme', '.'.ROOT_DIR.'theme');
        }
        // 删除临时目录和安装包
        Dir::delDir($decom_path);
        @unlink($file);
        // 注册模块
        $map = [];
        $map['name'] = $this->app_name;
        $map['identifier'] = $info['identifier'];
        $map['title'] = $info['title'];
        $map['intro'] = $info['intro'];
        $map['icon'] = $icon;
        $map['version'] = $info['version'];
        $map['author'] = isset($info['author']) ? $info['author'] : '';
        $map['url'] = isset($info['author_url']) ? $info['author_url'] : '';
        $map['app_id'] = $this->app_id;
        $map['app_keys'] = $this->app_secret_key;
        $map['config'] = '';
        $res = ModuleModel::create($map);
        if (!$res) {
            return $this->apiReturn('未知错误，模块推送失败！');
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
        $plugins = PluginsModel::where('name', $this->app_name)->find();
        if ($plugins) {
            if ($plugins->identifier == $this->app_identifier) {
                return $this->apiReturn('推送失败，您已安装过此插件，如需重新安装，请进入您的网站后台-插件管理-删除此插件并重新推送安装！');
            }
            return $this->apiReturn('推送失败，您的网站已存在相同插件名['.$this->app_name.']，但与当前插件不匹配！');
        }
        if (is_dir(ROOT_PATH.'plugins'.DS.$this->app_name)) {
            return $this->apiReturn('推送失败，插件路径[plugins/'.$this->app_name.']已经存在！');
        }
        $file = $this->cloud->data(['app_id' => $this->app_id, 'app_name' => $this->app_name, 'version' => $this->version, 'app_identifier' => $this->app_identifier, 'sign' => $this->sign, 'token' => $this->token])->down('plugins/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败，请稍后在试！');
        }

        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压安装包到$decom_path
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[backup/uppack]文件夹权限！');
        }
        if (!is_dir('.'.ROOT_DIR.'plugins/'.$this->app_name)) {
            Dir::create('.'.ROOT_DIR.'plugins/'.$this->app_name, 0777, true);
        }

        $app_path = $decom_path.DS.'upload'.DS.$this->app_name.DS;
        if (!is_file($app_path.'info.php')) {
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }
        $info = include_once $app_path.'info.php';
        // 复制到插件目录
        Dir::copyDir($app_path, '.'.ROOT_DIR.'plugins/'.$this->app_name);
        // 删除临时目录和安装包
        Dir::delDir($decom_path);
        @unlink($file);
        // 注册插件
        $map = [];
        $map['name'] = $this->app_name;
        $map['identifier'] = $info['identifier'];
        $map['title'] = $info['title'];
        $map['intro'] = $info['intro'];
        $map['icon'] = ROOT_DIR.'plugins/'.$this->app_name.'/'.$this->app_name.'.png';
        $map['version'] = $info['version'];
        $map['author'] = isset($info['author']) ? $info['author'] : '';
        $map['url'] = isset($info['author_url']) ? $info['author_url'] : '';
        $map['app_id'] = $this->app_id;
        $map['app_keys'] = $this->app_secret_key;
        $map['config'] = '';
        $res = PluginsModel::create($map);
        if (!$res) {
            return $this->apiReturn('未知错误，插件推送失败！');
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
        $file = $this->cloud->data(['app_id' => $this->app_id, 'app_name' => $this->app_name, 'version' => $this->version, 'app_identifier' => $this->app_identifier, 'sign' => $this->sign, 'token' => $this->token])->down('theme/get/install');
        if ($file === false || !is_file($file)) {
            return $this->apiReturn('获取升级包失败！');
        }

        $decom_path = $this->update_path.basename($file,".zip");
        if (!is_dir($decom_path)) {
            Dir::create($decom_path, 0777, true);
        }
        // 解压安装包到$decom_path
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decom_path, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->apiReturn('推送失败，请开启[backup/uppack]文件夹权限！');
        }
        $app_path = $decom_path.DS.'upload'.DS.$this->app_name.DS;
        if (!is_file($app_path.'config.xml')) {
            return $this->apiReturn('推送失败，升级包文件不完整！');
        }
        $xml = file_get_contents($app_path.'config.xml');
        $config = xml2array($xml);
        if (!isset($config['depend']) || empty($config['depend'])) {
            Dir::delDir($decom_path);
            @unlink($file);
            return $this->apiReturn('推送失败，模板配置有误！');
        }
        $depend = explode('.', strtolower($config['depend']));
        $module = $depend[0];
        if (!is_dir(ROOT_PATH.'theme'.DS.$module) || !is_dir(APP_PATH.$module)) {
            Dir::delDir($decom_path);
            @unlink($file);
            return $this->apiReturn('推送失败，您的网站未安装相应模块['.$config['depend'].']');
        }
        if (!is_file(APP_PATH.$module.DS.'info.php')) {
            Dir::delDir($decom_path);
            @unlink($file);
            return $this->apiReturn('推送失败，您的网站模块异常['.$config['depend'].']');
        }
        $module_info = include_once APP_PATH.$module.DS.'info.php';
        if ($module_info['identifier'] != $config['depend']) {
            Dir::delDir($decom_path);
            @unlink($file);
            return $this->apiReturn('推送失败，请先安装依赖模块['.$config['depend'].']');
        }
        // 复制到插件目录
        Dir::copyDir($app_path, '.'.ROOT_DIR.'theme/'.$module.'/'.$this->app_name);
        // 删除临时目录和安装包
        Dir::delDir($decom_path);
        @unlink($file);
        if (!is_file(ROOT_PATH.'theme'.DS.$module.DS.$this->app_name.DS.'config.xml')) {
            return $this->apiReturn('未知错误，模板推送失败！');
        }
        clearstatcache();
        return $this->apiReturn('恭喜您！插件推送成功。', 1);
    }
}
