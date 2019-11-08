<?php

namespace srp\component;

/**
 * Class Param
 * @package route\component
 *
 * @method Param string(int $maxLength = 1) static
 * @method Param bool(int $maxLength = 1) static
 * @method Param int(int $maxLength = 1) static
 * @method Param float(int $maxLength = 1) static
 * @method Param file() static
 */
class Param
{
    /**
     * 参数类型
     * @var string
     */
    protected $type;

    /**
     * 最大长度
     * @var int
     */
    protected $maxLen = 0;

    /**
     * 参数简述
     * @var string
     */
    protected $desc = '';

    /**
     * 是否必要
     * @var bool
     */
    protected $require = false;

    /**
     * 默认值
     * @var mixed
     */
    protected $default;

    /**
     * Param constructor.
     * @param string $type
     * @param int $maxLen
     */
    public function __construct(string $type = 'string', int $maxLen = 0)
    {
        $this->type = $type;
        $this->maxLen = $maxLen;
    }

    public static function __callStatic($name, $arguments)
    {
        return new static($name, $arguments[0]??0);
    }

    /**
     * 转为数组
     * @return array
     */
    public function toArray()
    {
        return [
            'type'      => $this->getType(),
            'max_len'   => $this->getMaxLen(),
            'desc'      => $this->getDesc(),
            'require'   => $this->getRequire(),
            'default'   => $this->getDefault(),
        ];
    }

    /**
     * 解析数组，返回实例
     * @param array $array
     * @return Param
     */
    public static function parse(array $array) : Param
    {
        $instance = new static($array['type']??'string', $array['max_len']);
        $instance
            ->setDesc($array['desc']??'')
            ->setRequire($array['require']??false)
            ->setDefault($array['default']??null);

        return $instance;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getMaxLen() : int
    {
        return $this->maxLen;
    }

    public function getDesc() : string
    {
        return $this->desc;
    }

    public function getRequire() : bool
    {
        return $this->require;
    }

    public function getDefault() : ?string
    {
        return $this->default;
    }

    public function setDesc(string $_) : Param
    {
        $this->desc = $_;
        return $this;
    }

    public function setRequire(bool $_) : Param
    {
        $this->require = $_;
        return $this;
    }

    public function setDefault($_ = null) : Param
    {
        $this->default = $_;
        return $this;
    }
}