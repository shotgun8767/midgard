<?php

namespace tracer;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use InvalidArgumentException;
use BadMethodCallException;
use Exception;
use tracer\exception\ClassNotFoundException;
use tracer\exception\FunctionNotFoundException;
use tracer\exception\InstantiationException;
use tracer\exception\MethodNotFoundException;

/**
 * Class Reflect
 * @package tracer
 * @author shotgun8767
 * @version 1.2.5
 *
 * @example <function>
 * function add(num1, num2) {return (num1 + num2);}
 * $reflect = new Reflect('add'); $n = $reflect(1, 3);   // $n = 4
 *
 * @example <closure>
 * Closure $closure = function (param1, param2) {};
 * $reflect = new Reflect($closure); $reflect(param1, param2);
 *
 * @example <class>
 * [ Class Student exists ]
 * $reflect = new Reflect('Student', true);
 * $instance = $reflect->instance()->getObject()
 *
 * @example <class and method>
 * [ Class Student includes field 'name' and methods 'setName' and 'getName' ]
 * $reflect = new Reflect('Student', 'setName'); $reflect("shotgun8767");
 * $reflect->setMethod('getName'); echo $reflect();
 * @since 1.2.0
 * $name = $reflect->getName();
 *
 * @example <class called via magic function __invoke>
 * [ Class Student includes method '__invoke($param)' ]
 * $reflect = new Reflect('Student', true); $reflect($param);
 *
 * @example <object>
 * [ Class Student includes method 'sayHello' ]
 * $Student = new Student(); $reflect = new Reflect($student);
 * $reflect->sayHello();
 *
 * @example <object method>
 * @since 1.2.3
 * [ Class Student includes method 'setMajor(string $major)' ]
 * $Student = new Student(); $reflect($Student, 'setMajor');
 * $reflect->invokeArgs(['major' => 'Engineering']);
 *
 * @since 1.2.4 <support variadic function>
 * function sum(... $nums) {};
 * $reflect = Reflect::new('sum'); $reflect(1, 2, 3);
 */
class Reflect
{
    /**
     * @var ReflectionClass|null
     */
    protected $Class;

    /**
     * @var ReflectionFunction|null
     */
    protected $Function;

    /**
     * @var ReflectionMethod|null
     */
    protected $Method;

    /**
     * @var object|null
     */
    protected $object;

    /**
     * Reflect constructor.
     * @param string|object $var
     * @param mixed|null $method
     * @throws ReflectionException
     */
    public function __construct($var, $method = null)
    {
        if (is_string($var)) {
                    if (!$method) {
                        if (function_exists($var)) {
                            $this->Function = new ReflectionFunction($var);
                        } else {
                            throw new FunctionNotFoundException($var);
                        }
            } else {
                if (class_exists($var)) {
                    $this->Class = new ReflectionClass($var);
                    if ($method !== true) {
                        $this->setMethod($method);
                    }
                } else {
                    throw new ClassNotFoundException($var);
                }
            }
        }

        if (is_object($var)) {
            if ($var instanceof Closure) {
                $this->Function = new ReflectionFunction($var);
            } else {
                $this->object = $var;
                $this->Class = new ReflectionClass($var);
                if ($method && $method !== true) {
                    $this->setMethod($method);
                }
            }
        }

        return null;
    }

    /**
     * @param $var
     * @param null $method
     * @return Reflect
     * @throws ReflectionException
     */
    public static function new($var, $method = null) : self
    {
        return new static($var, $method);
    }

    /**
     * instance class
     * @param array $args
     * @return Reflect
     * @throws ReflectionException
     */
    public function instance(array $args = []) : self
    {
        $constructor = $this->Class->getConstructor();

        $args = $constructor ? $this->bindArgs($constructor, $args): [];

        try {
            $this->object = $this->Class->newInstanceArgs($args);
        } catch (Exception $e) {
            throw new InstantiationException($this->Class->getName());
        }

        return $this;
    }

    /**
     * @return object|null
     * @throws ReflectionException
     */
    public function getObject() : ?object
    {
        if ($this->object) {
            return $this->object;
        } else {
            if ($this->Class) {
                return $this->instance();
            }
        }

        return null;
    }

    /**
     * whether method exists
     * @param string $name
     * @return bool
     */
    public function hasMethod(string $name) : bool
    {
        return $this->Class && $this->Class->hasMethod($name);
    }

