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

// 应用公共函数库
use think\exception\HttpException;
use think\facade\Env;

// 加载自定义函数库
include Env::get('app_path').'function.php';

if (!function_exists('hs_array_filter_callback')) {
    /**
     * array_filter 回调函数，只过滤空值
     * @param mixed $val 需要过滤的值
     * @return bool
     */
    function hs_array_filter_callback($val)
    {
        if ($val === '' || $val === 'NULL' || $val === null || $val === ' ') {
            return false;
        }
        return true;
    }
}


if (!function_exists('get_layer')) {
    /**
     * 自动获取层前缀
     * @param string $name 名称
     */
    function get_layer($name)
    {
        $layers = ['db', 'logic', 'model', 'service', 'validate'];
        foreach ($layers as $v) {
            if (substr($name, 0, strlen($v)) == $v) {
                return $v;
            }
        }
        return false;
    }
}

if (!function_exists('dblang')) {
    /**
     * 获取语言包ID，数据库读取时使用
     * @param string $group 分组[admin]，默认为前台
     * @return int
     */
    function dblang($group = '')
    {
        $lang = cookie($group.'_language');
        if (empty($lang)) {
            $lang = config('default_lang');
        }
        return (new app\system\model\SystemLanguage)->lists($lang);
    }
}

if (!function_exists('get_domain')) {
    /**
     * 获取当前域名
     * @param bool $http true 返回http协议头,false 只返回域名
     * @return string
     */
    function get_domain($http = true)
    {
        $host = input('server.http_host');
        $port = input('server.server_port');
        if ($port != 80 && $port != 443 && strpos($host, ':') === false) {
            $host .= ':'.input('server.server_port');
        }

        if ($http) {
            if (input('server.https') && input('server.https') == 'on') {
                return 'https://'.$host;
            }
            return 'http://'.$host;
        }

        return $host;
    }
}

if (!function_exists('get_num')) {
    /**
     * 获取数值型
     * @param string $field 要获取的字段名
     * @return bool
     */
    function get_num($field = 'id')
    {
        $_id = input('param.' . $field. '/d', 0);
        if ($_id > 0) {
            return $_id;
        }

        if (request()->isAjax()) {
            json(['msg'=> '参数传递错误', 'code'=> 0]);
        } else {
            throw new HttpException(404, $field.'参数传递错误！');
        }
        exit;
    }
}

if (!function_exists('is_email')) {
    /**
     * 判断邮箱
     * @param string $str 要验证的邮箱地址
     * @return bool
     */
    function is_email($str)
    {
        return preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $str);
    }
}

if (!function_exists('is_mobile')) {
    /**
     * 判断手机号
     * @param string $num 要验证的手机号
     * @return bool
     */
    function is_mobile($num)
    {
        return preg_match("/^1(3|4|5|6|7|8|9)\d{9}$/", $num);
    }
}

if (!function_exists('cur_url')) {
    /**
     * 获取当前访问的完整URL
     * @return string
     */
    function cur_url()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === 'on') {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
}

if (!function_exists('is_username')) {
    /**
     * 判断用户名
     * 用户名支持中文、字母、数字、下划线，但必须以中文或字母开头，长度3-20个字符
     * @param string $str 要验证的字符串
     * @return bool
     */
    function is_username($str)
    {
        return preg_match("/^[\x80-\xffA-Za-z]{1,1}[\x80-\xff_A-Za-z0-9]{2,19}+$/", $str);
    }
}

if (!function_exists('random')) {
    /**
     * 随机字符串
     * @param int $length 长度
     * @param int $type 类型(0：混合；1：纯数字)
     * @return string
     */
    function random($length = 16, $type = 1)
    {
        $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $type ? 10 : 35);
        $seed = $type ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        if ($type) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }
}

if (!function_exists('order_number')) {
    /**
     * 生成订单号(年月日时分秒+5位随机数)
     * @return int
     */
    function order_number()
    {
        return date('YmdHis').random(5);
    }
}

