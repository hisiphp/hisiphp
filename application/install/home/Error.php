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

namespace app\install\home;

use app\common\controller\Common;
use think\exception\HttpException;
use app\system\model\SystemUser as UserModel;
use think\Db;
use Env;

class Error extends Common
{
    public function initialize()
    {
        $this->root_path = Env::get('root_path');
    }

    public function index($step = 0)
    {

        // 检测PHP环境
        if(version_compare(PHP_VERSION,'5.6.0','<')) {
            return $this->error('PHP版本过低，最少需要PHP5.6，请升级PHP版本（推荐使用php7）！');
        };

        if (strpos($this->request->url(), 'public/')) {
            return $this->error('请将网站根目录指向public');
        }

        if (is_file(Env::get('app_path').'install.lock')) {
            return $this->error('如需重新安装，请手动删除/install.lock文件');
        }

        switch ($step) {
            case 2:
                session('install_error', false);
                return self::step2();
                break;
            case 3:
                if (session('install_error')) {
                    return $this->error('环境检测未通过，不能进行下一步操作！');
                }
                return self::step3();
                break;
            case 4:
                if (session('install_error')) {
                    return $this->error('环境检测未通过，不能进行下一步操作！');
                }
                return self::step4();
                break;
            case 5:
                if (session('install_error')) {
                    return $this->error('初始失败！');
                }
                return self::step5();
                break;
            
            default:
                session('install_error', false);
                return $this->fetch('index');
                break;
        }
    }

    /**
     * 第二步：环境检测
     * @return mixed
     */
    private function step2()
    {
        $data           = [];
        $data['env']    = self::checkNnv();
        $data['dir']    = self::checkDir();
        $data['func']   = self::checkFunc();

        $this->assign('data', $data);

        return $this->fetch('step2');
    }
    
    /**
     * 第三步：初始化配置
     * @return mixed
     */
    private function step3()
    {
        return $this->fetch('step3');
    }
    
    /**
     * 第四步：执行安装
     * @return mixed
     */
    private function step4()
    {
        if ($this->request->isPost()) {

            if (!is_writable($this->root_path.'config/database.php')) {
                return $this->error('[config/database.php]无读写权限！');
            }
            
            $data = $this->request->post();
            $data['type'] = 'mysql';
            $rule = [
                'hostname|服务器地址' => 'require',
                'hostport|数据库端口' => 'require|number',
                'database|数据库名称' => 'require',
                'username|数据库账号' => 'require',
                'prefix|数据库前缀' => 'require|regex:^[a-z0-9]{1,20}[_]{1}',
                'cover|覆盖数据库' => 'require|in:0,1',
            ];

            $validate = $this->validate($data, $rule);

            if (true !== $validate) {
                return $this->error($validate);
            }

            $cover = $data['cover'];
            unset($data['cover']);
            $config = include $this->root_path.'config/database.php';

            foreach ($data as $k => $v) {

                if (array_key_exists($k, $config) === false) {
                    return $this->error('参数'.$k.'不存在！');
                }

            }

            // 不存在的数据库会导致连接失败
            $database = $data['database'];
            unset($data['database']);

            // 创建数据库连接
            $db_connect = Db::connect($data);

            // 检测数据库连接
            try{
                $db_connect->execute('select version()');
            }catch(\Exception $e){
                $this->error('数据库连接失败，请检查数据库配置！');
            }

            // 不覆盖检测是否已存在数据库
            if (!$cover) {
                $check = $db_connect->execute('SELECT * FROM information_schema.schemata WHERE schema_name="'.$database.'"');
                if ($check) {
                    $this->error('该数据库已存在，如需覆盖，请选择覆盖数据库！');
                }
            }

            // 创建数据库
            if (!$db_connect->execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8")) {
                return $this->error($db_connect->getError());
            }

            $data['database'] = $database;

            // 生成配置文件
            self::mkDatabase($data);

            return $this->success('数据库连接成功', '');

        } else {

            return $this->error('非法访问');
            
        }
    }
    
    /**
     * 第五步：数据库安装
     * @return mixed
     */
    private function step5()
    {
        $account    = $this->request->post('account');
        $password   = $this->request->post('password');

        $config = include $this->root_path.'config/database.php';

        if (empty($config['hostname']) || empty($config['database']) || empty($config['username'])) {
            return $this->error('请先点击测试数据库连接！');
        }

        if (empty($account) || empty($password)) {
            return $this->error('请填写管理账号和密码！');
        }

        $rule = [
            'account|管理员账号' => 'require|alphaNum|length:4,20',
            'password|管理员密码' => 'require|length:6,20',
        ];

        $validate = $this->validate(['account' => $account, 'password' => $password], $rule);

        if (true !== $validate) {
            return $this->error($validate);
        }

        // 导入系统初始数据库结构
        $sqlFile = Env::get('app_path').'install/sql/install.sql';

        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, ['hisiphp_' => $config['prefix']]);

