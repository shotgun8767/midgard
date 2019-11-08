<?php

namespace jwt\extend;

use jwt\JWT;
use jwt\JwtExtendInterface;

/**
 * Class Expire
 * @see JWT
 * @package jwt\extend
 */
class Expire implements JwtExtendInterface
{
    const KEY = 'exp';

    /**
     * JWT expiration
     * @var int|null
     */
    protected $expire;

    /**
     * function will be autoload before JWT getting payload
     * @param JWT $jwt
     */
    public function beforeGetPayload(JWT $jwt)
    {
        $this->expire = $jwt->extendGetPayload()[self::KEY]??null;
        $jwt->hidden([self::KEY]);
    }

    /**
     * get JWT expired timestamp, 0 means permanently valid
     * @param JWT $jwt
     * @return int|null
     */
    public function getExpire(JWT $jwt)
    {
        if (is_null($this->expire)) {
            $jwt->getPayload();
        }
        return $this->expire;
    }

    /**
     * set JWT expired timestamp, 0 means permanently valid
     * @param JWT $jwt
     * @param int $expire
     * @return JWT
     */
    public function setExpire(JWT $jwt, int $expire)
    {
        if ($expire !== 0) $expire += time();
        $jwt->setPayload([self::KEY => $expire], true);

        return $jwt;
    }

    /**
     * check if JWT expires
     * @param JWT $jwt
     * @return bool
     */
    public function isExpire(JWT $jwt) : bool
    {
        $expire = $this->getExpire($jwt);

        return $expire === 0 ? false : ($expire < time());
    }
}