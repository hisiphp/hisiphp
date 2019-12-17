<?php

namespace hisi;

class Dir
{
    private $_values = array();
    public $error = "";
    // PHP禁用函数
    public static $disableFunc = 'phpinfo\(|eval\(|passthru\(|exec\(|system\(|chroot\(|scandir\(|chgrp\(|chown\(|shell_exec\(|proc_open\(|proc_get_status\(|ini_alter\(|ini_alter\(|ini_restore\(|dl\(|pfsockopen\(|openlog\(|syslog\(|readlink\(|symlink\(|popepassthru\(|stream_socket_server\(|fsocket\(|fsockopen|popen\(|assert\(';

    /**
     * 架构函数
     * @param string $path  目录路径
     */
    public function __construct($path = '', $pattern = '*')
    {
        if (!$path) {
            return false;
        }
        if (substr($path, -1) != "/") {
            $path .= "/";
        }
        $this->listFile($path, $pattern);
    }


    /**
     * 生成目录
     * @param  string  $path 目录
     * @param  integer $mode 权限
     * @return boolean
     */
    public static function create($path, $mode = 0755)
    {
        if (is_dir($path)) {
            return true;
        }
        $path = str_replace("\\", "/", $path);
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for ($i=0; $i<$max; $i++) {
            $cur_dir .= $temp[$i].'/';
            if (@is_dir($cur_dir)) {
                continue;
            }
            @mkdir($cur_dir, $mode, true);
            @chmod($cur_dir, $mode);
        }
        return is_dir($path);
    }

    /**
     * 取得目录下面的文件信息
     * @param mixed $pathname 路径
     */
    public function listFile($pathname, $pattern = '*')
    {
        $dir = array();
        $list = glob($pathname . $pattern);
        foreach ($list as $i => $file) {
            $dir[$i] = pathinfo($file);
            $dir[$i]['pathname'] = realpath($file);
            $dir[$i]['isDir'] = is_dir($file);
            $dir[$i]['atime'] = fileatime($file);
            $dir[$i]['ctime'] = filectime($file);
            $dir[$i]['mtime'] = filemtime($file);
            $dir[$i]['size'] = filesize($file);
            $dir[$i]['type'] = filetype($file);
            $dir[$i]['isReadable'] = is_readable($file);
            $dir[$i]['isWritable'] = is_writable($file);
            $dir[$i]['isFile'] = is_file($file);
            $dir[$i]['isLink'] = is_link($file);
            $dir[$i]['owner'] = fileowner($file);
            $dir[$i]['perms'] = fileperms($file);
            $dir[$i]['inode'] = fileinode($file);
            $dir[$i]['group'] = filegroup($file);
        }
        
        // 对结果排序 保证目录在前面
        usort($dir, function ($a, $b) {
            if ($a['isDir']  ==  $b['isDir']) {
                return  0;
            }
            return  $a['isDir'] > $b['isDir'] ? -1 : 1;
        });

        $this->_values = $dir;
            
        return $this->_values;
    }

    /**
     * 返回数组中的当前元素（单元）
     * @return array
     */
    public static function current($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        return current($arr);
    }

    /**
     * 文件上次访问时间
     * @return integer
     */
    public function getATime()
    {
        $current = $this->current($this->_values);
        return $current['atime'];
    }

    /**
     * 取得文件的 inode 修改时间
     * @return integer
     */
    public function getCTime()
    {
        $current = $this->current($this->_values);
        return $current['ctime'];
    }

    /**
     * 遍历子目录文件信息
     * @return DirectoryIterator
     */
    public function getChildren()
    {
        $current = $this->current($this->_values);
        if ($current['isDir']) {
            return new Dir($current['pathname']);
        }
        return false;
    }

    /**
     * 取得文件名
     * @return string
     */
    public function getFilename()
    {
        $current = $this->current($this->_values);
        return $current['filename'];
    }

    /**
     * 取得文件的组
     * @return integer
     */
    public function getGroup()
    {
        $current = $this->current($this->_values);
        return $current['group'];
    }

    /**
     * 取得文件的 inode
     * @return integer
     */
    public function getInode()
    {
        $current = $this->current($this->_values);
        return $current['inode'];
    }

    /**
     * 取得文件的上次修改时间
     * @return integer
     */
    public function getMTime()
    {
        $current = $this->current($this->_values);
        return $current['mtime'];
    }

    /**
     * 取得文件的所有者
     * @return string
     */
    public function getOwner()
    {
        $current = $this->current($this->_values);
        return $current['owner'];
    }

    /**
     * 取得文件路径，不包括文件名
     * @return string
     */
    public function getPath()
    {
        $current = $this->current($this->_values);
        return $current['path'];
    }

