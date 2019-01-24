### PHP Decorator
Dynamically add methods on objects with provided proper context and scope when executing.

### Usage

Class that can be decorated **MUST** use ``Decoratable`` trait.

``Decoratable`` trait relies on magic methods ``__call`` and ``__get``, therefor using them directly is not possible without breaking ``Decorator`` functionality.

To set custom ``__call`` and ``__get`` use ``__onCall`` and ``__onGet`` instead.

##### Basic Decoration

Add method to some object.

```php
    <?php

        class User
        {
            use Decoratable;
            
            private $firstName;
            private $lastName;
            
            public function __construct(string $firstName, string $lastName) {
                $this->firstName = $firstName;
                $this->lastName = $lastName;
            }
        }
        
        $user = Decorator::decorate(new User('Brown', 'Fox'), 'getFullName', function (): string {
            return "{$this->firstName} {$this->lastName}";
        });
        
        echo $user->getFullName(); // Brown Fox
```

##### Strict object type decoration

Strict object type decoration will ensure that object that is being changed is instance or subclass of provided name.

```php
    <?php

        class User
        {
            use Decoratable;
            
            private $firstName;
            private $lastName;
            
            public function __construct(string $firstName, string $lastName) {
                $this->firstName = $firstName;
                $this->lastName = $lastName;
            }
            
            private function getFirstName(): string
            {
                return $this->firstName;
            }
            
            public function getLastName(): string
            {
                return $this->lastName;
            }
        }
        
        $user = Decorator::decorate(new User('Brown', 'Fox'), 'getFullName', function (): string {
            return "{$this->getFirstName()} {$this->getLastName()}";
        }, User::class);
        
        echo $user->getFullName(); // Brown Fox
```

##### Defined method decoration

While attaching decorator method, script will check if original method exists.
Original method **MUST** be called same as decorated method without prefix.

Original method will be passed as first argument of decorated method, it will contain attached context and scope.
Execution is simple, just by calling e.g ``$original()`` where ``$original`` is first parameter of decorated method.

``Default prefix: decorated``

```php
    <?php

        class User
        {
            use Decoratable;
        
            private $firstName;
            private $lastName;
        
            public function __construct(string $firstName, string $lastName) {
                $this->firstName = $firstName;
                $this->lastName = $lastName;
            }
        
            public function getFullName(): string
            {
                return "{$this->firstName} {$this->lastName}";
            }
        }
        
        $user = Decorator::decorate(
            new User('Brown', 'Fox'), 'decoratedGetFullName',
            function (callable $original, string $title): string {
                return "{$title}. {$original()}";
            }
        );
        
        echo $user->decoratedGetFullName('Mr'); // Mr. Brown Fox
```

##### Decorate with class

Object can also be decorated with some class, all static methods found in provided decorator class will be applied to target object.

Using proper way of accessing data, script provides access to private properties outside class definition.

```php
    <?php
    
        class EntityHydrator
        {
            public function hydrate(callable $context, array $data): void
            {
                // $context callable provide us with instance of target as parameter
                // in this case called $postEntity, and script has access to its private properties
                // $this is instance of target object that is being decorated
                $context(function () use ($data) {
                    // id, firstName, lastName are private properties
                    $this->id = $data['id'] ?? null;
                    $this->firstName = $data['firstName'] ?? null;
                    $this->lastName = $data['lastName'] ?? null;
                }, User::class);
            }
        }

        $user = Decorator::decorateWithClass(new User(), EntityHydrator::class);
        $user->hydrate(['firstName' => 'Criss', 'lastName' => 'Popo']);
        
        echo $user->getFirstName(); // Criss
        
```

##### Decorate property

Add dynamic property to an object

```php
    <?php

        class User
        {
            use Decoratable;
        
            private $firstName;
            private $lastName;
        
            public function __construct(string $firstName, string $lastName) {
                $this->firstName = $firstName;
                $this->lastName = $lastName;
            }
        
            public function getFullName(): string
            {
                return "{$this->firstName} {$this->lastName}";
            }
        }
    
        $user = Decorator::decorate(new User('Brown', 'Fox'), 'title', 'Dr');
        
        echo $user->title; // Dr

```