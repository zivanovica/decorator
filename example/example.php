<?php
/**
 * Author: Aleksandar Zivanovic
 */

use PHPDecorator\Decoratable;
use PHPDecorator\Decorator;

require_once __DIR__ . '/../vendor/autoload.php';

class User
{
    use Decoratable;

    private $firstName;
    private $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    private function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}

class Vehicle
{
    use Decoratable;

    private $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    private function getModel(): string
    {
        return $this->model;
    }
}

$info = function (callable $original, string $title): string {
    return ($this->dynamicPhD ? 'Dr. ' : '') . "{$title} {$original()}";
};

$user = new User('Brown', 'Fox');
$vehicle = new Vehicle('Mustang');

Decorator::addDecoration($user, 'dynamicPhD', true);
Decorator::addDecoration($user, 'decoratedGetFullName', $info, User::class);
Decorator::addDecoration($vehicle, 'decoratedGetModel', $info);

class A {
    function getA(){
        return $this->a;
    }
}

Decorator::addClassDecoration($user, A::class);

echo "{$user->decoratedGetFullName('Quick')}\n"; // Dr. Quick Brown Fox
echo "{$vehicle->decoratedGetModel('Ford')}\n"; // Ford Mustang