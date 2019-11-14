<?php

namespace app\model;

class User extends BaseModel
{
    /**
     * 转化为数组时自动隐藏的字段
     * @var array
     */
    protected $hidden = ['status'];

    /**
     * 根据条件获取数据
     * @return array|null
     */
    public function getByCondition() : ?array
    {
        # 1. 根据主键获得单条记录
        $res = $this->get(['id' => 1]);     // 获取主键值为1的记录，此处'id'为该表单的主键字段名
//        $res = $this->get(1);        // 获取主键值为1的记录（主键必须为整形）

        # 2. 获取满足条件的第一条记录（默认从主键从小至大开始搜索）
//        $res = $this->get(['age' => 18]);   // 获取满足age=18的第一条记录
//        $res = $this->get([
//            ['age', '>', '18'],
//            ['gender', '=', 1]
//        ]);                                 // 获取满足age>18且gender=1的第一条记录

        # 3. 获取满足条件的所有记录
//        $res = $this
//            ->multi()
//            ->get([['age', '>', '16']]);    // 获取满足age>16的所有记录
//        $res = $this
//            ->multi()
//            ->get([['age', '<=', '18']]);    // 获取满足age<=18的两条记录

        return $res->toArray();
    }

    /**
     * 分页获取
     * @return array|null
     */
    public function getPaginated() : ?array
    {
        $res = $this
            ->multi()
            ->page(2, 2)
            ->get();    // 获取第2页的记录，每页行数为2

//        $res = $this
//            ->multi(1)
//            ->page(2, 2)
//            ->get();    // 获取第2页的第1条记录，每页行数为2

        return $res->toArray();
    }

    /**
     * 设置字段排序
     * @return array|null
     */
    public function getByOrder() : ?array
    {
        $res = $this
            ->multi(3)
            ->order(['id' => 'DESC'])
            ->get();                    // 设置字段排序：id倒叙排列（先排序后获取）

        // 不推荐使用，TP6还未解决获取后排序时，字段值为数值时会报错的问题
//        $res  = $this
//            ->multi(3)
//            ->get()
//            ->order('id', 'desc');      // 设置字段排序：id倒叙排列（先获取后排序）

        return $res->toArray();
    }

    /**
     * 获取指定字段值
     * @return string|null
     */
    public function getFieldOfName() : ?string
    {
        $res = $this->getField('name', 3);  // 获取主键为3的记录的name字段值

        return $res;
    }

    /**
     * 获取列数据
     * @return array|null
     */
    public function getColumnOfName() : ?array
    {
        $res = $this->getColumn('name');    // 获取所有记录中name字段的列数据

        // 添加条件
//        $condition = [
//            ['age', '>', '17']
//        ];
//        $res = $this->getColumn('name', $condition);

        return $res;
    }

    public function QueryInstruction1() : ?array
    {
        /**
         * BaseModel（基础模型）中含有一个Query（查询）实例
         * 一个Query实例只能完成一次增、删、改、查的行为
         * 如果要获取Query实例，则调用：getQuery()方法
         * 如果要获取新的Query实例，则调用getNewQuery()方法
         * 如果要刷新当前模型实例中的Query实例，则调用refreshQuery()方法
         */

        $this->get(1);

        # 错误示范：如果不刷新查询实例，则先前的查询内容不会去除
        # 此时会向数据库查找主键为1且主键为2的记录，不可能出现这样的记录，故返回null
        // $res = $this->get(2);

        # 正确示范
        $res = $this->refreshQuery()->get(2);

        return $res->toArray();
    }

    public function QueryInstruction2() : ?array
    {
        /**
         * 追加查询条件时，无需刷新查询
         */

        $this->get([['age', '<=', '18']]);

        $res = $this->get(['gender' => 0]); // 返回第一条age<=18且gender=0的记录

        return $res->toArray();
    }
}