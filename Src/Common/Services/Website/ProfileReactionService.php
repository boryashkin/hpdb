<?php

declare(strict_types=1);

namespace App\Common\Services\Website;

use App\Common\Dto\Website\WebsiteReactionDto;
use App\Common\Models\Website;
use App\Common\Models\WebsiteReaction;
use App\Common\Repositories\ReactionRepository;

class ProfileReactionService
{
    /** @var WebsiteService */
    private $websiteService;
    /** @var ReactionRepository */
    private $reactionRepository;

    public function __construct(ReactionRepository $reactionRepository, WebsiteService $websiteService)
    {
        $this->websiteService = $websiteService;
        $this->reactionRepository = $reactionRepository;
    }

    public function addReaction(Website $website, WebsiteReactionDto $dto): WebsiteReaction
    {
        $reaction = new WebsiteReaction();
        $reaction->website_id = $website->getAttributes()['_id'];
        $reaction->reaction = $dto->reaction;
        $reaction->ip = $dto->ip;
        $reaction->user_agent = $dto->userAgent;

        $this->reactionRepository->save($reaction);

        $this->websiteService->addReaction($website, $dto);

        return $reaction;
    }
}
