<?php

final class Random
{
    private const LOWER_LETTER = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPER_LETTER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGIT = '0123456789';

    const RANDOM_SET_UPPER  = 0b001;
    const RANDOM_SET_LOWER  = 0b010;
    const RANDOM_SET_LETTER = 0b011;
    const RANDOM_SET_DIGIT  = 0b100;
    const RANDOM_SET_MIXED  = 0b111;

    /**
     * 字符串长度
     * @var int
     */
    protected $length;

    /**
     * 随机池
     * @var array
     */
    protected $pool = [];

    /**
     * 随机规则
     * @var array
     */
    protected $rules = [
        'odd'   => [],
        'least' => [],
        'most'  => []
    ];

    /**
     * 混合字符
     * @var string
     */
    protected $string;

    /**
     * Random constructor.
     * @param int $length       随机字符长度
     * @param string $string    混合字符
     */
    public function __construct(int $length = 0, string $string = '')
    {
        if ($length > 0) {
            $this->length = $length;
        }

        $this->string = $string;
    }

    /**
     * 新建一个长度确定的随机字符串
     * @param int $length       随机字符长度
     * @param string $string    混合字符
     * @return Random
     */
    public static function fixed(int $length = 1, string $string = '') : Random
    {
        return new self($length, $string);
    }

    /**
     * 新建一个长度不确定的随机字符串
     * @param int $minLength
     * @param int $maxLength
     * @param string $string    混合字符
     * @return Random
     */
    public static function variable(int $minLength, int $maxLength, string $string = '') : Random
    {
        return new self(rand($minLength, $maxLength), $string);
    }

    /**
     * 混合
     * @param int $length
     * @return Random
     */
    public function mixFixed(int $length = 0) : Random
    {
        return self::fixed($length, $this->getString());
    }

    /**
     * @param int $minLength
     * @param int $maxLength
     * @return Random
     */
    public function mixVariable(int $minLength, int $maxLength) : Random
    {
        return self::variable($minLength, $maxLength, $this->getString());
    }

    /**
     * 清空规则
     * @return Random
     */
    public function clearRules() : Random
    {
        $this->rules = [];
        return $this;
    }

    /**
     * 清空随机池
     * @return Random
     */
    public function clearPool() : Random
    {
        $this->pool = [];
        return $this;
    }

    /**
     * 获取随机字符串
     * @param int|null $length
     * @return string
     */
    public function getString(?int $length = null) : string
    {
        $pool = array_unique($this->pool);
        $string = $this->getChar($pool);

        while (mb_strlen($string) < $this->length) {
            foreach ($this->rules['least'] as $s => $count) {
                $t = ceil(($this->length - mb_strlen($string)) / mb_strlen($s));
                if (mb_substr_count($string, $s) + $t <=  $count) {
                    $string .= $s;
                    continue 2;
                }
            }

            do {
                $s = $this->getChar();
                $most = $this->rules['most'][$s]??(ceil($this->length / mb_strlen($s)));
                $flag = mb_substr_count($string, $s) >= $most;
            } while ($flag);

            $string .= $s;
        }

        $s = mb_substr($string, 0, $this->length) . $this->string;
        $s = str_split($s);
        shuffle($s);
        $s = implode('', $s);
        return mb_substr($s, 0, $length ? $length : $this->length);
    }

    /**
     * 获取随机组
     * @param int count
     * @return array
     */
    public function get(?int $count) : array
    {
        if (!$count) {
            $count = $this->length;
        }

        $pool = $this->pool;
        shuffle($pool);
        return count($pool) > $count ? array_slice($pool, 0, $count) : $pool;
    }

    /**
     * 获取一个字符（串）
     * @param array|null $pool
     * @return int|mixed|string
     */
    protected function getChar(?array $pool = null)
    {
        static $sPool;
        if (!is_null($pool)) $sPool = $pool;

        $r = rand(1, 100) / 100;
        foreach ($this->rules['odd'] as $string => $odd) {
            if ($odd >= 0 && $odd <= 1 && $odd > $r) {
                return $string;
            }
            $r -= $odd;
        }

        return $sPool[array_rand($sPool)];
    }

    /**
     * 设定随机字符规则
     * @param $op
     * @param $condition
     * @return $this
     */
    public function rule($op, $condition)
    {
        if (is_array($op)) {
            foreach ($op as $key => $item) {
                $this->rule($key, $item);
            }
        }

        switch ($op) {
            case 'include' :
                if ($condition & 0b001) {
                    $this->pool = array_merge($this->pool, str_split(self::UPPER_LETTER));
                }
                if ($condition & 0b010) {
                    $this->pool = array_merge($this->pool, str_split(self::LOWER_LETTER));
                }
                if ($condition & 0b100) {
                    $this->pool = array_merge($this->pool, str_split(self::DIGIT));
                }
                break;
            case 'put' :
                if (!is_array($condition)) $condition = [$condition];
                $this->pool = array_merge($this->pool, $condition);
                break;
            case 'remove' :
                if (!is_array($condition)) $condition = [$condition];
                $this->pool = array_diff($this->pool, $condition);
                break;
            case 'odd' :
                $this->rules['odd'] = array_merge($this->rules['odd'], $condition);
                break;
            case 'least' :
                $this->rules['least'] = array_merge($this->rules['least'], $condition);
                break;
            case 'most' :
                $this->rules['most'] = array_merge($this->rules['most'], $condition);
                break;
        }

        return $this;
    }

    /**
     * 导入字符串集
     * @param $stringSet
     * @return Random
     */
    public function include($stringSet)
    {
        return $this->rule('include', $stringSet);
    }

    /**
     * 导入随机字符
     * @param $set
     * @return Random
     */
    public function put($set)
    {
        return $this->rule('put', $set);
    }

    /**
     * 导入小写字母集
     * @return Random
     */
    public function includeLowerLetters()
    {
        return $this->include(self::RANDOM_SET_LOWER);
    }

    /**
     * 导入大写字集
     * @return Random
     */
    public function includeUpperLetters()
    {
        return $this->include(self::RANDOM_SET_LETTER);
    }

    /**
     * 导入数字集
     * @return Random
     */
    public function includeDigit()
    {
        return $this->include(self::RANDOM_SET_DIGIT);
    }

    /**
     * 设置字符串最少出现次数
     * @param string $string
     * @param int $count
     * @return Random
     */
    public function least(string $string, int $count)
    {
        $this->put([$string]);

        return $this->rule('least', [$string => $count]);
    }

    /**
     * 设置字符串最多出现次数
     * @param string $string
     * @param int $count
     * @return Random
     */
    public function most(string $string, int $count)
    {
        $this->put([$string]);

        return $this->rule('most', [$string => $count]);
    }

    /**
     * 移除可选字符（串）
     * @param $strings
     * @return Random
     */
    public function remove($strings)
    {
        if (is_string($strings)) {
            $strings = str_split($strings);
        }

        return $this->rule('remove', $strings);
    }

    public function __toString()
    {
        return $this->getString();
    }
}