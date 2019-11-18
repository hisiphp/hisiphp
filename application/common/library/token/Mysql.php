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

namespace app\common\library\token;

use think\facade\Config;

class Mysql
{

    /**
     * 标签名
     * @var string
     */
    protected $tag = '';

    /**
     * 默认配置
     * @var array
     */
    protected $options = [
        'table'      => 'token',
        'expire'     => 2592000,
        'connection' => [],
    ];


    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if ($this->options['connection']) {
            $this->handler = \think\Db::connect($this->options['connection'])->name($this->options['table']);
        } else {
            $this->handler = \think\Db::name($this->options['table']);
        }
    }
    
    /**
     * 存储Token
     * @param   string $token Token
     * @param   int $value 
     * @param   int $expire 过期时长,0表示无限,单位秒
     * @return bool
     */
    public function set($token, $value, $expire = null)
    {
        $nowTime = time();
        $expiretime = !is_null($expire) && $expire !== 0 ? $nowTime + $expire : 0;

        $data               = [];
        $data['token']      = $token;
        $data['value']      = $value;
        $data['create_time']= $nowTime;
        $data['expire_time']= $expiretime;

        if ($this->tag) {
            $data['tag'] = $this->tag;
        }
        
        $this->handler->insert($data);

        return true;
    }

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  string|array
     */
    public function get($token = '')
    {
        if ($this->tag) {
            $data = $this->handler->where('tag', '=', $this->tag)->select();
            $newData = [];

            foreach($data as $v) {
                if (!$v['expire_time'] || $v['expire_time'] > time()) {
                    $newData[$v['token']] = $v['value'];
                } else {
                    self::delete($v['token']);
                }
            }
            
            return $newData;
            
        } else if ($token) {
            $data = $this->handler->where('token', '=', $token)->find();
            if ($data) {
                if (!$data['expire_time'] || $data['expire_time'] > time()) {
                    return $data['value'];
                } else {
                    self::delete($token);
                }
            }
        }
        
        return '';
    }

    /**
     * 判断Token是否可用
     * @param   string $token Token
     * @param   int $value 
     * @return  bool
     */
    public function check($token, $value)
    {
        $data = $this->get($token);
        return $data && $data['value'] == $value ? true : false;
    }

    /**
     * 删除Token
     * @param   string $token
     * @return  bool
     */
    public function delete($token)
    {
        $this->handler->where('token', '=', $token)->delete();
        return true;
    }

    /**
     * 缓存标签
     * 
     * @param  string $name 标签名
     * @return $this
     */
    public function tag($name)
    {
        $this->tag = $name;
        return $this;
    }

    /**
     * 清除 token
     * 支持根据 tag 或 value 批量删除
     * @param   int $val
     * @return  bool
     */
    public function clear($value = '')
    {
        if ($this->tag) {
            $where = [];
            $where[] = ['tag', '=', $this->tag];
            $value && $where[] = ['value', '=', $value];

            $this->handler->where($where)->delete();
        } else if ($value) {
            $this->handler->where('value', '=', $value)->delete();
        }

        return true;
    }
}