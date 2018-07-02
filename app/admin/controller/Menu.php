<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.HisiPHP.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AdminMenu as MenuModel;

/**
 * 菜单控制器
 * @package app\admin\controller
 */
class Menu extends Admin
{
    
    /**
     * 菜单管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    // public function index($pid = 0)
    // {
    //     $modules = MenuModel::where('pid', 0)->field('id,title')->order('sort asc')->select();
    //     $tab_data = [];
    //     foreach ($modules as $key => $value) {
    //         $tab_data['menu'][$key]['title'] = $value['title'];
    //         $tab_data['menu'][$key]['url'] = '?pid='.$value['id'];
    //     }
    //     if ($pid == 0) {
    //         $pid = $modules[0]['id'];
    //     }
    //     $menu_list = MenuModel::getAllChild($pid, 0);
    //     // print_r($menu_list);
    //     $tab_data['current'] = url('?pid='.$pid);

    //     $this->assign('menu_list', $menu_list);
    //     $this->assign('pid', $pid);
    //     $this->assign('tab_data', $tab_data);
    //     $this->assign('tab_type', 1);
    //     return $this->fetch();
    // }
    public function index()
    {
        $menu_list = MenuModel::getAllChild(0, 0);
        $tab_data = [];
        foreach ($menu_list as $key => $value) {
            $tab_data['menu'][$key]['title'] = $value['title'];
        }
        $push['title'] = '模块排序';
        array_push($tab_data['menu'], $push);
        $this->assign('menu_list', $menu_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch();
    }

    /**
     * 添加菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function add($pid = '', $mod = '')
    {
        if ($this->request->isPost()) {
            $model = new MenuModel();
            if (!$model->storage()) {
                return $this->error($model->getError());
            }
            return $this->success('保存成功。', url('index'));
        }
        $this->assign('module_option', model('AdminModule')->getOption($mod));
        $this->assign('menu_option', self::menuOption($pid));
        return $this->fetch('form');
    }

    /**
     * 修改菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function edit($id = 0)
    {
        if ($this->request->isPost()) {
            $model = new MenuModel();
            if (!$model->storage()) {
                return $this->error($model->getError());
            }
            return $this->success('保存成功。', url('index'));
        }

        $row = MenuModel::where('id', $id)->find();
        // admin模块 只允许超级管理员在开发模式下修改
        if ($row['module'] == 'admin' && (ADMIN_ID != 1 || config('develop.app_debug') == 0)) {
            return $this->error('禁止修改系统模块！');
        }
        // 多语言
        if (config('sys.multi_language') == 1) {
            $row['title'] = $row['lang']['title'];
        }
        
        $this->assign('data_info', $row);
        $this->assign('module_option', model('AdminModule')->getOption($row['module']));
        $this->assign('menu_option', self::menuOption($row['pid']));
        return $this->fetch('form');
    }

    /**
     * 下拉菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    private function menuOption($id = '', $str = '')
    {
        $menus = MenuModel::getAllChild();
        foreach ($menus as $v) {
            if ($id == $v['id']) {
                $str .= '<option level="1" value="'.$v['id'].'" selected>['.$v['module'].']'.$v['title'].'</option>';
            } else {
                $str .= '<option level="1" value="'.$v['id'].'">['.$v['module'].']'.$v['title'].'</option>';
            }
            if ($v['childs']) {
                foreach ($v['childs'] as $vv) {
                    if ($id == $vv['id']) {
                        $str .= '<option level="2" value="'.$vv['id'].'" selected>&nbsp;&nbsp;['.$vv['module'].']'.$vv['title'].'</option>';
                    } else {
                        $str .= '<option level="2" value="'.$vv['id'].'">&nbsp;&nbsp;['.$vv['module'].']'.$vv['title'].'</option>';
                    }
                    if ($vv['childs']) {
                        foreach ($vv['childs'] as $vvv) {
                            if ($id == $vvv['id']) {
                                $str .= '<option level="3" value="'.$vvv['id'].'" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;['.$vvv['module'].']'.$vvv['title'].'</option>';
                            } else {
                                $str .= '<option level="3" value="'.$vvv['id'].'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;['.$vvv['module'].']'.$vvv['title'].'</option>';
                            }
                        }
                    }
                }
            }
        }
        return $str;
    }

    /**
     * 删除菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del()
    {
        $id = input('param.ids/a');
        $model = new MenuModel();
        if ($model->del($id)) {
            return $this->success('删除成功。');
        }
        return $this->error($model->getError());
    }

    /**
     * 导出模块菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public function export()
    {
        $id = input('param.id/d');
        $map = [];
        $map['id'] = $id;
        $menu = MenuModel::where($map)->field('pid,title,icon,module,url,param,target,debug,system,nav,sort')->find()->toArray();
        if (!$menu) {
            return $this->error('模块不存在！');
        }
        if ($menu['pid'] > 0 && $menu['url'] != 'admin/plugins/run') {
            return $this->error('只能通过顶级菜单导出！');
        }
        if ($menu['url'] == 'admin/plugins/run' && MenuModel::where('id', $menu['pid'])->value('url') == 'admin/plugins/run') {
            return $this->error('只能通过顶级菜单导出！');
        }
        unset($menu['pid'], $menu['id']);
        $menus = [];
        $menus[0] = $menu;
        $menus[0]['childs'] = MenuModel::getAllChild($id, 0, 'id,pid,title,icon,module,url,param,target,debug,system,nav,sort');
        $menus = self::menuReor($menus);
        $menus = json_decode(json_encode($menus, 1), 1);
        // 美化数组格式
        $menus = var_export($menus, true);
        $menus = preg_replace("/(\d+|'id') =>(.*)/", '', $menus);
        $menus = preg_replace("/'childs' => (.*)(\r\n|\r|\n)\s*array/", "'childs' => $1array", $menus);
        $menus = str_replace(['array (', ')'], ['[', ']'], $menus);
        $menus = preg_replace("/(\s*?\r?\n\s*?)+/", "\n", $menus);
        $str = json_indent(json_encode($menus, 1));
        
        $str = "<?php\nreturn ".$menus.";\n";
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="menu.php"');
        header('Content-Length:'.strLen($str));
        echo $str;
    }

    /**
     * 添加快捷菜单
     * @author 橘子俊 <364666827@qq.com>
     * @return string
     */
    public function quick()
    {
        $id = input('param.id/d');
        if (!$id) {
            return $this->error('参数传递错误！');
        }
        $map = [];
        $map['id'] = $id;
        
        $row = MenuModel::where($map)->find()->toArray();
        if (!$row) {
            return $this->error('您添加的菜单不存在！');
        }
        
        unset($row['id'], $map['id']);
        $map['url'] = $row['url'];
        $map['param'] = $row['param'];
        $map['uid'] = ADMIN_ID;
        $row['pid'] = $map['pid'] = 4;
        if (MenuModel::where($map)->find()) {
            return $this->error('您已添加过此快捷菜单！');
        }
        $row['uid'] = ADMIN_ID;
        $row['debug'] = 0;
        $row['system'] = 0;
        $row['ctime'] = time();
        $model = new MenuModel();
        $res = $model->storage($row);
        if ($res === false) {
            return $this->error('快捷菜单添加失败！');
        }
        return $this->success('快捷菜单添加成功。');
    }

    /**
     * 菜单重组（导出专用），主要清除pid字段和空childs字段
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    private static function menuReor($data = [])
    {
        $menus = [];
        foreach ($data as $k => $v) {
            if (isset($v['pid'])) {
                unset($v['pid']);
            }
            if (isset($v['childs']) && !empty($v['childs'])) {
                $v['childs'] = self::menuReor($v['childs']);
            } else if (isset($v['childs'])) {
                unset($v['childs']);
            }
            $menus[] = $v;
        }
        return $menus;
    }
}
