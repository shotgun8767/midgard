<?php

namespace app\model;

class Picture extends BaseModel
{
    protected $hidden = ['status'];

    public function getInfo() : ?array
    {
        /**
         * $this->get()->toArray(); 等价于：$this->getArray();
         * get()方法返回的是：
         * 1. 模型对象：单条数据结果。可以对模型进行再加工，如隐藏字段，字段处理，获取原始字段值等。
         * 2. Collection对象（array的高级封装类）：多条数据结果；可用each()和map()遍历其中的模型对象逐一加工。
         */

        /**
         * 简单的关联预载入
         */
        return $this
            ->baseWith(['UserInfo' => 'id, name'])
            ->getArray(1);
    }

    public function JoinInstruction()
    {
        $join = [
            // user: 模型（表单）名称
            'user' => [
                'alias' => 'u',                     // u: 表单别名
                'condition' => ['user_id' => 'id'], // join条件；生成的mysql语句：ON this.user_id=u.id
                'type' => 'INNER'                   // join方式，有INNER, FULL, LEFT, RIGHT
            ]
        ];

        $res = $this
            ->baseJoin('this', $join)
            ->getArray();

        return $res;
    }

    /**
     * 关联预载入函数
     * @return \think\model\relation\BelongsTo
     */
    protected function UserInfo()
    {
        return $this->belongsTo('user','user_id', 'id');
    }
}