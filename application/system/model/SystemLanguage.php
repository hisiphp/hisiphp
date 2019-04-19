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
use Env;
use hisi\Dir;
use hisi\PclZip;

/**
 * 钩子模型
 * @package app\system\model
 */
class SystemLanguage extends Model
{
    protected $autoWriteTimestamp = false;

    public static function init()
    {
        // 新增前
        self::event('before_insert', function ($obj) {
            return true;
        });

        // 新增后
        self::event('after_insert', function ($obj) {
            // 安装语言包
            return $obj->install($obj);
        });

        // 更新前
        self::event('before_update', function ($obj) {
            if ($obj['id'] == 1) {// 禁止修改ID为1的语言包
                return false;
            }
            
            $row = self::where('id', $obj['id'])->find();
            if (empty($row['pack']) && !empty($obj['pack'])) {
                return $obj->install($obj);
            }

            return true;
        });

        // 删除前
        self::event('before_delete', function ($obj) {
            return $obj->deleteLang($obj);
        });
    }

    /**
     * 获取语言包列表
     * @param  string $name 配置名
     * @param  bool $update 是否更新缓存
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function lists($name = '', $update = false)
    {
        $result = cache('sys_language');
        if (!$result || $update == true) {
            $result = self::order('sort asc')->column('id,code,name,icon,pack', 'code');
            cache('sys_language', $result);
        }
        $lang = config('default_lang');
        if ($name) {
            if (isset($result[$name])) {
                return $result[$name]['id'];
            } else {
                $lang = current($result);
                return $lang['id'];
            }
        }
        return $result;
    }

    /**
     * 安装语言包
     * @param  object $obj 当前的模型对象实例
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function install($obj) 
    {
        if (empty($obj['pack'])) {
            $obj->error = '语言包不存在';
            return false;
        }

        $pack = '.'.$obj['pack'];
        if (file_exists($pack)) {

            $decomPath = Env::get('runtime_path').'lang/';
            if (!is_dir($decomPath)) {
                Dir::create($decomPath, 0777);
            }

            // 解压升级包
            $archive = new PclZip();
            $archive->PclZip($pack);
            if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
                $obj->error = '语言包解压失败！';
                return false;
            }

            // 导入系统模块语言包
            $adminLang = $decomPath.'system/'.$obj['code'].'.php';
            if (file_exists($adminLang)) {
                copy($adminLang, Env::get('app_path').'system/lang/'.$obj['code'].'.php');
            }

            // 导入公共语言包
            $commonLang = $decomPath.'lang/'.$obj['code'].'.php';
            if (file_exists($commonLang)) {
                if (!is_dir(Env::get('app_path').'lang/')) {
                    Dir::create(Env::get('app_path').'lang/');
                }
                copy($commonLang, Env::get('app_path').'lang/'.$obj['code'].'.php');
            }

            // 导入后台菜单
            if (file_exists($decomPath.'menu.php')) {
                $menu = include_once $decomPath.'menu.php';
                $menuData = [];
                foreach ($menu as $key => $v) {
                    if (empty($v['title'])) continue;
                    $menuData[$key]['menu_id'] = $v['menu_id'];
                    $menuData[$key]['title'] = $v['title'];
                    $menuData[$key]['lang'] = $obj['id'];
                }

                if ($menuData) {
                    db('system_menu_lang')->insertAll($menuData);
                }
            }

            Dir::delDir($decomPath);
        }

        return true;
    }

    /**
     * 删除语言包
     * @param  object $obj 当前的模型对象实例
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function deleteLang($obj)
    {
        if ($obj['id'] == 1) {
            return false;
        }
        
        // 删除语言包相关文件
        $admin_lang = Env::get('app_path').'system/lang/'.$obj['code'].'.php';
        if (file_exists($admin_lang)) {
            @unlink($admin_lang);
        }
        
        $common_lang = Env::get('app_path').'common/lang/'.$obj['code'].'.php';
        if (file_exists($common_lang)) {
            @unlink($common_lang);
        }

        if (file_exists('.'.$obj['pack'])) {
            @unlink('.'.$obj['pack']);
        }

        // 删除管理菜单
        db('system_menu_lang')->where('lang', $obj['id'])->delete();

        // 更新语言包缓存
        $obj->lists('', true);
        
        return true;
    }
}
