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
use think\Loader;

/**
 * 钩子模型
 * @package app\admin\model
 */
class AdminHook extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 钩子入库
     * @param array $data 入库数据
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */  
    public function storage($data = [])
    {
        if (empty($data)) {
            $data = request()->post();
        }

        // 如果钩子名称存在直接返回true
        if (self::where('name', $data['name'])->find()) {
            return true;
        }

        // 验证
        $valid = Loader::validate('Hook');
        if($valid->check($data) !== true) {
            $this->error = $valid->getError();
            return false;
        }

        if (isset($data['id']) && !empty($data['id'])) {
            $res = $this->update($data);
        } else {
            $res = $this->create($data);
        }
        if (!$res) {
            $this->error = '保存失败！';
            return false;
        }
        
        return $res;
    }

    /**
     * 删除钩子
     * @param string $source 来源名称
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */    
    public static function delHook($source = '')
    {
        if (empty($source)) {
            return false;
        }

        if (self::where('source', $source)->delete() === false) {
            return false;
        }
        return true;
    }
}