    /**
     * 取得文件的完整路径，包括文件名
     * @return string
     */
    public function getPathname()
    {
        $current = $this->current($this->_values);
        return $current['pathname'];
    }

    /**
     * 取得文件的权限
     * @return integer
     */
    public function getPerms()
    {
        $current = $this->current($this->_values);
        return $current['perms'];
    }

    /**
     * 取得文件的大小
     * @return integer
     */
    public function getSize()
    {
        $current = $this->current($this->_values);
        return $current['size'];
    }

    /**
     * 取得文件类型
     * @return string
     */
    public function getType()
    {
        $current = $this->current($this->_values);
        return $current['type'];
    }

    /**
     * 是否为目录
     * @return boolen
     */
    public function isDir()
    {
        $current = $this->current($this->_values);
        return $current['isDir'];
    }

    /**
     * 是否为文件
     * @return boolen
     */
    public function isFile()
    {
        $current = $this->current($this->_values);
        return $current['isFile'];
    }

    /**
     * 文件是否为一个符号连接
     * @return boolen
     */
    public function isLink()
    {
        $current = $this->current($this->_values);
        return $current['isLink'];
    }

    /**
     * 文件是否可以执行
     * @return boolen
     */
    public function isExecutable()
    {
        $current = $this->current($this->_values);
        return $current['isExecutable'];
    }

    /**
     * 文件是否可读
     * @return boolen
     */
    public function isReadable()
    {
        $current = $this->current($this->_values);
        return $current['isReadable'];
    }

    /**
     * 获取foreach的遍历方式
     * @return string
     */
    public function getIterator()
    {
        return new ArrayObject($this->_values);
    }

    // 返回目录的数组信息
    public function toArray()
    {
        return $this->_values;
    }

    /**
     * 判断目录是否为空
     * @return void
     */
    public function isEmpty($directory)
    {
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * 取得目录中的结构信息
     * @return void
     */
    public static function getList($directory)
    {
        $scandir = scandir($directory);
        $dir = [];
        foreach ($scandir as $k => $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $dir[] = $v;
        }
        return $dir;
    }

    /**
     * 删除目录（包括下面的文件）
     * @return void
     */
    public static function delDir($directory, $subdir = true)
    {
        if (is_dir($directory) == false) {
            return false;
        }
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                is_dir("$directory/$file") ?
                                Dir::delDir("$directory/$file") :
                                @unlink("$directory/$file");
            }
        }

        if (readdir($handle) == false) {
            closedir($handle);
            @rmdir($directory);
        }
    }

    /**
     * 删除目录下面的所有文件，但不删除目录
     * @return void
     */
    public static function del($directory)
    {
        if (is_dir($directory) == false) {
            return false;
        }
        $handle = opendir($directory);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != ".." && is_file("$directory/$file")) {
                @unlink("$directory/$file");
            }
        }
        closedir($handle);
    }

    /**
     * 复制目录
     * @return void
     */
    public static function copyDir($source, $destination)
    {
        if (is_dir($source) == false) {
            return false;
        }
        if (is_dir($destination) == false) {
            mkdir($destination, 0755, true);
        }
        $handle = opendir($source);
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (is_dir("$source/$file")) {
                    Dir::copyDir("$source/$file", "$destination/$file");
                } else {
                    copy("$source/$file", "$destination/$file");
                }
            }
        }
        closedir($handle);
    }

    /**
     * 获取指定文件夹下的指定后缀文件（含子目录）
     *
     * @param string $path 文件夹路径
     * @param array $suffix 指定后缀名
     * @param array $files 返回的结果集
     * @return array
     */
    public static function getFiles($path, $suffix = ['php', 'html'], &$files = [])
    {
        $response = opendir($path);
        while($file = readdir($response)) {
            if ($file != '..' && $file != '.') {
                if (is_dir($path.'/'.$file)) {
                    self::getFiles($path.'/'.$file, $suffix, $files);
                } else {
                    $pathinfo = pathinfo($file);
                    if (in_array(strtolower($pathinfo['extension']), $suffix)) {
                        $files[] = $path.'/'.$file;
                    }
                }
            }
        }
        closedir($response);
        return $files;
    }

    /**
     * PHP文件危险函数检查
     *
     * @param string $source
     * @param array $suffix 指定后缀名
     * @return array
     */
    public static function safeCheck($path, $suffix = ['php', 'html'])
    {
        $files = self::getFiles($path, $suffix);

        $result = [];
        foreach($files as $f) {
            $pattern = "/".self::$disableFunc."/i";
            $content = file_get_contents($f);
            if (preg_match_all($pattern, $content, $matches)) {
                if ($matches[0]) {
                    $result[] = ['file' => $f, 'function' => $matches[0]];
                }
            }
        }

        return $result;
    }
}
