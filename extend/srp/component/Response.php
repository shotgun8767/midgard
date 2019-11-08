<?php

namespace srp\component;

use sek\BaseException;

/**
 * Class Response
 * @package route\component
 */
class Response
{
    /**
     * @var array
     * @see BaseException
     */
    public static $errcodes = [];

    /**
     * 返回的数据类型
     */
    // 无数据返回类型
    public const RESPONSE_DATA_TYPE_NONE    = 0;
    // 单条数据返回类型
    public const RESPONSE_DATA_TYPE_SINGLE  = 1;
    // 多条数据返回类型
    public const RESPONSE_DATA_TYPE_MULTI   = 2;
    // 混合返回类型
    public const RESPONSE_DATA_TYPE_MIXED   = 3;

    /**
     * HTTP状态码
     * @var int
     */
    protected $code = 200;

    /**
     * 响应错误码
     * @var int
     */
    protected $errcode = 0;

    /**
     * 响应消息
     * @var string
     */
    protected $message = '';

    /**
     * 响应数据
     * @var array
     */
    protected $data = [];

    /**
     * 响应数据集返回类型
     * @var int
     */
    protected $dataType = self::RESPONSE_DATA_TYPE_SINGLE;

    /**
     * Response constructor.
     * @param int $code
     * @param string $message
     */
    public function __construct(int $code = 200, string $message = '')
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @param array $array
     * @return Response
     */
    public static function parse(array $array)
    {
        $instance = new static($array['code']??200, $array['message']??'');

        return $instance
            ->setErrcode($array['errcode']??0)
            ->setData($array['data']??[]);
    }

    /**
     * 转化为数组
     * @return array
     */
    public function toArray() : array
    {
        $return = [
            'code'      => $this->code,
            'message'   => $this->message,
            'data_type' => $this->dataType,
            'errcode'   => $this->errcode,
            'data'      => $this->data,
        ];

        if (key_exists($this->errcode, self::$errcodes)) {
            $return['code'] = self::$errcodes[$this->errcode][0]??200;
            $return['message'] = self::$errcodes[$this->errcode][1]??'';
        }

        return $return;
    }

    /**
     * 设置errcode
     * @param int $_
     * @return Response
     */
    public function setErrcode(int $_) : Response
    {
        if ($_ !== 0) {
            $this->errcode = $_;
            if (isset(self::$errcodes[$_])) {
                $this->code = self::$errcodes[$_][0]??200;
                $this->message = self::$errcodes[$_][1]??'';
            }
        }

        return $this;
    }

    /**
     * 设置数据
     * @param array $_
     * @return Response
     */
    public function setData(array $_) : Response
    {
        $this->data = $_;
        return $this;
    }

    public function setDataType(int $_) : Response
    {
        $this->dataType = $_;
        return $this;
    }

    public function noneData() : Response
    {
        $this->setDataType(self::RESPONSE_DATA_TYPE_NONE);
        return $this;
    }

    public function singleData() : Response
    {
        $this->setDataType(self::RESPONSE_DATA_TYPE_SINGLE);
        return $this;
    }

    public function multiData() : Response
    {
        $this->setDataType(self::RESPONSE_DATA_TYPE_MULTI);
        return $this;
    }

    public function mixedData() : Response
    {
        $this->setDataType(self::RESPONSE_DATA_TYPE_MIXED);
        return $this;
    }
}