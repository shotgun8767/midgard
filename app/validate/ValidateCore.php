<?php

namespace app\validate;

class ValidateCore extends ValidateRule
{
    /**
     * 是否分批处理
     * @var bool
     */
    protected $batch = true;

    /**
     * 载入规则和错误信息
     * @param string $name
     * @param array $validation
     * @example ['tel' => ['tel' => 'is not a tel']]
     */
    public function load(string $name, array $validation) {
        $rules = implode('|', array_keys($validation));

        $this->rule([$name => $rules]);
        foreach ($validation as $key => $item) {
            $this->message([$name . '.' . explode(':', $key)[0] => $item]);
        }
    }
}