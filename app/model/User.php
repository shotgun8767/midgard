<?php

namespace app\model;

class User extends BaseModel
{
    protected $hidden = ['status', 'open_id'];
}