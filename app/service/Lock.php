<?php

namespace app\service;

class Lock
{
    /**
     * 文件路径
     * @var string
     */
    private $file;

    /**
     * @var resource
     */
    private $fp;

    /**
     * Lock constructor.
     */
    public function __construct()
    {
        $this->file = config('setting.lock.filepath');
        $this->fp = fopen($this->file, 'w+');
    }

    /**
     * 文件上锁
     * @return bool 是否成功锁上文件
     */
    public function flock() : bool
    {
        return flock($this->fp, LOCK_EX);
    }

    /**
     * @return bool
     */
    public function isFlock() : bool
    {
        return !is_writable($this->file);
    }

    /**
     * 释放文件
     */
    public function release() : void
    {
        @flock($this->fp, LOCK_UN);
        @fclose($this->fp);
    }

    public function __destruct()
    {
        $this->release();
    }
}