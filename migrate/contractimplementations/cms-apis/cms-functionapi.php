<?php

namespace PoPSchema\Comments\WP;

use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\ComponentModel\TypeDataResolvers\APITypeDataResolverTrait;

class FunctionAPI extends \PoPSchema\Comments\FunctionAPI_Base
{
    use APITypeDataResolverTrait;

    protected $cmsToPoPCommentStatusConversion = [
        // 'all' => POP_COMMENTSTATUS_ALL,
        'approve' => POP_COMMENTSTATUS_APPROVED,
        'hold' => POP_COMMENTSTATUS_ONHOLD,
        'spam' => POP_COMMENTSTATUS_SPAM,
        'trash' => POP_COMMENTSTATUS_TRASH,
    ];
    protected $popToCMSCommentStatusConversion;

    public function __construct()
    {
        parent::__construct();

        $this->popToCMSCommentStatusConversion = array_flip($this->cmsToPoPCommentStatusConversion);
    }

    protected function convertCommentStatusFromCMSToPoP($status)
    {
        // Convert from the CMS status to PoP's one
        return $this->cmsToPoPCommentStatusConversion[$status];
    }
    protected function convertCommentStatusFromPoPToCMS($status)
    {
        // Convert from the CMS status to PoP's one
        return $this->popToCMSCommentStatusConversion[$status];
    }
    public function getComments($query, array $options = []): array
    {
        if ($return_type = $options['return-type']) {
            if ($return_type == POP_RETURNTYPE_IDS) {
                $query['fields'] = 'ids';
            }
        }

        // Accept field atts to filter the API fields
        $this->maybeFilterDataloadQueryArgs($query, $options);

        // Convert the parameters
        if (isset($query['status'])) {
            $query['status'] = $this->convertCommentStatusFromPoPToCMS($query['status']);
        }
        if (isset($query['include'])) {
            $query['comment__in'] = $query['include'];
            unset($query['include']);
        }
        if (isset($query['customPostID'])) {
            $query['post_id'] = $query['customPostID'];
            unset($query['customPostID']);
        }
        if (\PoPSchema\Comments\Server::mustHaveUserAccountToAddComment()) {
            if (isset($query['userID'])) {

                $query['user_id'] = $query['userID'];
                unset($query['userID']);
            }
            if (isset($query['authors'])) {

                // Only 1 author is accepted
                $query['user_id'] = $query['authors'][0];
                unset($query['authors']);
            }
        }
        if (isset($query['order'])) {
            // Same param name, so do nothing
        }
        if (isset($query['orderby'])) {
            // Same param name, so do nothing
            // This param can either be a string or an array. Eg:
            // $query['orderby'] => array('date' => 'DESC', 'title' => 'ASC');
        }
        // For the comments, if there's no limit then it brings all results
        if ($query['limit']) {
            $query['number'] = $query['limit'];
            unset($query['limit']);
        }
        if (isset($query['search'])) {
            // Same param name, so do nothing
        }
        // Filtering by date: Instead of operating on the query, it does it through filter 'posts_where'
        if (isset($query['date-from'])) {

            $query['date_query'][] = [
                'after' => $query['date-from'],
                'inclusive' => false,
            ];
            unset($query['date-from']);
        }
        if (isset($query['date-from-inclusive'])) {

            $query['date_query'][] = [
                'after' => $query['date-from-inclusive'],
                'inclusive' => true,
            ];
            unset($query['date-from-inclusive']);
        }
        if (isset($query['date-to'])) {

            $query['date_query'][] = [
                'before' => $query['date-to'],
                'inclusive' => false,
            ];
            unset($query['date-to']);
        }
        if (isset($query['date-to-inclusive'])) {

            $query['date_query'][] = [
                'before' => $query['date-to-inclusive'],
                'inclusive' => true,
            ];
            unset($query['date-to-inclusive']);
        }
        // Only comments, no trackbacks or pingbacks
        $query['type'] = 'comment';

        $query = HooksAPIFacade::getInstance()->applyFilters(
            'CMSAPI:comments:query',
            $query,
            $options
        );
        return (array) \get_comments($query);
    }
    public function getComment($comment_id)
    {
        return \get_comment($comment_id);
    }
    public function insertComment($comment_data)
    {
        // Convert the parameters
        if (\PoPSchema\Comments\Server::mustHaveUserAccountToAddComment()) {
            if (isset($comment_data['userID'])) {

                $comment_data['user_id'] = $comment_data['userID'];
                unset($comment_data['userID']);
            }
        }
        if (isset($comment_data['author'])) {

            $comment_data['comment_author'] = $comment_data['author'];
            unset($comment_data['author']);
        }
        if (isset($comment_data['authorEmail'])) {

            $comment_data['comment_author_email'] = $comment_data['authorEmail'];
            unset($comment_data['authorEmail']);
        }
        if (isset($comment_data['author-URL'])) {

            $comment_data['comment_author_url'] = $comment_data['author-URL'];
            unset($comment_data['author-URL']);
        }
        if (isset($comment_data['author-IP'])) {

            $comment_data['comment_author_IP'] = $comment_data['author-IP'];
            unset($comment_data['author-IP']);
        }
        if (isset($comment_data['agent'])) {

            $comment_data['comment_agent'] = $comment_data['agent'];
            unset($comment_data['agent']);
        }
        if (isset($comment_data['content'])) {

            $comment_data['comment_content'] = $comment_data['content'];
            unset($comment_data['content']);
        }
        if (isset($comment_data['parent'])) {

            $comment_data['comment_parent'] = $comment_data['parent'];
            unset($comment_data['parent']);
        }
        if (isset($comment_data['customPostID'])) {

            $comment_data['comment_post_ID'] = $comment_data['customPostID'];
            unset($comment_data['customPostID']);
        }
        return \wp_insert_comment($comment_data);
    }
    public function getCommentNumber($post_id): int
    {
        return (int) \get_comments_number($post_id);
    }
    public function areCommentsOpen($post_id): bool
    {
        return \comments_open($post_id);
    }
}

/**
 * Initialize
 */
new FunctionAPI();
