<?php

namespace app\model\traits;

/**
 * Trait SoftDelete
 * @package app\model\traits
 */
trait SoftDelete
{
    /**
     * 软删除status状态值
     * @var string
     */
    protected $deletedStatus = "DELETED";

    /**
     * 记录删除时间
     * @var bool
     */
    protected $recordDeleteTime = false;

    /**
     * 删除时间字段名
     * @var string
     */
    protected $deleteTime = 'deleteTime';

    /**
     * 软删除
     * @param $where
     * @return int
     */
    public function softDelete($where) : int
    {
        if (!$this->statusMode) return 0;

        if (!$status = $this->getStatus($this->deletedStatus)) $status = -1;

        if ($this->recordDeleteTime) {
            $data = [
                $this->statusField => $status,
                $this->deleteTime => time()
            ];
            return $this
                ->whereBase($where)
                ->getQuery()
                ->update($data);
        } else {
            return $this->updateStatus($where, $status);
        }
    }
}