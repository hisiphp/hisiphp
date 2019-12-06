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

namespace app\common\model;

use think\Model;
use think\Db;

class Base extends Model
{
    /**
     * 自动获取模型层、验证器层、服务层、数据库实例（不支持跨模块）
     */
    public function __get($name)
    {
        $class  = $name;
        $layer  = get_layer($name);

        if (!$layer) {
            $name   = ltrim($name, 'plugins');
            $layer  = get_layer(strtolower($name));
            $use    = 'plugins\\'.request()->param('_p').'\\'.$layer.'\\'.ltrim($name, ucfirst($layer));
        
            if (!$layer) {
                return parent::__get($class);
            }
        } else {
            $name   = ltrim($name, $layer); 
            $use    = 'app\\'.request()->module().'\\'.$layer.'\\'.$name;
        }

        if ($layer == 'db') {
            return $this->$class = Db::name($name);
        }

        return $this->$class = (new $use);
    }

    public function __set($name, $value)
    {
        $layer  = get_layer($name);
        if (!$layer) {
            parent::__set($name, $value);
        }

        $this->$name = $value;
    }
}