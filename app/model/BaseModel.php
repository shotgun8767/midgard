<?php

namespace app\model;

use Exception;
use ReflectionException;
use app\model\traits\{SoftDelete, Status};
use think\db\BaseQuery;
use think\db\Query;
use think\Model;
use app\exception\DataBaseException;
use think\model\Collection;

class BaseModel extends Model
{
    use Status;
    use SoftDelete;

    protected const FIELD_HIDDEN_NAME = 'hidden';

    protected const BASE_WITH_FIELD_NAME = 'field';
    protected const BASE_WITH_HIDDEN_NAME = 'hidden';
    protected const BASE_WITH_WITH_NAME = 'with';

    /**
     * 是否自动将获取的模型转化为数组
     * @var bool
     */
    protected $toArray = false;

    /**
     * 允许被更新字段（空数组表示无限制）
     * @var array
     */
    protected $updatedFields = [];

    /**
     * 是否获取多条信息
     * @var bool
     */
    protected $multi = false;

    /**
     * 查询数量(null表示无数量限制)
     * @var int|null
     */
    protected $limit = null;

    /**
     * 查询实例
     * @var BaseQuery
     */
    protected $queryInstance;

    /**
     * ModelCore constructor
     * @param array $data .
     * @param string $pk
     * @param string|null $StatusEnum
     * @throws ReflectionException
     */
    public function __construct(array $data = [], string $pk = 'id', ?string $StatusEnum = null)
    {
        parent::__construct($data);
        $this->queryInstance = $this->pk($pk);

        if ($this->statusMode) {
            $this->loadStatus($StatusEnum);
            $this->initStatus();
        }
    }

    /**
     * 获取当前查询
     * @return BaseModel
     */
    public function getQuery() : BaseQuery
    {
        return $this->queryInstance;
    }

    /**
     * 创建并获取新查询
     * @return BaseQuery
     */
    public function getNewQuery() : BaseQuery
    {
        return $this->queryInstance = $this->db();
    }

    /**
     * 刷新查询
     * @return static
     */
    public function refreshQuery() : self
    {
        $this->getNewQuery();

        return $this;
    }

    /**
     * 设置是否获取数据而不是模型
     * @param bool $_
     * @return BaseModel
     */
    public function setToArray(bool $_) : self
    {
        $this->toArray = $_;
        return $this;
    }

    /**
     * 清楚查询选项
     * @param string $option
     * @return BaseModel
     */
    public function removeOption(string $option = '') : self
    {
        $this->getQuery()->removeOption($option);
        return $this;
    }

    /**
     * 设置排序
     * @param array|string|\think\db\Raw $field
     * @param string $order
     * @return $this|Model
     */
    public function order($field, string $order = '') : self
    {
        $this->getQuery()->order($field, $order);
        return $this;
    }

