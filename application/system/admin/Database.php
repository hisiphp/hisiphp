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

use hisi\Dir;
use hisi\Database as dbOper;
use think\Db;
use Env;

/**
 * 数据库管理控制器
 * @package app\system\admin
 */
class Database extends Admin
{
    /**
     * 初始化方法
     */
    protected function initialize()
    {
        parent::initialize();
        $this->backupPath = Env::get('root_path').'backup/'.trim(config('databases.backup_path'), '/').'/';
        $tabData['menu'] = [
            [
                'title' => '备份数据库',
                'url'   => 'system/database/index?group=export',
            ],
            [
                'title' => '恢复数据库',
                'url'   => 'system/database/index?group=import',
            ],
        ];

        $this->hisiTabData = $tabData;
    }

    /**
     * 数据库管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($group = 'export')
    {
        if ($this->request->isAjax()) {

            $group = $this->request->param('group');
            $data = [];

            if ($group == 'export') {
                $tables = Db::query("SHOW TABLE STATUS");

                foreach ($tables as $k => &$v) {
                    $v['id'] = $v['Name'];
                }

                $data['data'] = $tables;
                $data['code'] = 0;

            } else {

                //列出备份文件列表
                if (!is_dir($this->backupPath)) {
                    Dir::create($this->backupPath);
                }

                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($this->backupPath,  $flag);

                $dataList = [];

                foreach ($glob as $name => $file) {

                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if(isset($dataList["{$date} {$time}"])) {

                            $info           = $dataList["{$date} {$time}"];
                            $info['part']   = max($info['part'], $part);
                            $info['size']   = $info['size'] + $file->getSize();

                        } else {

                            $info['part']   = $part;
                            $info['size']   = $file->getSize();

                        }

                        $info['time']       = "{$date} {$time}";
                        $time               = strtotime("{$date} {$time}");
                        $extension          = strtoupper($file->getExtension());
                        $info['compress']   = ($extension === 'SQL') ? '无' : $extension;
                        $info['name']       = date('Ymd-His', $time);
                        $info['id']         = $time;

                        $dataList["{$date} {$time}"] = $info;

                    }

                }

                $data['data'] = $dataList;
                $data['code'] = 0;
            }

            return json($data);
        }

        $tabData            = $this->hisiTabData;
        $tabData['current'] = url('?group='.$group);

        $this->assign('hisiTabData', $tabData);
        $this->assign('hisiTabType', 3);
        return $this->fetch($group);
    }

    /**
     * 备份数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $id 表名
     * @param integer $start 起始行数
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function export($id = '', $start = 0)
    {

        if ($this->request->isPost()) {

            if (empty($id)) {
                return $this->error('请选择您要备份的数据表');
            }

            if (!is_array($id)) {
                $tables[] = $id;
            } else {
                $tables = $id;
            }

            //读取备份配置
            $config = array(
                'path'     => $this->backupPath,
                'part'     => config('databases.part_size'),
                'compress' => config('databases.compress'),
                'level'    => config('databases.compress_level'),
            );

            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";

            if(is_file($lock)){
                return $this->error('检测到有一个备份任务正在执行，请稍后再试');
            } else {

                if (!is_dir($config['path'])) {
                    Dir::create($config['path'], 0755, true);
                }

                //创建锁文件
                file_put_contents($lock, $this->request->time());
            }

            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1,
            ];

            // 创建备份文件
            $database = new dbOper($file, $config);

            if($database->create() !== false) {

                // 备份指定表
                foreach ($tables as $table) {
                    $start = $database->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->error('备份出错');
                        }
                        $start = $database->backup($table, $start[0]);
                    }
                }

                // 备份完成，删除锁定文件
                unlink($lock);
            }

            return $this->success('备份完成');
        }
        return $this->error('备份出错');
    }

    /**
     * 恢复数据库 [参考原作者 麦当苗儿 <zuojiazi@vip.qq.com>]
     * @param string|array $ids 表名
     * @param integer $start 起始行数
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function import($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要恢复的备份文件');
        }

        $name  = date('Ymd-His', $id) . '-*.sql*';
        $path  = $this->backupPath.$name;
        $files = glob($path);
        $list  = array();

        foreach($files as $name){
            $basename = basename($name);
            $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
            $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
            $list[$match[6]] = array($match[6], $name, $gz);
        }

        ksort($list);

        // 检测文件正确性
        $last = end($list);

        if(count($list) === $last[0]) {

            foreach ($list as $item) {

                $config = [
                    'path'     => $this->backupPath,
                    'compress' => $item[2]
                ];

                $database = new dbOper($item, $config);
                $start = $database->import(0);

                // 导入所有数据
                while (0 !== $start) {

                    if (false === $start) {
                        return $this->error('数据恢复出错');
                    }

                    $start = $database->import($start[0]);
                }
            }

            return $this->success('数据恢复完成');
        }

        return $this->error('备份文件可能已经损坏，请检查');
    }

    /**
     * 优化数据表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function optimize($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要优化的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("OPTIMIZE TABLE `{$tables}`");
        if ($res) {
            return $this->success('数据表优化完成');
        }

        return $this->error('数据表优化失败');
    }

    /**
     * 修复数据表
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function repair($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要修复的数据表');
        }

        if (!is_array($id)) {
            $table[] = $id;
        } else {
            $table = $id;
        }

        $tables = implode('`,`', $table);
        $res = Db::query("REPAIR TABLE `{$tables}`");

        if ($res) {
            return $this->success('数据表修复完成');
        }

        return $this->error('数据表修复失败');
    }

    /**
     * 删除备份
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function del($id = '')
    {
        if (empty($id)) {
            return $this->error('请选择您要删除的备份文件');
        }

        $name  = date('Ymd-His', $id) . '-*.sql*';
        $path = $this->backupPath.$name;
        array_map("unlink", glob($path));

        if(count(glob($path)) && glob($path)){
            return $this->error('备份文件删除失败，请检查权限');
        }
        
        return $this->success('备份文件删除成功');
    }
}
