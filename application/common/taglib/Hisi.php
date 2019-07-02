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

namespace app\common\taglib;

use think\template\TagLib;

/**
 * Hisi标签库解析类
 * @author 橘子俊 <364666827@qq.com>
 */
class Hisi extends Taglib
{
    // 标签定义
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'loop' => [
            'attr' => 'model,table,data,with,where,group,cache,order,id,key,sql,field,limit,page,mod,empty',
            'close' => 1,
            'level' => 5,
        ],
    ];

    /**
     * [通用]获取数据
     */
    public function tagLoop($tag, $content)
    {
        $model      = isset($tag['model'])  ? $tag['model']         : '';
        $table      = isset($tag['table'])  ? $tag['table']         : '';
        $data       = isset($tag['data'])   ? $tag['data']          : '';
        $order      = isset($tag['order'])  ? $tag['order']         : '';
        $with       = isset($tag['with'])   ? $tag['with']          : '';
        $group      = isset($tag['group'])  ? $tag['group']         : '';
        $limit      = isset($tag['limit'])  ? intval($tag['limit']) : 20;
        $where      = isset($tag['where'])  ? $tag['where']         : '';
        $page       = isset($tag['page'])   ? $tag['page']          : false;
        $sql        = isset($tag['sql'])    ? $tag['sql']           : '';
        $field      = isset($tag['field'])  ? $tag['field']         : '';
        $cache      = isset($tag['cache'])  ? $tag['cache']         : false;
        $id         = isset($tag['id'])     ? $tag['id']            : 'r';
        $key        = isset($tag['key'])    ? $tag['key']           : 'i';
        $mod        = isset($tag['mod'])    ? $tag['mod']           : '2';
        $empty      = isset($tag['empty'])  ? $tag['empty']         : '';

        $parseStr = '<?php ';

        if ($sql) {

            $parseStr .= '$hisiData = \think\Db::query("'.$sql.'");';

        } else if ($data) {

            $parseStr .= '$hisiData = '.$data.';';

        } else if (isset($tag['model']) || isset($tag['table'])) {

            $parseStr .= '$params = [];';
            $dbStr = '';

            if ($model) {
                $dbStr .= '$hisiData = model("'.$model.'")';

                if ($with) {
                    $dbStr .= '->with("'.$with.'")';
                }
            } else {
                $dbStr .= '$hisiData = db("'.$table.'")';
            }

            if ($where) {
                $dbStr .= '->where('.$where.')';
            }

            if ($group) {
                $dbStr .= '->group("'.$group.'")';
            }

            if ($field) {
                $dbStr .= '->field("'.$field.'")';
            }

            if ($page) {
                $dbStr .= '->page('.$page.')';
            }

            if ($limit) {
                $dbStr .= '->limit('.$limit.')';
            }

            if ($order) {
                $dbStr .= '->order("'.$order.'")';
            }

            if ($cache) {
                $dbStr .= '->cache('.$cache.')';
            }

            $parseStr .= $dbStr.'->select();';
        } else {

            throw new \think\exception\HttpException(500, 'model、table、data、sql属性至少传一个');

        }

        $parseStr .= 'if(is_array($hisiData) || $hisiData instanceof \think\Collection || $hisiData instanceof \think\Paginator): $' . $key . ' = 0;';
        $parseStr .= ' $__LIST__ = $hisiData;';
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "' . $empty . '" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$' . $id . '): ';
        $parseStr .= '$mod = ($' . $key . ' % ' . $mod . ' );';
        $parseStr .= '++$' . $key . ';?>';
        $parseStr .= $content;
        $parseStr .= '<?php endforeach; endif; else: echo "' . $empty . '" ;endif; ?>';
        return $parseStr;
    }
}
