<?php

namespace app\model;

class Picture extends BaseModel
{
    protected $hidden = ['status'];

    public function getInfo(int $id) : ?array
    {
        return $this
            ->baseWith(['UserInfo' => 'id'])
            ->get($id)
            ->hidden(['user_id'])
            ->toArray();
    }

    protected function UserInfo()
    {
        return $this->belongsTo('user','user_id', 'id');
    }
}