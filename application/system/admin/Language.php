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

namespace app\system\admin;

use app\system\model\SystemLanguage as LanguageModel;

/**
 * 语言包管理控制器
 * @package app\system\admin
 */
class Language extends Admin
{
    // [通用添加、修改专用] 模型名称，格式：模块名/模型名
    protected $hisiModel = 'SystemLanguage';
    // [通用添加、修改专用] 验证器类，格式：app\模块\validate\验证器类名
    protected $hisiValidate = 'app\system\validate\SystemLanguage';

    /**
     * 语言包管理首页
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $data           = [];
            $data['data']   = LanguageModel::order('sort asc')->select();
            $data['code']   = 0;
            return json($data);
        }

        return $this->fetch();
    }
}
