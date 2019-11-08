<?php

namespace jwt\extend;

use jwt\JWT;
use jwt\JwtExtendInterface;

class Common implements JwtExtendInterface
{
    public function beforeGetPayload(JWT $Jwt)
    {
    }

    /**
     * get payload
     * @param JWT $jwt
     * @param string|null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function payload(JWT $jwt, ?string $key = null, $default = null)
    {
        $payload = $jwt->getPayload();

        return is_null($key) ? $payload : ($payload[$key]??$default);
    }

    /**
     * remove a key or some keys in payload
     * @param JWT $jwt
     * @param $keys
     * @return JWT
     */
    public function remove(JWT $jwt, $keys)
    {
        if (!is_array($keys) && !is_object($keys)) {
            $keys = [$keys];
        }

        $payload = $jwt->extendGetPayload();

        $jwt->clearPayload()->setPayload(array_diff_key($payload, $keys));

        return $jwt;
    }
}