<?php
/**
 * Author: Aleksandar Zivanovic
 */

namespace App\ClassDecorators;

use App\Entities\CommentEntity;
use App\Entities\PostEntity;

class EntityHydrator
{
    public static function hydrate(callable $context, array $data): object
    {
        $entity = null;

        $context(function ($postEntity) use ($data, &$entity) {
            $postEntity->id = $data['id'] ?? 0;
            $postEntity->title = $data['title'] ?? '--missing title--';
            $postEntity->content = $data['content'] ?? '--no content--';

            $entity = $postEntity;
        }, PostEntity::class);

        $context(function ($commentEntity) use ($data, &$entity) {
            $commentEntity->postId = $data['postId'] ?? null;
            $commentEntity->comment = $data['comment'] ?? '--no content--';

            $entity = $commentEntity;
        }, CommentEntity::class);

        return $entity;
    }
}