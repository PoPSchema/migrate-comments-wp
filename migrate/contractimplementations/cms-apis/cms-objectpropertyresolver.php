<?php
namespace PoP\Comments\WP;
use PoP\Hooks\Facades\HooksAPIFacade;

class ObjectPropertyResolver extends \PoP\Comments\ObjectPropertyResolver_Base
{
    public function getCommentContent($comment)
    {
        return HooksAPIFacade::getInstance()->applyFilters(
            'comment_text',
            $this->getCommentPlainContent($comment)
        );
    }
    public function getCommentPlainContent($comment)
    {
        return $comment->comment_content;
    }
    public function getCommentUserId($comment)
    {
        return (int)$comment->user_id;
    }
    public function getCommentPostId($comment)
    {
        return (int)$comment->comment_post_ID;
    }
    public function isCommentApproved($comment)
    {
        return $comment->comment_approved == "1";
    }
    public function getCommentType($comment)
    {
        return $comment->comment_type;
    }
    public function getCommentParent($comment)
    {
        // If it has no parent, it is assigned 0. In that case, return null
        if ($parent = $comment->comment_parent) {
            return (int)$parent;
        }
        return null;
    }
    public function getCommentDateGmt($comment)
    {
        return $comment->comment_date_gmt;
    }
    public function getCommentId($comment)
    {
        return (int)$comment->comment_ID;
    }
    public function getCommentAuthor($comment)
    {
        return (int)$comment->comment_author;
    }
    public function getCommentAuthorEmail($comment)
    {
        return $comment->comment_author_email;
    }
}

/**
 * Initialize
 */
new ObjectPropertyResolver();
