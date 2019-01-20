<?php

class Decorator
{
    const METHOD_ACCESS_IDENTIFIER = 'decorated';

    /**
     * @param object $object
     * @param string $attributeName
     * @param $value
     * @param null|string $class
     * @return object
     */
    public static function decorate(object $object, string $attributeName, $value, ?string $class = null): object
    {
        $className = get_class($object);

        if (false === self::isDecoratable($object)) {
            throw new RuntimeException("{$className} cannot be decorated. Trait not used");
        }

        if (null !== $class && false === is_subclass_of($object, $class) && false === $object instanceof $class) {
            throw new RuntimeException("{$className} must implement {$class}");
        }

        return is_callable($value) ?
            self::decorateWithMethod($object, $attributeName, $value) :
            self::decorateWithProperty($object, $attributeName, $value);
    }

    /**
     * Decorate $object with method
     *
     * @param object $object
     * @param string $methodName
     * @param callable $callback
     * @return object
     */
    private static function decorateWithMethod(object $object, string $methodName, callable $callback): object
    {
        if (0 !== strpos($methodName, self::METHOD_ACCESS_IDENTIFIER)) {
            return Decorator::invokeDecorateMethod($object, $methodName, $callback, 'Decoratable__addMethod');
        }

        $reflection = new ReflectionObject($object);

        $class = get_class($object);
        $decorateMethodName = substr($methodName, strlen(self::METHOD_ACCESS_IDENTIFIER));

        if (false === $reflection->hasMethod($decorateMethodName)) {
            throw new RuntimeException("Method {$decorateMethodName} not found in {$class}");
        }

        $reflectionMethod = $reflection->getMethod($decorateMethodName);

        $originalMethod = function () use ($reflectionMethod, $object, $class) {
            return $reflectionMethod->getClosure($object)->bindTo($object, $class)->call($object);
        };

        $callback = function (...$arguments) use ($callback, $originalMethod, $object, $class) {
            return call_user_func_array(
                Closure::fromCallable($callback)->bindTo($object, $class), array_merge([$originalMethod], $arguments)
            );
        };

        return Decorator::invokeDecorateMethod($object, $methodName, $callback, 'Decoratable__addMethod');
    }

    /**
     * Decorate $object with property
     *
     * @param object $object
     * @param string $propertyName
     * @param $value
     * @return object
     */
    private static function decorateWithProperty(object $object, string $propertyName, $value): object
    {
        return Decorator::invokeDecorateMethod($object, $propertyName, $value, 'Decoratable__addProperty');
    }

    /**
     * Invoke proper decorator method on given object
     *
     * @param object $object
     * @param string $propertyName
     * @param $value
     * @param string $method
     * @return object
     */
    private static function invokeDecorateMethod(object $object, string $propertyName, $value, string $method): object
    {
        $reflection = new ReflectionObject($object);

        $decoratorMethod = $reflection->getMethod($method);

        $decoratorMethod->setAccessible(true);
        $decoratorMethod->invoke($object, $propertyName, $value);

        unset($reflection, $decoratorMethod);

        return $object;
    }

    /**
     * @param object $object
     * @return bool
     */
    private static function isDecoratable(object $object): bool
    {
        $reflection = new ReflectionObject($object);

        return in_array(Decoratable::class, $reflection->getTraitNames());
    }
}