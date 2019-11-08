<?php

namespace app\service;

use think\facade\App;

class Resource
{
    /**
     * 文件夹相对路径
     * @var string
     */
    protected $dir;

    /**
     * 资源文件夹根目录
     * @var string
     */
    protected $root;

    /**
     * 域名根目录
     * @var string
     */
    protected $domainRoot;

    /**
     * Resource constructor.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $this->root = App::getRootPath() . 'public/resource/';

        $this->domainRoot = DOMAIN . 'resource/';

        $name = strtolower(get_called_class());
        $origin = strtolower(get_class());
        $s = explode('\\', strtolower(basename($name)));
        $s = array_pop($s);
        if ($s == 'resource') $s = '';
        while ($name != $origin) {
            $name = strtolower((new \ReflectionClass($name))->getParentClass()->getName());
            $basename = explode('\\', $name);
            $basename = strtolower(array_pop($basename));
            if ($basename != 'resource') {
                $s = $basename . '/' . $s;
            }
        }
        $this->dir .= ($s ? $s . '/' : '');
        $this->init();
    }

    /**
     * 用户自定义初始化内容
     */
    public function init() {}

    /**
     * 获取文件夹相对路径
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * 获取文件夹名
     * @return string
     */
    public function getDirPath()
    {
        return $this->root . $this->dir;
    }

    /**
     * 获取资源地址
     * @return string
     */
    public function getDomain()
    {
        return $this->domainRoot . $this->dir;
    }
}