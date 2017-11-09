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

use app\common\model\AdminAnnexGroup as GroupModel;
use think\Model;
use think\Image;
use think\File;

/**
 * 附件模型
 * @package app\common\model
 */
class AdminAnnex extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = false;

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 附件上传
     * @param string $from 来源
     * @param string $group 附件分组,默认sys[系统]，模块格式：m_模块名，插件：p_插件名
     * @param string $water 水印，参数为空默认调用系统配置，no直接关闭水印，image 图片水印，text文字水印
     * @param string $thumb 缩略图，参数为空默认调用系统配置，no直接关闭缩略图，如需生成 500x500 的缩略图，则 500x500多个规格请用";"隔开
     * @param string $thumb_type 缩略图方式
     * @param string $input 文件表单字段名
     * @author 橘子俊 <364666827@qq.com>
     * @return json
     */
    public static function upload($from = 'input', $group = 'sys', $water = '', $thumb = '', $thumb_type = '', $input = 'file')
    {
        switch ($from) {
            case 'kindeditor':
                $input = 'imgFile';
                break;
            case 'umeditor':
                $input = 'upfile';
                break;
            case 'ckeditor':
                $input = 'upload';
                break;
            case 'ueditor':
                $input = 'upfile';
                if (isset($_GET['action']) && $_GET['action'] == 'config') {
                    $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents('.'.config('view_replace_str.__PUBLIC_JS__').'/editor/ueditor/config.json')), true);
                    echo json_encode($CONFIG);
                    exit;
                }
                break;
            
            default:// 默认使用layui.upload上传控件
                break;
        }
        $file = request()->file($input);
        $data = [];
        if (empty($file)) {
            return self::result('未找到上传的文件(原因：表单名可能错误，默认表单名“upload”)！', $from);
        }
        if ($file->getMime() == 'text/x-php' || $file->getMime() == 'text/html') {
            return self::result('禁止上传php,html文件！', $from);
        }
        // 格式、大小校验
        if ($file->checkExt(config('upload.upload_image_ext'))) {
            $type = 'image';
            if (config('upload.upload_image_size') > 0 && !$file->checkSize(config('upload.upload_image_size')*1024)) {
                return self::result('上传的图片大小超过系统限制['.config('upload.upload_image_size').'KB]！', $from);
            }
        } else if ($file->checkExt(config('upload.upload_file_ext'))) {
            $type = 'file';
            if (config('upload.upload_file_size') > 0 && !$file->checkSize(config('upload.upload_file_size')*1024)) {
                return self::result('上传的文件大小超过系统限制['.config('upload.upload_file_size').'KB]！', $from);
            }
        } else if ($file->checkExt('avi,mkv')) {
            $type = 'media';
        } else {
            return self::result('非系统允许的上传格式！', $from);
        }
        // 上传附件路径
        $_upload_path = ROOT_PATH . 'upload' . DS . $group . DS . $type . DS;
        // 附件访问路径
        $_file_path = ROOT_DIR.'upload/'.$group.'/'.$type.'/';

        // 如果文件已经存在，直接返回数据
        // $res = self::where('hash', $file->hash())->find();
        // if ($res) {
        //     return self::result('文件上传成功。', $from, 1, $res);
        // }

        // 移动到upload 目录下
        $upfile = $file->rule('md5')->move($_upload_path);
        if (!is_file($_upload_path.$upfile->getSaveName())) {
            return self::result('文件上传失败！', $from);
        }
        $file_count = 1;
        $file_size = round($upfile->getInfo('size')/1024, 2);
        $data = [
            'file'  => $_file_path.str_replace('\\', '/', $upfile->getSaveName()),
            'hash'  => $upfile->hash(),
            'data_id' => input('param.data_id', 0),
            'type'  => $type,
            'size'  => $file_size,
            'group' => $group,
            'ctime' => request()->time(),
        ];

        // 记录入库
        // self::create($data);
        // $group_info = GroupModel::where('name', $group)->find();
        // if (!$group_info) {
        //     GroupModel::create(['name' => $group]);
        // }

        $data['thumb'] = [];
        if ($type == 'image') {
            // 水印
            if ($water != 'no') {
                if (!empty($water)) {// 传参优先
                    $image = \think\Image::open('.'.$data['file']);
                    if ($water == 'text') {
                        if (is_file('.'.config('upload.text_watermark_font'))) {
                            $image->text(config('upload.text_watermark_content'), '.'.config('upload.text_watermark_font'), config('upload.text_watermark_size'), config('upload.text_watermark_color'))
                            ->save('.'.$data['file']); 
                        }
                    } else {
                        if (is_file('.'.config('upload.image_watermark_pic'))) {
                            $image->water('.'.config('upload.image_watermark_pic'), config('upload.image_watermark_location'), config('upload.image_watermark_opacity'))
                            ->save('.'.$data['file']); 
                        }
                    }
                } else if (config('upload.image_watermark') == 1) {// 未传参，图片水印优先[开启图片水印]
                    $image = \think\Image::open('.'.$data['file']);
                    if (is_file('.'.config('upload.image_watermark_pic'))) {
                        $image->water('.'.config('upload.image_watermark_pic'), config('upload.image_watermark_location'), config('upload.image_watermark_opacity'))
                        ->save('.'.$data['file']); 
                    }
                } else if (config('upload.text_watermark') == 1) {// 开启文字水印
                    if (is_file('.'.config('upload.text_watermark_font'))) {
                        $image->text(config('upload.text_watermark_content'), '.'.config('upload.text_watermark_font'), config('upload.text_watermark_size'), config('upload.text_watermark_color'))
                        ->save('.'.$data['file']); 
                    }
                }
            }

            // 缩略图
            if ($thumb != 'no') {
                if (empty($thumb_type)) {
                    $thumb_type = config('upload.thumb_type');
                }
                if (!empty($thumb) && strpos($thumb, ',')) {// 传参优先
                    $image = \think\Image::open('.'.$data['file']);
                    // 支持多种尺寸的缩略图
                    $thumbs = explode(';', $thumb);
                    foreach ($thumbs as $k => $v) {
                        $t_size = explode('x', strtolower($v));
                        if (!isset($t_size[1])) {
                            $t_size[1] = $t_size[0];
                        }
                        $new_thumb = $data['file'].'_'.$t_size[0].'x'.$t_size[1].'.'.strtolower(pathinfo($upfile->getInfo('name'), PATHINFO_EXTENSION));
                        $image->thumb($t_size[0], $t_size[1], $thumb_type)->save('.'.$new_thumb);
                        $thumb_size = round(filesize('.'.$new_thumb)/1024, 2);
                        $data['thumb'][$k]['type'] = 'image';
                        $data['thumb'][$k]['group'] = $group;
                        $data['thumb'][$k]['file'] = $new_thumb;
                        $data['thumb'][$k]['size'] = $thumb_size;
                        $data['thumb'][$k]['hash'] = hash_file('md5', '.'.$new_thumb);
                        $data['thumb'][$k]['ctime'] = request()->time();
                        $data['thumb'][$k]['data_id'] = input('param.data_id', 0);
                        $file_size+$thumb_size;
                        $file_count++;
                    }
                } else if (!empty(config('upload.thumb_size'))) {
                    $image = \think\Image::open('.'.$data['file']);
                    // 支持多种尺寸的缩略图
                    $thumbs = explode(';', config('upload.thumb_size'));
                    foreach ($thumbs as $k => $v) {
                        $t_size = explode('x', strtolower($v));
                        if (!isset($t_size[1])) {
                            $t_size[1] = $t_size[0];
                        }
                        $new_thumb = $data['file'].'_'.$t_size[0].'x'.$t_size[1].'.'.strtolower(pathinfo($upfile->getInfo('name'), PATHINFO_EXTENSION));
                        $image->thumb($t_size[0], $t_size[1], $thumb_type)->save('.'.$new_thumb);
                        $thumb_size = round(filesize('.'.$new_thumb)/1024, 2);
                        $data['thumb'][$k]['type'] = 'image';
                        $data['thumb'][$k]['group'] = $group;
                        $data['thumb'][$k]['file'] = $new_thumb;
                        $data['thumb'][$k]['size'] = $thumb_size;
                        $data['thumb'][$k]['hash'] = hash_file('md5', '.'.$new_thumb);
                        $data['thumb'][$k]['ctime'] = request()->time();
                        $data['thumb'][$k]['data_id'] = input('param.data_id', 0);
                        $file_size+$thumb_size;
                        $file_count++;
                    }
                }
                // if (!empty($data['thumb'])) {
                //     self::insertAll($data['thumb']);
                // }
            }
        }
        
        // 附件分组统计
        // GroupModel::where('name', $group)->setInc('count', $file_count);
        // GroupModel::where('name', $group)->setInc('size', $file_size);

        runhook('system_annex_upload', $data);
        return self::result('文件上传成功。', $from, 1, $data);
    }

    /**
     * favicon 图标上传
     * @return json
     */
    public static function favicon()
    {
        // $file = request()->file('upload');
        $data['file'] = '/favicon.ico';
        return self::result('文件上传成功。', 'input', 1, $data);
    }

    /**
     * 返回结果
     * @author 橘子俊 <364666827@qq.com>
     * @return array
     */
    private static function result($info = '', $from = 'input', $status = 0, $data = [])
    {
        unset($data['hash'], $data['group'], $data['ctime']);
        $arr = [];
        switch ($from) {
            case 'kindeditor':
                if ($status == 0) {
                    $arr['error'] = 1;
                    $arr['message'] = $info;  
                } else {
                    $arr['error'] = 0;
                    $arr['url'] = $data['file'];
                }
                break;
            case 'ckeditor':
                if ($status == 1) {
                    echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(1, "'.$data['file'].'", "");</script>';
                } else {
                    echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(1, "", "'.$info.'");</script>';
                }
                exit;
                break;
            case 'umeditor':
            case 'ueditor':
                if ($status == 0) {
                    $arr['message'] = $info;
                    $arr['state'] = 'ERROR';
                } else {
                    $arr['message'] = $info;
                    $arr['url'] = $data['file'];
                    $arr['state'] = 'SUCCESS';
                }
                echo json_encode($arr, 1);exit;
                break;
            
            default:
                $arr['msg'] = $info;
                $arr['code'] = $status;
                $arr['data'] = $data;
                break;
        }
        return $arr;
    }
}