            if ($sqlList) {

                $sqlList = array_filter($sqlList);

                foreach ($sqlList as $v) {

                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        return $this->error('请启用InnoDB数据引擎，并检查数据库是否有DROP和CREATE权限');
                    }

                }

            }

        }

        // 注册管理员账号
        $user = new UserModel;

        $map                    = [];
        $map['role_id']         = 1;
        $map['nick']            = '超级管理员';
        $map['username']        = $account;
        $map['password']        = password_hash(md5($password), PASSWORD_DEFAULT);;
        $map['auth']            = '';
        $map['email']           = '';
        $map['mobile']          = '';
        $map['last_login_ip']   = '';
        $map['last_login_time'] = request()->time();
        $res = $user->create($map);

        if (!$res) {
            return $this->error($user->getError() ? $user->getError() : '管理员账号设置失败！');
        }

        file_put_contents($this->root_path.'install.lock', "如需重新安装，请手动删除此文件\n安装时间：".date('Y-m-d H:i:s'));

        //站点密匙
        $auth = password_hash(request()->time(), PASSWORD_DEFAULT);
        $hs_auth = <<<INFO
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

return ['key' => '{$auth}'];

INFO;
        file_put_contents(Env::get('config_path').'hs_auth.php', $hs_auth);

        // 获取站点根目录
        $rootDir = request()->baseFile();
        $rootDir  = preg_replace(['/index.php$/'], [''], $rootDir);

        return $this->success('恭喜您！系统安装成功，稍后将自动跳转至后台(/admin.php)', $rootDir.'admin.php');
    }
    
    /**
     * 环境检测
     * @return array
     */
    private function checkNnv()
    {
        $items = [
            'os'      => ['操作系统', '不限制', '类Unix', PHP_OS, 'ok'],
            'php'     => ['PHP版本', '5.6', '5.6及以上', PHP_VERSION, 'ok'],
            'gd'      => ['GD库', '2.0', '2.0及以上', '未知', 'ok'],
        ];

        if ($items['php'][3] < $items['php'][1]) {

            $items['php'][4] = 'no';
            session('install_error', true);

        }

        $tmp = function_exists('gd_info') ? gd_info() : [];

        if (empty($tmp['GD Version'])) {

            $items['gd'][3] = '未安装';
            $items['gd'][4] = 'no';
            session('install_error', true);

        } else {

            $items['gd'][3] = $tmp['GD Version'];

        }

        return $items;
    }
    
    /**
     * 目录权限检查
     * @return array
     */
    private function checkDir()
    {
        $items = [
            ['dir', $this->root_path.'application', 'application', '读写', '读写', 'ok'],
            ['dir', $this->root_path.'extend', 'extend', '读写', '读写', 'ok'],
            ['dir', $this->root_path.'backup', 'backup', '读写', '读写', 'ok'],
            ['dir', $this->root_path.'plugins', 'plugins', '读写', '读写', 'ok'],
            ['dir', './static', './static', '读写', '读写', 'ok'],
            ['dir', './theme', './theme', '读写', '读写', 'ok'],
            ['dir', './upload', './upload', '读写', '读写', 'ok'],
            ['file', $this->root_path.'config', 'config', '读写', '读写', 'ok'],
            ['file', $this->root_path.'version.php', 'version.php', '读写', '读写', 'ok'],
            ['file', './admin.php', './admin.php', '读写', '读写', 'ok'],
        ];

        foreach ($items as &$v) {

            if ($v[0] == 'dir') {// 文件夹

                if(!is_writable($v[1])) {

                    if(is_dir($v[1])) {
                        $v[4] = '不可写';
                        $v[5] = 'no';
                    } else {
                        $v[4] = '不存在';
                        $v[5] = 'no';
                    }

                    session('install_error', true);
                }

            } else {// 文件

                if(!is_writable($v[1])) {

                    $v[4] = '不可写';
                    $v[5] = 'no';
                    session('install_error', true);

                }

            }

        }

        return $items;
    }
    
    /**
     * 函数及扩展检查
     * @return array
     */
    private function checkFunc()
    {
        $items = [
            ['pdo', '支持', 'yes', '类'],
            ['pdo_mysql', '支持', 'yes', '模块'],
            ['zip', '支持', 'yes', '模块'],
            ['fileinfo', '支持', 'yes', '模块'],
            ['curl', '支持', 'yes', '模块'],
            ['xml', '支持', 'yes', '函数'],
            ['file_get_contents', '支持', 'yes', '函数'],
            ['mb_strlen', '支持', 'yes', '函数'],
            ['gzopen', '支持', 'yes', '函数'],
        ];

        foreach ($items as &$v) {
            if(('类'==$v[3] && !class_exists($v[0])) || ('模块'==$v[3] && !extension_loaded($v[0])) || ('函数'==$v[3] && !function_exists($v[0])) ) {
                $v[1] = '不支持';
                $v[2] = 'no';
                session('install_error', true);
            }
        }

        return $items;
    }
    
    /**
     * 生成数据库配置文件
     * @return array
     */
    private function mkDatabase(array $data)
    {
        $code = <<<INFO
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '{$data['hostname']}',
    // 数据库名
    'database'        => '{$data['database']}',
    // 用户名
    'username'        => '{$data['username']}',
    // 密码
    'password'        => '{$data['password']}',
    // 端口
    'hostport'        => '{$data['hostport']}',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => '{$data['prefix']}',
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 自动读取主库数据
    'read_master'     => false,
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Builder类
    'builder'         => '',
    // Query类
    'query'           => '\\think\\db\\Query',
    // 是否需要断线重连
    'break_reconnect' => false,
    // 断线标识字符串
    'break_match_str' => [],
];

INFO;
        file_put_contents(Env::get('config_path').'database.php', $code);

        // 判断写入是否成功
        $config = include Env::get('config_path').'database.php';

        if (empty($config['database']) || $config['database'] != $data['database']) {
            return $this->error('[config/database.php]数据库配置写入失败！');
            exit;
        }
    }
}
