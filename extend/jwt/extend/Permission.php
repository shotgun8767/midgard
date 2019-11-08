<?php

namespace Jwt\extend;

use Jwt\Jwt;
use jwt\JwtExtendInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class Permission
 * @see Jwt
 * @package Jwt\extend
 */
class Permission implements JwtExtendInterface
{
    const KEY = 'per';

    /*
     * The length of permission string, maximum 32
     */
    const PERMISSION_LENGTH = 8;

    /**
     * encrypted secret string
     */
    const SECRET = 'permission';

    /**
     * permission level (encrypted)
     * @var string|null
     */
    protected $permission;

    /**
     *
     * @var string|null
     */
    protected $enumClass;

    /**
     * @var array
     */
    protected $enum = [];

    /**
     * function will be autoload before Jwt getting payload
     * @param Jwt $Jwt
     */
    public function beforeGetPayload(Jwt $Jwt)
    {
        $this->permission = $Jwt->extendGetPayload()[self::KEY]??null;
        $Jwt->hidden([self::KEY]);
    }

    /**
     * Permission constructor.
     * @param string $enumClass
     * @throws ReflectionException
     */
    public function __construct(string $enumClass)
    {
        if (class_exists($enumClass)) {
            $this->enumClass = $enumClass;
            $this->enum = (new ReflectionClass($enumClass))->getConstants();
        }
    }

    /**
     * @param Jwt $Jwt
     * @param string $name
     * @return Jwt
     */
    public function setPermission(Jwt $Jwt, string $name)
    {
        $this->encryptEnum();
        if ($ps = ($this->enum[$name]??false)) {
            $this->permission = $ps;
            $Jwt->setPayload([self::KEY => $ps], true);
        }

        return $Jwt;
    }

    /**
     * @param Jwt $Jwt
     * @param string $name
     * @return bool
     */
    public function permissionReach(Jwt $Jwt, string $name) : bool
    {
        $Jwt->getPayload();

        if ($this->permission) {
            $this->encryptEnum();
            $s = false;

            foreach ($this->enum as $key => $permission) {
                if ($key === $name) {
                    $s = true;
                }
                if ($s && $permission === $this->permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Jwt $Jwt
     * @param string $name
     * @return bool
     */
    public function permissionOver(Jwt $Jwt, string $name) : bool
    {
        $Jwt->getPayload();

        if ($this->permission) {
            $s = false;
            foreach ($this->enum as $key => $permission) {
                if ($s && $permission === $this->permission) {
                    return true;
                }
                if ($key === $name) {
                    $s = true;
                }
            }
        }

        return false;
    }

    /**
     * To encrypt Enum
     */
    protected function encryptEnum()
    {
        static $switch = true;

        if ($this->enum && $switch) {
            uasort($this->enum, function ($a, $b) {
                return $a <=> $b;
            });

            $s = $this->enum;
            $this->enum = array_map(function ($permission) {
                return is_int($permission) ? $this->encrypt($permission) : $permission;
            }, $s);
            $switch = false;
        }
    }

    /**
     * To encrypt permission, encrypted permission string return
     * @param int $permission
     * @return string
     */
    private function encrypt(int $permission) : string
    {
        return substr(md5($this->enumClass . $permission . self::SECRET), 0, self::PERMISSION_LENGTH);
    }
}