<?php
/**
 * Author: Aleksandar Zivanovic
 */

use App\ClassDecorators\CRUDDecorator;
use App\ClassDecorators\EntityHydrator;
use App\Entities\CommentEntity;
use App\Entities\PostEntity;
use PHPDecorator\Decorator;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var PostEntity $post */
$post = Decorator::decorateWithClasses(new PostEntity(), [
    new CRUDDecorator(), new EntityHydrator('post')
])->hydrate(['id' => 1, 'title' => 'My first post!', 'content' => 'Hello, World!']);

echo "Title: {$post->getTitle()}\n";
echo "Content: {$post->getContent()}\n";

/** @var CommentEntity $comment */
$comment = Decorator::decorateWithClasses(new CommentEntity(), [
    new CRUDDecorator(), new EntityHydrator('comment')
]);

$comment->hydrate([
    'postId' => $post->getId(),
    'comment' => 'and here we have my first comment :)'
]);

echo "Post: {$comment->getPostId()}\n";
echo "Comment: {$comment->getComment()}";