if (!function_exists('hide_str')) {
    /**
     * 将一个字符串部分字符用*替代隐藏
     * @param string    $string   待转换的字符串
     * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串中间用***代替
     * @param string    $glue     分割符
     * @return string   处理后的字符串
     */
    function hide_str($string, $bengin=0, $len = 4, $type = 0, $glue = "@")
    {
        if (empty($string)) {
            return false;
        }
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", $array);
        } elseif ($type == 1) {
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", array_reverse($array));
        } elseif ($type == 2) {
            $array = explode($glue, $string);
            if (isset($array[0])) {
                $array[0] = hide_str($array[0], $bengin, $len, 1);
            }
            $string = implode($glue, $array);
        } elseif ($type == 3) {
            $array = explode($glue, $string);
            if (isset($array[1])) {
                $array[1] = hide_str($array[1], $bengin, $len, 0);
            }
            $string = implode($glue, $array);
        } elseif ($type == 4) {
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i])) {
                    $tem[] = $i >= $left ? "" : $array[$i];
                }
            }
            $tem[] = '*****';
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                if (isset($array[$i])) {
                    $tem[] = $array[$i];
                }
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @param int $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0, $adv = false)
    {
        $type       =  $type ? 1 : 0;
        static $ip  =   null;
        if ($ip !== null) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip     =   trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('parse_attr')) {
    /**
     * 配置值解析成数组
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value = '')
    {
        if (is_array($value)) {
            return $value;
        }
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k]   = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

if (!function_exists('xml2array')) {
    /**
     * XML转数组
     * @param string $xml xml格式内容
     * @param bool $isnormal
     * @return array
     */
    function xml2array(&$xml, $isnormal = false)
    {
        $xml_parser = new hisi\Xml($isnormal);
        $data = $xml_parser->parse($xml);
        $xml_parser->destruct();
        return $data;
    }
}

if (!function_exists('array2xml')) {
    /**
     * 数组转XML
     * @param array $arr 待转换的数组
     * @param bool $ignore XML解析器忽略
     * @param intval $level 层级
     * @return type
     */
    function array2xml($arr, $ignore = true, $level = 1)
    {
        $s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
        $space = str_repeat("\t", $level);
        foreach ($arr as $k => $v) {
            if (!is_array($v)) {
                $s .= $space."<item id=\"$k\">".($ignore ? '<![CDATA[' : '').$v.($ignore ? ']]>' : '')."</item>\r\n";
            } else {
                $s .= $space."<item id=\"$k\">\r\n".array2xml($v, $ignore, $level + 1).$space."</item>\r\n";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</root>" : $s;
    }
}

if (!function_exists('form_type')) {
    /**
     * 获取表单类型(中文描述)
     * @param string $type 类型(英文)
     * @return array|string
     */
    function form_type($type = '')
    {
        $arr = [];
        $arr['input'] = '单行文本';
        $arr['textarea'] = '多行文本';
        $arr['array'] = '数组';
        $arr['switch'] = '开关';
        $arr['radio'] = '单选按钮';
        $arr['checkbox'] = '多选框';
        $arr['tags'] = '标签';
        $arr['select'] = '下拉框';
        $arr['hidden'] = '隐藏';
        $arr['image'] = '图片';
        $arr['file'] = '文件';
        $arr['date'] = '日期';
        $arr['datetime'] = '日期+时间';
        $arr['time'] = '时间';
        if (isset($arr[$type])) {
            return $arr[$type];
        }
        return $arr;
    }
}

if (!function_exists('json_indent')) {
    /**
     * JSON数据美化
     * @param string $json json字符串
     * @return string
     */
    function json_indent($json)
    {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;
        for ($i=0; $i<=$strLen; $i++) {
            $char = substr($json, $i, 1);
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            } elseif (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $result .= $char;
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }
        return $result;
    }
}

if (!function_exists('parse_sql')) {
    /**
     * 解析sql语句
     * @param  string $content sql内容
     * @param  int $limit  如果为1，则只返回一条sql语句，默认返回所有
     * @param  array $prefix 替换表前缀
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    function parse_sql($sql = '', $limit = 0, $prefix = [])
    {
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';

        // 替换表前缀
        if (!empty($prefix)) {
            $to   = current($prefix);
            $from = current(array_flip($prefix));
        }

        if ($sql != '') {
            // 纯sql内容
            $pure_sql = [];

            // 多行注释标记
            $comment = false;

            // 按行分割，兼容多个平台
            $sql = str_replace(["\r\n", "\r"], "\n", $sql);
            $sql = explode("\n", trim($sql));

            // 循环处理每一行
            foreach ($sql as $key => $line) {
                // 跳过空行
                if ($line == '') {
                    continue;
                }

                // 跳过以#或者--开头的单行注释
                if (preg_match("/^(#|--)/", $line)) {
                    continue;
                }

                // 跳过以/**/包裹起来的单行注释
                if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                    continue;
                }

                // 多行注释开始
                if (substr($line, 0, 2) == '/*') {
                    $comment = true;
                    continue;
                }

                // 多行注释结束
                if (substr($line, -2) == '*/') {
                    $comment = false;
                    continue;
                }

                // 多行注释没有结束，继续跳过
                if ($comment) {
                    continue;
                }

                // 替换表前缀
                if ($from != '') {
                    $line = str_replace('`'.$from, '`'.$to, $line);
                }
                if ($line == 'BEGIN;' || $line =='COMMIT;') {
                    continue;
                }
                // sql语句
                array_push($pure_sql, $line);
            }

            // 只返回一条语句
            if ($limit == 1) {
                return implode($pure_sql, "");
            }

            // 以数组形式返回sql语句
            $pure_sql = implode("\n", $pure_sql);
            $pure_sql = explode(";\n", $pure_sql);
            return $pure_sql;
        } else {
            return $limit == 1 ? '' : [];
        }
    }
}

