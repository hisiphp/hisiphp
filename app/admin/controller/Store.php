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
use app\admin\model\AdminLog as LogModel;
use app\admin\model\AdminModule as ModuleModel;
use app\admin\model\AdminPlugins as PluginsModel;
use app\common\util\Cloud;
use app\common\util\Dir;
use app\common\util\PclZip;
use think\Loader;
/**
 * 应用市场控制器
 * @package app\admin\controller
 */
class Store extends Admin
{
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->tempPath = RUNTIME_PATH.'app/';
        $this->cloud = new Cloud(config('hs_cloud.identifier'), $this->tempPath);
    }
    
    /**
     * 应用列表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
    	if ($this->request->isAjax()) {
    		$data             = $param = [];
    		$param['page']    = $this->request->param('page/d', 1);
    		$param['cat_id']  = $this->request->param('cat_id/d', 0);
    		$param['type']    = $this->request->param('type/d', 1);
    		$param['limit']   = $this->request->param('limit/d', 10);
  
    		$data['code'] = 0;
    		$cloudData = $this->cloud->data($param)->api('apps');
    		if ($cloudData['code'] == 1) {
                if ($param['type'] == 1) {
                    $locApp = ModuleModel::where('system', 0)->column('identifier,version');
                } else {
                    $locApp = PluginsModel::where('system', 0)->column('identifier,version');
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
    		} else {
    			$data['code'] = 1;
    		}

    		return json($data);
    	}

        $data['cats'] = [];
        // 因应用太少，暂时隐藏分类筛选
        // $data['cats'] = cache('cloud_app_cats');
        // if (!$data['cats']) {
        //     $cats = $this->cloud->api('cats');
        //     $data['cats'] = $cats['data'];
        //     cache('cloud_app_cats', $data['cats']);
        // }
        
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

        $data = [];
        $data['branch_id'] = $branchId;
        $data['order_id'] = $orderId;
        $data['app_keys'] = $appKeys;

        // 下载应用安装包
        $file = $this->cloud->data($data)->down('downAppInstall');
        if (!file_exists($file)) {
            return $this->error('安装文件获取失败，请稍后在试！');
        }

        // 解压包路径
        $unzipPath = $this->tempPath.basename($file,".zip");
        if (!is_dir($unzipPath)) {
            Dir::create($unzipPath, 0777, true);
        }

        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $unzipPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->error('安装失败，请检查[/runtime/app/]文件夹权限');
        }

        if ($appType == 1) {// 模块安装
            $res = self::_moduleInstall($appName, $appKeys, $file, $unzipPath);
        } else if ($appType == 2) {// 插件安装
            $res = self::_pluginsInstall($appName, $appKeys, $file, $unzipPath);
        } else {// 主题安装
            $res = self::_themeInstall($appName, $appKeys, $file, $unzipPath);
        }

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
        $appPath = $unzipPath.'/upload/app/'.$appName.'/';

        // 导入模块路由
        if (is_file($appPath.'route.php')) {
            Dir::copyDir($appPath.'route.php', APP_PATH);
        }

        // 获取模块信息
        $info = include_once $appPath.'info.php';

        if (!is_dir(APP_PATH.$appName)) {
            Dir::create(APP_PATH.$appName, 0777, true);
        }
        Dir::copyDir($appPath, APP_PATH.$appName);

        // 复制static目录
        if (is_dir($unzipPath.'/upload/static')) {
            Dir::copyDir($unzipPath.'/upload/static', ROOT_PATH.'static');
        }

        // 复制theme目录
        if (is_dir($unzipPath.'/upload/theme')) {
            Dir::copyDir($unzipPath.'/upload/theme', ROOT_PATH.'theme');
        }

        // 复制应用图标
        $icon = ROOT_DIR.'static/admin/image/app.png';
        $soureIcon = $unzipPath.'/upload/app/'.$appName.DS.$appName.'.png';
        if (is_file($soureIcon)) {
            copy($soureIcon, ROOT_PATH.'static/app_icon/'.$appName.'.png');
            $icon = ROOT_DIR.'static/app_icon/'.$appName.'.png';
        }

        // 删除临时目录和安装包
        Dir::delDir($unzipPath);
        @unlink($file);

        // 注册模块
        $sqlmap                = [];
        $sqlmap['name']        = $appName;
        $sqlmap['identifier']  = $info['identifier'];
        $sqlmap['title']       = $info['title'];
        $sqlmap['intro']       = $info['intro'];
        $sqlmap['icon']        = $icon;
        $sqlmap['version']     = $info['version'];
        $sqlmap['author']      = $info['author'];
        $sqlmap['url']         = $info['author_url'];
        $sqlmap['app_id']      = 0;// 无效
        $sqlmap['app_keys']    = $appKeys;
        $sqlmap['config']      = '';
        $result = ModuleModel::create($sqlmap);
        if (!$result) {
            return '异常错误，请<a href="'.url('module/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入模块管理</a>页面手动安装！';
        }

        clearstatcache();
        $result = Loader::controller('admin/module', 'controller')->execInstall($result->id, 1);
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

        $appPath = $unzipPath.'/upload/plugins/'.$appName.'/';
        $info = include_once $appPath.'info.php';

        // 复制到插件目录
        Dir::copyDir($appPath, ROOT_PATH.'plugins/'.$appName);
        // 删除临时目录和安装包
        Dir::delDir($unzipPath);
        @unlink($file);

        // 注册插件
        $sqlmap                = [];
        $sqlmap['name']        = $appName;
        $sqlmap['identifier']  = $info['identifier'];
        $sqlmap['title']       = $info['title'];
        $sqlmap['intro']       = $info['intro'];
        $sqlmap['icon']        = ROOT_DIR.'plugins/'.$appName.'/'.$appName.'.png';
        $sqlmap['version']     = $info['version'];
        $sqlmap['author']      = $info['author'];
        $sqlmap['url']         = $info['author_url'];
        $sqlmap['app_id']      = 0;
        $sqlmap['app_keys']    = $appKeys;
        $sqlmap['config']      = '';
        $result = PluginsModel::create($sqlmap);
        if (!$result) {
            return '异常错误，请<a href="'.url('plugins/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入插件管理</a>页面手动安装！';
        }

        $result = Loader::controller('admin/plugins', 'controller')->execInstall($result->id, 1);
        if ($result !== true) {
            return $result.'<br><br>请<a href="'.url('plugins/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入插件管理</a>页面手动安装！';
        }

        clearstatcache();
        return true;
    }

    /**
     * 安装主题
     * @date   2018-10-31
     * @access private
     * @author 橘子俊 364666827@qq.com
     * @param  string     $appName   应用名称
     * @param  string     $appKeys   应用私钥
     * @param  string     $file      安装包路径
     * @param  string     $unzipPath 解压路径
     * @return bool|string
     */
    private function _themeInstall($appName, $appKeys, $file, $unzipPath)
    {
        return true;
    }
}
