<?php

class Decorator
{
    const METHOD_ACCESS_IDENTIFIER = 'decorated';

    /**
     * @param object $object
     * @param string $attributeName
     * @param $value
     * @param null|string $class
     * @return void
     */
    public static function addDecoration(object $object, string $attributeName, $value, ?string $class = null): void
    {
        $className = get_class($object);

        if (false === self::isDecoratable($object)) {
            throw new RuntimeException("{$className} cannot be decorated. Trait not used");
        }

        if (null !== $class && false === is_subclass_of($object, $class) && false === $object instanceof $class) {
            throw new RuntimeException("{$className} must implement {$class}");
        }

        is_callable($value) ?
            self::method($object, $attributeName, $value) : self::property($object, $attributeName, $value);
    }

    /**
     * @param object $object
     * @param array $decorations
     * @param null|string $class
     */
    public static function addDecorations(object $object, array $decorations, ?string $class = null): void
    {
        foreach ($decorations as $attributeName => $value) {
            self::addDecoration($object, $attributeName, $value, $class);
        }
    }

    /**
     * Decorate $object with method
     *
     * @param object $object
     * @param string $methodName
     * @param callable $callback
     * @return void
     */
    private static function method(object $object, string $methodName, callable $callback): void
    {
        if (0 !== strpos($methodName, self::METHOD_ACCESS_IDENTIFIER)) {
            Decorator::invokeDecorateMethod($object, $methodName, $callback, 'Decoratable__addMethod');

            return;
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

        Decorator::invokeDecorateMethod($object, $methodName, $callback, 'Decoratable__addMethod');
    }

    /**
     * Decorate $object with property
     *
     * @param object $object
     * @param string $propertyName
     * @param $value
     * @return void
     */
    private static function property(object $object, string $propertyName, $value): void
    {
        Decorator::invokeDecorateMethod($object, $propertyName, $value, 'Decoratable__addProperty');
    }

    /**
     * Invoke proper decorator method on given object
     *
     * @param object $object
     * @param string $propertyName
     * @param $value
     * @param string $method
     * @return void
     */
    private static function invokeDecorateMethod(object $object, string $propertyName, $value, string $method): void
    {
        $reflection = new ReflectionObject($object);

        $decoratorMethod = $reflection->getMethod($method);

        $decoratorMethod->setAccessible(true);
        $decoratorMethod->invoke($object, $propertyName, $value);

        unset($reflection, $decoratorMethod);
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