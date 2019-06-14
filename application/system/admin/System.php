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

use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use think\Validate;
use Env;

/**
 * 系统设置控制器
 * @package app\system\admin
 */
class System extends Admin
{

    /**
     * 系统基础配置
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($group = 'base')
    {
        if ($this->request->isPost()) {
            $webPath = Env::get('root_path').'public/';
            $data = $this->request->post();
            $types = $data['type'];

            if (isset($data['id'])) {
                $ids = $data['id'];
            } else {
                $ids = $data['id'] = '';
            }
            
            unset($data['upload']);// 清除上传字段
            
            // token 验证
            $validate = new Validate([
                '__token__' => 'token',
            ]);

            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }

            // 非系统模块配置保存
            if (isset($data['module'])) {
                $row = ModuleModel::where('name', $data['module'])->field('id,config')->find()->toArray();
                if (!isset($row['config'])) {
                    return $this->error('保存失败(原因：'.$data['module'].'模块无需配置)');
                }
                $row['config'] = json_decode($row['config'], 1);
                foreach ($row['config'] as $key => &$conf) {
                    if (!isset($ids[$key]) && $conf['type'] =='switch') {
                        $conf['value'] = 0;
                    } else if ($conf['type'] =='checkbox') {
                        if (isset($ids[$key])) {
                            $conf['value'] = implode(',', $ids[$key]);
                        } else {
                            $conf['value'] = '';
                        }
                    } else {
                        $conf['value'] = $ids[$key];
                    }
                }

                if (ModuleModel::where('id', $row['id'])->setField('config', json_encode($row['config'], 1)) === false) {
                    return $this->error('保存失败');
                }
                ModuleModel::getConfig('', true);
                return $this->success('保存成功');
            }
            
            // 系统模块配置保存
            if (!$types) return false;
            $adminPath = config('sys.admin_path');
            foreach ($types as $k => $v) {
                if ($v == 'switch' && !isset($ids[$k])) {
                    ConfigModel::where('name', $k)->update(['value' => 0]);
                    continue;
                }

                if ($v == 'checkbox') {
                    if (isset($ids[$k])) {
                        $ids[$k] = implode(',', $ids[$k]);
                    } else {
                        $ids[$k] = '';
                    }
                }
                
                // 修改后台管理目录
                if ($k == 'admin_path' && $ids[$k] != config('sys.admin_path')) {
                    if (is_file($webPath.config('sys.admin_path')) && is_writable($webPath.config('sys.admin_path'))) {
                        @rename($webPath.config('sys.admin_path'), $webPath.$ids[$k]);
                        if (!is_file($webPath.$ids[$k])) {
                            $ids[$k] = config('sys.admin_path');
                        }
                        $adminPath = $ids[$k];
                    }
                }
                ConfigModel::where('name', $k)->update(['value' => $ids[$k]]);
            }

            // 更新配置缓存
            $config = ConfigModel::getConfig('', true);

            if ($group == 'sys') {
                $rootPath = Env::get('root_path');
                if (file_exists($rootPath.'.env')) {
                    unlink($rootPath.'.env');
                }
                $env = "//设置开启调试模式\napp_debug = " . ($config['sys']['app_debug'] ? 'true' : 'false');
                $env .= "\n//应用Trace\napp_trace = " . ($config['sys']['app_trace'] ? 'true' : 'false');
                file_put_contents($rootPath.'.env', $env);
            }

            return $this->success('保存成功', ROOT_DIR.$adminPath.'/system/system/index/group/'.$group.'.html');
        }
        $tabData = [];
        foreach (config('sys.config_group') as $key => $value) {
            $arr = [];
            $arr['title'] = $value;
            $arr['url'] = '?group='.$key;
            $tabData['menu'][] = $arr;
        }
        $map = [];
        $map['group'] = $group;
        $map['status'] = 1;
        $dataList = ConfigModel::where($map)->order('sort,id')->column('id,name,title,group,url,value,type,options,tips');
        foreach ($dataList as $k => &$v) {
            $v['id'] = $v['name'];
            if (!empty($v['options'])) {
                $v['options'] = parse_attr($v['options']);
            }

            if ($v['type'] == 'checkbox') {
                $v['value'] = explode(',', $v['value']);
            }
        }

        // 模块配置
        $module = ModuleModel::where('status', 2)->column('name,title,config', 'name');
        foreach ($module as $mod) {
            if (empty($mod['config'])) continue;
            $arr = [];
            $arr['title'] = $mod['title'];
            $arr['url'] = '?group='.$mod['name'];
            $tabData['menu'][] = $arr;
            if ($group == $mod['name']) {
                $dataList = json_decode($mod['config'], 1);
                foreach ($dataList as $k => &$v) {
                    if (!empty($v['options'])) {
                        $v['options'] = parse_attr($v['options']);
                    }
                    $v['id'] = $k;
                    $v['module'] = $mod['name'];
                }
            }
        }
        $tabData['current'] = url('?group='.$group);
        $_GET['group'] = $group;
        $this->assign('data_list', $dataList);
        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch();
    }

    private function mkAdmin($file)
    {
        $code = <<<INFO
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

// [ 后台入口文件 ]
namespace think;

header('Content-Type:text/html;charset=utf-8');

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 定义入口为admin
define('ENTRANCE', 'admin');

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 检查是否安装
if(!is_file('./../install.lock')) {
    header('location: /');
} else {
    Container::get('app')->run()->send();
}

INFO;
        if (!file_put_contents(ROOT_PATH.$file, $code)) {
            return fasle;
        }
        return true;
    }
}
