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

use app\system\model\SystemRole as RoleModel;
use think\facade\Cache;
use think\Model;

/**
 * 管理员角色索引模型
 * @package app\system\model
 */
class SystemUserRole extends Model
{
    protected $autoWriteTimestamp = false;

    // 缓存标签名
    const CACHE_TAG = 'system@user_role';

    protected static function init()
    {
        // 新增后
        self::event('after_insert', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });

        // 更新后
        self::event('after_update', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });

        // 删除后
        self::event('after_delete', function ($obj) {
            Cache::rm(self::CACHE_TAG);
        });
    }

    /**
     * 获取同组织下的所有管理员ID
     *
     * @param string|array $roleIds
     * @return array
     */
    public static function getOrgUserId($roleIds)
    {
        $cacheName = 'org_user_id_'.$roleIds;
        $ids = Cache::get($cacheName);
        if (!$ids) {
            $ids = self::where('role_id', 'in', $roleIds)->distinct(true)->column('user_id');
            Cache::tag(self::CACHE_TAG)->set($cacheName, $ids);
        }
        
        return $ids;
    }
}
