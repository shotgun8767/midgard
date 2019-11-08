<?php

namespace sek;

use Exception;

/**
 * Class BaseException
 * @package sek
 * @since 2019/9/23
 * @author shotgun8767
 */
class BaseException extends Exception
{
    /**
     * 错误码(errcode)的保留区间
     */
    const ERRCODE_RESERVE_RANGE = 10000;

    /**
     * HTTP状态码
     * @var int
     */
    protected $code = HttpCode::SC_INTERNAL_SERVER_ERROR;

    /**
     * 错误消息
     * @var string
     */
    protected $message = 'backend fatal error!';

    /**
     * 抛出数据
     * @var array|null
     */
    protected $data;

    /**
     * 错误码及对应信息
     * @var array
     */
    protected $errcodes = [];

    /**
     * 当前错误码
     * @var int
     */
    protected $errcode = 0;

    /**
     * BaseException constructor.
     * @param int $errcode
     * @param string $message
     * @param int $code
     */
    public function __construct(int $errcode = 0, string $message = '', ?int $code = null)
    {
        parent::__construct($message);
        if (!is_null($code)) {
            $this->code = $code;
        }

        if ($errcode) {
            if (is_numeric($errcode)) {
                if ($errcode < self::ERRCODE_RESERVE_RANGE) {
                    $errcode += (int)$this->errcode;
                }
                $this->errcode = $errcode;
            }

            // 根据errcode获取httpCode和message
            if (is_int($errcode) && key_exists($errcode, $this->errcodes)) {
                if (is_array($this->errcodes[$errcode])) {
                    $this->code = $this->errcodes[$errcode][0]??$this->code;
                    $this->message = $this->errcodes[$errcode][1]??$this->message;
                } else {
                    $this->code = $this->errcodes[$errcode];
                }
            }
        }

        if ($message) {
            $this->message = $message;
        }

        // 若错误信息为空，将用默认值覆盖之
        if (!$this->message) {
            $class = explode('Exception', basename(get_called_class()))[0];
            $this->message = $class . ' relative error!';
        }

        $this->beforeThrow();
    }

    /**
     * 设置数据
     * @param array|null $data
     * @return $this
     */
    public function setData(?array $data) : self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 获取数据
     * @return array
     */
    public function getData() : ?array
    {
        return $this->data;
    }

    /**
     * 获取当前错误类错误码
     * @return int
     */
    public function getErrcode() : int
    {
        return $this->errcode;
    }

    /**
     * 获取当前异常类全部错误码
     * @return array
     */
    public function getErrcodes() : array
    {
        return $this->errcodes;
    }

    /**
     * @throws BaseException
     */
    public function throwMe()
    {
        throw $this;
    }

    /**
     * 抛出错误前执行
     */
    public function beforeThrow() {}
}