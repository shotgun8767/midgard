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

    /**
     * 软删除
     * @return int
     */
    public function softDeleteCase() : int
    {
        /**
         * softDelete: 软删除，指不将指定记录从数据库中抹去，而只是改变指定记录中的某个字段
         * 的值，获取记录时，不获取该数据
         *
         * 在BaseModel中有特性（trait）Status（详见app\model\traits\Status.php）
         * 这个特性引入了一些规则，也是BaseModel的核心之一，具体使用方法如下：
         *
         * 1. 设定类属性statusField，默认为'status'。这个属性指的是该模型对应的表单中，表现状态（status）的字段名
         * 2. 设定状态模板，默认模板为app\model\status\Model.php；如果有专属的状态模板，在app\model\status文件夹下，
         *    添加与类名同名的类文件即可（格式见默认模板）；模板中规定了status字段取不同值时的意义，其中比较特殊的是
         *    'DELETED'和'NORMAL'。'NORMAL'是默认的status，不声明目标的状态值时，只获取status值为'NORMAL'对应的
         *    整形的记录；'DELETED'指的是软删除时（即调用'softDelete()'方法时），status字段的修改值。
         * 3. 关闭status模式：该表单无需使用status模式，在类中添加$statusMode = true即可
         */

        // 软删除gender=0的所有记录
        $where = ['gender' => 0];
        $this->softDelete($where);  // softDelete()返回删除记录的数目

        // 恢复gender=0的所有被删除的记录
        $res = $this
            ->refreshQuery()
            ->status('DELETED')
            ->updateStatus($where, 'NORMAL');   // updateStatus() 返回修改记录的数目

        return $res;
    }

    /**
     * 获取
     * @return array|null
     */
    public function getStatusCase() : ?array
    {
        /**
         * 获取status=1(NORMAL)或status=0(ABNORMAL)的两条记录
         */

        $res = $this
            ->status(['NORMAL', 'ABNORMAL'])
            ->multi(2)
            ->getArray();

        return $res;
    }

    /**
     * 更新
     * @return int
     */
    public function updateCase() : int
    {
        /**
         * 对id=1的记录进行更新：gender字段值更新为0
         */
        $data = ['gender' => 0];
        $res = $this
            ->updates(1, $data);

        // 另一种写法（比较清晰，推荐）
//        $res = $this
//            ->whereBase(1)
//            ->updateStatus($data);

        return $res;
    }

    /**
     * 插入数据
     * @return int
     */
    public function insertCase() : int
    {
        /**
         * 插入一条数据，name=James, age=20, gender=1
         */

        $data = [
            'name'      => 'James',
            'age'       => 20,
            'gender'    => 1
        ];


        # 插入数据status为默认的1（'NORMAL'）
        $res = $this->inserts($data);   // 返回插入数据的主键值

        # 插入数据，status为0（'ABNORMAL'）
//        $res = $this->inserts($data, 'ABNORMAL');

        # 先按$replaceWhen搜索记录，如果匹配，则将该记录的status更新为指定值；若找不到，则添加记录
//        $replaceWhen = [
//            'name'      => 'James',
//            'age'       => 20,
//        ];
//        $res = $this->inserts($data, $replaceWhen);

        # 当inserts()方法第二个参数为数组时，它的含义为replaceWhen；当为int或string时，含义为status
        # 当第二个参数的值为true时，replaceWhen=data，如：
//        $res = $this->inserts($data, true, 'ABNORMAL');
        # 这行语句执行的流程：先将data作为条件搜索数据（忽略status），若匹配到记录，则将其status更新为1；若
        # 没有匹配到记录，则按data和status=1插入记录；不管何种情况，返回都是更新/插入的记录的主键值。


        return $res;
    }
}