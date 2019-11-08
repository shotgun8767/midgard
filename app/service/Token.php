<?php

namespace app\service;

use jwt\JWT;
use jwt\extend\{Common, Expire, Permission};

/**
 * Class Token
 *
 * @see Common
 * @method mixed payload(?string $key = null, $default = null)
 * @method Token remove($keys)
 *
 * @see Permission
 * @method Token setPermission(string $name)
 * @method bool permissionReach(string $name)
 * @method bool permissionOver(string $name)
 *
 * @see Expire
 * @method Token setExpire(int $expire)
 * @method int getExpire()
 * @method bool isExpire()
 *
 * @package app\service
 */
class Token extends JWT
{
    /**
     * Token constructor.
     * @param string $Permission
     * @param string|null $token
     * @throws \ReflectionException
     * @throws \jwt\exception\TokenException
     */
    public function __construct(string $Permission = '', ?string $token = null)
    {
        parent::__construct($token);

        // load Expire and Permission extends
        $this
            ->loadExtend(new Common)
            ->loadExtend(new Expire)
            ->loadExtend(new Permission($Permission));
    }
}