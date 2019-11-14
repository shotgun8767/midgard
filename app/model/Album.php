<?php

namespace app\model;

class Album extends BaseModel
{
    protected $hidden = ['status', 'create_time', 'picture_id'];

    public function getInfo() : ?array
    {
        /**
         * 复杂关联预载入
         */
        $with = [
            'PictureInfo' => [
                'hidden' => ['status', 'user_id'],
                'with' => [
                    'UserInfo' => [
                        'field' => 'name',
                        'hidden' => 'id'
                    ]
                ]
            ]
        ];

        return $this
            ->multi(2)
            ->baseWith($with)
            ->getArray();
    }

    /**
     * 关联预载入函数
     * @return \think\model\relation\BelongsTo
     */
    public function PictureInfo()
    {
        return $this->belongsTo('picture', 'picture_id', 'id');
    }
}