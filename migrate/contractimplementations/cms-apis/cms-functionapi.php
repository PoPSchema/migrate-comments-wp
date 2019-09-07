<?php
namespace PoP\Comments\WP;
use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\ComponentModel\DataloaderAPITrait;

class FunctionAPI extends \PoP\Comments\FunctionAPI_Base
{
    use DataloaderAPITrait;

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
    public function getComments($query, array $options = [])
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
        if (isset($query['post-id'])) {

            $query['post_id'] = $query['post-id'];
            unset($query['post-id']);
        }
        if (\PoP\Comments\Server::mustHaveUserAccountToAddComment()) {
            if (isset($query['user-id'])) {

                $query['user_id'] = $query['user-id'];
                unset($query['user-id']);
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
        return get_comments($query);
    }
    public function getComment($comment_id)
    {
        return get_comment($comment_id);
    }
    public function insertComment($comment_data)
    {
        // Convert the parameters
        if (\PoP\Comments\Server::mustHaveUserAccountToAddComment()) {
            if (isset($comment_data['user-id'])) {

                $comment_data['user_id'] = $comment_data['user-id'];
                unset($comment_data['user-id']);
            }
        }
        if (isset($comment_data['author'])) {

            $comment_data['comment_author'] = $comment_data['author'];
            unset($comment_data['author']);
        }
        if (isset($comment_data['author-email'])) {

            $comment_data['comment_author_email'] = $comment_data['author-email'];
            unset($comment_data['author-email']);
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
        if (isset($comment_data['post-id'])) {

            $comment_data['comment_post_ID'] = $comment_data['post-id'];
            unset($comment_data['post-id']);
        }
        return wp_insert_comment($comment_data);
    }
    public function getCommentsNumber($post_id)
    {
        return get_comments_number($post_id);
    }
}

/**
 * Initialize
 */
new FunctionAPI();