if (!function_exists('editor')) {
    /**
     * 富文本编辑器
     * @param array $obj 编辑器的容器id或class
     * @param string $name [为了方便大家能在系统设置里面灵活选择编辑器，建议不要指定此参数]，目前支持的编辑器[ueditor,umeditor,ckeditor,kindeditor]
     * @param string $url [选填]附件上传地址，建议保持默认
     * @return html
     */
    function editor($obj = [], $name = '', $url = '')
    {
        $jsPath = '/static/js/editor/';
        if (empty($name)) {
            $name = config('sys.editor');
        }

        if (empty($url)) {
            $url = url("system/annex/upload?full_path=yes&thumb=no&from=".$name);
        }

        switch (strtolower($name)) {
            case 'ueditor':
                $html = '<script src="'.$jsPath.'ueditor/ueditor.config.js"></script>';
                $html .= '<script src="'.$jsPath.'ueditor/ueditor.all.min.js"></script>';
                $html .= '<script src="'.$jsPath.'ueditor/plugins/135editor.js"></script>';
                $html .= '<script>';
                foreach ($obj as $k =>$v) {
                    $html .= 'var ue'.$k.' = UE.ui.Editor({serverUrl:"'.$url.'",initialFrameHeight:500,initialFrameWidth:"100%",autoHeightEnabled:false});ue'.$k.'.render("'.$v.'");';
                }
                $html .= '</script>';
                break;
            case 'kindeditor':
                if (is_array($obj)) {
                    $obj = implode(',#', $obj);
                }
                $html = '<script src="'.$jsPath.'kindeditor/kindeditor-min.js"></script>
                        <script>
                            var editor;
                            KindEditor.ready(function(K) {
                                editor = K.create(\'#'.$obj.'\', {uploadJson: "'.$url.'",allowFileManager : false,minHeight:500, width:"100%",autoHeightEnabled:false, afterBlur:function(){this.sync();}});
                            });
                        </script>';
                break;
            case 'ckeditor':
                $html = '<script src="'.$jsPath.'ckeditor/ckeditor.js"></script>';
                $html .= '<script>';
                foreach ($obj as  $v) {
                    $html .= 'CKEDITOR.replace("'.$v.'",{filebrowserImageUploadUrl:"'.$url.'"});';
                }
                $html .= '</script>';
                break;
            
            default:
                $html = '<link href="'.$jsPath.'umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">';
                $html .= '<script src="'.$jsPath.'umeditor/third-party/jquery.min.js"></script>';
                $html .= '<script src="'.$jsPath.'umeditor/third-party/template.min.js"></script>';
                $html .= '<script src="'.$jsPath.'umeditor/umeditor.config.js"></script>';
                $html .= '<script src="'.$jsPath.'umeditor/umeditor.min.js"></script>';
                $html .= '<script>';
                foreach ($obj as  $k => $v) {
                    $html .= 'var um'.$k.' = UM.getEditor("'.$v.'", {
                                initialFrameWidth:"100%"
                                ,initialFrameHeight:"500"
                                ,autoHeightEnabled:false
                                ,imageUrl:"'.$url.'"
                                ,imageFieldName:"upfile"});';
                }
                $html .= '</script>';
                break;
        }

        return $html;
    }
}

