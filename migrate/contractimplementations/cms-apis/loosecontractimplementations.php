<?php
namespace PoP\Comments\WP;
use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\LooseContracts\Facades\Contracts\NameResolverFacade;

class CMSLooseContractImplementations
{
	function __construct() {
		
		$hooksapi = HooksAPIFacade::getInstance();

		// Actions
		$hooksapi->addAction('wp_insert_comment', function($comment_id, $comment) use($hooksapi) {
			$hooksapi->doAction('popcms:insertComment', $comment_id, $comment);
		}, 10, 2);
		$hooksapi->addAction('spam_comment', function($comment_id, $comment) use($hooksapi) {
			$hooksapi->doAction('popcms:spamComment', $comment_id, $comment);
		}, 10, 2);
		$hooksapi->addAction('delete_comment', function($comment_id, $comment) use($hooksapi) {
			$hooksapi->doAction('popcms:deleteComment', $comment_id, $comment);
		}, 10, 2);

		$loosecontract_manager->implementHooks([
			'popcms:insertComment',
			'popcms:spamComment',
			'popcms:deleteComment',
		]);

		$nameresolver = NameResolverFacade::getInstance();
		$nameresolver->implementNames([
			'popcms:dbcolumn:orderby:comments:date' => 'comment_date_gmt',
			'popcms:dbcolumn:orderby:posts:comment-count' => 'comment_count',
		]);
	}
}

/**
 * Initialize
 */
new CMSLooseContractImplementations();

