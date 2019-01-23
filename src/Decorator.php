<?php

namespace PHPDecorator;

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
            throw new \RuntimeException("{$className} cannot be decorated. Trait not used");
        }

        if (null !== $class && false === is_subclass_of($object, $class) && false === $object instanceof $class) {
            throw new \RuntimeException("{$className} must implement {$class}");
        }

        is_callable($value) ?
            self::method($object, $attributeName, $value) : self::property($object, $attributeName, $value);

        return $object;
    }

    /**
     * Decorate provided target with methods from provided decorator class
     *
     * @param object $target
     * @param object $decorator
     * @param null|string $targetClass
     * @return object
     */
    public static function decorateWithClass(object $target, object $decorator, ?string $targetClass = null): object
    {
        $className = get_class($target);

        if (false === self::isDecoratable($target)) {
            throw new \RuntimeException("{$className} cannot be decorated. Trait not used");
        }

        $reflection = new \ReflectionObject($decorator);

        foreach ($reflection->getMethods() as $method) {
            $closure = $method->getClosure($decorator);
            $context = self::getCallableContext($target);

            self::decorate($target, $method->getName(), function (...$arguments) use ($closure, $context) {
                array_unshift($arguments, $context);

                return call_user_func_array($closure, $arguments);
            }, $targetClass);
        }

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            self::decorate(
                $target, $reflectionProperty->getName(), $reflectionProperty->getValue($decorator), $targetClass
            );
        }

        return $target;
    }

    /**
     * Same as "addClassDecorator", difference is that this one accepts multiple (array) decorator classes
     *
     * @param object $target
     * @param object[] $decorators
     * @param null|string $targetClass
     * @return object
     * @throws \ReflectionException
     */
    public static function decorateWithClasses(object $target, array $decorators, ?string $targetClass = null): object
    {
        foreach ($decorators as $decorator) {
            self::decorateWithClass($target, $decorator, $targetClass);
        }

        return $target;
    }

    /**
     * @param object $object
     * @param array $decorations
     * @param null|string $class
     * @return object
     */
    public static function decorateAll(object $object, array $decorations, ?string $class = null): object
    {
        foreach ($decorations as $attributeName => $value) {
            self::decorate($object, $attributeName, $value, $class);
        }

        return $object;
    }

    /**
     * Retrieve callable with attached context and scope so that its argument can access private properties
     *
     * @param object $target
     * @return callable
     */
    private static function getCallableContext(object $target): callable
    {
        return function (callable $callback, ?string $targetClass = null) use ($target) {
            if (null !== $targetClass && false === $target instanceof $targetClass) {
                return;
            }

            \Closure::fromCallable($callback)->bindTo($target, get_class($target))->call($target);
        };
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

        $reflection = new \ReflectionObject($object);

        $class = get_class($object);
        $decorateMethodName = substr($methodName, strlen(self::METHOD_ACCESS_IDENTIFIER));

        if (false === $reflection->hasMethod($decorateMethodName)) {
            throw new \RuntimeException("Method {$decorateMethodName} not found in {$class}");
        }

        $reflectionMethod = $reflection->getMethod($decorateMethodName);

        $originalMethod = function () use ($reflectionMethod, $object, $class) {
            return $reflectionMethod->getClosure($object)->bindTo($object, $class)->call($object);
        };

        $callback = function (...$arguments) use ($callback, $originalMethod, $object, $class) {
            return call_user_func_array(
                \Closure::fromCallable($callback)->bindTo($object, $class), array_merge([$originalMethod], $arguments)
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
        $reflection = new \ReflectionObject($object);

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
        $reflection = new \ReflectionObject($object);

        return in_array(Decoratable::class, $reflection->getTraitNames());
    }
}