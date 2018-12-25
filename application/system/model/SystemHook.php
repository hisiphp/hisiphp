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

/**
 * 钩子模型
 * @package app\system\model
 */
class SystemHook extends Model
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
        $validate = new \app\system\validate\Hook;;
        if($validate->check($data) !== true) {
            $this->error = $validate->getError();
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
