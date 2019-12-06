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

use app\system\model\SystemLog as LogModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use hisi\Cloud;
use hisi\Dir;
use hisi\PclZip;
use Env;

/**
 * 应用市场控制器
 * @package app\system\admin
 */
class Store extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();

        $this->rootPath = Env::get('root_path');
        $this->appPath  = Env::get('app_path');
        $this->tempPath = Env::get('runtime_path').'app/';
        $this->cloud    = new Cloud(config('hs_cloud.identifier'), $this->tempPath);
    }
    
    /**
     * 应用列表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
    	if ($this->request->isAjax()) {
    		$data = $param    = [];
    		$param['page']    = $this->request->param('page/d', 1);
    		$param['cat_id']  = $this->request->param('cat_id/d', 0);
    		$param['type']    = $this->request->param('type/d', 1);
    		$param['limit']   = $this->request->param('limit/d', 10);
  
    		$data['code'] = 0;
            $data['data'] = [];
    		$cloudData = $this->cloud->data($param)->api('apps');
    		if ($cloudData['code'] == 1) {
                switch ($param['type']) {
                    case 1:// 模块
                        $locApp = ModuleModel::where('system', 0)->column('identifier,version');
                        break;
                    case 2:// 插件
                        $locApp = PluginsModel::where('system', 0)->column('identifier,version');
                        break;
                    
                    default:// 主题
                        $locApp = [];
                        break;
                }


                $apps = [];
                foreach ($cloudData['data']['apps'] as $k => $v) {
                    $v['install'] = 0;
                    $v['upgrade'] = 0;
                    // 检查是否已有安装某个分支
                    foreach ($v['branchs'] as $kk => $vv) {
                        if (array_key_exists($kk, $locApp)) {
                            $v['install'] = $kk;
                            if (version_compare($vv['version'], $locApp[$kk], '>')) {
                                $v['upgrade'] = 1;
                            }
                            continue;
                        }
                    }
                    $apps[] = $v;
                }
    			$data['data'] = $apps;
    			$data['count'] = $cloudData['data']['count'];
    		}

    		return json($data);
    	}

    	$data['cats'] = cache('cloud_app_cats');
    	if (!$data['cats']) {
            $cats = $this->cloud->api('cats');
            if (!isset($cats['data'])) {
                $this->error('云市场请求失败');
            }
            
            $data['cats'] = $cats['data'];
    		cache('cloud_app_cats', $data['cats']);
    	}
        
        $this->assign('api_url', $this->cloud->apiUrl());
    	$this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 安装应用
     * @date   2018-10-31
     * @access public
     * @author 橘子俊 364666827@qq.com
     * @return json
     */
    public function install()
    {
        $appType    = $this->request->get('app_type');
        $appName    = $this->request->get('app_name');
        $appKeys    = $this->request->get('app_keys');
        $branchId   = $this->request->get('branch_id');
        $orderId    = $this->request->get('order_id');

        $data               = [];
        $data['branch_id']  = $branchId;
        $data['order_id']   = $orderId;
        $data['app_keys']   = $appKeys;

        // 下载应用安装包
        $file = $this->cloud->data($data)->down('downAppInstall');
        if (!file_exists($file)) {
            return $this->error('安装文件获取失败，请稍后在试');
        }

        // 解压包路径
        $unzipPath = $this->tempPath.basename($file,".zip");
        if (!is_dir($unzipPath)) {
            Dir::create($unzipPath, 0777);
        }

        // 解压安装包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $unzipPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->error('安装失败（安装包可能已损坏）');
        }

        if ($appType == 1) {// 模块安装
            $res = self::_moduleInstall($appName, $appKeys, $file, $unzipPath);
        } else if ($appType == 2) {// 插件安装
            $res = self::_pluginsInstall($appName, $appKeys, $file, $unzipPath);
        } else {// 主题安装
            $res = self::_themeInstall($appName, $appKeys, $file, $unzipPath);
        }

        clearstatcache();

        // 删除临时目录
        Dir::delDir($this->tempPath);

        if ($res === true) {
            return $this->success('安装成功');
        }

        return $this->error($res);
    }

    /**
     * 安装模块
     * @date   2018-10-31
     * @access private
     * @author 橘子俊 364666827@qq.com
     * @param  string     $appName   应用名称
     * @param  string     $appKeys   应用私钥
     * @param  string     $file      安装包路径
     * @param  string     $unzipPath 解压路径
     * @return bool|string
     */
    private function _moduleInstall($appName, $appKeys, $file, $unzipPath)
    {
        // 防止重复安装
        if (ModuleModel::where('name', $appName)->find()) {
            return true;
        }

        // 应用目录
        $appPath = $unzipPath.'/upload/application/'.$appName.'/';

        // 获取模块信息
        $info = include_once $appPath.'info.php';

        // 复制app目录
        if (!is_dir($unzipPath.'/upload/application')) {
            return '安装失败（安装包可能已损坏）';
        }

        if (!is_dir($this->rootPath.'application/'.$appName)) {
            Dir::copyDir($unzipPath.'/upload/application', $this->appPath);
        } else {
            return '已存在同名模块';
        }

        // 复制static目录
        if (is_dir($unzipPath.'/upload/public/static')) {
            Dir::copyDir($unzipPath.'/upload/public/static', './static');
        }

        // 复制theme目录
        if (is_dir($unzipPath.'/upload/public/theme')) {
            Dir::copyDir($unzipPath.'/upload/public/theme', './theme');
        }

        // 删除临时目录和安装包
        Dir::delDir($unzipPath);
        @unlink($file);
        clearstatcache();

        // 注册模块
        $sqlmap                = [];
        $sqlmap['name']        = $appName;
        $sqlmap['identifier']  = $info['identifier'];
        $sqlmap['title']       = $info['title'];
        $sqlmap['intro']       = $info['intro'];
        $sqlmap['icon']        = '/static/'.$appName.'/'.$appName.'.png';
        $sqlmap['version']     = $info['version'];
        $sqlmap['author']      = $info['author'];
        $sqlmap['url']         = $info['author_url'];
        $sqlmap['app_keys']    = $appKeys;
        $sqlmap['config']      = '';

        $result = ModuleModel::create($sqlmap);
        if (!$result) {
            return '异常错误，请<a href="'.url('module/index/status/0').'">点此进入模块管理</a>页面手动安装！';
        }

        $result = action('system/module/execInstall', ['id' => $result->id, 'clear' => 1], 'admin');
        if ($result !== true) {
            return $result.'<br><br>请<a href="'.url('module/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入模块管理</a>页面手动安装！';
        }

        return true;
    }

    /**
     * 安装插件
     * @date   2018-10-31
     * @access private
     * @author 橘子俊 364666827@qq.com
     * @param  string     $appName   应用名称
     * @param  string     $appKeys   应用私钥
     * @param  string     $file      安装包路径
     * @param  string     $unzipPath 解压路径
     * @return bool|string
     */
    private function _pluginsInstall($appName, $appKeys, $file, $unzipPath)
    {
        // 防止重复安装
        if (PluginsModel::where('name', $appName)->find()) {
            return true;
        }

        $appPath    = $unzipPath.'/upload/plugins/'.$appName.'/';
        $staticPath = $unzipPath.'/upload/public/static/'.$appName.'/';
        $info       = include_once $appPath.'info.php';

        if (!is_dir($this->rootPath.'plugins/'.$appName)) {
            // 复制插件
            Dir::copyDir($appPath, $this->rootPath.'plugins/'.$appName);
            // 复制静态资源
            Dir::copyDir($staticPath, '.'.ROOT_DIR.'/static/plugins/'.$appName);
        } else {
            return '已存在同名插件';
        }

        // 删除临时目录和安装包
        Dir::delDir($unzipPath);
        @unlink($file);
        clearstatcache();

        // 注册插件
        $map                = [];
        $map['name']        = $appName;
        $map['identifier']  = $info['identifier'];
        $map['title']       = $info['title'];
        $map['intro']       = $info['intro'];
        $map['icon']        = ROOT_DIR.'static/plugins/'.$appName.'/'.$appName.'.png';
        $map['version']     = $info['version'];
        $map['author']      = $info['author'];
        $map['url']         = $info['author_url'];
        $map['app_keys']    = $appKeys;
        $map['config']      = '';
        $result = PluginsModel::create($map);
        if (!$result) {
            return '异常错误，请<a href="'.url('plugins/index/status/0').'">点此进入插件管理</a>页面手动安装！';
        }

        $result = action('system/plugins/execInstall', ['id' => $result->id], 'admin');
        if ($result !== true) {
            return $result.'<br><br>请<a href="'.url('plugins/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入插件管理</a>页面手动安装！';
        }
        
        return true;
    }
    
    /**
     * 安装主题
     * @date   2018-10-31
     * @access private
     * @author 橘子俊 364666827@qq.com
     * @param  [string]     $appName   应用名称
     * @param  [string]     $appKeys   应用私钥
     * @param  [string]     $file      安装包路径
     * @param  [string]     $unzipPath 解压路径
     * @return bool|string
     */
    private function _themeInstall($appName, $appKeys, $file, $unzipPath)
    {
        $base = $unzipPath.'/upload/';
        if (is_file($base.$appName.'/config.json')) {
            $json = file_get_contents($base.$appName.'/config.json');
            $config = json_decode($json, 1);
        } elseif (is_file($base.$appName.'/config.xml')) {
            $xml = file_get_contents($base.$appName.'/config.xml');
            $config = xml2array($xml);
        } else  {
            return '缺少配置文件';
        }
        
        if (!isset($config['depend']) || empty($config['depend'])) {
            return '配置文件信息不完整！';
        }

        $depend = trim($config['depend']);
        $exp = explode('.', $depend);
        if (count($exp) != 4) {
            return '依赖的模块标识格式错误！';
        }

        if (!ModuleModel::where('identifier', $depend)->find()) {
            return '请先安装'.$exp[0].'模块';
        }

        $target = './theme/'.$exp[0].'/'.$appName;
        if (is_dir($target)) {
            return '请勿重复安装';
        }

        Dir::copyDir($base.$appName, $target);

        return true;
    }
}
