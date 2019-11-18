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

namespace app\common\library;

use think\facade\Cache;
use think\facade\Config;
use think\facade\Env;
use think\Loader;

class Token
{
    /**
     * token实例
     * @var array
     */
    public static $instance = [];

    /**
     * 驱动句柄
     * @var object
     */
    public static $handler;

    /**
     * 连接驱动
     * @access public
     * @param  array         $options  配置数组
     * @param  bool|string   $name 缓存连接标识 true 强制重新连接
     * @return Driver
     */
    public static function connect(array $options = [], $name = false)
    {
        $type = !empty($options['type']) ? $options['type'] : 'Mysql';

        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {

            $class = Loader::factory($type, '\\app\\common\\library\\token\\', $options);
            if (true === $name) {
                return $class;
            }

            self::$instance[$name] = $class;
        }

        return self::$instance[$name];
    }

    /**
     * 自动初始化Token
     * @access public
     * @param  array $options 配置数组
     * @return Driver
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            if (empty($options) && 'complex' == Config::get('token.type')) {
                $default = Config::get('token.default');
                $options = Config::get('token.' . $default['type']) ?: $default;
            } elseif (empty($options)) {
                $options = Config::get('token.');
            }

            $options['extend'] =  $options['extend'] ?? (array)Config::get('token.extend');

            if (in_array($options['type'], $options['extend'])) {
                self::$handler = self::connect($options);
            } else {
                self::$handler = Cache::connect($options);
            }
        }

        return self::$handler;
    }

    /**
     * 写入Token
     * 
     * @param  string $token token标识
     * @param  mixed $value 值
     * @param  int|null $expire 有效时间 0、null为永久
     * @return boolean
     */
    public static function set($token, $value, $expire = null)
    {
        return self::init()->set($token, $value, $expire);
    }

    /**
     * 读取Token
     * 
     * @param  string $token Token标识
     * @param  mixed $default 默认值
     * @return mixed
     */
    public static function get($token, $default = false)
    {
        return self::init()->get($token, $default);
    }

    /**
     * 判断Token是否存在
     * 
     * @param  string $token token标识
     * @return bool
     */
    public static function has($token)
    {
        return self::get($token) ? true : false;
    }

    /**
     * 判断Token是否可用
     * 
     * @param string $token token标识
     * @param  mixed $value 值
     * @return bool
     */
    public static function check($token, $value)
    {
        return self::init()->check($token, $value);
    }

    /**
     * 删除Token
     * 
     * @param  string $token token标识
     * @return bool
     */
    public static function rm($token)
    {
        return self::delete($token);
    }

    /**
     * 删除Token
     * @param string $token 标签名
     * @return bool
     */
    public static function delete($token)
    {
        return self::init()->delete($token);
    }

    /**
     * 按映射值清除Token
     * 
     * @param  int $value 值
     * @return bool
     */
    public static function clear($value = null)
    {
        return self::init()->clear($value);
    }

    /**
     * 自动生成并缓存token
     * 
     * @param  int $value 值
     * @return bool
     */
    public static function build($value, $expire = null)
    {
        $config = Config::get('token.');
        $token  = hash_hmac($config['algos'], time(), random());

        self::init()->set($token, $value, $expire);
        
        return $token;
    }
}
