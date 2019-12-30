<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5.1开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2021 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------

namespace app\common\behavior;

use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use Env;
use Request;
use Lang;
use View;
/**
 * 初始化基础配置行为
 * 将扩展的全局配置本地化
 */
class Base
{
    public function run()
    {
        
        // 获取当前模块名称
        $module = strtolower(Request::module());
        
        // 安装操作直接return
        if (defined('INSTALL_ENTRANCE')) return;

        // 设置插件配置
        config(PluginsModel::getConfig());
        
        // 设置模块配置
        config(ModuleModel::getConfig());

        // 设置系统配置
        config(ConfigModel::getConfig());

        // 获取站点根目录
        $entry  = Request::baseFile();
        $rootDir= preg_replace(['/index.php$/', '/plugins.php$/', '/'.config('sys.admin_path').'$/'], ['', '', ''], $entry);

        define('ROOT_DIR', $rootDir);
        
        // 判断模块是否存在且已安装
        $theme = 'default';
        if (in_array($module, ['index', 'system']) === false) {

            if (empty($module)) {
                $module = config('default_module');
            }

            $modInfo = ModuleModel::where(['name' => $module, 'status' => 2])->find();
            if (!$modInfo) {
                exit($module.' 模块可能未启用或者未安装！');
            }

            // 设置模块的默认主题
            $theme = $modInfo['theme'] ? $modInfo['theme'] : 'default';
        }
        
        $themePath = $rootDir.'theme/'.$module.'/'.$theme.'/';
        $viewReplaceStr = [
            // 站点根目录
            '__ROOT_DIR__'      => $rootDir,
            // 静态资源根目录
            '__STATIC__'        => $rootDir.'static',
            // 文件上传目录
            '__UPLOAD__'        => $rootDir.'upload',
            // 插件目录
            '__PLUGINS__'       => $rootDir.'plugins',
            // 后台公共静态目录
            '__ADMIN_CSS__'     => $rootDir.'static/system/css',
            '__ADMIN_JS__'      => $rootDir.'static/system/js',
            '__ADMIN_IMG__'     => $rootDir.'static/system/image',
            // 后台模块静态目录
            '__ADMIN_MOD_CSS__' => $rootDir.'static/'.$module.'/css',
            '__ADMIN_MOD_JS__'  => $rootDir.'static/'.$module.'/js',
            '__ADMIN_MOD_IMG__' => $rootDir.'static/'.$module.'/image',
            // 前台公共静态目录
            '__PUBLIC_CSS__'    => $rootDir.'static/css',
            '__PUBLIC_JS__'     => $rootDir.'static/js',
            '__PUBLIC_IMG__'    => $rootDir.'static/image',
            // 前台模块静态目录
            '__CSS__'           => $themePath.'static/css',
            '__JS__'            => $themePath.'static/js',
            '__IMG__'           => $themePath.'static/image',
            '__WAP_CSS__'       => $themePath.'wap/static/css',
            '__WAP_JS__'        => $themePath.'wap/static/js',
            '__WAP_IMG__'       => $themePath.'wap/static/image',
        ];

        if ($pName = Request::param('_p')) {
            $static = $rootDir.'static/plugins/'.$pName.'/static/';
            $viewReplaceStr['__PLUGINS_CSS__']  = $static.'css';
            $viewReplaceStr['__PLUGINS_JS__']   = $static.'js';
            $viewReplaceStr['__PLUGINS_IMG__']  = $static.'image';
        }
        
        View::config(['tpl_replace_string' => $viewReplaceStr]);

        if(defined('ENTRANCE') && ENTRANCE == 'admin') {

            if ($module == 'index') {
                header('Location: '.url('system/publics/index'));
                exit;
            }

            self::setLang('admin');

        } else {

            if (config('base.site_status') != 1) {
                exit('站点已关闭！');
            }

            if (Request::isAjax() === false && 
                strpos(Request::server('http_user_agent'), 'miniProgram') === false) {
                    
                $domain = Request::domain();
                $wap    = config('base.wap_domain');
                $viewPath = 'theme/'.$module.'/'.$theme.'/';
        
                // 定义前台模板路径[分手机和PC]
                if (Request::isMobile() === true &&
                    config('base.wap_site_status') &&
                    file_exists('.'.ROOT_DIR.$viewPath.'wap/')) {
                    if ($wap && $wap != $domain) {
                        header('Location: '.$wap.Request::url());
                        exit();
                    }

                    $viewPath .= 'wap/';
                } elseif (config('base.wap_site_status') && $domain == $wap) {
                    $viewPath .= 'wap/';
                }

                View::config(['view_path' => $viewPath]);
            }

            self::setLang();
        }

        define('HISI_LANG', Lang::range());
    }

    // 设置前台默认语言到cookie
    private function setLang($admin = '')
    {
        $cookieName = $admin.'_language';
        if (isset($_GET['lang']) && !empty($_GET['lang'])) {
            cookie($cookieName, $_GET['lang']);
        } elseif (cookie($cookieName)) {
            Lang::range(cookie($cookieName));
        } else {
            cookie($cookieName, Lang::range());
        }
    }
}
