<?php

class Decorator
{
    /**
     * @param object $object
     * @param string $attributeName
     * @param callable|mixed $value
     * @param null|string $class
     * @return object
     * @throws ReflectionException
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
        $reflection = new ReflectionObject($object);

        $decoratorMethod = $reflection->getMethod('Decoratable__addMethod');

        $decoratorMethod->setAccessible(true);
        $decoratorMethod->invoke($object, $methodName, $callback);

        unset($reflection, $decoratorMethod);

        return $object;
    }

    private static function decorateWithProperty(object $object, string $propertyName, $value): object
    {
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