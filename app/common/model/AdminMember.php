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
namespace app\common\model;

use think\Model;

/**
 * 会员模型
 * @package app\common\model
 */
class AdminMember extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = false;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 对密码进行加密
    public function setPasswordAttr($value)
    {
        if (empty($value)) return '';
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 最后登陆ip
    public function setLastLoginIpAttr()
    {
        return get_client_ip();
    }

    // 最后登陆ip
    public function setLastLoginTimeAttr()
    {
        return request()->time();
    }

    // 有效期
    public function setExpireTimeAttr($value)
    {
        if ($value != 0) {
            return strtotime($value);
        }
        return 0;
    }

    // 有效期
    public function getExpireTimeAttr($value)
    {
        if ($value != 0) {
            return date('Y-m-d', $value);
        }
        return 0;
    }

    /**
     * 注册
     * @param array $data 参数，可传参数username,password,email,mobile,nick
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    public function register($data = [])
    {
        $map = [];
        $map['nick'] = '';
        $map['email'] = '';
        $map['mobile'] = '';
        $map['username'] = '';
        $map['avatar'] = '';
        $map['level_id'] = 0;
        if (empty($data['account']) && empty($data['email']) && empty($data['mobile']) && empty($data['username'])) {
            $this->error = '用户名、手机、邮箱至少选填一项！';
            return false;

        }
        if (!isset($data['password']) || empty($data['password'])) {
            $this->error = '密码为必填项！';
            return false;
        }
        // 匹配账号类型
        if (is_email($data['account'])) {// 邮箱
            $map['email'] = $data['account'];
        } elseif (is_mobile($data['account'])) {// 手机号
            $map['mobile'] = $data['account'];
        } elseif (is_username($data['account'])) {// 用户名
            $map['username'] = $data['account'];
        } else {
            $this->error = '注册账号异常！';
            return false;
        }
        // 匹配注册方式
        if (isset($data['email']) && !empty($data['email'])) {
            $map['email'] = $data['email'];
        }
        if (isset($data['mobile']) && !empty($data['mobile'])) {
            $map['mobile'] = $data['mobile'];
        }
        if (isset($data['username']) && !empty($data['username'])) {
            $map['username'] = $data['username'];
        }

        $map['password'] = $data['password'];
        if (isset($data['nick']) && !empty($data['nick'])) {
            $map['nick'] = $data['nick'];
        }

        $level = model('AdminMemberLevel')->where('default',1)->find();
        if ($level) {
            $map['level_id'] = $level['id'];
            $map['expire_time'] = $level['expire'] > 0 ? strtotime('+ '.$level['expire'].' days') : 0;
        }
        $res = $this->validate('AdminMember')->isUpdate(false)->save($map);
        if (!$res) {
            $this->error = $this->getError() ? $this->getError() : '注册失败！';
            return false;
        }
        $map['id'] = $this->id;
        unset($map['password']);
        return self::autoLogin($map);
    }

    /**
     * 授权登录注册，除了昵称无其他任何注册信息，只为了提供授权登录时绑定member_id
     * @param string $nick 昵称
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    public function oauthRegister($nick = '')
    {
        $level = model('AdminMemberLevel')->where('default',1)->find();
        $map = [];
        $map['nick'] = $nick;
        $map['password'] = '';
        $map['email'] = '';
        $map['mobile'] = '';
        $map['username'] = '';
        $map['avatar'] = '';
        $map['last_login_ip'] = get_client_ip();
        $map['last_login_time'] = request()->time();
        if ($level) {
            $map['level_id'] = $level['id'];
            $map['expire_time'] = $level['expire'] > 0 ? strtotime('+ '.$level['expire'].' days') : 0;
        }
        $res = $this->create($map);
        if (!$res) {
            $this->error = $this->getError() ? $this->getError() : '授权登录失败！';
            return false;
        }

        $map['id'] = $res->id;
        unset($map['password']);
        return self::autoLogin($map);
    }

    /**
     * 登录
     * @param string $account 账号
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @param string $field 登陆之后缓存的字段
     * @author 橘子俊 <364666827@qq.com>
     * @return stirng|array
     */
    public function login($account = '', $password = '', $remember = false, $field = 'nick,username,mobile,email,avatar', $token = true)
    {
        $account = trim($account);
        $password = trim($password);
        $field = trim($field, ',');
        if (empty($account) || empty($password)) {
            $this->error = '请输入账号和密码！';
            return false;
        }

        $map = $rule = [];
        $map['status'] = 1;

        // 匹配登录方式
        if (is_email($account)) {
            // 邮箱登录
            $map['email'] = $rule['email'] = $account;
        } elseif (is_mobile($account)) {
            // 手机号登录
            $map['mobile'] = $rule['mobile']  = $account;
        } elseif (is_username($account)) {
            // 用户名登录
            $map['username'] = $rule['username']  = $account;
        } else {
            $this->error = '登陆账号异常！';
            return false;
        }
        $rule['password'] = $password;
        if ($token !== false) {
            $rule['__token__'] = input('param.__token__') ? input('param.__token__') : $token;
            $scene = 'login_token';
        } else {
            $scene = 'login';
        }
        // 验证
        if ($this->validateData($rule, 'AdminMember.'.$scene) != true) {
            $this->error = $this->getError();
            return false;
        }

        $member = self::where($map)->field('id,'.$field.',level_id,password,expire_time')->find();
        if (!$member) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }

        // 密码校验
        if (!password_verify($password, $member->password)) {
            $this->error = '登陆密码错误！';
            return false;
        }

        // 检查有效期
        if ($member->expire_time > 0 &&  $member->expire_time < request()->time()) {
            $this->error = '账号已过期！';
            return false;
        }

        $login = [];
        $login['id'] = $member->id;
        $login['level_id'] = $member->level_id;
        $fields = explode(',', $field);
        foreach ($fields as $v) {
            if ($v == 'password') {
                continue;
            }
            $login[$v] = $member->$v;
        }
        return self::autoLogin($login);
    }

    /**
     * 判断是否登录
     * @author 橘子俊 <364666827@qq.com>
     * @return bool|array
     */
    public function isLogin() 
    {
        $user = session('login_member');
        if (!isset($user['id'])) {
            return false;
        } else {
            return session('login_member_sign') == $this->dataSign($user) ? $user : false;
        }
    }

    /**
     * 自动登陆
     * @author 橘子俊 <364666827@qq.com>
     * @param bool $oauth 第三方授权登录
     * @return bool|array
     */
    public function autoLogin($data = [], $oauth = false)
    {
        if ($oauth) {
            $map = [];
            $map['id'] = $data['id'];
            $map['status'] = 1;
            $data = $this->where($map)->field('id,level_id,nick,username,mobile,email,expire_time,avatar')->find();
            if (!$data) {
                $this->error = '用户不存在或被禁用！';
                return false;
            }

            // 检查有效期
            if ($data['expire_time'] > 0 &&  $data['expire_time'] < request()->time()) {
                $this->error = '账号已过期！';
                return false;
            }
        }
        $map = [];
        $map['last_login_ip'] = get_client_ip();
        $map['last_login_time'] = request()->time();
        $this->where('id', $data['id'])->update($map);
        session('login_member', $data);
        session('login_member_sign', $this->dataSign($data));
        runhook('system_member_login', $data);
        return $data;
    }

    /**
     * 退出登陆
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public static function logout() 
    {
        session(null);
        cookie(null);
        return true;
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
