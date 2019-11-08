<?php

namespace app\model\traits;

use ReflectionClass;
use app\model\status\Model;
use think\facade\App;

/**
 * Trait Status
 * @package app\model\traits
 */
trait Status
{
    /**
     * 是否开启status模式（仅当表单中存在status字段时允许开启）
     * @var bool
     */
    public $statusMode = true;

    /**
     * status字段在数据库中的名称
     * @var string
     */
    public $statusField = 'status';

    /**
     * 需要筛选的status
     * @var array
     */
    public $_status = [];

    /**
     * 全部status
     * @var string
     */
    protected $Status = [];

    /**
     * 载入status模板文件
     * @param null|string $statusClass
     * @throws \ReflectionException
     */
    public function loadStatus(?string $statusClass = null) : void
    {
        if ($statusClass == null) {
            // 自动匹配
            $files = scandir(App::getAppPath() . 'model/status');
            $className = basename(str_replace('\\', '/', get_called_class()));
            foreach ($files as $file) {
                if ($file == "$className.php") {
                    $class = 'app\model\status\\' . $className;
                }
            }
            if (!isset($class)) $class = Model::class;
        } else {
            $class = $statusClass;
        }

        $this->Status = (new ReflectionClass($class))->getConstants();
    }

    /**
     * 初始化_status
     */
    public function initStatus() : void
    {
        $this->_status = [$this->getStatus('NORMAL')];
    }

    /**
     * 获取status的值（索引）
     * @param string|int $name
     * @return null|int
     */
    public function getStatus($name) : ?int
    {
        return is_numeric($name) ? $name : $this->Status[$name]??null;
    }

    /**
     * 设置status
     * @param array|string $status
     * @param string $logic
     * @return $this
     */
    public function status($status, string $logic = "with")
    {
        if (!is_array($status)) $status = [$status];

        $_status = [];

        // 将status全转换为数值
        foreach ($status as $item) {
            if (is_numeric($item)) {
                $_status[] = $item;
            } else {
                if ($t = $this->getStatus($item)) {
                    $_status[] = $t;
                }
            }
        }

        switch (strtolower($logic)) {
            case 'with' :
                $this->_status = $_status;
                break;
            case 'all' :
                $this->_status = $this->Status;
                break;
            case 'remove' :
                $this->_status = array_diff($this->_status, $_status);
                break;
            case 'append' :
                $this->_status = array_merge($this->_status, $_status);
        }

        return $this;
    }

    /**
     * 导入所有的status
     * @return $this
     */
    public function statusAll()
    {
        return $this->status([], "all");
    }

    /**
     * 追加status
     * @param $status
     * @return $this
     */
    public function statusAppend($status)
    {
        return $this->status($status, "append");
    }

    /**
     * 除去指定status
     * @param $status
     * @return $this
     */
    public function statusRemove($status)
    {
        return $this->status($status, "remove");
    }

    /**
     * 除去指定status
     * @param $status
     * @return $this
     */
    public function statusExcept($status)
    {
        return $this->statusAll()->statusRemove($status);
    }

    /**
     * 更新status
     * @param array|int $where
     * @param string|null $status
     * @return int
     */
    public function updateStatus($where = [], ?string $status = null) : int
    {
        if (!$this->statusMode) return 0;

        $this->parseWhere($where);
        if (is_null($status)) {
            $status = $this->_status[0]??0;
        }
        $data[$this->statusField] = $this->getStatus($status);

        return $this->whereBase($where)->queryUpdate($data);
    }
}