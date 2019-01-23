<?php
/**
 * Author: Aleksandar Zivanovic
 */

namespace App\ClassDecorators;

use App\Entities\CommentEntity;
use App\Entities\PostEntity;

class EntityHydrator
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function hydrate(callable $context, array $data): object
    {
        $entity = null;
        $id = $this->id;

        $context(function () use ($data, &$entity, $id) {
            // $this points to PostEntity instance, and acts same as if it was implemented there
            // to access private properties from EntityHydrator, pass it as "use"
            $this->id = $data['id'] ?? 0;
            $this->title = $data['title'] ?? '--missing title--';
            $this->content = $data['content'] ?? '--no content--';

            var_dump('Entity id ' . $id);

            $entity = $this;
        }, PostEntity::class);

        $context(function () use ($data, &$entity) {
            $this->postId = $data['postId'] ?? null;
            $this->comment = $data['comment'] ?? '--no content--';

            $entity = $this;
        }, CommentEntity::class);

        return $entity;
    }
}