    /**
     * 多项查询
     * @param bool $limit
     * @return BaseModel
     */
    public function multi($limit = true) : self
    {
        if ($limit === true) {
            $this->multi = true;
        }
        elseif ($limit === false) {
            $this->multi = false;
        }
        elseif (is_numeric($limit) && $limit > 0 && !strpos($limit, '.')) {
            $this->multi = true;
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * 设置页码
     * @param int $page
     * @param int|null $listRows
     * @return BaseModel
     */
    public function page(int $page, int $listRows = null) : self
    {
        $this->getQuery()->page($page, $listRows);
        return $this;
    }

    /**
     * 隐藏
     * @param array $hidden
     * @param bool $append
     * @return BaseModel
     */
    public function hidden(array $hidden = [], bool $append = false) : self
    {
        if ($append) {
            $hidden = array_merge($this->hidden, $hidden);
        }

        parent::hidden($hidden);

        return $this;
    }

    /**
     * Base查询
     * @param array $where
     * @return BaseModel
     */
    public function baseWhere($where = []) : self
    {
        $this->parseWhere($where);
        $this->getQuery()->where($where);

        # 处理status
        if ($this->statusMode && !isset($this->where[$this->statusField])) {
            $this->getQuery()
                ->whereIn($this->getFieldName($this->statusField), $this->_status);
        }

        return $this;
    }

    /**
     * 搜索器查询
     * @param array $data
     * @param string $prefix
     * @return BaseModel
     */
    public function baseSearch(array $data, string $prefix = '') : self
    {
        $this
            ->getQuery()
            ->withSearch(array_keys($data), $data, $prefix);

        return $this;
    }

    /**
     * 获取数据
     * @param array $where
     * @param array $field
     * @return array|\think\Collection|Model|null
     * @throws DataBaseException
     */
    public function get($where = [], $field = [])
    {
        $query = $this->getQuery();

        if (is_string($field)) {
            $field = [$field];
        }

        if ($field && in_array(self::FIELD_HIDDEN_NAME, $field)) {
            $field = array_diff($field, [self::FIELD_HIDDEN_NAME]);
            $query->hidden($field);
        } else {
            $query->field($field);
        }

        if (is_int($this->limit)) $query->limit($this->limit);
        $this->baseWhere($where);
        $models = $this->multi ? $this->baseSelect() : $this->baseFind();

        if ($this->limit && $models->count() > $this->limit) {
            $models = $models->slice(0, $this->limit);
        }

        return $models ? ($this->toArray ? $models->toArray() : $models) : null;
    }

    /**
     * 获取数组（返回数组）
     * @param array $where
     * @param array $field
     * @return array|null
     * @throws DataBaseException
     */
    public function getArray($where = [], $field = []) : ?array
    {
        $toArray = $this->toArray;
        $res = $this->setToArray(true)->get($where, $field);
        $this->setToArray($toArray);

        return $res;
    }

    /**
     * 获取某字段的值
     * @param string $field
     * @param array $where
     * @param bool $origin
     * @return array|mixed|\think\Collection|Model|null
     * @throws DataBaseException
     */
    public function getField(string $field, $where = [], $origin = false)
    {
        $res = $this->get($where, [$field]);

        if (!is_null($res)) {
            $res = $origin ? $res->getOrigin($field) : $res->getAttr($field);
            if ($res instanceof Model) {
                return $this->_toArray($res);
            }
        }

        return $res;
    }

    /**
     * 获取某个字段的列数据（主键值 => 字段值）
     * @param string|null $field
     * @param array $where
     * @return array
     * @throws DataBaseException
     */
    public function getColumn(?string $field, $where = []) : array
    {
        $fields = [$this->getFieldName($this->pk)];
        if ($field) {
            $fields = array_merge($fields, [$field]);
        }

        $res = $this
            ->multi()
            ->getArray($where, $fields);

        if ($res) {
            $column = [];
            foreach ($res as $arr) {
                $column[$arr[$this->pk]] = $field ? $arr[$field] : 0;
            }
            return $field ? $column : array_keys($column);
        } else {
            return [];
        }
    }

    /**
     * 获取复合条件的记录数量
     * @param array $where
     * @return int
     */
    public function getCount($where = [])
    {
        return $this->baseWhere($where)->getQuery()->count();
    }

    /**
     * 插入一条数据
     * @param array $data
     * @param bool $replaceWhen
     * @param null $status
     * @return int
     * @throws DataBaseException
     */
    public function inserts(array $data, $replaceWhen = false, $status = null) : int
    {
        if ($this->statusMode) {
            if (is_int($replaceWhen) || is_string($replaceWhen)) {
                $status = $replaceWhen;
            }

            $status = is_null($status) ? ($this->_status[0] ?? 0) : $this->getStatus($status);
            $data[$this->statusField] = $status;
        }

        if ($this->statusMode && ($replaceWhen === true || is_array($replaceWhen))) {
            if ($replaceWhen === true) $replaceWhen = $data;

            if ($replaceWhen) {
                $t = $this->_status;
                $this->statusAll();
                $res = $this->getArray($replaceWhen, ['id', 'status']);
                $_status = $this->getOrigin('status');
                $this->_status = $t;

                if ($res && $status != $_status) {
                    $id = $res['id'];
                    $this->updateStatus($id, $status);
                    return $id;
                }

                $this->refreshQuery();
            }
        }

        $this->queryInstance->field(true);
        return $this->baseInsert($data);
    }

    /**
     * 更新数据
     * @param array $where
     * @param array $data
     * @return int
     * @throws DataBaseException
     */
    public function updates($where = [], ?array $data = null) : int
    {
        if (is_null($data)) {
            $data = $where;
            $where = [];
        }

        return $this->baseUpdate($data, $this->get($where));
    }

    /**
     * 真实删除数据
     * @param array $where
     * @return int
     * @throws DataBaseException
     */
    public function destroys($where = []) : int
    {
        $this->parseWhere($where);
        $this->baseWhere($where);

        return $this->baseDelete();
    }

    /**
     * BASE关联预载入
     * @param string|array $with
     * @return BaseModel
     */
    public function baseWith($with) : self
    {
        $fieldsPro = function ($fields, string $pk) : array {
            if (is_array($fields) && !empty($fields)) {
                if (!in_array($pk, $fields)) {
                    $fields[] = $pk;
                }
            }

            return $fields;
        };

        $explodeTrim = function (string $str) {
            return array_map('trim', explode(',', $str));
        };

        $pro = function (array &$withArray) use ($fieldsPro, &$pro, $explodeTrim) : void {
            foreach ($withArray as $name => $content) {
                if (is_string($name)) {
                    if (is_string($content)) {
                        $content = $explodeTrim($content);
                    }

                    if (is_array($content)) {
                        $field  = $content[self::BASE_WITH_FIELD_NAME]??[];
                        $hidden = $content[self::BASE_WITH_HIDDEN_NAME]??[];
                        $with   = $content[self::BASE_WITH_WITH_NAME]??[];

                        if (is_string($field)) {
                            $field = $explodeTrim($field);
                        }

                        if (is_string($hidden)) {
                            $hidden = $explodeTrim($hidden);
                        }

                        if (is_array($with) && !empty($with)) {
                            $pro($with);
                        }

                        if ($field || $hidden || $with) {
                            $withArray[$name] = function (BaseQuery $query) use ($field, $hidden, $with, $fieldsPro) {
                                $query->field($fieldsPro($field, $query->getModel()->getPk()));
                                $query->hidden($hidden);
                                $query->with($with);
                            };
                        } else {
                            $withArray[$name] = function (BaseQuery $query) use ($content, &$fieldsPro){
                                $query->field($fieldsPro($content, $query->getModel()->getPk()));
                            };
                        }
                    } else {
                        unset($withArray[$name]);
                        $withArray[] = $name;
                    }
                }
            }
        };

        $pro($with);
        $this->getQuery()->with($with);
        return $this;
    }

    /**
     * BASE联合查询
     * @param string $alias 当前模型别名
     * @param array $join   join方式
     * @return BaseModel
     */
    public function baseJoin(string $alias, array $join) : self
    {
        $query = $this->getQuery()->alias($alias);

        if ($join) {
            foreach ($join as $table => $item) {
                $_alias = $item['alias'];
                $condition = $item['condition']??'';
                if (is_array($condition)) {
                    $key = key($condition);
                    $value = $condition[$key];
                    $condition = "$alias.$key=$_alias.$value";
                }

                /** @var Query $query */
                $query->join([$table => $_alias], $condition, $item['type']??'INNER');
            }
        }

        return $this;
    }

    /**
     * 获取带模型别称的字段名
     * @param string $field         字段名
     * @param string|null $model    模型名
     * @return string   加工后的带模型别称的字段名
     */
    public function getFieldName(string $field, ?string $model = null): string
    {
        $alias = $this->getQuery()->getOptions('alias')[$this->getQuery()->getTable()];

        if (is_null($model)) {
            return ($alias ? $alias . '.' : '') . $field;
        } else {
            if (is_array($op_join = $this->getOptions('join'))) {
                foreach ($op_join as $item) {
                    if (key_exists($model, $item[0])) {
                        return $item[0][$model] . '.' . $field;
                    }
                }
            }
        }

        return $field;
    }

    /**
     * 获取单条数据
     * @param null $data
     * @return array|Model|null
     * @throws DataBaseException
     */
    protected function baseFind($data = null)
    {
        $find = function () use ($data){
            return $this->getQuery()->find($data);
        };

        if (is_debug()) {
            return $find();
        } else {
            try {
                return $find();
            } catch (Exception $e) {
                throw new DataBaseException(20001);
            }
        }
    }

    /**
     * 获取多条数据
     * @param null $data
     * @return \think\Collection
     * @throws DataBaseException
     */
    protected function baseSelect($data = null)
    {
        $select = function () use ($data){
            return $this->getQuery()->select($data);
        };

        if (is_debug()) {
            return $select();
        } else {
            try {
                return $select();
            } catch (Exception $e) {
                throw new DataBaseException(20002);
            }
        }
    }

    /**
     * 更新数据
     * @param array $data
     * @param $models
     * @return BaseModel|int
     * @throws DataBaseException
     */
    protected function  baseUpdate(array $data, $models)
    {
        $this->allowField($this->updatedFields);

        $update = function () use ($data, $models){
            if ($models instanceof Model) {
                return $models->save($data);
            } elseif ($models instanceof Collection) {
                $models->map(function (Model $model) use ($data) {
                    $model->save($data);
                });
                return $models->count();
            }
            return 0;
        };

        if (is_debug()) {
            return $update();
        } else {
            try {
                return $update();
            } catch (Exception $e) {
                throw new DataBaseException(20003);
            }
        }
    }

    /**
     * 插入数据
     * @param array $data
     * @return int|string
     * @throws DataBaseException
     */
    protected function baseInsert(array $data)
    {
        $insert = function () use ($data){
            return $this->getQuery()->insertGetId($data);
        };

        if (is_debug()) {
            return $insert();
        } else {
            try {
                return $insert();
            } catch (Exception $e) {
                throw new DataBaseException(20004);
            }
        }
    }

    /**
     * 真实删除
     * @param array $where
     * @return int
     * @throws DataBaseException
     */
    protected function baseDelete(array $where = []) : int
    {
        $delete = function () use ($where){
            return $this->getQuery()->where($where)->delete();
        };

        if (is_debug()) {
            return $delete();
        } else {
            try {
                return $delete();
            } catch (Exception $e) {
                throw new DataBaseException(20005);
            }
        }
    }

    /**
     * 解析查询条件
     * @param $where
     */
    protected function parseWhere(&$where)
    {
        if (!is_array($where)) {
            $where = [$this->getFieldName($this->getPk()) => $where];
        }
    }

    /**
     * 将模型转化为数组
     * @param $models
     * @return array
     */
    protected function _toArray($models) : array
    {
        if (is_object($models) && method_exists($models, 'toArray')) {
            return $this->_toArray($models->toArray());
        }

        return array_map(function($value) {
            if (is_array($value)) return $this->_toArray($value);
            return is_object($value) ? $this->_toArray($value) : $value;
        }, $models);
    }
}