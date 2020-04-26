<?php

declare(strict_types=1);

namespace App\Web\Actions\Admin\Website\Categorisation;

use App\Admin\Category\Dto\AdminUserDataDto;
use App\Admin\Category\Services\Website\CategoryService;
use App\Common\Abstracts\BaseAction;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\WebsiteRepository;
use App\Web\Middlewares\UserAssignHashMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $redis = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE);
        $repo = new WebsiteRepository($mongo);
        $indexRepo = new WebsiteIndexHistoryRepository($mongo);
        $service = new CategoryService($repo, $indexRepo, $redis);

        $userData = new AdminUserDataDto(
            $response->getHeaderLine(UserAssignHashMiddleware::USER_HASH_HEADER),
            $_SERVER['REMOTE_ADDR']
        );
        $fromId = $service->getLastWebsiteId($userData);
        $website = current($service->getWebsites($fromId, 1));

        return $this->getView()->render($response, 'admin/website/categorisation/index.twig', [
            'title' => 'Соотнесение сайта категории',
            'website' => $website,
        ]);
    }
}
