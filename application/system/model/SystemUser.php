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
namespace app\system\model;

use think\Model;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemLog as LogModel;
use think\facade\Cache;

/**
 * 后台用户模型
 * @package app\system\model
 */
class SystemUser extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public function setAuthAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value);
    }

    public function getAuthAttr($value)
    {
        if (empty($value)) return [];
        return json_decode($value, 1);
    }

    public function setRoleIdAttr($value)
    {
        if (empty($value)) return '';
        return implode(',', (array)$value);
    }

    public function getRoleIdAttr($value)
    {
        if (empty($value)) return [];
        return explode(',', $value);
    }

    // 获取最后登陆ip
    public function setLastLoginIpAttr($value)
    {
        return get_client_ip();
    }

    // 格式化最后登录时间
    public function getLastLoginTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }
    
    // 关联角色
    public function hasRoles()
    {
        return $this->belongsToMany('SystemRole', 'SystemUserRole', 'role_id', 'user_id');
    }

    // 关联索引
    public function hasIndexs()
    {
        return $this->hasMany('SystemUserRole', 'user_id');
    }

    // 模型事件
    public static function init()
    {
        // 新增后
        self::event('after_insert', function($obj) {
            $obj->hasRoles()->saveAll($obj->role_id);
            runhook('admin_create', $obj);
        });

        // 更新前
        self::event('before_update', function ($obj) {
            $data = $obj->getData();
            
            if ($data['id'] == 1 && ADMIN_ID != 1) {
                $obj->error = '禁止修改超级管理员';
                return false;
            }
            
            (isset($obj->role_id) && $obj->role_id) && $obj->hasRoles()->detach();
            
            return true;
        });

        // 更新后
        self::event('after_update', function($obj) {
            (isset($obj->role_id) && $obj->role_id) && $obj->hasRoles()->saveAll($obj->role_id);
            runhook('admin_update', $obj);
        });

        // 删除前
        self::event('before_delete', function ($obj) {

            if ($obj['id'] == ADMIN_ID) {
                $obj->error = '不能删除当前登陆的用户';
                return false;
            }

            if ($obj['id'] == 1) {
                $obj->error = '不能删除超级管理员';
                return false;
            }
            
            // 删除角色索引表
            $obj->hasRoles()->detach();

            // 删除用户收藏的菜单
            (new MenuModel)->delUser($obj['id']);
            runhook('admin_delete', $obj);
        });
    }
    
    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @author 橘子俊 <364666827@qq.com>
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $remember = false)
    {
        $username = trim($username);
        $password = trim($password);
        $map = [];
        $map['status'] = 1;
        $map['username'] = $username;

        $validate = new \app\system\validate\SystemUser;
        
        if ($validate->scene('login')->check(input('post.')) !== true) {
            $this->error = $validate->getError();
            return false;
        }
        
        $user = self::with('hasRoles')->where($map)->find();
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }

        // 密码校验
        if (!password_verify($password, $user->password)) {
            $this->error = '登录密码错误！';
            return false;
        }

        $roleIds = [];
        if ($user['id'] != 1) {
            // 非超级管理员，提取关联角色
            $roles = $user->hasRoles->toArray();
            if (empty($roles)) {
                $this->error = '未绑定角色';
                return false;
            }
            
            foreach($roles as $k => $v) {
                $v['status'] == 1 && $roleIds[] = $v['id'];
            }

            if (empty($roleIds)) {
                $this->error = '绑定的角色不可用';
                return false;
            }
        }
        
        // 自动清除过期的系统日志
        LogModel::where('ctime', '<', strtotime('-'.(int)config('sys.system_log_retention').' days'))->delete();

        if ($user->where('id', '=', $user->id)->update(['last_login_time' => time(), 'last_login_ip' => get_client_ip()])) {
            // 执行登陆
            $login              = [];
            $login['uid']       = $user->id;
            $login['role_id']   = implode(',', $roleIds);
            $login['nick']      = $user->nick;
            $login['mobile']    = $user->mobile;
            $login['email']     = $user->email;
            
            // 主题设置
            self::setTheme(isset($user->theme) ? $user->theme : 0);
            self::getThemes(true);
            
            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));
            cookie('hisi_iframe', (int)$user->iframe);
            runhook('admin_login', $login);
            return $user->id;
        }

        return false;
    }

    /**
     * 获取主题列表
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public static function getThemes($cache = false)
    {
        $themeFile = '.'.config('view_replace_str.__ADMIN_CSS__').'/theme.css';
        $themes = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        if (is_file($themeFile)) {
            $content = file_get_contents($themeFile);
            preg_match_all("/\/\*{6}(.+?)\*{6}\//", $content, $diyTheme);
            if (isset($diyTheme[1]) && count($diyTheme[1]) > 0) {
                foreach ($diyTheme[1] as $v) {
                    if (preg_match("/^[A-Za-z0-9\-\_]+$/", trim($v))) {
                        array_push($themes, trim($v));
                    }
                }
                $themes = array_unique($themes);
            }
        }
        if ($cache) {
            session('hisi_admin_themes', $themes);
        }
        return $themes;
    }

    /**
     * 设置主题
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public static function setTheme($name = 'default', $update = false)
    {
        cookie('hisi_admin_theme', $name);
        $result = true;
        if ($update && defined('ADMIN_ID')) {
            $result = self::where('id', ADMIN_ID)->setField('theme', $name);
        }
        return $result;
    }

    /**
     * 判断是否登录
     * @author 橘子俊 <364666827@qq.com>
     * @return bool|array
     */
    public function isLogin() 
    {
        $user = session('admin_user');
        if (isset($user['uid'])) {
            if (!self::where('id', $user['uid'])->find()) {
                return false;
            }
            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }
        return false;
    }

    /**
     * 退出登陆
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public function logout() 
    {
        $user = session('admin_user');
        
        session('admin_user', null);
        session('admin_user_sign', null);

        if (isset($user['uid'])) {
            session('role_auth_'.$user['uid'], null);
            Cache::rm('admin_menu_'.$user['uid'].'_'.dblang('admin').'_'.config('sys.app_debug'));
        }

        runhook('admin_logout', $user);
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @author 橘子俊 <364666827@qq.com>
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }
}