    /**
     * @param string $name
     * @return Reflect
     * @throws ReflectionException
     */
    public function setMethod(string $name) : self
    {
        if ($this->hasMethod($name)) {
            $this->Method = $this->Class->getMethod($name);
        } else {
            throw new MethodNotFoundException($name);
        }

        return $this;
    }

    /**
     * @return Reflect
     */
    public function unsetMethod() : self
    {
        $this->Method = null;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClass() : bool
    {
        return $this->Class ? true : false;
    }

    /**
     * @return bool
     */
    public function isFunction() : bool
    {
        return $this->Function ? true : false;
    }

    /**
     * @return bool
     */
    public function isClosure() : bool
    {
        return $this->Function && $this->Function->isClosure();
    }

    /**
     * @return ReflectionClass|ReflectionFunction
     */
    public function getReflection()
    {
        return $this->isClass() ? $this->Class : $this->Function;
    }

    /**
     * @return ReflectionMethod|null
     */
    public function getMethodReflection()
    {
        return $this->Method;
    }

    /**
     * call function or method
     * @param mixed ...$args
     * @return mixed|null
     * @throws ReflectionException
     */
    public function invoke(... $args)
    {
        return $this->invokeArgs($args);
    }

    /**
     * call function or method
     * @param array $args
     * @return mixed|null
     * @throws ReflectionException
     */
    public function invokeArgs(array $args = [])
    {
        # function
        if ($this->Function) {
            return $this->Function->invokeArgs($this->bindArgs($this->Function, $args));
        }

        if ($this->Method) {
            # method has been set
            $args = $this->bindArgs($this->Method, $args);
            if ($this->Method->isStatic()) {
                # static method
                return $this->Method->invokeArgs(null, $args);
            } else {
                # non-static method
                if ($this->object) {
                    return $this->Method->invokeArgs($this->object, $args);
                } else {
                    try {
                        # auto instance
                        $this->instance();
                    } catch (ReflectionException $e) {
                        $name = $this->Method->getName();
                        throw new BadMethodCallException("non-static method [$name] called incorrectly.");
                    }
                    return $this->Method->invokeArgs($this->object, $args);
                }
            }
        } else {
            # otherwise, if class includes __invoke
            if ($this->Class->hasMethod('__invoke')) {
                $this->getObject();
                $Method = $this->Class->getMethod('__invoke');
                $args = $this->bindArgs($Method, $args);
                return $Method->invokeArgs($this->object, $args);
            }
        }

        return null;
    }

    /**
     * @param mixed ...$args
     * @throws ReflectionException
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return $this->invokeArgs($args);
    }

    /**
     * call method from object directly
     * you cannot call methods below via __call :
     * [new, instance, getObject, hasMethod, setMethod, unsetMethod,
     * isClass, isFunction, isClosure, getReflection, getMethodReflection, invoke, invokeArgs]
     * you can also use 'setMethod' to appoint the method you wanna call
     * @since 1.2.0
     * @param $method
     * @param $args
     * @throws ReflectionException
     * @return mixed
     */
    public function __call(string $method, $args)
    {
        if ($this->isClass()) {
            $this->getObject();

            if ($this->hasMethod($method)) {
                return $this->Class->getMethod($method)->invokeArgs($this->object, $args);
            } else {
                throw new MethodNotFoundException($method);
            }
        }

        return null;
    }

    /**
     * get field from object directly
     * you cannot get fields below via __get :
     * [Class, Function, Method, object]
     * @since 1.2.0
     * @param string $name
     * @return mixed|null
     * @throws ReflectionException
     */
    public function __get(string $name)
    {
        if ($this->isClass()) {
            $this->getObject();

            return $this->object->$name;
        }

        return null;
    }

    /**
     * bind params
     * @param ReflectionMethod|ReflectionFunction $reflect
     * @param array $args arguments
     * @return array
     * @throws ReflectionException
     */
    protected function bindArgs($reflect, array $args = []) : array
    {
        if ($reflect->getNumberOfParameters() == 0) return [];

        reset($args);
        $assoc  = key($args) !== 0;
        $_args  = [];

        foreach ($reflect->getParameters() as $param) {
            $name = $param->getName();

            if (!$assoc && !empty($args)) {
                $_args[] = array_shift($args);
            } elseif ($assoc && isset($args[$name])) {
                $_args[] = $args[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $_args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException("method param miss: [$name].");
            }

            if ($param->isVariadic() && !$assoc) {
                $_args = array_merge($_args, $args);
            }
        }

        return $_args;
    }
}