<?php

namespace app\model;

class User extends BaseModel
{
    protected $hidden = ['status'];

    public function getInfo($id) : ?array
    {
        return $this->getArray($id);
    }
}