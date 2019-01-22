<?php
/**
 * Author: Aleksandar Zivanovic
 */

namespace App\Entities;

use PHPDecorator\Decoratable;

class CommentEntity
{
    use Decoratable;

    private $postId;

    private $comment = '--empty comment--';

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }
}