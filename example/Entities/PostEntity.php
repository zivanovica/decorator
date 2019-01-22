<?php
/**
 * Author: Aleksandar Zivanovic
 */

namespace App\Entities;

use PHPDecorator\Decoratable;

class PostEntity
{
    use Decoratable;

    private $id = 0;
    private $title = 'None';
    private $content = '--empty--';

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}