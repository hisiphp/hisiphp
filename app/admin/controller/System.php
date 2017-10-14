<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\model\AdminConfig as ConfigModel;
use app\admin\model\AdminModule as ModuleModel;
use app\admin\model\AdminPlugins as PluginsModel;
/**
 * 系统设置控制器
 * @package app\admin\controller
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
            $data = $this->request->post();
            $types = $data['type'];
            if (isset($data['id'])) {
                $ids = $data['id'];
            } else {
                $ids = $data['id'] = '';
            }
            unset($data['upload']);// 清除上传字段
            // 非系统模块配置保存
            if (isset($data['module'])) {
                $row = ModuleModel::where('name', $data['module'])->field('id,config')->find()->toArray();
                if (!isset($row['config'])) {
                    return $this->error('保存失败！(原因：'.$data['module'].'模块无需配置)');
                }
                $row['config'] = json_decode($row['config'], 1);
                foreach ($row['config'] as $key => &$conf) {
                    if (!isset($ids[$key]) && $conf['type'] =='switch') {
                        $conf['value'] = 0;
                    } else if ($conf['type'] =='checkbox' && isset($ids[$key])) {
                        $conf['value'] = json_encode($ids[$key], 1);
                    } else {
                        $conf['value'] = $ids[$key];
                    }
                }

                if (ModuleModel::where('id', $row['id'])->setField('config', json_encode($row['config'], 1)) === false) {
                    return $this->error('保存失败！');
                }
                ModuleModel::getConfig('', true);
                return $this->success('保存成功。');
            }
            // 系统模块配置保存
            if (!$types) return false;
            $admin_path = config('sys.admin_path');
            foreach ($types as $k => $v) {
                if ($v == 'switch' && !isset($ids[$k])) {
                    ConfigModel::where('name', $k)->update(['value' => 0]);
                    continue;
                }
                if ($v == 'checkbox' && isset($ids[$k])) {
                    $ids[$k] = json_encode($ids[$k], 1);
                }
                // 修改后台管理目录
                if ($k == 'admin_path' && $ids[$k] != config('sys.admin_path')) {
                    if (is_file(ROOT_PATH.config('sys.admin_path')) && is_writable(ROOT_PATH.config('sys.admin_path'))) {
                        @rename(ROOT_PATH.config('sys.admin_path'), ROOT_PATH.$ids[$k]);
                        if (!is_file(ROOT_PATH.$ids[$k])) {
                            $ids[$k] = config('sys.admin_path');
                        }
                        $admin_path = $ids[$k];
                    }
                }
                ConfigModel::where('name', $k)->update(['value' => $ids[$k]]);
            }
            // 更新配置缓存
            ConfigModel::getConfig('', true);

            return $this->success('保存成功。', ROOT_DIR.$admin_path.'/admin/system/index/group/'.$group.'.html');
        }
        $tab_data = [];
        foreach (config('sys.config_group') as $key => $value) {
            $arr = [];
            $arr['title'] = $value;
            $arr['url'] = '?group='.$key;
            $tab_data['menu'][] = $arr;
        }
        $map = [];
        $map['group'] = $group;
        $map['status'] = 1;
        $data_list = ConfigModel::where($map)->order('sort,id')->column('id,name,title,group,url,value,type,options,tips');
        foreach ($data_list as $k => &$v) {
            $v['id'] = $v['name'];
            if (!empty($v['options'])) {
                $v['options'] = parse_attr($v['options']);
            }
        }
        // 模块配置
        $module = ModuleModel::where('status', 2)->column('name,title,config', 'name');
        foreach ($module as $mod) {
            if (empty($mod['config'])) continue;
            $arr = [];
            $arr['title'] = $mod['title'];
            $arr['url'] = '?group='.$mod['name'];
            $tab_data['menu'][] = $arr;
            if ($group == $mod['name']) {
                $data_list = json_decode($mod['config'], 1);
                foreach ($data_list as $k => &$v) {
                    if (!empty($v['options'])) {
                        $v['options'] = parse_attr($v['options']);
                    }
                    $v['id'] = $k;
                    $v['module'] = $mod['name'];
                }
            }
        }
        $tab_data['current'] = url('?group='.$group);
        $_GET['group'] = $group;
        $this->assign('data_list', $data_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        return $this->fetch();
    }

    private function mkAdmin($file)
    {
        $code = <<<INFO
<?php
// [ 后台入口文件 ]
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.6.0','<'))  die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');

// 定义应用目录
define('APP_PATH', __DIR__ . '/app/');

// 定义入口为admin
define('ENTRANCE', 'admin');

// 检查是否安装
if(!is_file(APP_PATH.'install/install.lock')) {
    header('Location: /');
    exit;
}

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
INFO;
        if (!file_put_contents(ROOT_PATH.$file, $code)) {
            return fasle;
        }
        return true;
    }
}