if (!function_exists('str_coding')) {
    /**
     * 字符串加解密
     * @param  string  $string   要加解密的原始字符串
     * @param  string  $operation 加密：ENCODE，解密：DECODE
     * @param  string  $key      密钥
     * @param  integer $expiry   有效期
     * @return string
     */
    function str_coding($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key ? $key : config('hs_auth.key'));
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}

if (!function_exists('is_empty')) {
    /**
     * 判断是否为空值
     * @param array|string $value 要判断的值
     * @return bool
     */
    function is_empty($value)
    {
        if (!isset($value)) {
            return true;
        }

        if ($value === null) {
            return true;
        }

        if (trim($value) === '') {
            return true;
        }

        return false;
    }
}

if (!function_exists('module_info')) {
    /**
     * 获取模块信息[非系统模块]
     * @param string $name 模块名
     * @return bool|array
     */
    function module_info($name = '')
    {
        if (is_empty($name)) {
            $name = request()->module();
        }

        $module = model('system/SystemModule')->where('name', $name)->find();
        if (!$module) {
            $path = \Env::get('app_path').strtolower($name).'/info.php';
            if (!file_exists($path)) {
                return false;
            }

            return include_once $path;
        }

        return $module->toArray();
    }
}

if (!function_exists('module_config')) {
    /**
     * 获取模块配置[非系统模块]
     * @param string $name 模块名
     * @param string $fileName 配置文件名
     * @return bool|array
     */
    function module_config($name = '', $fileName = 'config')
    {
        if (is_empty($name)) {
            return [];
        }

        $path = \Env::get('app_path').strtolower($name).'/config/'.$fileName.'.php';
        if (!file_exists($path)) {
            return false;
        }

        return include_once $path;
    }
}

// +----------------------------------------------------------------------
// | 插件相关函数 start
// +----------------------------------------------------------------------

if (!function_exists('runhook')) {
    /**
     * 监听钩子的行为
     * @param string $name 钩子名称
     * @param array $params 参数
     * @param  bool   $return   是否需要返回结果
     * @param  bool   $once   只获取一个有效返回值
     */
    function runhook($name = '', $params = null, $return = false, $once = false)
    {
        $result = \Hook::listen($name, $params, $once);
        if ($return) {
            return $result;
        }
    }
}

if (!function_exists('get_plugins_class')) {
    /**
     * 获取插件类名
     * @param  string $name 插件名
     * @return string
     */
    function get_plugins_class($name)
    {
        return "plugins\\{$name}\\{$name}";
    }
}

