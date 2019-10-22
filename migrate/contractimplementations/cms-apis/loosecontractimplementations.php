<?php
namespace PoP\Comments\WP;
use PoP\Hooks\Facades\HooksAPIFacade;
use PoP\LooseContracts\Facades\Contracts\NameResolverFacade;
use PoP\LooseContracts\Facades\Contracts\LooseContractManagerFacade;
use PoP\LooseContracts\Contracts\AbstractLooseContractResolutionSet;

class CMSLooseContractImplementations extends AbstractLooseContractResolutionSet
{
	protected function resolveContracts()
    {
		// Actions
		$this->hooksAPI->addAction('wp_insert_comment', function($comment_id, $comment) {
			$this->hooksAPI->doAction('popcms:insertComment', $comment_id, $comment);
		}, 10, 2);
		$this->hooksAPI->addAction('spam_comment', function($comment_id, $comment) {
			$this->hooksAPI->doAction('popcms:spamComment', $comment_id, $comment);
		}, 10, 2);
		$this->hooksAPI->addAction('delete_comment', function($comment_id, $comment) {
			$this->hooksAPI->doAction('popcms:deleteComment', $comment_id, $comment);
		}, 10, 2);

		$this->looseContractManager->implementHooks([
			'popcms:insertComment',
			'popcms:spamComment',
			'popcms:deleteComment',
		]);

		$this->nameResolver->implementNames([
			'popcms:dbcolumn:orderby:comments:date' => 'comment_date_gmt',
			'popcms:dbcolumn:orderby:posts:comment-count' => 'comment_count',
		]);
	}
}

/**
 * Initialize
 */
new CMSLooseContractImplementations(
	LooseContractManagerFacade::getInstance(),
	NameResolverFacade::getInstance(),
	HooksAPIFacade::getInstance()
);

