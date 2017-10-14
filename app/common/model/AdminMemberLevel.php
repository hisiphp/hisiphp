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
use app\common\model\AdminMember as MemberModel;
/**
 * 会员等级模型
 * @package app\common\model
 */
class AdminMemberLevel extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取所有会员等级(下拉列)
     * @param int $id 选中的ID
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public static function getOption($id = 0)
    {
        $rows = self::column('id,name');
        $str = '';
        foreach ($rows as $k => $v) {
            if ($id == $k) {
                $str .= '<option value="'.$k.'" selected>'.$v.'</option>';
            } else {
                $str .= '<option value="'.$k.'">'.$v.'</option>';
            }
        }
        return $str;
    }

    /**
     * 删除会员
     * @param string $id 会员ID
     * @author 橘子俊 <364666827@qq.com>
     * @return bool
     */
    public function del($id = 0) 
    {
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v <= 0) {
                    $error .= '参数传递错误['.$v.']！<br>';
                    continue;
                }

                // 判断是否有会员绑定此等级
                if (MemberModel::where('level_id', $v)->find()) {
                    $error .= '删除失败，已有会员绑定此等级ID['.$v.']！<br>';
                    continue;
                }
                $map = [];
                $map['id'] = $v;
                self::where($map)->delete();
            }

            if ($error) {
                $this->error = $error;
                return false;
            }
        } else {
            $id = (int)$id;
            if ($id <= 0) {
                $this->error = '参数传递错误！';
                return false;
            }

            // 判断是否有会员绑定此等级
            if (MemberModel::where('level_id', $id)->find()) {
                $this->error = '删除失败，已有会员绑定此等级ID！<br>';
                return false;
            }

            $map = [];
            $map['id'] = $id;
            self::where($map)->delete();
        }

        return true;
    }

    /**
     * 获取所有会员等级
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    public static function getAll()
    {
        return self::column('id,name,discount,min_exper,max_exper');
    }
}
