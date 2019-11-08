<?php

namespace sek;

/**
 * Class Package
 * @package sek
 */
class Package
{
    /**
     * 抛出信息（成功包|错误包）
     * @var string
     */
    protected $message;

    /**
     * 错误码(错误包)
     * @var int
     */
    protected $errcode;

    /**
     * 异常类（错误包）
     * @var string
     */
    protected $exception;

    /**
     * 抛出数据（成功包|错误包）
     * @var array|null
     */
    protected $data = [];

    /**
     * 成功抛出包类型
     * @var string
     */
    protected $successType = '';

    /**
     * Package constructor.
     * @param string $exception
     * @param int $errcode
     * @param string $message
     * @param string $successType
     * @param array|null|object $data
     */
    public function __construct(?string $exception, int $errcode = 0, string $message = '', $data = null, string $successType = '')
    {
        $this->exception = $exception;
        $this->errcode = $errcode;
        $this->message = $message;
        $this->data = $data;
        $this->successType = $successType;
    }

    /**
     * 返回一个错误抛出包
     * @param string $e
     * @param int $errcode
     * @param array|null|object $data
     * @param string $message
     * @return Package
     */
    public static function error(string $e, int $errcode, $data = null, string $message = '') : Package
    {
        return new static($e, $errcode, $message, $data, false);
    }

    /**
     * 返回一个成功抛出包(OK)
     * @param string $message
     * @param array|null $data
     * @return Package
     */
    public static function ok(string $message = '', $data = null) : Package
    {
        return new static(null, 0, $message, $data, 'OK');
    }

    /**
     * 返回一个成功抛出包(ACCEPTED)
     * @param string $message
     * @param array|null $data
     * @return Package
     */
    public static function accepted(string $message = '', $data = null) : Package
    {
        return new static(null, 0, $message, $data, 'ACCEPTED');
    }

    /**
     * 返回一个成功抛出包(CREATED)
     * @param string $message
     * @param array|null $data
     * @return Package
     */
    public static function created(string $message = '', $data = null) : Package
    {
        return new static(null, 0, $message, $data, 'CREATED');
    }

    /**
     * 返回一个成功抛出包(NO_CONTENT)
     * @param string $message
     * @param array|null $data
     * @return Package
     */
    public static function noContent(string $message = '', $data = null) : Package
    {
        return new static(null, 0, $message, $data, 'NO_CONTENT');
    }

    /**
     * 抛出本包
     * @throws BaseException
     */
    public function throw()
    {
        if ($this->successType) {
            // 成功抛出包
            $code = [
                'OK'            => 200,
                'CREATED'       => 201,
                'ACCEPTED'      => 202,
                'NO_CONTENT'    => 204
            ];
            $exception = new BaseException(0, $this->message, $code[$this->successType]??200);
            $exception->setData($this->data);
            throw $exception;
        } else {
            // 错误抛出包
            $Exception = new$this->exception($this->errcode, $this->message);
            if ($Exception instanceof BaseException) {
                throw $Exception->setData($this->data);
            } else {
                throw new BaseException(999999, 'exception not exists: ' . $this->exception);
            }
        }
    }

    /**
     * 获取包数据或其中的参数值
     * @param string|null $name 键名
     * @param mixed $default 默认值
     * @return array|null
     */
    public function data(?string $name = null, $default = null) : ?array
    {
        if ($name === null) {
            return $this->data;
        } else {
            return $this->data[$name]??$default;
        }
    }

    /**
     * 设置抛出数据
     * @param array $_
     * @return Package
     */
    public function setData($_ = []) : Package
    {
        $this->data = $_;
        return $this;
    }

    /**
     * 获取或设置包信息
     * @param string|null $_
     * @return $this|string
     */
    public function message(?string $_ = null)
    {
        if ($_) {
            $this->message = $_;
            return $this;
        } else {
            return $this->message;
        }
    }

    /**
     * 获取或设置errcode
     * @param int|null $_
     * @return $this|int
     */
    public function errcode(?int $_)
    {
        if ($_) {
            $this->errcode = $_;
            return $this;
        } else {
            return $this->errcode;
        }
    }

    /**
     * 是否成功抛出包
     * @return bool
     */
    public function isSuccess() : bool
    {
        return $this->successType ? true : false;
    }

    /**
     * 是否错误抛出包
     * @return bool
     */
    public function isError() : bool
    {
        return !$this->isSuccess();
    }

    /**
     * 抽取所有数据中的第一条数据并返回
     * @return mixed
     */
    public function dataSingle()
    {
        return key($this->data) == 0 ? array_shift($this->data) : null;
    }
}