if (!function_exists('plugins_action_exist')) {
    /**
     * 检查插件操作是否存在
     * @param string $path 插件操作路径：插件名/控制器/[操作]
     * @param string $group 控制器分组[admin,home]
     * @return bool
     */
    function plugins_action_exist($path = '', $group = 'admin')
    {
        if (strpos($path, '/')) {
            list($name, $controller, $action) = explode('/', $path);
        }
        $controller = empty($controller) ? 'Index' : ucfirst($controller);
        $action = empty($action) ? 'index' : $action;

        return method_exists("plugins\\{$name}\\{$group}\\{$controller}", $action);
    }
}

if (!function_exists('plugins_run')) {
    /**
     * 运行插件操作
     * @param string $path  执行操作路径：插件名/控制器/[操作]
     * @param mixed $params 参数
     * @param string $group 控制器分组[admin,home]
     * @return mixed
     */
    function plugins_run($path = '', $params = [], $group = 'admin')
    {
        !defined('IS_PLUGINS') && define('IS_PLUGINS', true);
        if (strpos($path, '/')) {
            list($name, $controller, $action) = explode('/', $path);
        } else {
            $name = $path;
        }
        $controller = empty($controller) ? 'index' : ucfirst($controller);
        $action = empty($action) ? 'index' : $action;
        if (!is_array($params)) {
            $params = (array)$params;
        }
        $class = "plugins\\{$name}\\{$group}\\{$controller}";
        $obj = new $class;
        $_GET['_p'] = $name;
        $_GET['_c'] = $controller;
        $_GET['_a'] = $action;
        return call_user_func_array([$obj, $action], [$params]);
    }
}

if (!function_exists('plugins_info')) {
    /**
     * 获取插件信息
     * @param string $name 插件名
     * @return bool
     */
    function plugins_info($name = '')
    {
        $path = \Env::get('root_path').'plugins/'.$name.'/info.php';
        if (!file_exists($path)) {
            return false;
        }
        return include_once $path;
    }
}

if (!function_exists('plugins_url')) {
    /**
     * 生成插件URL
     * @param string $url 链接：插件名称/控制器/操作
     * @param array $param 参数
     * @param string $group 控制器分组[admin,home]
     * @param integer $urlmode URL模式
     * URL模式1 [/plugins/插件名/控制器/[方法]?参数1=参数值&参数2=参数值]
     * URL模式2 [/plugins.php?_p=插件名&_c=控制器&_a=方法&参数1=参数值&参数2=参数值] 推荐
     * @return string
     */
    function plugins_url($url = '', $param = [], $group = '', $urlmode = 2)
    {
        $params = [];
        $params['_p'] = input('param._p');
        $params['_c'] = input('param._c', 'Index');
        $params['_a'] = input('param._a', 'index');
        if ($url) {
            $url = explode('/', $url);
            $count = count($url);
            if ($count == 3) {
                $params['_p'] = isset($url[0]) ? $url[0] : '';
                $params['_c'] =  isset($url[1]) ? ucfirst($url[1]) : 'Index';
                $params['_a'] = isset($url[2]) ? $url[2] : 'index';
            } elseif ($count == 2) {
                $params['_c'] =  isset($url[0]) ? ucfirst($url[0]) : 'Index';
                $params['_a'] = isset($url[1]) ? $url[1] : 'index';
            } else {
                $params['_a'] = $url[0];
            }
        }

        if (!$params['_p']) {
            return '#链接错误';
        }

        $params = array_merge($params, $param);
        if (empty($group)) {
            if (defined('ENTRANCE')) {
                return url('system/plugins/run', $params);
            } else {
                if ($urlmode == 2) {
                    return '/plugins.php?'.http_build_query($params);
                }
                return '/plugins/'.$params['_p'].'/'.$params['_c'].'/'.$params['_a'].'?'.http_build_query($param);
            }
        } elseif ($group == 'admin') {
            return url('system/plugins/run', $params);
        } else {
            if ($urlmode == 2) {
                return '/plugins.php?'.http_build_query($params);
            }
            return '/plugins/'.$params['_p'].'/'.$params['_c'].'/'.$params['_a'].'?'.http_build_query($param);
        }
    }
}

// +----------------------------------------------------------------------
// | 插件相关函数 end
// +----------------------------------------------------------------------
