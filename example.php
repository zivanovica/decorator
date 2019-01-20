<?php

require_once __DIR__ . '/Decoratable.php';
require_once  __DIR__ . '/Decorator.php';

require_once __DIR__ . '/User.php';

$a = Decorator::decorate(new User(), 'speak', function () {
    return $this->name;
});

var_dump($a->speak());