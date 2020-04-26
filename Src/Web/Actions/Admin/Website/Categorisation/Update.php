<?php

declare(strict_types=1);

namespace App\Web\Actions\Admin\Website\Categorisation;

use App\Admin\Category\Dto\AdminUserDataDto;
use App\Admin\Category\Services\Website\CategoryService;
use App\Common\Abstracts\BaseAction;
use App\Common\Models\WebsiteCategory;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\Repositories\WebsiteRepository;
use App\Web\Middlewares\UserAssignHashMiddleware;
use MongoDB\BSON\ObjectId;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Exception\SlimException;

class Update extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $websiteId = $request->getAttribute('id');
        try {
            $websiteId = new ObjectId($websiteId);
        } catch (\Exception $exception) {
            throw new NotFoundException($request, $response);
        }

        $service = $this->getCatService();
        $websites = $service->getWebsites($websiteId, 2);
        if (!$websites) {
            throw new NotFoundException($request, $response);
        }
        $website = array_shift($websites);
        $nextId = $websites ? array_shift($websites)->id : null;
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            if (
                !isset($params['category'])
                || !is_numeric($params['category'])
                || !in_array((int)$params['category'], WebsiteCategory::CODES)
            ) {
                $response = $response->withStatus(422);

                throw new SlimException($request, $response);
            }
            $userDto = new AdminUserDataDto(
                $response->getHeaderLine(UserAssignHashMiddleware::USER_HASH_HEADER),
                $_SERVER['REMOTE_ADDR']
            );
            $service->addWebsiteCategoryMatch($userDto, $websiteId, (int)$params['category']);
            $service->saveUserHashLastWebsiteId($userDto, $nextId ? new ObjectId($nextId) : null);

            $response = $response->withStatus(303);
            $response = $response->withAddedHeader('Location', "/admin/website/categorisation/$nextId");
        }

        return $this->getView()->render($response, 'admin/website/categorisation/update.twig', [
            'title' => 'Соотнесение сайта категории',
            'website' => $website,
            'nextId' => $nextId,
            'cat_personal' => WebsiteCategory::CODE_PERSONAL,
            'cat_commercial' => WebsiteCategory::CODE_COMMERCIAL,
            'cat_government' => WebsiteCategory::CODE_GOVERNMENT,
            'cat_non_profit' => WebsiteCategory::CODE_NON_PROFIT,
        ]);
    }

    private function getCatService(): CategoryService
    {
        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $redis = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE);
        $websiteRepo = new WebsiteRepository($mongo);
        $indexRepo = new WebsiteIndexHistoryRepository($mongo);

        return new CategoryService($websiteRepo, $indexRepo, $redis);
    }
}
