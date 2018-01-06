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
namespace app\admin\model;

use think\Model;
/**
 * 后台角色模型
 * @package app\admin\model
 */
class AdminModule extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取模块配置信息
     * @param  string $name 配置名
     * @param  bool $update 是否更新缓存
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public static function getConfig($name = '', $update = false)
    {
        $result = cache('module_config');
        if (!$result || $update == true) {
            $rows = self::where('status', 2)->column('name,config', 'name');
            $result = [];
            foreach ($rows as $k => $r) {
                if (empty($r)) {
                    continue;
                }
                $config = json_decode($r, 1);
                if (!is_array($config)) {
                    continue;
                }
                foreach ($config as $rr) {
                    switch ($rr['type']) {
                        case 'array':
                        case 'checkbox':
                            $result['module_'.$k][$rr['name']] = parse_attr($rr['value']);
                            break;
                        default:
                            $result['module_'.$k][$rr['name']] = $rr['value'];
                            break;
                    }
                }
            }
            cache('module_config', $result);
        }
        return $name != '' ? $result[$name] : $result;
    }

    /**
     * 将已安装模块添加到路由配置文件
     * @param  bool $update 是否更新缓存
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    public static function moduleRoute($update = false)
    {
        $result = cache('module_route');
        if (!$result || $update == true) {
            $map = [];
            $map['status'] = 2;
            $map['name'] =  ['neq', 'admin'];
            $result = self::where($map)->column('name');
            if (!$result) {
                $result = ['route'];
            }
            array_push($result, 'route');
            cache('module_route', $result);
        }
        return $result;
    }

    /**
     * 获取所有已安装模块(下拉列)
     * @param string $select 选中的值
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public static function getOption($select = '', $field='name,title')
    {
        $rows = self::column($field);
        $str = '';
        foreach ($rows as $k => $v) {
            if ($k == 1) {// 过滤超级管理员角色
                continue;
            }
            if ($select == $k) {
                $str .= '<option value="'.$k.'" selected>['.$k.']'.$v.'</option>';
            } else {
                $str .= '<option value="'.$k.'">['.$k.']'.$v.'</option>';
            }
        }
        return $str;
    }

    /**
     * 设计并生成标准模块结构
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public function design($data = [])
    { 
        if (empty($data)) {
            $data = $this->request->post();
        }
        $data['icon'] = ROOT_DIR.'static/app_icon/'.$data['name'].'.png';
        $mod_path = APP_PATH.$data['name'] . DS;
        if (is_dir($mod_path) || self::where('name', $data['name'])->find() || in_array($data['name'], config('hs_system.modules')) !== false) {
            $this->error = '模块已存在！';
            return false;
        }

        if (!is_writable(ROOT_PATH.'app')) {
            $this->error = 'app]目录不可写！';
            return false;
        }

        if (!is_writable(ROOT_PATH.'theme')) {
            $this->error = '[theme]目录不可写！';
            return false;
        }

        if (!is_writable(ROOT_PATH.'static')) {
            $this->error = '[static]目录不可写！';
            return false;
        }

        // 生成模块目录结构
        $build = [];
        if ($data['file']) {
            $build[$data['name']]['__file__'] = explode(',', $data['file']);  
        }
        $build[$data['name']]['__dir__'] = parse_attr($data['dir']);
        $build[$data['name']]['model'] = ['example'];
        $build[$data['name']]['view'] = ['index/index'];
        \think\Build::run($build);
        if (!is_dir($mod_path)) {
            $this->error = '模块目录生成失败[app/'.$data['name'].']！';
            return false;
        }

        // 生成对应的前台主题模板目录、静态资源目录、后台静态资源目录
        $dir_list = [
            'theme'.DS.$data['name'].DS.'default'.DS.'static'.DS.'css',
            'theme'.DS.$data['name'].DS.'default'.DS.'static'.DS.'js',
            'theme'.DS.$data['name'].DS.'default'.DS.'static'.DS.'image',
            'theme'.DS.$data['name'].DS.'default'.DS.'index',
            'static'.DS.$data['name'].DS.'css',
            'static'.DS.$data['name'].DS.'js',
            'static'.DS.$data['name'].DS.'image',
        ];
        self::mkDir($dir_list);
        self::mkThemeConfig('theme'.DS.$data['name'].DS.'default'.DS, $data);
        self::mkSql($mod_path, $data);
        self::mkMenu($mod_path, $data);
        self::mkInfo($mod_path, $data);
        self::mkControl($mod_path, $data);

        // 将生成的模块信息添加到模块管理表
        $sql = [];
        $sql['name'] = $data['name'];
        $sql['identifier'] = $data['identifier'];
        $sql['title'] = $data['title'];
        $sql['intro'] = $data['intro'];
        $sql['author'] = $data['author'];
        $sql['icon'] = $data['icon'];
        $sql['version'] = $data['version'];
        $sql['url'] = $data['url'];
        $sql['config'] = '';
        $sql['status'] = 0;
        self::create($sql);
        if (!is_dir(ROOT_PATH.'static'.DS.'app_icon'.DS)) {
            mkdir(ROOT_PATH.'static'.DS.'app_icon'.DS, 0755, true);
        }
        // 复制默认应用图标
        copy(ROOT_PATH.'static'.DS.'admin'.DS.'image'.DS.'app.png', APP_PATH.$data['name'].DS.$data['name'].'.png');
        copy(ROOT_PATH.'static'.DS.'admin'.DS.'image'.DS.'app.png', ROOT_PATH.'static'.DS.'app_icon'.DS.$data['name'].'.png');
        // 复制admin布局模板到当前模块
        copy(APP_PATH.'admin'.DS.'view'.DS.'block'.DS.'layout.php', $mod_path.'view'.DS.'layout.php');
        return true;
    }

    /**
     * 生成目录
     * @param array $list 目录列表
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function mkDir($list)
    {
        foreach ($list as $dir) {
            if (!is_dir(ROOT_PATH . $dir)) {
                // 创建目录
                mkdir(ROOT_PATH . $dir, 0755, true);
            }
        }
    }

    /**
     * 生成模块控制器
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function mkControl($path = '', $data = [])
    {
        // 删除默认控制器目录和文件
        unlink($path.'controller'.DS.'Index.php');
        rmdir($path.'controller');
        // 生成后台默认控制器
        if (is_dir($path.'admin')) {
            $admin_contro = "<?php\nnamespace app\\".$data["name"]."\\admin;\nuse app\admin\controller\Admin;\n\nclass Index extends Admin\n{\n    public function index()\n    {\n        return ".'$this->afetch()'.";\n    }\n}";
            // 删除框架生成的html文件
            @unlink($path . 'view'.DS.'index'.DS.'index.html');
            file_put_contents($path . 'admin'.DS.'Index.php', $admin_contro);
            file_put_contents($path . 'view'.DS.'index'.DS.'index.php', 'Hellow '.$data["name"]."\n{include file=\"admin@block/layui\" /}");
        }

        // 生成前台默认控制器
        if (is_dir($path.'home')) {
            $home_contro = "<?php\nnamespace app\\".$data["name"]."\\home;\nuse app\common\controller\Common;\n\nclass Index extends Common\n{\n    public function index()\n    {\n        return ".'$this->fetch()'.";\n    }\n}";
            file_put_contents($path . 'home'.DS.'Index.php', $home_contro);
            file_put_contents(ROOT_PATH.'theme'.DS.$data['name'].DS.'default'.DS.'index'.DS.'index.php', '<?php defined("IN_SYSTEM") or die("Access Denied");/* 防止模板被盗 */?>');
        }
    }

    /**
     * 生成SQL文件
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function mkSql($path = '')
    {
        if (!is_dir($path . 'sql')) {
            mkdir($path . 'sql', 0755, true);
        }
        file_put_contents($path . 'sql'.DS.'install.sql', "/*\n sql安装文件\n*/");
        file_put_contents($path . 'sql'.DS.'uninstall.sql', "/*\n sql卸载文件\n*/");
    }

    /**
     * 生成模块菜单文件
     */
    public static function mkMenu($path = '', $data = [])
    {
        // 菜单示例代码
        $menus = <<<INFO
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
 * 模块菜单
 * 字段说明
 * url 【链接地址】格式：{$data['name']}/控制器/方法，可填写完整外链[必须以http开头]
 * param 【扩展参数】格式：a=123&b=234555
 */
return [
    [
        'pid'           => 0,
        'title'         => '{$data['title']}',
        'icon'          => 'aicon ai-shezhi',
        'module'        => '{$data['name']}',
        'url'           => '{$data['name']}',
        'param'         => '',
        'target'        => '_self',
        'sort'          => 100,
    ],
];
INFO;
        file_put_contents($path . 'menu.php', $menus);
    }

    /**
     * 生成模块信息文件
     * @author 橘子俊 <364666827@qq.com>
     */
    public static function mkInfo($path = '', $data = [])
    {
        // 配置内容
        $config = <<<INFO
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
 * 模块基本信息
 */
return [
    // 模块名[必填]
    'name'        => '{$data['name']}',
    // 模块标题[必填]
    'title'       => '{$data['title']}',
    // 模块唯一标识[必填]，格式：模块名.[应用市场ID].module.[应用市场分支ID]
    'identifier'  => '{$data['identifier']}',
    // 主题模板[必填]，默认default
    'theme'        => 'default',
    // 模块图标[选填]
    'icon'        => '{$data['icon']}',
    // 模块简介[选填]
    'intro' => '{$data['intro']}',
    // 开发者[必填]
    'author'      => '{$data['author']}',
    // 开发者网址[选填]
    'author_url'  => '{$data['url']}',
    // 版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
    // 主版本号【位数变化：1-99】：当模块出现大更新或者很大的改动，比如整体架构发生变化。此版本号会变化。
    // 次版本号【位数变化：0-999】：当模块功能有新增或删除，此版本号会变化，如果仅仅是补充原有功能时，此版本号不变化。
    // 修订版本号【位数变化：0-999】：一般是 Bug 修复或是一些小的变动，功能上没有大的变化，修复一个严重的bug即发布一个修订版。
    'version'     => '{$data['version']}',
    // 模块依赖[可选]，格式[[模块名, 模块唯一标识, 依赖版本, 对比方式]]
    'module_depend' => [],
    // 插件依赖[可选]，格式[[插件名, 插件唯一标识, 依赖版本, 对比方式]]
    'plugin_depend' => [],
    // 模块数据表[有数据库表时必填,不包含表前缀]
    'tables' => [
        // 'table_name',
    ],
    // 原始数据库表前缀,模块带sql文件时必须配置
    'db_prefix' => 'db_',
    // 模块预埋钩子[非系统钩子，必须填写]
    'hooks' => [
        // '钩子名称' => '钩子描述'
    ],
    // 模块配置，格式['sort' => '100','title' => '配置标题','name' => '配置名称','type' => '配置类型','options' => '配置选项','value' => '配置默认值', 'tips' => '配置提示'],各参数设置可参考管理后台->系统->系统功能->配置管理->添加
    'config' => [],
];
INFO;
        file_put_contents($path . 'info.php', $config);
    }

    public static function mkThemeConfig($path, $data = [])
    {
        $str = '<?xml version="1.0" encoding="ISO-8859-1"?>
<root>
    <item id="title"><![CDATA[默认模板]]></item>
    <item id="version"><![CDATA[v1.0.0]]></item>
    <item id="time"><![CDATA['.date('Y-m-d H:i').']]></item>
    <item id="author"><![CDATA[HisiPHP]]></item>
    <item id="copyright"><![CDATA[HisiPHP]]></item>
    <item id="identifier" title="默认模板必须留空，非默认模板必须填写对应的应用标识"><![CDATA[]]></item>
    <item id="depend" title="请填写当前对应的模块标识"><![CDATA['.$data['identifier'].']]></item>
</root>';
        file_put_contents($path.'config.xml', $str);
    }
}
