<?php

require_once __DIR__ . '/src/Decoratable.php';
require_once __DIR__ . '/src/Decorator.php';

class User
{
    use Decoratable;

    private $firstName;
    private $lastName;

    public function __construct(string $firstName, string $lastName) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    private function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}

$user = Decorator::decorate(new User('Brown', 'Fox'), 'dynamicPhD', true);
$user = Decorator::decorate(
    $user,
    'decoratedGetFullName',
    function (callable $original, string $title): string {
        return ($this->dynamicPhD ? 'Dr. ' : '') . "{$title} {$original()}";
    },
    User::class
);

echo $user->decoratedGetFullName('Quick'); // Dr. Quick Brown Fox