<?php
/**
 * Author: Aleksandar Zivanovic
 */

namespace App\ClassDecorators;

use App\Entities\CommentEntity;
use App\Entities\PostEntity;

class CRUDDecorator
{
    public static function create(callable $context, array $data): object
    {
        $context(function ($object) use ($data) {
            var_dump($object->title, $data);
        }, PostEntity::class);

        $context(function () use ($data) {

        }, CommentEntity::class);
    }
}