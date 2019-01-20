<?php
/**
 * Created by IntelliJ IDEA.
 * User: Coa
 * Date: 1/20/2019
 * Time: 8:39 AM
 */

trait Decoratable
{
    private $__methods = [];
    private $__properties = [];

    /**
     *
     * NOTE: In base class use __onCall instead of __call to avoid failures.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (is_callable($this->__methods[$name] ?? null)) {
            return call_user_func_array($this->__methods[$name], $arguments);
        }

        if (method_exists($this, '__onCall')) {
            return call_user_func([$this, '__onCall'], $name, $arguments);
        }

        $className = get_class($this);

        throw new Exception("Call to undefined method {$className}::{$name}()", 0);
    }

    /**
     *
     * NOTE: In base class use __onGet instead of __get to avoid failures.
     *
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (isset($this->__properties[$name])) {
            return $this->__properties[$name];
        }

        if (method_exists($this, '__onGet')) {
            return call_user_func([$this, '__onGet'], $name);
        }
    }

    /**
     * Add custom method to list
     *
     * @param string $methodName
     * @param callable $callback
     */
    private function Decoratable__addMethod(string $methodName, callable $callback): void
    {
        $this->__methods[$methodName] = Closure::fromCallable($callback)->bindTo($this, get_class($this));
    }

    /**
     * Adds custom property to list
     *
     * @param string $name
     * @param $value
     */
    private function Decoratable__addProperty(string $name, $value): void
    {
        $this->__properties[$name] = $value;
    }
}